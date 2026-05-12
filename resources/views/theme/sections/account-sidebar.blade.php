@php
    $customer = Auth::guard('customer')->user();
    $currentRoute = request()->route()->getName();
@endphp

<aside class="w-full lg:w-64 shrink-0">
    {{-- Profile card --}}
    <div class="bg-gray-50 rounded-2xl p-5 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center text-white font-semibold text-sm shrink-0">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $customer->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $customer->email }}</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="space-y-1">
        @php
            $links = [
                ['route' => 'account.index',     'label' => 'Dashboard',  'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
                ['route' => 'account.orders',    'label' => 'Pesanan saya', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
                ['route' => 'account.addresses', 'label' => 'Alamat',      'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
            ];
        @endphp

        @foreach ($links as $link)
            @php $active = str_starts_with($currentRoute, $link['route']); @endphp
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ $active ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/>
                </svg>
                {{ $link['label'] }}
            </a>
        @endforeach

        <div class="pt-2 border-t border-gray-100 mt-2">
            <form method="POST" action="{{ route('account.logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </nav>
</aside>
