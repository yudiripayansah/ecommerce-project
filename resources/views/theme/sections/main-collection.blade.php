@php
    $imgSrc = null;
    if ($collection->storeFile) {
        $imgSrc = parse_url($collection->storeFile->url, PHP_URL_PATH)
                  ?? '/storage/' . $collection->storeFile->path;
    }
@endphp

{{-- Header koleksi --}}
<section class="relative {{ $imgSrc ? 'bg-gray-900' : 'bg-gray-50' }} overflow-hidden">
    @if ($imgSrc)
        <img src="{{ $imgSrc }}" alt="{{ $collection->title }}"
             class="absolute inset-0 w-full h-full object-cover opacity-30">
    @endif
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
        <h1 class="text-3xl md:text-5xl font-bold {{ $imgSrc ? 'text-white' : 'text-gray-900' }} mb-3">
            {{ $collection->title }}
        </h1>
        @if ($collection->description)
            <div class="mt-4 text-base {{ $imgSrc ? 'text-gray-300' : 'text-gray-600' }} max-w-2xl mx-auto prose prose-sm">
                {!! $collection->description !!}
            </div>
        @endif
    </div>
</section>

{{-- Produk --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">{{ $products->total() }} produk</p>
        <form method="GET" id="sort-form">
            <select name="sort" onchange="document.getElementById('sort-form').submit()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>Unggulan</option>
                <option value="title-asc" {{ $sort === 'title-asc' ? 'selected' : '' }}>Nama A–Z</option>
                <option value="title-desc" {{ $sort === 'title-desc' ? 'selected' : '' }}>Nama Z–A</option>
                <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>Harga: Termurah</option>
                <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>Harga: Termahal</option>
            </select>
        </form>
    </div>

    @if ($products->isNotEmpty())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-8">
            @foreach ($products as $product)
                @include('theme.snippets.product-card', ['product' => $product])
            @endforeach
        </div>

        {{ $products->links('theme.snippets.pagination') }}
    @else
        <div class="py-24 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
            <p class="text-sm">Belum ada produk dalam koleksi ini.</p>
        </div>
    @endif
</section>
