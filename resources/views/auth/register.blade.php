@extends('layouts.app', ['title' => 'Inscription', 'showHero' => false])

@section('content')
    <div class="mx-auto max-w-xl">
        <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Inscription</p>
            <h2 class="mt-2 text-2xl font-semibold text-white">Créer ton espace organisateur</h2>
            <p class="mt-2 text-sm leading-6 text-slate-300">Une fois inscrit, tu seras redirigé vers ton dashboard pour créer tes sondages.</p>

            <form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-6">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-200">Prénom ou nom</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="field-input" required autofocus>
                    @error('name')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="field-input" required>
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

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-200">Confirmer le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="field-input" required>
                </div>

                <button type="submit" class="btn-primary w-full">Créer mon compte</button>
            </form>

            <p class="mt-6 text-sm text-slate-400">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="font-semibold text-cyan-300">Se connecter</a>
            </p>
        </section>
    </div>
@endsection
