<section class="relative bg-gray-950 text-white overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/40 via-gray-950 to-purple-950/30"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(99,102,241,0.15),_transparent_60%)]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 md:py-44">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-400 mb-5">Koleksi Terbaru</p>
            <h1 class="text-5xl md:text-7xl font-black leading-[1.05] mb-6 text-white">
                Temukan<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Koleksi</span><br>
                Terbaru Kami
            </h1>
            <p class="text-base text-gray-400 mb-10 leading-relaxed max-w-lg">
                Jelajahi pilihan produk premium kami, dibuat dengan kualitas terbaik
                dan dikirim langsung ke pintu Anda.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('collections.show', 'all') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-7 py-3.5 rounded-full transition-colors text-sm">
                    Belanja Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
                <a href="{{ route('collections.show', 'all') }}"
                   class="inline-flex items-center gap-2 border border-gray-700 hover:border-gray-500 text-gray-300 hover:text-white font-semibold px-7 py-3.5 rounded-full transition-colors text-sm">
                    Lihat Koleksi
                </a>
            </div>
        </div>
    </div>
</section>
