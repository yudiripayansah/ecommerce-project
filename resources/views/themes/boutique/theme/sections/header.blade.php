@php
    $cartCount = array_sum(array_column(session('cart', []), 'quantity'));
@endphp

<header class="sticky top-0 z-40 bg-stone-50 border-b border-stone-200">
    {{-- Top bar: nav left | logo center | icons right --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 items-center h-16">

            {{-- Left: Desktop nav --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-medium tracking-widest uppercase">
                <a href="{{ route('home') }}" class="text-stone-500 hover:text-stone-900 transition-colors">Beranda</a>
                <a href="{{ route('collections.show', 'all') }}" class="text-stone-500 hover:text-stone-900 transition-colors">Koleksi</a>
                <a href="{{ route('collections.show', 'all') }}" class="text-stone-500 hover:text-stone-900 transition-colors">Produk</a>
            </nav>

            {{-- Mobile: hamburger on left --}}
            <div class="flex md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 -ml-2 text-stone-600" aria-label="Toggle menu">
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Center: Logo --}}
            <div class="flex justify-center">
                @php $logoUrl = store_logo_url(); @endphp
                <a href="{{ route('home') }}" class="flex items-center">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ store_name() }}" class="h-8 w-auto object-contain">
                    @else
                        <span class="font-bold text-lg tracking-tight text-stone-900">{{ store_name() }}</span>
                    @endif
                </a>
            </div>

            {{-- Right: Icons --}}
            <div class="flex items-center justify-end gap-1">
                <button @click="searchOpen = !searchOpen" class="p-2 text-stone-600 hover:text-stone-900 transition-colors" aria-label="Toggle search">
                    <svg x-show="!searchOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <svg x-show="searchOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>

                @if (Auth::guard('customer')->check())
                <a href="{{ route('account.index') }}" class="p-2 text-stone-600 hover:text-stone-900 transition-colors" aria-label="My account">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </a>
                @else
                <a href="{{ route('account.login') }}" class="p-2 text-stone-600 hover:text-stone-900 transition-colors" aria-label="Login">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                </a>
                @endif

                <button @click="cartOpen = true" class="relative p-2 text-stone-600 hover:text-stone-900 transition-colors -mr-2" aria-label="Open cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-0.5 right-0 w-4 h-4 bg-stone-900 text-stone-50 text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ $cartCount > 9 ? '9+' : $cartCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Search bar --}}
    <div x-show="searchOpen"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
        class="border-t border-stone-200 bg-stone-50">
        <form method="GET" action="{{ route('search') }}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center gap-3 bg-white rounded-xl border border-stone-200 px-4 py-2.5 focus-within:ring-2 focus-within:ring-stone-400">
                <svg class="w-4 h-4 text-stone-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
                    class="flex-1 bg-transparent text-sm text-stone-900 placeholder-stone-400 focus:outline-none"
                    x-ref="searchInput" x-init="$watch('searchOpen', v => v && $nextTick(() => $refs.searchInput.focus()))">
                <button type="submit" class="text-xs font-medium text-stone-400 hover:text-stone-900 transition-colors shrink-0 uppercase tracking-wider">Cari</button>
            </div>
        </form>
    </div>

    {{-- Mobile nav --}}
    <div x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden border-t border-stone-200 bg-stone-50">
        <nav class="px-4 py-5 flex flex-col gap-4 text-xs font-medium uppercase tracking-widest">
            <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="text-stone-600 hover:text-stone-900">Beranda</a>
            <a href="{{ route('collections.show', 'all') }}" @click="mobileMenuOpen = false" class="text-stone-600 hover:text-stone-900">Koleksi</a>
            <a href="{{ route('collections.show', 'all') }}" @click="mobileMenuOpen = false" class="text-stone-600 hover:text-stone-900">Produk</a>
        </nav>
    </div>
</header>
