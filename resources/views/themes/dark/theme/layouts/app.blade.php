<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $faviconUrl = store_favicon_url(); @endphp
    @if ($faviconUrl)<link rel="icon" href="{{ $faviconUrl }}">@endif
    <title>@yield('title', store_name())</title>
    <meta name="description" content="@yield('meta_description', '')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        /* Dark theme — critical colors, no Tailwind compilation needed */
        :root {
            --dk-body:        #030712;
            --dk-body-text:   #f1f5f9;
            --dk-header:      #111827;
            --dk-border:      #1f2937;
            --dk-hero:        #030712;
            --dk-surface:     #1f2937;
            --dk-accent:      #6366f1;
            --dk-accent-h:    #4f46e5;
            --dk-muted:       #9ca3af;
            --dk-footer:      #111827;
        }
        body { background-color: var(--dk-body); color: var(--dk-body-text); }
    </style>
</head>
<body
    class="antialiased min-h-screen flex flex-col"
    x-data="{ cartOpen: false, mobileMenuOpen: false, searchOpen: false }"
>
    @include('theme.sections.header')

    @if (session('cart_success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition
            class="fixed top-20 right-4 z-50 text-white text-sm px-4 py-3 rounded-lg shadow-lg max-w-xs"
            style="background-color: var(--dk-accent);"
        >
            {{ session('cart_success') }}
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    @include('theme.sections.footer')
    @include('theme.snippets.cart-drawer')

    @stack('scripts')
</body>
</html>
