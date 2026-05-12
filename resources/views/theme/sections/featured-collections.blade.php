@if ($collections->isNotEmpty())
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Belanja per Koleksi</h2>
                <p class="text-gray-500 mt-1 text-sm">Jelajahi koleksi pilihan kami</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($collections as $collection)
                @php
                    $imgSrc = null;
                    if ($collection->storeFile) {
                        $imgSrc = parse_url($collection->storeFile->url, PHP_URL_PATH)
                                  ?? '/storage/' . $collection->storeFile->path;
                    }
                @endphp
                <a href="{{ route('collections.show', $collection->handle) }}"
                   class="group relative rounded-2xl overflow-hidden bg-gray-100 aspect-square block">
                    @if ($imgSrc)
                        <img src="{{ $imgSrc }}" alt="{{ $collection->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-4">
                        <h3 class="text-white font-semibold text-sm md:text-base">{{ $collection->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
