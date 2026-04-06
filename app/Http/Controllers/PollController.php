<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Poll;
use App\Models\PollDate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(Request $request): View
    {
        $recentPolls = collect();

        if (Schema::hasTable('polls') && $request->user()) {
            /** @var User $user */
            $user = $request->user();

            $recentPolls = $user->polls()
                ->latest()
                ->take(6)
                ->get();
        }

        return view('polls.index', [
            'recentPolls' => $recentPolls,
        ]);
    }

    public function dashboard(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $range = $this->resolveDashboardRange($request->string('range')->toString());
        $dashboardData = $this->buildDashboardData($user, $range);

        return view('polls.dashboard', $dashboardData + [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
        ]);
    }

    public function exportDashboardPdf(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $range = $this->resolveDashboardRange($request->string('range')->toString());
        $dashboardData = $this->buildDashboardData($user, $range);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('polls.dashboard-pdf', $dashboardData + [
            'range' => $range,
            'rangeLabel' => $this->dashboardRangeOptions()[$range],
            'user' => $user,
        ])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'dashboard-sondages-'.$range.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportDashboardCsv(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $range = $this->resolveDashboardRange($request->string('range')->toString());
        $dashboardData = $this->buildDashboardData($user, $range);

        $lines = [
            ['Titre', 'Mode de vote', 'Dates proposées', 'Réponses', 'Créé le', 'Lien'],
        ];

        foreach ($dashboardData['allMyPolls'] as $poll) {
            $lines[] = [
                $poll->title,
                $poll->allows_multiple_choices ? 'Vote multiple' : 'Vote unique',
                (string) $poll->dates_count,
                (string) $poll->responses_count,
                $poll->created_at->format('Y-m-d H:i:s'),
                route('polls.show', $poll),
            ];
        }

        $csv = collect($lines)
            ->map(fn (array $row) => collect($row)
                ->map(fn (string $value) => '"'.str_replace('"', '""', $value).'"')
                ->implode(';'))
            ->implode("\r\n");

        $fileName = 'dashboard-sondages-'.$range.'.csv';

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    protected function buildDashboardData(User $user, string $range): array
    {
        $query = $user->polls()
            ->withCount(['dates', 'responses'])
            ->latest();

        if ($startDate = $this->dashboardRangeStart($range)) {
            $query->where('created_at', '>=', $startDate);
        }

        $allMyPolls = (clone $query)->get();
        $myPolls = $query->paginate(5)->withQueryString();

        $totalPolls = $allMyPolls->count();
        $totalResponses = $allMyPolls->sum('responses_count');
        $totalDates = $allMyPolls->sum('dates_count');
        $singleChoicePolls = $allMyPolls->where('allows_multiple_choices', false)->count();
        $multipleChoicePolls = $allMyPolls->where('allows_multiple_choices', true)->count();
        $averageResponses = $totalPolls > 0 ? round($totalResponses / $totalPolls, 1) : 0;
        $topPoll = $allMyPolls->sortByDesc('responses_count')->first();
        $maxResponses = max($allMyPolls->max('responses_count') ?? 0, 1);

        $performancePolls = $allMyPolls
            ->sortByDesc('responses_count')
            ->take(6)
            ->values()
            ->map(function (Poll $poll) use ($maxResponses) {
                return [
                    'title' => Str::limit($poll->title, 28),
                    'responses' => $poll->responses_count,
                    'dates' => $poll->dates_count,
                    'percentage' => (int) round(($poll->responses_count / $maxResponses) * 100),
                    'mode' => $poll->allows_multiple_choices ? 'multiple' : 'unique',
                    'url' => route('polls.show', $poll),
                ];
            });

        $trendPoints = $this->buildTrendPoints($allMyPolls, $range);
        $datePopularityPoints = $this->buildDatePopularityPoints($allMyPolls);

        return [
            'myPolls' => $myPolls,
            'allMyPolls' => $allMyPolls,
            'dashboardStats' => [
                'totalPolls' => $totalPolls,
                'totalResponses' => $totalResponses,
                'totalDates' => $totalDates,
                'singleChoicePolls' => $singleChoicePolls,
                'multipleChoicePolls' => $multipleChoicePolls,
                'averageResponses' => $averageResponses,
                'topPoll' => $topPoll,
            ],
            'performancePolls' => $performancePolls,
            'trendPoints' => $trendPoints,
            'datePopularityPoints' => $datePopularityPoints,
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'dates' => $this->sanitizeDates($request->input('dates', []))->all(),
        ]);

        $validated = $this->validatePoll($request);

        /** @var User $user */
        $user = $request->user();

        $poll = DB::transaction(function () use ($validated, $user) {
            $poll = $user->polls()->create([
                'title' => $validated['title'],
                'organizer_name' => $user->name,
                'description' => $validated['description'] ?? null,
                'allows_multiple_choices' => $validated['allows_multiple_choices'],
                'slug' => $this->generateUniqueSlug($validated['title']),
            ]);

            $poll->dates()->createMany(
                collect($validated['dates'])
                    ->sort()
                    ->map(fn (string $date) => ['date' => $date])
                    ->all(),
            );

            return $poll;
        });

        return redirect()
            ->route('polls.show', $poll)
            ->with('status', 'Le sondage est prêt. Partage le lien avec tes invités.');
    }

    public function edit(Request $request, Poll $poll): View
    {
        $this->authorizeOwner($request, $poll);

        return view('polls.edit', [
            'poll' => $poll->load('dates'),
        ]);
    }

    public function update(Request $request, Poll $poll): RedirectResponse
    {
        $this->authorizeOwner($request, $poll);

        $request->merge([
            'dates' => $this->sanitizeDates($request->input('dates', []))->all(),
        ]);

        $validated = $this->validatePoll($request);

        DB::transaction(function () use ($validated, $poll, $request) {
            /** @var User $user */
            $user = $request->user();

            $poll->update([
                'title' => $validated['title'],
                'organizer_name' => $user->name,
                'description' => $validated['description'] ?? null,
                'allows_multiple_choices' => $validated['allows_multiple_choices'],
            ]);

            $existingDates = $poll->dates()->get()->keyBy(fn ($date) => $date->date->toDateString());
            $requestedDates = collect($validated['dates'])->sort()->values();

            $poll->dates()
                ->whereNotIn('date', $requestedDates->all())
                ->delete();

            $requestedDates
                ->reject(fn (string $date) => $existingDates->has($date))
                ->each(fn (string $date) => $poll->dates()->create(['date' => $date]));
        });

        return redirect()
            ->route('polls.show', $poll)
            ->with('status', 'Le sondage a été mis à jour.');
    }

    public function destroy(Request $request, Poll $poll): RedirectResponse
    {
        $this->authorizeOwner($request, $poll);

        $poll->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Le sondage a été supprimé.');
    }

    public function show(Request $request, Poll $poll): View
    {
        $poll->load([
            'dates.responseChoices.response',
        ])->loadCount('responses');

        $responses = $poll->responses()
            ->with('choices.date')
            ->paginate(10)
            ->withQueryString();

        $dateSummaries = $poll->dates->map(function ($date) {
            $participants = $date->responseChoices
                ->map(fn ($choice) => $choice->response?->participant_name)
                ->filter()
                ->unique()
                ->values();

            return [
                'id' => $date->id,
                'label' => $date->date->locale('fr')->isoFormat('dddd D MMMM YYYY'),
                'short_label' => $date->date->locale('fr')->isoFormat('D MMM'),
                'count' => $participants->count(),
                'participants' => $participants,
            ];
        });

        $bestCount = $dateSummaries->max('count') ?? 0;

        return view('polls.show', [
            'poll' => $poll,
            'responses' => $responses,
            'dateSummaries' => $dateSummaries,
            'bestCount' => $bestCount,
        ]);
    }

    protected function sanitizeDates(array $dates): Collection
    {
        return collect($dates)
            ->filter(fn ($date) => filled($date))
            ->map(fn ($date) => trim((string) $date))
            ->unique()
            ->values();
    }

    protected function validatePoll(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'allows_multiple_choices' => ['required', 'boolean'],
            'dates' => ['required', 'array', 'min:2', 'max:10'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'distinct', 'after_or_equal:today'],
        ], [
            'dates.min' => 'Ajoute au moins deux dates pour lancer le sondage.',
            'dates.*.after_or_equal' => 'Choisis uniquement des dates à partir d’aujourd’hui.',
        ]);
    }

    protected function authorizeOwner(Request $request, Poll $poll): void
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($poll->user_id !== $user->id, 403);
    }

    protected function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? Str::limit($base, 45, '') : 'sondage';

        do {
            $slug = $base.'-'.Str::lower(Str::random(5));
        } while (Poll::query()->where('slug', $slug)->exists());

        return $slug;
    }

    protected function resolveDashboardRange(string $range): string
    {
        return array_key_exists($range, $this->dashboardRangeOptions()) ? $range : '30d';
    }

    protected function dashboardRangeOptions(): array
    {
        return [
            '7d' => '7 jours',
            '30d' => '30 jours',
            'all' => 'Tout',
        ];
    }

    protected function dashboardRangeStart(string $range): ?Carbon
    {
        return match ($range) {
            '7d' => now()->startOfDay()->subDays(6),
            '30d' => now()->startOfDay()->subDays(29),
            default => null,
        };
    }

    protected function buildTrendPoints(Collection $polls, string $range): Collection
    {
        if ($polls->isEmpty()) {
            return collect();
        }

        if ($startDate = $this->dashboardRangeStart($range)) {
            $endDate = now()->startOfDay();

            return collect(range(0, $startDate->diffInDays($endDate)))
                ->map(function (int $dayOffset) use ($startDate, $polls) {
                    $date = $startDate->copy()->addDays($dayOffset);
                    $responses = $polls
                        ->filter(fn (Poll $poll) => $poll->created_at->isSameDay($date))
                        ->sum('responses_count');

                    return [
                        'label' => $date->locale('fr')->isoFormat('D MMM'),
                        'value' => $responses,
                    ];
                });
        }

        return $polls
            ->groupBy(fn (Poll $poll) => $poll->created_at->toDateString())
            ->map(function (Collection $items, string $date) {
                return [
                    'label' => Carbon::parse($date)->locale('fr')->isoFormat('D MMM'),
                    'value' => $items->sum('responses_count'),
                ];
            })
            ->values();
    }

    protected function buildDatePopularityPoints(Collection $polls): Collection
    {
        if ($polls->isEmpty()) {
            return collect();
        }

        return PollDate::query()
            ->with(['poll:id,title'])
            ->withCount('responseChoices')
            ->whereIn('poll_id', $polls->pluck('id'))
            ->orderByDesc('response_choices_count')
            ->orderBy('date')
            ->take(8)
            ->get()
            ->map(function (PollDate $pollDate) {
                $title = $pollDate->poll?->title ? Str::limit($pollDate->poll->title, 18) : 'Sondage';

                return [
                    'label' => $pollDate->date->locale('fr')->isoFormat('D MMM').' · '.$title,
                    'value' => $pollDate->response_choices_count,
                ];
            })
            ->values();
    }
}