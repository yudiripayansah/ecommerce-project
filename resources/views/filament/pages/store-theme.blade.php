<x-filament-panels::page>

<style>
/* Layout helpers not guaranteed in Filament's Tailwind bundle */
.st-active-card {
    display: flex;
    flex-direction: column;
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgb(229 231 235);
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.dark .st-active-card {
    border-color: rgb(55 65 81);
    background: rgb(17 24 39);
}
@media (min-width: 1024px) {
    .st-active-card { flex-direction: row; }
    .st-active-preview { width: 60%; }
    .st-active-info { width: 40%; }
}

.st-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 640px)  { .st-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .st-grid { grid-template-columns: repeat(3, 1fr); } }

.st-card {
    display: flex;
    flex-direction: column;
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgb(229 231 235);
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    transition: box-shadow .2s, border-color .2s;
}
.st-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    border-color: rgb(156 163 175);
}
.dark .st-card {
    border-color: rgb(55 65 81);
    background: rgb(17 24 39);
}
.dark .st-card:hover { border-color: rgb(75 85 99); }

.st-preview-bg {
    background: rgb(245 245 244);
    padding: 1rem 1rem 0;
}
.dark .st-preview-bg { background: rgb(38 38 38); }

.st-check-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    color: rgb(16 185 129); /* emerald-500 */
}
.st-check-icon-sm {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
    color: rgb(156 163 175);
}

.st-activate-btn {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .4rem .9rem;
    font-size: .75rem;
    font-weight: 600;
    border-radius: .5rem;
    border: 1px solid rgb(209 213 219);
    background: #fff;
    color: rgb(55 65 81);
    cursor: pointer;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.st-activate-btn:hover {
    background: rgb(249 250 251);
    border-color: rgb(156 163 175);
}
.st-activate-btn:disabled { opacity: .5; cursor: not-allowed; }
.dark .st-activate-btn {
    background: rgb(31 41 55);
    border-color: rgb(75 85 99);
    color: rgb(209 213 219);
}
.dark .st-activate-btn:hover {
    background: rgb(55 65 81);
    border-color: rgb(107 114 128);
}

.st-badge-active {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .3rem .75rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .02em;
    border-radius: 9999px;
    color: rgb(4 120 87);
    background: rgb(236 253 245);
    border: 1px solid rgb(167 243 208);
}
.dark .st-badge-active {
    color: rgb(52 211 153);
    background: rgb(6 78 59 / .3);
    border-color: rgb(6 78 59);
}

.st-section-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgb(156 163 175);
    margin-bottom: 1rem;
}
.dark .st-section-label { color: rgb(107 114 128); }

.st-divider {
    border-top: 1px solid rgb(243 244 246);
    margin-top: 1.25rem;
    padding-top: 1.25rem;
}
.dark .st-divider { border-color: rgb(31 41 55); }
</style>

@php
    $themes = $this->themes();
    $active = $themes[$this->activeTheme] ?? $themes['minimal'];
    $p      = $active['preview'];
    $others = array_filter($themes, fn($k) => $k !== $this->activeTheme, ARRAY_FILTER_USE_KEY);
@endphp

{{-- ── Active theme ────────────────────────────────────────── --}}
<div style="margin-bottom: 2.5rem;">
    <p class="st-section-label">Tema Aktif</p>

    <div class="st-active-card">

        {{-- Preview --}}
        <div class="st-active-preview st-preview-bg">
            @include('filament.pages.partials.theme-mockup', ['p' => $p, 'name' => $active['label'], 'tall' => true])
        </div>

        {{-- Info --}}
        <div class="st-active-info" style="padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between; gap: 1.5rem;">
            <div>
                <div style="margin-bottom: 1rem;">
                    <span class="st-badge-active">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: currentColor; display: inline-block; flex-shrink: 0;"></span>
                        Sedang Digunakan
                    </span>
                </div>

                <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: .4rem;" class="text-gray-900 dark:text-white">
                    {{ $active['label'] }}
                </h2>
                <p style="font-size: .875rem; line-height: 1.6;" class="text-gray-500 dark:text-gray-400">
                    {{ $active['desc'] }}
                </p>

                <ul style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: .6rem;">
                    @foreach ($active['features'] as $feat)
                        <li style="display: flex; align-items: center; gap: .5rem; font-size: .875rem;" class="text-gray-700 dark:text-gray-300">
                            <svg class="st-check-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:14px;height:14px;flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            {{ $feat }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="st-divider">
                <p style="font-size: .75rem;" class="text-gray-400 dark:text-gray-500">
                    Mengubah tema akan langsung memengaruhi tampilan toko.
                </p>
            </div>
        </div>

    </div>
</div>

{{-- ── Theme library ───────────────────────────────────────── --}}
<div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <p class="st-section-label" style="margin-bottom: 0;">Library Tema</p>
        <span style="font-size: .75rem;" class="text-gray-400 dark:text-gray-500">
            {{ count($others) }} tema tersedia
        </span>
    </div>

    <div class="st-grid">
        @foreach ($others as $key => $theme)
            @php $tp = $theme['preview']; @endphp

            <div class="st-card">
                {{-- Preview --}}
                <div class="st-preview-bg">
                    @include('filament.pages.partials.theme-mockup', ['p' => $tp, 'name' => $theme['label'], 'tall' => false])
                </div>

                {{-- Info + action --}}
                <div style="padding: 1rem 1.25rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex: 1;">
                    <div style="min-width: 0;">
                        <p style="font-size: .875rem; font-weight: 700; margin-bottom: .25rem;" class="text-gray-900 dark:text-white">
                            {{ $theme['label'] }}
                        </p>
                        <p style="font-size: .75rem; line-height: 1.5; margin-bottom: .75rem;" class="text-gray-500 dark:text-gray-400">
                            {{ $theme['desc'] }}
                        </p>
                        <ul style="display: flex; flex-direction: column; gap: .35rem;">
                            @foreach ($theme['features'] as $feat)
                                <li style="display: flex; align-items: center; gap: .4rem; font-size: .7rem;" class="text-gray-500 dark:text-gray-400">
                                    <svg class="st-check-icon-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:12px;height:12px;flex-shrink:0;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    {{ $feat }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <button
                        type="button"
                        wire:click="activateTheme('{{ $key }}')"
                        wire:loading.attr="disabled"
                        wire:target="activateTheme('{{ $key }}')"
                        class="st-activate-btn"
                    >
                        <span wire:loading.remove wire:target="activateTheme('{{ $key }}')">Aktifkan</span>
                        <span wire:loading wire:target="activateTheme('{{ $key }}')">
                            <svg style="width:12px;height:12px;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 70" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<x-filament-actions::modals />
</x-filament-panels::page>
