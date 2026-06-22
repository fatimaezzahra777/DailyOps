@php
    $colors = [
        '#c50064' => 'Framboise',
        '#2563eb' => 'Bleu',
        '#7c3aed' => 'Violet',
        '#059669' => 'Vert',
        '#ea580c' => 'Orange',
        '#0f766e' => 'Sarcelle',
    ];
@endphp

<section data-appearance-form>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Apparence</h2>
        <p class="mt-1 text-sm text-gray-600">
            Personnalisez la couleur principale et la taille des textes. Ces choix sont enregistrés uniquement pour votre compte.
        </p>
    </header>

    <form method="post" action="{{ route('profile.appearance.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <fieldset>
            <legend class="text-sm font-medium text-gray-900">Taille du texte</legend>
            <p class="mt-1 text-xs text-gray-500">La taille normale est recommandée pour la plupart des écrans.</p>

            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                @foreach ([
                    'small' => ['label' => 'Petite', 'sample' => 'Aa', 'class' => 'text-sm'],
                    'normal' => ['label' => 'Normale', 'sample' => 'Aa', 'class' => 'text-base'],
                    'large' => ['label' => 'Grande', 'sample' => 'Aa', 'class' => 'text-xl'],
                ] as $value => $option)
                    <label class="appearance-option">
                        <input type="radio" name="font_size" value="{{ $value }}" class="sr-only"
                            data-font-size-option
                            @checked(old('font_size', $user->font_size ?? 'normal') === $value)>
                        <span class="{{ $option['class'] }} font-semibold">{{ $option['sample'] }}</span>
                        <span class="text-sm font-medium">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('font_size')" />
        </fieldset>

        <fieldset>
            <legend class="text-sm font-medium text-gray-900">Couleur principale</legend>
            <p class="mt-1 text-xs text-gray-500">Elle sera utilisée pour les boutons, les liens actifs et les éléments importants.</p>

            <div class="mt-3 flex flex-wrap gap-3">
                <input type="hidden" name="theme_color" value="{{ old('theme_color', $user->themeColor()) }}" data-theme-color-field>

                @foreach ($colors as $value => $label)
                    <label class="appearance-color" title="{{ $label }}">
                        <input type="radio" name="theme_color" value="{{ $value }}" class="sr-only"
                            data-theme-color-option
                            @checked(strtolower(old('theme_color', $user->themeColor())) === $value)>
                        <span class="appearance-color-swatch" style="--swatch: {{ $value }}"></span>
                        <span class="sr-only">{{ $label }}</span>
                    </label>
                @endforeach

                <label class="appearance-color appearance-color-custom" title="Couleur personnalisée">
                    <input type="color" value="{{ old('theme_color', $user->themeColor()) }}"
                        data-custom-theme-color aria-label="Choisir une couleur personnalisée">
                    <span class="text-xs font-medium">Autre</span>
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('theme_color')" />
        </fieldset>

        <div class="appearance-preview" data-appearance-preview>
            <div>
                <p class="font-semibold text-[var(--text-strong)]">Aperçu du thème</p>
                <p class="mt-1 text-sm text-[var(--muted)]">Voici comment vos éléments principaux seront affichés.</p>
            </div>
            <button type="button" class="btn-primary">Bouton principal</button>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Enregistrer l’apparence</x-primary-button>

            @if (session('status') === 'appearance-updated')
                <p class="flash-message text-sm font-medium text-green-600" data-flash-message role="status">
                    Préférences enregistrées.
                </p>
            @endif
        </div>
    </form>
</section>
