<footer class="bg-stone-100 border-t border-stone-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

        {{-- Brand center --}}
        <div class="text-center mb-10">
            <h3 class="font-bold text-stone-900 text-lg tracking-tight mb-2">{{ store_name() }}</h3>
            <p class="text-sm text-stone-500 max-w-xs mx-auto">
                Temukan produk berkualitas terbaik untuk kebutuhan Anda.
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-x-8 gap-y-2 text-xs font-medium uppercase tracking-widest text-stone-500 mb-10">
            <a href="{{ route('collections.show', 'all') }}" class="hover:text-stone-900 transition-colors">Koleksi</a>
            <a href="{{ route('collections.show', 'all') }}" class="hover:text-stone-900 transition-colors">Produk</a>
            <a href="{{ route('pages.show', 'about') }}" class="hover:text-stone-900 transition-colors">Tentang Kami</a>
            <a href="{{ route('pages.show', 'contact') }}" class="hover:text-stone-900 transition-colors">Hubungi Kami</a>
            <a href="{{ route('pages.show', 'privacy-policy') }}" class="hover:text-stone-900 transition-colors">Kebijakan Privasi</a>
        </div>

        <div class="border-t border-stone-200 pt-6 text-center text-xs text-stone-400">
            <p>&copy; {{ date('Y') }} {{ store_name() }}. Semua hak dilindungi.</p>
        </div>
    </div>
</footer>
