@extends('layouts.app', ['title' => $poll->title, 'showHero' => false])

@section('content')
    @php
        $isOwner = auth()->id() === $poll->user_id;
    @endphp

    <div class="{{ $isOwner ? 'grid gap-6 xl:grid-cols-[1.15fr,0.85fr]' : 'mx-auto max-w-2xl' }}">
        @if ($isOwner)
            <section class="space-y-8">
                <div class="glass-panel rounded-[2rem] p-5 sm:p-7">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Sondage public</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ $poll->title }}</h2>
                            <p class="mt-2 text-sm text-slate-400">Créé par {{ $poll->organizer_name }}</p>

                            @if ($poll->description)
                                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300">{{ $poll->description }}</p>
                            @endif
                        </div>

                        <div class="soft-panel rounded-3xl p-4 lg:min-w-96">
                            <label for="share-link" class="mb-2 block text-sm font-medium text-slate-200">Lien de partage</label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <input id="share-link" type="text" readonly value="{{ route('polls.show', $poll) }}" class="field-input">
                                <button type="button" class="btn-primary sm:min-w-36" data-copy-button="#share-link">Copier</button>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-3">
                                <a href="{{ route('polls.edit', $poll) }}" class="btn-secondary">Modifier</a>
                                <a href="{{ route('dashboard') }}" class="btn-secondary">Dashboard</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="soft-panel rounded-2xl px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Dates</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ $poll->dates->count() }}</p>
                        </div>
                        <div class="soft-panel rounded-2xl px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Réponses</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ $poll->responses_count }}</p>
                        </div>
                        <div class="soft-panel rounded-2xl px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Meilleur score</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ $bestCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="glass-panel rounded-[2rem] p-5 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Tendance</p>
                            <h3 class="mt-2 text-xl font-semibold text-white sm:text-2xl">Popularité des dates</h3>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($dateSummaries as $summary)
                            @php
                                $percentage = $bestCount > 0 ? (int) round(($summary['count'] / $bestCount) * 100) : 0;
                                $isBest = $bestCount > 0 && $summary['count'] === $bestCount;
                            @endphp

                            <div class="rounded-[1.5rem] border {{ $isBest ? 'border-emerald-400/35 bg-emerald-400/10' : 'border-white/10 bg-white/5' }} p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $summary['short_label'] }}</p>
                                        <h4 class="mt-2 text-base font-semibold text-white sm:text-lg">{{ ucfirst($summary['label']) }}</h4>
                                    </div>
                                    @if ($isBest)
                                        <span class="badge">Top choix</span>
                                    @endif
                                </div>

                                <div class="mt-5 flex items-center justify-between text-sm">
                                    <span class="text-slate-400">Disponibles</span>
                                    <span class="font-semibold text-white">{{ $summary['count'] }}</span>
                                </div>

                                <div class="progress-track mt-3">
                                    <div class="progress-fill" style="width: {{ max($percentage, $summary['count'] > 0 ? 12 : 0) }}%"></div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @forelse ($summary['participants'] as $participant)
                                        <span class="rounded-full bg-white/8 px-3 py-1 text-xs font-medium text-slate-200">{{ $participant }}</span>
                                    @empty
                                        <span class="text-sm text-slate-500">Aucune réponse pour l’instant</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="reponses" class="glass-panel rounded-[2rem] p-5 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-violet-300">Participants</p>
                            <h3 class="mt-2 text-xl font-semibold text-white sm:text-2xl">Participants par choix</h3>
                        </div>

                        <span class="participant-badge participant-badge--accent">{{ $poll->responses_count }} réponse(s)</span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        @forelse ($dateSummaries as $summary)
                            <article class="response-card">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $summary['short_label'] }}</p>
                                        <h4 class="mt-2 text-base font-semibold text-white sm:text-lg">{{ ucfirst($summary['label']) }}</h4>
                                    </div>

                                    <span class="participant-badge participant-badge--soft">
                                        {{ $summary['count'] }} participant(s)
                                    </span>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    @forelse ($summary['participants'] as $participant)
                                        <span class="response-chip">{{ $participant }}</span>
                                    @empty
                                        <span class="text-sm text-slate-500">Aucun participant pour cette date</span>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-white/4 px-4 py-12 text-center text-sm text-slate-400">
                                Aucune réponse pour le moment. Les participations apparaîtront ici dès les premiers retours.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <aside id="participer" class="glass-panel rounded-[2rem] p-5 sm:p-7 h-fit">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Sondage public</p>
                <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ $poll->title }}</h2>
                <p class="mt-2 text-sm text-slate-400">Créé par {{ $poll->organizer_name }}</p>

                @if ($poll->description)
                    <p class="mt-3 text-sm leading-6 text-slate-300">{{ $poll->description }}</p>
                @endif

                <div class="mt-4 rounded-2xl border border-cyan-400/15 bg-cyan-400/8 px-4 py-3 text-sm text-cyan-100">
                    {{ $poll->allows_multiple_choices ? 'Tu peux sélectionner plusieurs dates.' : 'Tu dois sélectionner une seule date.' }}
                </div>

                <p class="text-sm font-medium uppercase tracking-[0.2em] text-amber-300">Participer</p>
                <h3 class="mt-2 text-xl font-semibold text-white sm:text-2xl">Indiquer ses disponibilités</h3>
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    {{ $poll->allows_multiple_choices ? 'Sélectionne toutes les dates qui te conviennent.' : 'Sélectionne la date qui te convient le mieux.' }}
                </p>

                <form action="{{ route('polls.responses.store', $poll) }}" method="POST" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="participant_name" class="mb-2 block text-sm font-medium text-slate-200">Ton prénom</label>
                        <input id="participant_name" type="text" name="participant_name" value="{{ old('participant_name') }}" class="field-input" required>
                        @error('participant_name')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="comment" class="mb-2 block text-sm font-medium text-slate-200">Commentaire</label>
                        <textarea id="comment" name="comment" rows="3" class="field-input" placeholder="Ex. Je préfère en début d'après-midi">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-medium text-slate-200">Dates disponibles</p>
                        <div class="mb-4 rounded-2xl border border-cyan-400/15 bg-cyan-400/8 px-4 py-3 text-sm text-cyan-100">
                            Appuie sur une carte pour sélectionner {{ $poll->allows_multiple_choices ? 'une ou plusieurs dates.' : 'la date qui te convient.' }}
                        </div>

                        <div class="space-y-3" data-selectable-group>
                            @foreach ($poll->dates as $date)
                                <label class="date-option-card" data-selectable-option>
                                    @if ($poll->allows_multiple_choices)
                                        <input
                                            type="checkbox"
                                            name="dates[]"
                                            value="{{ $date->id }}"
                                            class="date-option-input mt-1 h-5 w-5 rounded border-white/10 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                            @checked(collect(old('dates', []))->contains($date->id) || collect(old('dates', []))->contains((string) $date->id))
                                        >
                                    @else
                                        <input
                                            type="radio"
                                            name="selected_date"
                                            value="{{ $date->id }}"
                                            class="date-option-input mt-1 h-5 w-5 border-white/10 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                            @checked((string) old('selected_date') === (string) $date->id)
                                        >
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-medium text-white">{{ ucfirst($date->date->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</span>
                                        <span class="mt-1 block text-sm text-slate-400">{{ $date->responseChoices->count() }} participant(s) disponibles</span>
                                    </span>
                                    <span class="date-option-hint">
                                        {{ $poll->allows_multiple_choices ? 'Sélectionner' : 'Choisir' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('dates')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror

                        @error('selected_date')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">Envoyer ma réponse</button>
                </form>
            </aside>
        @endif

        @if ($isOwner)
            <aside class="space-y-6 xl:sticky xl:top-6 h-fit">
                <section class="glass-panel rounded-[2rem] p-5 sm:p-7">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Gestion du sondage</p>
                    <h3 class="mt-2 text-xl font-semibold text-white sm:text-2xl">Piloter et partager</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        Accède rapidement aux actions essentielles pour diffuser, modifier ou supprimer ce sondage.
                    </p>

                    <div class="mt-6 soft-panel rounded-3xl p-4">
                        <label for="share-link" class="mb-2 block text-sm font-medium text-slate-200">Lien de partage</label>
                        <div class="flex flex-col gap-3">
                            <input id="share-link" type="text" readonly value="{{ route('polls.show', $poll) }}" class="field-input">
                            <button type="button" class="btn-primary w-full" data-copy-button="#share-link">Copier le lien</button>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                        <a href="{{ route('polls.edit', $poll) }}" class="btn-secondary w-full">Modifier le sondage</a>
                        <a href="{{ route('dashboard') }}" class="btn-secondary w-full">Retour au dashboard</a>
                    </div>

                    <form action="{{ route('polls.destroy', $poll) }}" method="POST" class="mt-4" onsubmit="return confirm('Supprimer ce sondage ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary w-full border-rose-400/25 text-rose-200 hover:border-rose-400/45 hover:bg-rose-500/10">
                            Supprimer le sondage
                        </button>
                    </form>
                </section>

                <section class="glass-panel rounded-[2rem] p-5 sm:p-7">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Résumé rapide</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Lecture instantanée</h3>

                    <div class="mt-6 space-y-3">
                        <div class="mini-stat">
                            <p class="text-sm text-slate-400">Mode de vote</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ $poll->allows_multiple_choices ? 'Plusieurs dates' : 'Une seule date' }}</p>
                        </div>
                        <div class="mini-stat">
                            <p class="text-sm text-slate-400">Créé le</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ $poll->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                        <div class="mini-stat">
                            <p class="text-sm text-slate-400">Statut</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ $poll->responses->isNotEmpty() ? 'Collecte en cours' : 'En attente de réponses' }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        @endif
    </div>
@endsection