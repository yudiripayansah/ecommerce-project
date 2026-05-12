@php
    // Build image list: featured image first, then remaining media
    $images = collect();
    if ($product->featuredImage) {
        $images->push($product->featuredImage);
    }
    foreach ($product->media as $m) {
        if (! $product->featuredImage || $m->id !== $product->featuredImage->id) {
            $images->push($m);
        }
    }

    $hasVariants = $product->option1_name || $product->option2_name || $product->option3_name;
    $firstVariant = $product->variants->first();
@endphp

<section
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14"
    x-data="productPage({
        variants: @js($product->variants),
        option1Name: @js($product->option1_name),
        option2Name: @js($product->option2_name),
        option3Name: @js($product->option3_name),
    })"
    x-init="init()"
>
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <a href="{{ route('home') }}" class="hover:text-gray-600">Beranda</a>
        <span>/</span>
        <a href="{{ route('collections.show', 'all') }}" class="hover:text-gray-600">Produk</a>
        <span>/</span>
        <span class="text-gray-700">{{ $product->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">

        {{-- Gallery --}}
        <div x-data="{ active: 0 }" class="flex flex-col-reverse md:flex-row gap-3">
            {{-- Thumbnails --}}
            @if ($images->count() > 1)
                <div class="flex md:flex-col gap-2 overflow-x-auto md:overflow-y-auto md:max-h-[560px] shrink-0">
                    @foreach ($images as $i => $img)
                        @php $imgSrc = parse_url($img->url, PHP_URL_PATH) ?? '/storage/' . $img->path; @endphp
                        <button
                            @click="active = {{ $i }}"
                            :class="active === {{ $i }} ? 'ring-2 ring-gray-900' : 'ring-1 ring-gray-200 hover:ring-gray-400'"
                            class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 transition-all"
                        >
                            <img src="{{ $imgSrc }}" alt="{{ $img->alt ?? $product->title }}"
                                 class="w-full h-full object-cover" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Main image --}}
            <div class="flex-1 rounded-2xl overflow-hidden bg-gray-100 aspect-square">
                @foreach ($images as $i => $img)
                    @php $imgSrc = parse_url($img->url, PHP_URL_PATH) ?? '/storage/' . $img->path; @endphp
                    <img
                        src="{{ $imgSrc }}"
                        alt="{{ $img->alt ?? $product->title }}"
                        x-show="active === {{ $i }}"
                        class="w-full h-full object-contain"
                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                    >
                @endforeach
                @if ($images->isEmpty())
                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" stroke-width="0.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        {{-- Product info --}}
        <div class="flex flex-col">
            @if ($product->vendor)
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">{{ $product->vendor }}</p>
            @endif

            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">{{ $product->title }}</h1>

            {{-- Price --}}
            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-2xl font-bold" x-text="formatPrice(currentPrice)">
                    {{ rupiah($firstVariant?->price ?? $product->price) }}
                </span>
                @if ($product->compare_at_price && $product->compare_at_price > $product->price)
                    <span class="text-base text-gray-400 line-through">
                        {{ rupiah($product->compare_at_price) }}
                    </span>
                    <span class="text-xs font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded">
                        {{ round((1 - $product->price / $product->compare_at_price) * 100) }}% OFF
                    </span>
                @endif
            </div>

            {{-- Add to cart form --}}
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf

                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" :value="currentVariantId">

                {{-- Variant options --}}
                @if ($hasVariants)
                    <div class="space-y-4 mb-6">
                        @if ($product->option1_name)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ $product->option1_name }}:
                                    <span class="font-normal text-gray-500" x-text="selected.option1"></span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="val in uniqueOption1" :key="val">
                                        <button
                                            type="button"
                                            @click="selected.option1 = val; findVariant()"
                                            :class="selected.option1 === val
                                                ? 'theme-variant-selected'
                                                : 'bg-white text-gray-700 border-gray-300 hover:border-gray-600'"
                                            class="px-4 py-2 text-sm border rounded-lg transition-all"
                                            x-text="val"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        @endif

                        @if ($product->option2_name)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ $product->option2_name }}:
                                    <span class="font-normal text-gray-500" x-text="selected.option2"></span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="val in uniqueOption2" :key="val">
                                        <button
                                            type="button"
                                            @click="selected.option2 = val; findVariant()"
                                            :class="selected.option2 === val
                                                ? 'theme-variant-selected'
                                                : 'bg-white text-gray-700 border-gray-300 hover:border-gray-600'"
                                            class="px-4 py-2 text-sm border rounded-lg transition-all"
                                            x-text="val"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        @endif

                        @if ($product->option3_name)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ $product->option3_name }}:
                                    <span class="font-normal text-gray-500" x-text="selected.option3"></span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="val in uniqueOption3" :key="val">
                                        <button
                                            type="button"
                                            @click="selected.option3 = val; findVariant()"
                                            :class="selected.option3 === val
                                                ? 'theme-variant-selected'
                                                : 'bg-white text-gray-700 border-gray-300 hover:border-gray-600'"
                                            class="px-4 py-2 text-sm border rounded-lg transition-all"
                                            x-text="val"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Quantity --}}
                <div class="flex items-center gap-4 mb-4">
                    <label class="text-sm font-semibold text-gray-700">Jumlah</label>
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)"
                                class="px-4 py-2.5 text-gray-600 hover:bg-gray-50 text-base font-medium transition-colors">−</button>
                        <input type="number" name="quantity" x-model="quantity"
                               min="1" max="99"
                               class="w-12 text-center text-sm font-medium py-2.5 border-0 focus:outline-none [appearance:textfield]">
                        <button type="button" @click="quantity = Math.min(99, quantity + 1)"
                                class="px-4 py-2.5 text-gray-600 hover:bg-gray-50 text-base font-medium transition-colors">+</button>
                    </div>
                </div>

                {{-- Submit buttons --}}
                <div class="flex flex-col gap-3">
                    <button type="submit"
                            class="w-full theme-btn-primary font-semibold py-3.5 rounded-xl text-sm">
                        Tambah ke Keranjang
                    </button>
                    <button type="submit" name="redirect_to_cart" value="1"
                            class="w-full border border-gray-300 text-gray-900 font-semibold py-3.5 rounded-xl hover:bg-gray-50 transition-colors text-sm">
                        Beli Sekarang
                    </button>
                </div>
            </form>

            {{-- Description --}}
            @if ($product->description)
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Deskripsi Produk</h2>
                    <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                        {!! $product->description !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
function productPage({ variants, option1Name, option2Name, option3Name }) {
    return {
        variants,
        option1Name,
        option2Name,
        option3Name,
        selected: { option1: null, option2: null, option3: null },
        selectedVariant: null,
        quantity: 1,

        get uniqueOption1() {
            return [...new Set(this.variants.map(v => v.option1).filter(Boolean))]
        },
        get uniqueOption2() {
            return [...new Set(this.variants.map(v => v.option2).filter(Boolean))]
        },
        get uniqueOption3() {
            return [...new Set(this.variants.map(v => v.option3).filter(Boolean))]
        },
        get currentPrice() {
            return this.selectedVariant?.price ?? this.variants[0]?.price ?? 0
        },
        get currentVariantId() {
            return this.selectedVariant?.id ?? this.variants[0]?.id ?? null
        },

        findVariant() {
            this.selectedVariant = this.variants.find(v =>
                (!this.option1Name || v.option1 === this.selected.option1) &&
                (!this.option2Name || v.option2 === this.selected.option2) &&
                (!this.option3Name || v.option3 === this.selected.option3)
            ) ?? null
        },

        formatPrice(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price)
        },

        init() {
            if (this.variants.length > 0) {
                const first = this.variants[0]
                this.selected.option1 = first.option1 ?? null
                this.selected.option2 = first.option2 ?? null
                this.selected.option3 = first.option3 ?? null
                this.selectedVariant = first
            }
        }
    }
}
</script>
@endpush
