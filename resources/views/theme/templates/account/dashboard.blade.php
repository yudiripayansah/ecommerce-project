@extends('theme.layouts.app')

@section('title', 'Akun Saya — ' . config('app.name'))

@section('content')
<x-account-layout>

    <div class="flex flex-col gap-8">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">Total Pesanan</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $customer->orders->count() }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">Total Belanja</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ rupiah($customer->orders->sum('total')) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 col-span-2 sm:col-span-1">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">Bergabung</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ $customer->created_at->format('M Y') }}</p>
            </div>
        </div>

        {{-- Edit Profile --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Informasi Profil</h2>
            </div>
            <form method="POST" action="{{ route('account.profile.update') }}" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="name">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $customer->name) }}" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="phone">No. telepon</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('phone') border-red-400 @enderror"
                            placeholder="08xx-xxxx-xxxx">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('email') border-red-400 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-gray-900 text-white font-medium text-sm rounded-lg px-6 py-2.5 hover:bg-gray-700 transition-colors">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Ubah Password</h2>
            </div>
            <form method="POST" action="{{ route('account.password.update') }}" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="current_password">Password saat ini</label>
                    <input id="current_password" name="current_password" type="password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('current_password') border-red-400 @enderror"
                        placeholder="••••••••">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="new_password">Password baru</label>
                        <input id="new_password" name="password" type="password"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('password') border-red-400 @enderror"
                            placeholder="Min. 8 karakter">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password_confirmation">Konfirmasi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                            placeholder="Ulangi password baru">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-gray-900 text-white font-medium text-sm rounded-lg px-6 py-2.5 hover:bg-gray-700 transition-colors">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>

    </div>

</x-account-layout>
@endsection
