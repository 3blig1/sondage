<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollResponseController extends Controller
{
    public function store(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->allows_multiple_choices) {
            $request->merge([
                'dates' => collect($request->input('dates', []))
                    ->filter(fn ($date) => filled($date))
                    ->map(fn ($date) => (int) $date)
                    ->unique()
                    ->values()
                    ->all(),
            ]);

            $validated = $request->validate([
                'participant_name' => ['required', 'string', 'max:80'],
                'comment' => ['nullable', 'string', 'max:300'],
                'dates' => ['required', 'array', 'min:1'],
                'dates.*' => ['required', 'integer'],
            ], [
                'dates.min' => 'Sélectionne au moins une date avant de répondre.',
            ]);

            $selectedDateIds = collect($validated['dates']);
        } else {
            $validated = $request->validate([
                'participant_name' => ['required', 'string', 'max:80'],
                'comment' => ['nullable', 'string', 'max:300'],
                'selected_date' => ['required', 'integer'],
            ], [
                'selected_date.required' => 'Sélectionne une date avant de répondre.',
            ]);

            $selectedDateIds = collect([(int) $validated['selected_date']]);
        }

        $selectedDates = $poll->dates()
            ->whereIn('id', $selectedDateIds->all())
            ->pluck('id');

        if ($selectedDates->count() !== $selectedDateIds->count()) {
            return back()
                ->withErrors([
                    $poll->allows_multiple_choices ? 'dates' : 'selected_date' => 'Une des dates sélectionnées n’existe plus pour ce sondage.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($poll, $validated, $selectedDates) {
            $response = $poll->responses()->create([
                'participant_name' => $validated['participant_name'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $response->choices()->createMany(
                $selectedDates->map(fn (int $dateId) => ['poll_date_id' => $dateId])->all(),
            );
        });

        return redirect()
            ->route('polls.show', $poll)
            ->withFragment('reponses')
            ->with('status', 'Réponse enregistrée. Merci pour ta participation.');
    }
}