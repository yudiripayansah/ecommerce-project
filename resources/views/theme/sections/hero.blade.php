<section class="relative bg-gray-900 text-white overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 opacity-90"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 md:py-40">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Produk Terbaru</p>
            <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                Temukan<br>Koleksi<br>Terbaru Kami
            </h1>
            <p class="text-lg text-gray-300 mb-8 leading-relaxed">
                Jelajahi pilihan produk premium kami,<br class="hidden md:block">
                dibuat dengan kualitas terbaik dan dikirim langsung ke pintu Anda.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('collections.show', 'all') }}"
                   class="inline-flex items-center gap-2 bg-white text-gray-900 font-semibold px-6 py-3 rounded-full hover:bg-gray-100 transition-colors text-sm">
                    Belanja Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
