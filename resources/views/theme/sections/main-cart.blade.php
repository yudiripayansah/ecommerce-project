<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Keranjang Belanja</h1>

    @if (count($cart) > 0)
        <div class="lg:grid lg:grid-cols-3 lg:gap-10">

            {{-- Produk --}}
            <div class="lg:col-span-2">
                <div class="divide-y divide-gray-100">
                    @foreach ($cart as $key => $item)
                        <div class="flex gap-5 py-5">
                            {{-- Gambar --}}
                            <a href="{{ route('products.show', $item['handle']) }}" class="shrink-0">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                                         class="w-20 h-20 object-cover rounded-xl bg-gray-100">
                                @else
                                    <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-gray-300">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>

                            {{-- Detail --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('products.show', $item['handle']) }}"
                                           class="text-sm font-semibold text-gray-900 hover:underline">
                                            {{ $item['title'] }}
                                        </a>
                                        @if ($item['variant_title'])
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item['variant_title'] }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 shrink-0">
                                        {{ rupiah($item['price'] * $item['quantity']) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-4 mt-3">
                                    {{-- Kontrol jumlah --}}
                                    <form method="POST" action="{{ route('cart.update', $key) }}" class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        @csrf
                                        <button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}"
                                                class="px-3 py-1.5 text-gray-600 hover:bg-gray-50 text-sm transition-colors">−</button>
                                        <span class="px-3 py-1.5 text-sm font-medium min-w-[2.5rem] text-center">{{ $item['quantity'] }}</span>
                                        <button type="submit" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}"
                                                class="px-3 py-1.5 text-gray-600 hover:bg-gray-50 text-sm transition-colors">+</button>
                                    </form>

                                    <span class="text-xs text-gray-400">
                                        {{ rupiah($item['price']) }} / item
                                    </span>

                                    <form method="POST" action="{{ route('cart.remove', $key) }}" class="ml-auto">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Lanjutkan belanja --}}
                <div class="mt-4">
                    <a href="{{ route('collections.show', 'all') }}"
                       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Lanjutkan belanja
                    </a>
                </div>
            </div>

            {{-- Ringkasan pesanan --}}
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <div class="bg-gray-50 rounded-2xl p-6 sticky top-24">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Subtotal</dt>
                            <dd class="font-medium">{{ rupiah($total) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Ongkos Kirim</dt>
                            <dd class="text-gray-500">Dihitung saat checkout</dd>
                        </div>
                    </dl>

                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between text-base font-bold">
                        <span>Total</span>
                        <span>{{ rupiah($total) }}</span>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">Pajak dan biaya dihitung saat checkout.</p>

                    <a href="{{ route('checkout') }}"
                       class="mt-5 block w-full text-center theme-btn-primary font-semibold py-3.5 rounded-xl transition-colors text-sm">
                        Lanjutkan ke Checkout
                    </a>

                    <form method="POST" action="{{ route('cart.clear') }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                class="w-full text-center text-xs text-gray-400 hover:text-red-500 py-1 transition-colors">
                            Kosongkan keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="py-24 text-center">
            <svg class="w-16 h-16 mx-auto mb-6 text-gray-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Anda kosong</h2>
            <p class="text-gray-400 text-sm mb-6">Sepertinya belum ada yang ditambahkan.</p>
            <a href="{{ route('collections.show', 'all') }}"
               class="inline-flex items-center gap-2 theme-btn-primary text-sm font-semibold px-6 py-3 rounded-full transition-colors">
                Mulai belanja
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
    @endif
</section>
