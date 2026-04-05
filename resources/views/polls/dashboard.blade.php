@extends('layouts.app', ['title' => 'Dashboard', 'showHero' => false])

@section('content')
    <div class="dashboard-shell">
        <section class="space-y-6">
            <div class="glass-panel rounded-[2rem] p-6 sm:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Dashboard</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">Pilotage de tes sondages</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                            Un tableau de bord structuré pour suivre tes créations, mesurer la participation et repérer rapidement les sondages les plus performants.
                        </p>
                    </div>

                    <div class="badge">
                        <span>{{ auth()->user()->name }}</span>
                        <span>connecté</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($rangeOptions as $key => $label)
                            <a
                                href="{{ route('dashboard', ['range' => $key]) }}"
                                class="{{ $range === $key ? 'btn-primary' : 'btn-secondary' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('dashboard.export.csv', ['range' => $range]) }}" class="btn-secondary">
                            Export CSV
                        </a>
                        <a href="{{ route('dashboard.export.pdf', ['range' => $range]) }}" class="btn-secondary">
                            Export PDF
                        </a>
                    </div>
                </div>

                <div class="dashboard-grid mt-8">
                    <div class="dashboard-card dashboard-kpi">
                        <p class="text-sm text-slate-400">Sondages créés</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboardStats['totalPolls'] }}</p>
                        <p class="mt-2 text-sm text-slate-400">Base active de tes événements et consultations.</p>
                    </div>

                    <div class="dashboard-card dashboard-kpi">
                        <p class="text-sm text-slate-400">Réponses collectées</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboardStats['totalResponses'] }}</p>
                        <p class="mt-2 text-sm text-slate-400">Total des participations enregistrées sur tous tes sondages.</p>
                    </div>

                    <div class="dashboard-card dashboard-kpi">
                        <p class="text-sm text-slate-400">Dates proposées</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboardStats['totalDates'] }}</p>
                        <p class="mt-2 text-sm text-slate-400">Ensemble des créneaux actuellement mis au vote.</p>
                    </div>

                    <div class="dashboard-card dashboard-kpi">
                        <p class="text-sm text-slate-400">Moyenne de réponses</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboardStats['averageResponses'] }}</p>
                        <p class="mt-2 text-sm text-slate-400">Participation moyenne par sondage publié.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
                <section class="glass-panel rounded-[2rem] p-6 sm:p-8 xl:col-span-2">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Évolution</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Réponses selon la date de création</h3>
                        </div>
                        <span class="text-sm text-slate-400">Période : {{ $rangeOptions[$range] }}</span>
                    </div>

                    <div class="chart-canvas-wrap mt-6">
                        <canvas
                            data-trend-chart
                            data-labels='@json($trendPoints->pluck('label')->values())'
                            data-values='@json($trendPoints->pluck('value')->values())'
                        ></canvas>
                    </div>
                </section>

                <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Performance</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Sondages les plus actifs</h3>
                        </div>
                        <span class="text-sm text-slate-400">Top 6</span>
                    </div>

                    <div class="chart-canvas-wrap mt-6">
                        <canvas
                            data-performance-chart
                            data-labels='@json($performancePolls->pluck('title')->values())'
                            data-values='@json($performancePolls->pluck('responses')->values())'
                        ></canvas>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($performancePolls as $item)
                            <a href="{{ $item['url'] }}" class="block rounded-[1.5rem] border border-white/10 bg-white/5 p-4 transition hover:border-cyan-400/30 hover:bg-white/8">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-white">{{ $item['title'] }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item['responses'] }} réponse(s) · {{ $item['dates'] }} date(s)</p>
                                    </div>
                                    <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-medium text-cyan-100">
                                        {{ $item['mode'] === 'multiple' ? 'Vote multiple' : 'Vote unique' }}
                                    </span>
                                </div>

                                <div class="chart-bar-track mt-4">
                                    <div class="chart-bar-fill" style="width: {{ max($item['percentage'], $item['responses'] > 0 ? 10 : 0) }}%"></div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 px-4 py-10 text-center text-sm text-slate-400">
                                Crée un premier sondage pour afficher les métriques de performance.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-amber-300">Dates populaires</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Créneaux les plus choisis</h3>
                        </div>
                        <span class="text-sm text-slate-400">Top 8</span>
                    </div>

                    @if ($datePopularityPoints->isNotEmpty())
                        <div class="chart-canvas-wrap mt-6">
                            <canvas
                                data-date-popularity-chart
                                data-labels='@json($datePopularityPoints->pluck('label')->values())'
                                data-values='@json($datePopularityPoints->pluck('value')->values())'
                            ></canvas>
                        </div>
                    @else
                        <div class="mt-6 rounded-[1.5rem] border border-dashed border-white/10 px-4 py-10 text-center text-sm text-slate-400">
                            Les créneaux les plus plébiscités apparaîtront ici dès les premières réponses.
                        </div>
                    @endif
                </section>

                <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-violet-300">Vue d’ensemble</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Répartition et insights</h3>

                    <div class="chart-canvas-wrap chart-canvas-wrap-sm mt-6">
                        <canvas
                            data-choice-chart
                            data-labels='@json(["Vote unique", "Vote multiple"])'
                            data-values='@json([$dashboardStats['singleChoicePolls'], $dashboardStats['multipleChoicePolls']])'
                        ></canvas>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="mini-stat">
                            <p class="text-sm text-slate-400">Vote unique</p>
                            <p class="mt-2 text-2xl font-semibold text-white">{{ $dashboardStats['singleChoicePolls'] }}</p>
                        </div>
                        <div class="mini-stat">
                            <p class="text-sm text-slate-400">Vote multiple</p>
                            <p class="mt-2 text-2xl font-semibold text-white">{{ $dashboardStats['multipleChoicePolls'] }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm text-slate-400">Sondage le plus performant</p>
                        @if ($dashboardStats['topPoll'])
                            <p class="mt-2 text-lg font-semibold text-white">{{ $dashboardStats['topPoll']->title }}</p>
                            <p class="mt-2 text-sm text-slate-300">
                                {{ $dashboardStats['topPoll']->responses_count }} réponse(s) pour {{ $dashboardStats['topPoll']->dates_count }} date(s) proposées.
                            </p>
                            <a href="{{ route('polls.show', $dashboardStats['topPoll']) }}" class="btn-secondary mt-4">Ouvrir ce sondage</a>
                        @else
                            <p class="mt-2 text-sm text-slate-400">Aucune donnée disponible pour le moment.</p>
                        @endif
                    </div>

                    <div class="dashboard-divider mt-6 pt-6">
                        <p class="text-sm text-slate-400">Lecture rapide</p>
                        <ul class="mt-3 space-y-3 text-sm text-slate-300">
                            <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                                Les cartes KPI donnent une vue immédiate du volume et de l’engagement.
                            </li>
                            <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                                Le graphique met en avant les sondages qui génèrent le plus de réponses.
                            </li>
                            <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                                La liste détaillée reste disponible plus bas pour la gestion opérationnelle.
                            </li>
                        </ul>
                    </div>
                </section>
            </div>

            <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Mes sondages</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Gestion détaillée</h3>
                    </div>
                    <span class="text-sm text-slate-400">{{ $myPolls->count() }} élément(s)</span>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($myPolls as $ownedPoll)
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 sm:p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <a href="{{ route('polls.show', $ownedPoll) }}" class="text-lg font-semibold text-white hover:text-cyan-200">{{ $ownedPoll->title }}</a>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-medium text-cyan-100">
                                            {{ $ownedPoll->allows_multiple_choices ? 'Vote multiple' : 'Vote unique' }}
                                        </span>
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-300">
                                            {{ $ownedPoll->dates_count }} date(s)
                                        </span>
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-300">
                                            {{ $ownedPoll->responses_count }} réponse(s)
                                        </span>
                                    </div>
                                    @if ($ownedPoll->description)
                                        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">{{ $ownedPoll->description }}</p>
                                    @endif
                                </div>

                                <div class="text-sm text-slate-500">
                                    {{ $ownedPoll->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('polls.edit', $ownedPoll) }}" class="btn-secondary">Modifier</a>
                                <a href="{{ route('polls.show', $ownedPoll) }}" class="btn-secondary">Voir</a>
                                <form action="{{ route('polls.destroy', $ownedPoll) }}" method="POST" onsubmit="return confirm('Supprimer ce sondage ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary border-rose-400/25 text-rose-200 hover:border-rose-400/45 hover:bg-rose-500/10">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-white/10 px-4 py-10 text-center text-sm text-slate-400">
                            Aucun sondage créé pour l’instant.
                        </div>
                    @endforelse
                </div>
            </section>
        </section>

        <aside class="glass-panel rounded-[2rem] p-6 sm:p-8 xl:sticky xl:top-6">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Nouveau sondage</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Créer rapidement</h3>
            <p class="mt-2 text-sm leading-6 text-slate-300">
                Formulaire dédié avec structure claire pour lancer un nouveau sondage depuis le dashboard.
            </p>

            @include('polls.partials.form', [
                'poll' => null,
                'formAction' => route('polls.store'),
                'formMethod' => 'POST',
                'submitLabel' => 'Créer le sondage',
            ])
        </aside>
    </div>
@endsection
