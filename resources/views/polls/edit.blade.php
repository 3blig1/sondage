@extends('layouts.app', ['title' => 'Modifier le sondage', 'showHero' => false])

@section('content')
    <div class="mx-auto max-w-4xl">
        <section class="glass-panel rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-300">Modification</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">Modifier le sondage</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Tu peux ajuster le titre, le contexte et les dates. Les votes déjà liés à une date retirée seront automatiquement supprimés pour cette date.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="{{ route('dashboard') }}" class="btn-secondary w-full sm:w-auto">Retour dashboard</a>
                    <a href="{{ route('polls.show', $poll) }}" class="btn-secondary w-full sm:w-auto">Voir le sondage</a>
                </div>
            </div>

            @include('polls.partials.form', [
                'poll' => $poll,
                'formAction' => route('polls.update', $poll),
                'formMethod' => 'PATCH',
                'submitLabel' => 'Mettre à jour',
            ])
        </section>
    </div>
@endsection
