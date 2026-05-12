@php
    $customer = Auth::guard('customer')->user();
    $currentRoute = request()->route()->getName();
@endphp

<div class="bg-gray-50 min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-12">
    <div style="display:flex; flex-wrap:wrap; gap:2rem; align-items:flex-start;">

        {{-- Sidebar --}}
        <aside style="flex:0 0 224px; min-width:200px; max-width:100%;">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-widest font-medium">Akun</p>
                    <p class="font-semibold text-gray-900 mt-0.5 truncate">{{ $customer->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $customer->email }}</p>
                </div>
                <nav class="p-2 flex flex-col gap-0.5">
                    <a href="{{ route('account.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $currentRoute === 'account.index' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" style="flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <a href="{{ route('account.orders') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'account.orders') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" style="flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                        Riwayat Pesanan
                    </a>
                    <a href="{{ route('account.addresses') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'account.addresses') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-4 h-4" style="flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        Alamat Saya
                    </a>
                </nav>
                <div class="p-2 border-t border-gray-100">
                    <form method="POST" action="{{ route('account.logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" style="flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <main style="flex:1 1 0%; min-width:0;">
            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-4 h-4 text-green-500" style="flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{ $slot }}
        </main>

    </div>
</div>
</div>
