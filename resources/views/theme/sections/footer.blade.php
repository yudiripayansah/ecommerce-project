<footer class="bg-gray-50 border-t border-gray-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div>
                <h3 class="font-bold text-gray-900 mb-3">{{ config('app.name', 'Toko') }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Temukan produk berkualitas terbaik untuk kebutuhan Anda.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Belanja</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="{{ route('collections.show', 'all') }}" class="hover:text-gray-900 transition-colors">Semua Koleksi</a></li>
                    <li><a href="{{ route('collections.show', 'all') }}" class="hover:text-gray-900 transition-colors">Semua Produk</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Informasi</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><a href="{{ route('pages.show', 'about') }}" class="hover:text-gray-900 transition-colors">Tentang Kami</a></li>
                    <li><a href="{{ route('pages.show', 'contact') }}" class="hover:text-gray-900 transition-colors">Hubungi Kami</a></li>
                    <li><a href="{{ route('pages.show', 'privacy-policy') }}" class="hover:text-gray-900 transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="{{ route('pages.show', 'terms-and-conditions') }}" class="hover:text-gray-900 transition-colors">Syarat &amp; Ketentuan</a></li>
                </ul>
            </div>

        </div>

        <div class="mt-10 pt-6 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Toko') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</footer>
