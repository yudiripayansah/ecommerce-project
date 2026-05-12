@php
    $drawerCart = session('cart', []);
    $drawerTotal = collect($drawerCart)->sum(fn ($i) => $i['price'] * $i['quantity']);
@endphp

{{-- Backdrop --}}
<div
    x-show="cartOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="cartOpen = false"
    class="fixed inset-0 z-50 bg-black/40"
    style="display: none;"
></div>

{{-- Drawer --}}
<div
    x-show="cartOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed right-0 top-0 h-full w-full max-w-md z-50 bg-white shadow-2xl flex flex-col"
    style="display: none;"
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
        <h2 class="text-base font-semibold">Keranjang Belanja</h2>
        <button @click="cartOpen = false" class="p-1 text-gray-500 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Items --}}
    <div class="flex-1 overflow-y-auto px-5 py-4">
        @forelse ($drawerCart as $key => $item)
            <div class="flex gap-4 py-4 border-b border-gray-100 last:border-0">
                <a href="{{ route('products.show', $item['handle']) }}" class="shrink-0">
                    @if ($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                             class="w-16 h-16 object-cover rounded-lg bg-gray-100">
                    @else
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                    @endif
                </a>

                <div class="flex-1 min-w-0">
                    <a href="{{ route('products.show', $item['handle']) }}"
                       class="text-sm font-medium text-gray-900 hover:underline line-clamp-2">{{ $item['title'] }}</a>
                    @if ($item['variant_title'])
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item['variant_title'] }}</p>
                    @endif
                    <p class="text-sm font-semibold mt-1">{{ rupiah($item['price']) }}</p>

                    <div class="flex items-center gap-3 mt-2">
                        <form method="POST" action="{{ route('cart.update', $key) }}" class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                            @csrf
                            <button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}"
                                    class="px-2 py-1 text-gray-600 hover:bg-gray-50 text-sm">−</button>
                            <span class="px-2 py-1 text-sm font-medium min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                            <button type="submit" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}"
                                    class="px-2 py-1 text-gray-600 hover:bg-gray-50 text-sm">+</button>
                        </form>

                        <form method="POST" action="{{ route('cart.remove', $key) }}">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <p class="text-gray-500 text-sm">Keranjang Anda kosong</p>
                <a href="{{ route('collections.show', 'all') }}" @click="cartOpen = false"
                   class="mt-4 text-sm font-medium text-gray-900 underline underline-offset-2">
                    Lanjutkan belanja
                </a>
            </div>
        @endforelse
    </div>

    {{-- Footer --}}
    @if (count($drawerCart) > 0)
        <div class="px-5 py-4 border-t border-gray-200 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold">{{ rupiah($drawerTotal) }}</span>
            </div>
            <p class="text-xs text-gray-400">Pajak dan ongkos kirim dihitung saat checkout.</p>
            <a href="{{ route('cart') }}" @click="cartOpen = false"
               class="block w-full text-center bg-gray-900 text-white text-sm font-semibold py-3 rounded-xl hover:bg-gray-700 transition-colors">
                Lihat Keranjang
            </a>
            <a href="{{ route('checkout') }}" @click="cartOpen = false"
               class="block w-full text-center border border-gray-200 text-gray-900 text-sm font-semibold py-3 rounded-xl hover:bg-gray-50 transition-colors">
                Checkout
            </a>
        </div>
    @endif
</div>
