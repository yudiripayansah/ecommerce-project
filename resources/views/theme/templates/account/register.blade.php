@extends('theme.layouts.app')

@section('title', 'Daftar Akun — ' . store_name())

@section('content')
<div class="min-h-[calc(100vh-9rem)] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Buat akun baru</h1>
            <p class="mt-1 text-sm text-gray-500">Sudah punya akun?
                <a href="{{ route('account.login') }}" class="text-gray-900 font-medium underline underline-offset-2">Masuk di sini</a>
            </p>
        </div>

        <form method="POST" action="{{ route('account.register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="name">Nama lengkap</label>
                <input
                    id="name" name="name" type="text"
                    value="{{ old('name') }}"
                    required autocomplete="name"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('name') border-red-400 @enderror"
                    placeholder="Nama kamu"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="phone">No. telepon <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input
                    id="phone" name="phone" type="tel"
                    value="{{ old('phone') }}"
                    autocomplete="tel"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('phone') border-red-400 @enderror"
                    placeholder="08xx-xxxx-xxxx"
                >
                @error('phone')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 @enderror"
                    placeholder="Min. 8 karakter"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password_confirmation">Konfirmasi password</label>
                <input
                    id="password_confirmation" name="password_confirmation" type="password"
                    required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                    placeholder="Ulangi password"
                >
            </div>

            <button type="submit" class="w-full theme-btn-primary font-medium text-sm rounded-lg px-4 py-3 transition-colors">
                Buat Akun
            </button>
        </form>

    </div>
</div>
@endsection
