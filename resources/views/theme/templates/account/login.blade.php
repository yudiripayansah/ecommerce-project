@extends('theme.layouts.app')

@section('title', 'Login — ' . config('app.name'))

@section('content')
<div class="min-h-[calc(100vh-9rem)] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Masuk ke akun</h1>
            <p class="mt-1 text-sm text-gray-500">Belum punya akun?
                <a href="{{ route('account.register') }}" class="text-gray-900 font-medium underline underline-offset-2">Daftar sekarang</a>
            </p>
        </div>

        <form method="POST" action="{{ route('account.login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">Email</label>
                <input
                    id="email" name="email" type="email"
                    value="{{ old('email') }}"
                    required autocomplete="email"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('email') border-red-400 @enderror"
                    placeholder="kamu@email.com"
                >
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    required autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 @enderror"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                <label for="remember" class="text-sm text-gray-600 select-none">Ingat saya</label>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white font-medium text-sm rounded-lg px-4 py-3 hover:bg-gray-700 transition-colors">
                Masuk
            </button>
        </form>

    </div>
</div>
@endsection
