@extends('layouts.app', ['title' => 'Accueil du sondage de date'])

@section('content')
    <div class="grid gap-8 lg:grid-cols-[1.15fr,0.85fr]">
        <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Accueil</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">Crée tes sondages depuis un espace privé</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Pour créer un sondage, il faut d’abord s’inscrire puis se connecter au dashboard. Les participants, eux, peuvent répondre librement sans compte.
                    </p>
                </div>

                @auth
                    <div class="badge">
                        <span>{{ $recentPolls->count() }}</span>
                        <span>tes sondages récents</span>
                    </div>
                @endauth
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="soft-panel rounded-[1.75rem] p-5">
                    <p class="text-sm text-slate-400">Organisateurs</p>
                    <p class="mt-2 text-lg font-semibold text-white">Compte requis</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Inscription rapide, connexion sécurisée et gestion centralisée des sondages.</p>
                </div>

                <div class="soft-panel rounded-[1.75rem] p-5">
                    <p class="text-sm text-slate-400">Participants</p>
                    <p class="mt-2 text-lg font-semibold text-white">Vote sans inscription</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Ils ouvrent le lien, sélectionnent les dates disponibles et valident leur réponse.</p>
                </div>

                <div class="soft-panel rounded-[1.75rem] p-5">
                    <p class="text-sm text-slate-400">Résultat</p>
                    <p class="mt-2 text-lg font-semibold text-white">Vue instantanée</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Les meilleures dates remontent automatiquement selon les réponses enregistrées.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-4 border-t border-white/10 pt-6 sm:flex-row sm:items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">Accéder à mon dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary">Créer un compte</a>
                    <a href="{{ route('login') }}" class="btn-secondary">J’ai déjà un compte</a>
                @endauth
            </div>
        </section>

        <aside class="space-y-8">
            <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-violet-300">Comment ça marche</p>
                <h3 class="mt-3 text-xl font-semibold text-white">Flux recommandé</h3>

                <div class="mt-6 space-y-4">
                    <div class="soft-panel rounded-3xl p-4">
                        <p class="text-sm text-slate-400">1. Inscription</p>
                        <p class="mt-2 text-sm leading-6 text-slate-200">L’organisateur crée son compte pour accéder à son dashboard personnel.</p>
                    </div>

                    <div class="soft-panel rounded-3xl p-4">
                        <p class="text-sm text-slate-400">2. Création</p>
                        <p class="mt-2 text-sm leading-6 text-slate-200">Depuis le dashboard, il ajoute un titre, un contexte et plusieurs dates possibles.</p>
                    </div>

                    <div class="soft-panel rounded-3xl p-4">
                        <p class="text-sm text-slate-400">3. Votes publics</p>
                        <p class="mt-2 text-sm leading-6 text-slate-200">Les invités votent sans créer de compte, ce qui réduit les frictions au maximum.</p>
                    </div>
                </div>
            </section>

            @auth
                <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Sondages récents</p>
                            <h3 class="mt-2 text-xl font-semibold text-white">Retrouver tes dernières créations</h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($recentPolls as $poll)
                            <a href="{{ route('polls.show', $poll) }}" class="block rounded-3xl border border-white/10 bg-white/5 p-4 transition hover:border-cyan-400/30 hover:bg-white/8">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-semibold text-white">{{ $poll->title }}</p>
                                        <p class="mt-1 text-sm text-slate-400">Créé par {{ $poll->organizer_name }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-500">{{ $poll->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-slate-400">
                                Tu n’as encore créé aucun sondage.
                            </div>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Confidentialité</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Les sondages récents restent privés</h3>
                    <p class="mt-4 text-sm leading-6 text-slate-300">
                        Les visiteurs publics ne voient pas la liste des derniers sondages créés. Seuls les organisateurs connectés accèdent à cette vue depuis leur espace.
                    </p>
                </section>
            @endauth
        </aside>
    </div>
@endsection