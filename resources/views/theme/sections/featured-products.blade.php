@if ($products->isNotEmpty())
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Produk Terbaru</h2>
                <p class="text-gray-500 mt-1 text-sm">Pilihan terkini yang akan Anda sukai</p>
            </div>
            <a href="{{ route('collections.show', 'all') }}"
               class="hidden md:inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                Lihat semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-8">
            @foreach ($products as $product)
                @include('theme.snippets.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-10 text-center md:hidden">
            <a href="{{ route('collections.show', 'all') }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-sm font-medium text-gray-700 px-6 py-2.5 rounded-full hover:bg-gray-50 transition-colors">
                Lihat semua produk
            </a>
        </div>
    </div>
</section>
@endif
