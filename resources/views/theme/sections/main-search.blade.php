<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Form pencarian --}}
    <form method="GET" action="{{ route('search') }}" class="mb-8">
        <div class="flex gap-3">
            <div class="flex-1 flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus-within:ring-2 focus-within:ring-gray-300 focus-within:border-gray-300 transition">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Cari produk, merek…"
                    autofocus
                    class="flex-1 bg-transparent text-sm text-gray-900 placeholder-gray-400 focus:outline-none"
                >
            </div>
            <button type="submit"
                    class="px-5 py-3 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-700 transition-colors shrink-0">
                Cari
            </button>
        </div>
    </form>

    @if ($q !== '')
        {{-- Toolbar --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    Hasil untuk <span class="text-gray-500 font-normal">"{{ $q }}"</span>
                </h1>
                @if ($products->total() > 0)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $products->total() }} produk ditemukan</p>
                @endif
            </div>

            @if ($products->total() > 0)
                <form method="GET" id="search-sort-form">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <select name="sort" onchange="document.getElementById('search-sort-form').submit()"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>Terbaru</option>
                        <option value="title-asc" {{ $sort === 'title-asc' ? 'selected' : '' }}>Nama A–Z</option>
                        <option value="title-desc" {{ $sort === 'title-desc' ? 'selected' : '' }}>Nama Z–A</option>
                        <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>Harga: Termurah</option>
                        <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>Harga: Termahal</option>
                    </select>
                </form>
            @endif
        </div>

        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-8">
                @foreach ($products as $product)
                    @include('theme.snippets.product-card', ['product' => $product])
                @endforeach
            </div>

            {{ $products->links('theme.snippets.pagination') }}
        @else
            <div class="py-20 text-center text-gray-400">
                <svg class="w-14 h-14 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <p class="text-base font-medium text-gray-600 mb-1">Produk tidak ditemukan</p>
                <p class="text-sm">Coba kata kunci lain atau jelajahi koleksi kami.</p>
                <a href="{{ route('collections.show', 'all') }}"
                   class="inline-flex items-center gap-2 mt-6 bg-gray-900 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-gray-700 transition-colors">
                    Lihat semua produk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        @endif
    @else
        {{-- Belum ada query --}}
        <div class="py-20 text-center text-gray-400">
            <svg class="w-14 h-14 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <p class="text-base font-medium text-gray-600 mb-1">Cari di toko kami</p>
            <p class="text-sm">Ketik kata kunci di atas untuk menemukan produk.</p>
        </div>
    @endif
</section>
