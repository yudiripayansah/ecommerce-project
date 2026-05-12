<section class="max-w-2xl mx-auto px-4 sm:px-6 py-16 md:py-24 text-center">

    {{-- Ikon sukses --}}
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Pesanan Berhasil Dibuat!</h1>
    <p class="text-gray-500 mb-1">Terima kasih, <span class="font-medium text-gray-700">{{ $order->customer_name }}</span>.</p>
    <p class="text-gray-500 text-sm">
        Pesanan Anda <span class="font-mono font-semibold text-gray-800">{{ $order->order_number }}</span> telah diterima.
    </p>

    {{-- Instruksi pembayaran --}}
    @if ($order->payment_method === 'midtrans')
        <div class="mt-6 p-5 bg-green-50 border border-green-100 rounded-2xl text-left">
            <p class="text-sm font-semibold text-green-900 mb-2">Status Pembayaran</p>
            @if (in_array($order->status, ['processing', 'shipped', 'delivered']))
                <div class="flex items-center gap-2 text-green-700 text-sm font-medium">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    Pembayaran dikonfirmasi. Pesanan Anda sedang diproses.
                </div>
            @else
                <p class="text-sm text-green-800">Pembayaran Anda sedang diverifikasi. Kami akan segera memproses pesanan Anda.</p>
            @endif
            @if ($order->midtrans_payment_type)
                <p class="text-xs text-green-600 mt-1">Metode: {{ str_replace('_', ' ', ucwords($order->midtrans_payment_type)) }}</p>
            @endif
        </div>
    @elseif ($order->payment_method === 'bank_transfer')
        <div class="mt-6 p-5 bg-blue-50 border border-blue-100 rounded-2xl text-left">
            <p class="text-sm font-semibold text-blue-900 mb-2">Selesaikan Pembayaran Anda</p>
            <p class="text-sm text-blue-800 mb-3">Transfer sebesar <span class="font-bold">{{ rupiah($order->total) }}</span> ke:</p>
            @if (!empty($bankAccounts))
                <div class="space-y-2 text-sm text-blue-800">
                    @foreach ($bankAccounts as $account)
                        <div class="flex flex-wrap gap-x-4 gap-y-0.5">
                            <span class="font-semibold">{{ $account['bank_name'] }}</span>
                            <span class="font-mono font-bold">{{ $account['account_number'] }}</span>
                            <span>a.n. {{ $account['account_holder'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-blue-700 italic">Tim kami akan menghubungi Anda dengan detail pembayaran segera.</p>
            @endif
            <p class="mt-3 text-xs text-blue-600">Gunakan <span class="font-mono font-semibold">{{ $order->order_number }}</span> sebagai referensi transfer. Pesanan akan diproses setelah pembayaran dikonfirmasi.</p>

            {{-- Upload bukti --}}
            <div class="mt-4 pt-4 border-t border-blue-200">
                @if (session('proof_uploaded') || $order->payment_proof)
                    <div class="flex items-center gap-2 text-sm text-green-700 font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Bukti transfer sudah dikirim. Tim kami akan segera memverifikasi.
                    </div>
                @else
                    <p class="text-sm font-semibold text-blue-900 mb-2">Upload bukti transfer</p>
                    @error('proof') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    <form method="POST" action="{{ route('checkout.payment-proof') }}" enctype="multipart/form-data"
                          x-data="{ fileName: '' }">
                        @csrf
                        <label class="flex flex-col items-center gap-2 cursor-pointer border-2 border-dashed border-blue-300 rounded-xl p-4 hover:border-blue-500 transition-colors bg-white/60"
                               x-on:dragover.prevent x-on:drop.prevent="fileName = $event.dataTransfer.files[0]?.name; $refs.proofInput.files = $event.dataTransfer.files">
                            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                            </svg>
                            <span class="text-xs text-blue-700" x-text="fileName || 'Klik atau drag foto bukti transfer di sini'"></span>
                            <input type="file" name="proof" accept="image/*" class="hidden" x-ref="proofInput"
                                   x-on:change="fileName = $event.target.files[0]?.name">
                        </label>
                        <button type="submit"
                                class="mt-3 w-full bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold py-2 rounded-xl transition-colors">
                            Kirim Bukti Transfer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <div class="mt-6 p-5 bg-gray-50 border border-gray-100 rounded-2xl text-left">
            <p class="text-sm text-gray-700">Anda memilih <span class="font-semibold">Bayar di Tempat (COD)</span>. Siapkan uang tunai sebesar <span class="font-bold">{{ rupiah($order->total) }}</span> saat pesanan tiba.</p>
        </div>
    @endif

    {{-- Ringkasan pesanan --}}
    <div class="mt-8 text-left">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Produk yang dipesan</h2>
        <div class="divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden">
            @foreach ($order->items as $item)
                <div class="flex items-center gap-3 px-4 py-3 bg-white">
                    @if ($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->title }}"
                             class="w-12 h-12 object-cover rounded-lg bg-gray-100 shrink-0">
                    @else
                        <div class="w-12 h-12 bg-gray-100 rounded-lg shrink-0"></div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->title }}</p>
                        @if ($item->variant_title)
                            <p class="text-xs text-gray-500">{{ $item->variant_title }}</p>
                        @endif
                        <p class="text-xs text-gray-400">Qty {{ $item->quantity }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 shrink-0">
                        {{ rupiah($item->price * $item->quantity) }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-3 px-4 py-3 bg-gray-50 rounded-2xl space-y-2 text-sm text-gray-700">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ rupiah($order->subtotal) }}</span>
            </div>
            @if ($order->shipping_cost > 0)
                <div class="flex justify-between">
                    <span>Ongkos Kirim</span>
                    <span>{{ rupiah($order->shipping_cost) }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-200">
                <span>Total</span>
                <span>{{ rupiah($order->total) }}</span>
            </div>
        </div>
    </div>

    {{-- Alamat pengiriman --}}
    <div class="mt-6 p-4 bg-gray-50 rounded-2xl text-left text-sm text-gray-600">
        <p class="font-semibold text-gray-900 mb-2">Dikirim ke</p>
        <p>{{ $order->shipping_address }}</p>
        <p>{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
    </div>

    {{-- Aksi --}}
    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('collections.show', 'all') }}"
           class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-semibold px-6 py-3 rounded-full hover:bg-gray-700 transition-colors">
            Lanjutkan Belanja
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>
</section>
