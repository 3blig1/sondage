<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Crée un sondage de date, partage un lien, puis visualise les créneaux préférés de ton groupe.">

        <title>{{ $title ?? 'Sondage de date' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen antialiased">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="grid-pattern absolute inset-0 opacity-25"></div>
            <div class="absolute left-0 top-0 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-10">
            <div class="mb-4 flex flex-col gap-3 text-sm sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-secondary w-full sm:w-auto">Dashboard</a>
                    <a href="{{ route('dashboard') }}#nouveau-sondage-modal" class="btn-primary w-full sm:w-auto" data-open-modal-target="nouveau-sondage-modal">Créer un sondage</a>
                    <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="btn-secondary w-full sm:w-auto">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary w-full sm:w-auto">Connexion</a>
                    <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto">Inscription</a>
                @endauth
            </div>

            @if (($showHero ?? true) === true)
                <header class="glass-panel mb-8 rounded-[2rem] p-6 sm:p-8">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <a href="{{ route('home') }}" class="badge mb-4">
                                <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                                Sondage de date
                            </a>
                            <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                Organise une date commune en quelques secondes.
                            </h1>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                                Crée un lien partagé, propose plusieurs jours et laisse ton groupe voter sur les disponibilités.
                            </p>
                        </div>

                        <div class="grid gap-3 text-sm text-slate-300 sm:grid-cols-3">
                            <div class="soft-panel rounded-2xl px-4 py-3">
                                <p class="text-slate-400">Étape 1</p>
                                <p class="mt-1 font-semibold text-white">Connexion au dashboard</p>
                            </div>
                            <div class="soft-panel rounded-2xl px-4 py-3">
                                <p class="text-slate-400">Étape 2</p>
                                <p class="mt-1 font-semibold text-white">Partager le lien</p>
                            </div>
                            <div class="soft-panel rounded-2xl px-4 py-3">
                                <p class="text-slate-400">Étape 3</p>
                                <p class="mt-1 font-semibold text-white">Choisir la meilleure date</p>
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </body>
</html>