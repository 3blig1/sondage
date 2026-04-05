@php
    $poll = $poll ?? null;
    $submitLabel = $submitLabel ?? 'Enregistrer';
    $formAction = $formAction ?? route('polls.store');
    $formMethod = $formMethod ?? 'POST';

    $dateValues = collect(old('dates', $poll?->dates?->map(fn ($date) => $date->date->toDateString())->all() ?? []))->values();

    while ($dateValues->count() < 3) {
        $dateValues->push('');
    }
@endphp

<form action="{{ $formAction }}" method="POST" class="mt-8 space-y-6">
    @csrf
    @if (! in_array($formMethod, ['GET', 'POST'], true))
        @method($formMethod)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="mb-2 block text-sm font-medium text-slate-200">Titre du sondage</label>
            <input id="title" type="text" name="title" value="{{ old('title', $poll?->title) }}" class="field-input" placeholder="Ex. Déjeuner d'équipe de mai" required>
            @error('title')
                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="mb-2 block text-sm font-medium text-slate-200">Contexte</label>
            <input id="description" type="text" name="description" value="{{ old('description', $poll?->description) }}" class="field-input" placeholder="Ex. 1h sur place ou en visio">
            @error('description')
                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <p class="mb-3 block text-sm font-medium text-slate-200">Mode de vote</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-4 transition hover:border-cyan-400/30 hover:bg-white/8">
                    <input
                        type="radio"
                        name="allows_multiple_choices"
                        value="0"
                        class="mt-1 h-5 w-5 border-white/10 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                        @checked((string) old('allows_multiple_choices', (int) ($poll?->allows_multiple_choices ?? true)) === '0')
                    >
                    <span>
                        <span class="block font-medium text-white">Une seule date</span>
                        <span class="mt-1 block text-sm text-slate-400">Le participant doit choisir un seul créneau.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-4 transition hover:border-cyan-400/30 hover:bg-white/8">
                    <input
                        type="radio"
                        name="allows_multiple_choices"
                        value="1"
                        class="mt-1 h-5 w-5 border-white/10 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                        @checked((string) old('allows_multiple_choices', (int) ($poll?->allows_multiple_choices ?? true)) === '1')
                    >
                    <span>
                        <span class="block font-medium text-white">Plusieurs dates</span>
                        <span class="mt-1 block text-sm text-slate-400">Le participant peut sélectionner plusieurs créneaux.</span>
                    </span>
                </label>
            </div>

            @error('allows_multiple_choices')
                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <div class="mb-3 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-medium text-slate-100">Dates proposées</h3>
                <p class="text-sm text-slate-400">Ajoute entre 2 et 10 créneaux.</p>
            </div>
            <button type="button" class="btn-secondary" data-add-date>Ajouter une date</button>
        </div>

        <div class="space-y-3" data-date-fields>
            @foreach ($dateValues as $value)
                <div class="flex flex-col gap-3 sm:flex-row" data-date-row>
                    <input type="date" name="dates[]" value="{{ $value }}" min="{{ now()->toDateString() }}" class="field-input">
                    <button type="button" class="btn-secondary sm:min-w-32" data-remove-date>Retirer</button>
                </div>
            @endforeach
        </div>

        @error('dates')
            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
        @enderror

        @error('dates.*')
            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-4 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-xl text-sm leading-6 text-slate-400">
            Le prénom de l’organisateur est repris automatiquement depuis ton compte.
        </p>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
