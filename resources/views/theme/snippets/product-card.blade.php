{{--
    Props:
    $product  - Product model (with featuredImage)
    $showBadge - bool, default false
--}}
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
    {{-- Image --}}
    <div class="relative aspect-square bg-gray-100 rounded-xl overflow-hidden mb-3">
        @if ($src)
            <img
                src="{{ $src }}"
                alt="{{ $product->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
            </div>
        @endif

        @if ($product->compare_at_price && $product->compare_at_price > $product->price)
            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">SALE</span>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex flex-col gap-0.5">
        @if ($product->vendor)
            <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $product->vendor }}</p>
        @endif
        <h3 class="text-sm font-medium text-gray-900 group-hover:underline underline-offset-2 line-clamp-2">
            {{ $product->title }}
        </h3>
        <div class="flex items-baseline gap-2 mt-1">
            <span class="text-sm font-semibold">{{ rupiah($product->price) }}</span>
            @if ($product->compare_at_price && $product->compare_at_price > $product->price)
                <span class="text-xs text-gray-400 line-through">{{ rupiah($product->compare_at_price) }}</span>
            @endif
        </div>
    </div>
</a>
