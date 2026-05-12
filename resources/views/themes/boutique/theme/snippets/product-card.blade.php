@php
    $src = null;
    if ($product->featuredImage) {
        $src = parse_url($product->featuredImage->url, PHP_URL_PATH) ?? '/storage/' . $product->featuredImage->path;
    } elseif ($product->media->isNotEmpty()) {
        $first = $product->media->first();
        $src = parse_url($first->url, PHP_URL_PATH) ?? '/storage/' . $first->path;
    }
@endphp

<a href="{{ route('products.show', $product->handle) }}" class="group flex flex-col">
    {{-- Portrait ratio image --}}
    <div class="relative aspect-[3/4] bg-stone-100 overflow-hidden mb-3">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $product->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                loading="lazy">

            {{-- Hover overlay with CTA --}}
            <div class="absolute inset-0 bg-stone-900/0 group-hover:bg-stone-900/20 transition-colors duration-300 flex items-end justify-center pb-4 opacity-0 group-hover:opacity-100">
                <span class="bg-white text-stone-900 text-[10px] font-bold uppercase tracking-widest px-4 py-2">
                    Lihat Produk
                </span>
            </div>
        @else
            <div class="w-full h-full flex items-center justify-center text-stone-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
            </div>
        @endif

        @if ($product->compare_at_price && $product->compare_at_price > $product->price)
            <span class="absolute top-2 left-2 bg-stone-900 text-stone-50 text-[9px] font-bold px-2 py-0.5 uppercase tracking-widest">SALE</span>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex flex-col gap-1 text-center">
        @if ($product->vendor)
            <p class="text-[10px] text-stone-400 uppercase tracking-widest">{{ $product->vendor }}</p>
        @endif
        <h3 class="text-sm text-stone-800 leading-snug line-clamp-2">{{ $product->title }}</h3>
        <div class="flex items-baseline justify-center gap-2 mt-0.5">
            <span class="text-sm font-semibold text-stone-900">{{ rupiah($product->price) }}</span>
            @if ($product->compare_at_price && $product->compare_at_price > $product->price)
                <span class="text-xs text-stone-400 line-through">{{ rupiah($product->compare_at_price) }}</span>
            @endif
        </div>
    </div>
</a>
