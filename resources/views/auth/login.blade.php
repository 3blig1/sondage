@extends('layouts.app', ['title' => 'Connexion', 'showHero' => false])

@section('content')
    <div class="mx-auto max-w-xl">
        <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Connexion</p>
            <h2 class="mt-2 text-2xl font-semibold text-white">Accéder au dashboard</h2>
            <p class="mt-2 text-sm leading-6 text-slate-300">Connecte-toi pour créer et gérer tes sondages. Les participants n’ont pas besoin de compte.</p>

            <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-6">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="field-input" required autofocus>
                    @error('email')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Mot de passe</label>
                    <input id="password" type="password" name="password" class="field-input" required>
                    @error('password')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-white/10 bg-slate-900 text-cyan-400 focus:ring-cyan-400">
                    Se souvenir de moi
                </label>

                <button type="submit" class="btn-primary w-full">Se connecter</button>
            </form>

            <p class="mt-6 text-sm text-slate-400">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="font-semibold text-cyan-300">Créer un compte</a>
            </p>
        </section>
    </div>
@endsection
