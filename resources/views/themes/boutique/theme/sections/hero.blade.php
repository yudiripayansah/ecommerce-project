<section class="relative overflow-hidden bg-stone-900 text-white">
    {{-- Subtle texture overlay --}}
    <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCI+PHBhdGggZD0iTTAgMGg2MHY2MEgweiIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0zMCAwdjYwTTAgMzBoNjAiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLW9wYWNpdHk9Ii4wNSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9zdmc+')]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 min-h-[480px] md:min-h-[560px]">

            {{-- Left: Text --}}
            <div class="flex flex-col justify-center py-20 md:py-28 pr-0 md:pr-12">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-400 mb-6">Koleksi Terbaru</p>
                <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6 text-white">
                    Temukan<br>
                    Koleksi<br>
                    Terbaru Kami
                </h1>
                <p class="text-sm text-stone-400 mb-8 leading-relaxed max-w-sm">
                    Jelajahi pilihan produk premium kami, dibuat dengan kualitas terbaik
                    dan dikirim langsung ke pintu Anda.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('collections.show', 'all') }}"
                       class="inline-flex items-center gap-2 bg-white text-stone-900 font-semibold px-6 py-3 text-xs uppercase tracking-widest hover:bg-stone-100 transition-colors">
                        Belanja Sekarang
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('collections.show', 'all') }}"
                       class="inline-flex items-center gap-2 border border-stone-600 text-stone-300 hover:border-stone-400 hover:text-white font-semibold px-6 py-3 text-xs uppercase tracking-widest transition-colors">
                        Lihat Koleksi
                    </a>
                </div>
            </div>

            {{-- Right: Decorative grid (visible only on md+) --}}
            <div class="hidden md:grid grid-cols-2 gap-0.5 bg-stone-800/50 self-stretch">
                <div class="bg-stone-800/30 flex items-center justify-center text-stone-700">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="0.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                </div>
                <div class="bg-stone-700/20 flex items-center justify-center text-stone-700">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="0.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                    </svg>
                </div>
                <div class="bg-stone-700/20 flex items-center justify-center text-stone-700">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="0.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                    </svg>
                </div>
                <div class="bg-stone-800/30 flex items-center justify-center text-stone-700">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="0.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582"/>
                    </svg>
                </div>
            </div>

        </div>
    </div>
</section>
