<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Store'))</title>
    <meta name="description" content="@yield('meta_description', '')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    class="bg-white text-gray-900 antialiased min-h-screen flex flex-col"
    x-data="{ cartOpen: false, mobileMenuOpen: false, searchOpen: false }"
>
    @include('theme.sections.header')

    @if (session('cart_success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition
            class="fixed top-20 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-3 rounded-lg shadow-lg max-w-xs"
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
