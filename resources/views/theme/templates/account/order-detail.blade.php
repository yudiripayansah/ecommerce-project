@use('Illuminate\Support\Facades\Storage')
@extends('theme.layouts.app')

@section('title', 'Pesanan ' . $order->order_number . ' — ' . store_name())

@section('content')
<x-account-layout>

    <div>
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('account.orders') }}" class="text-gray-400 hover:text-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h2>
                <p class="text-xs text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>

        @php
            $statusColor = match($order->status) {
                'pending'    => 'bg-yellow-50 text-yellow-700 ring-yellow-200',
                'processing' => 'bg-blue-50 text-blue-700 ring-blue-200',
                'shipped'    => 'bg-purple-50 text-purple-700 ring-purple-200',
                'delivered'  => 'bg-green-50 text-green-700 ring-green-200',
                'cancelled'  => 'bg-red-50 text-red-700 ring-red-200',
                default      => 'bg-gray-50 text-gray-700 ring-gray-200',
            };
            $statusLabel = match($order->status) {
                'pending'    => 'Menunggu Konfirmasi',
                'processing' => 'Sedang Diproses',
                'shipped'    => 'Dalam Pengiriman',
                'delivered'  => 'Pesanan Selesai',
                'cancelled'  => 'Pesanan Dibatalkan',
                default      => ucfirst($order->status),
            };
        @endphp

        <div class="flex flex-col gap-4">

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Status Pesanan</p>
                        <span class="mt-1.5 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ring-1 {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-medium">Metode Pembayaran</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ match($order->payment_method) {
                                'bank_transfer' => 'Transfer Bank',
                                'midtrans'      => 'Bayar Online (Midtrans)',
                                default         => 'Bayar di Tempat (COD)',
                            } }}
                        </p>
                    </div>
                </div>

                @if ($order->tracking_number)
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                    <div>
                        <p class="text-xs text-gray-500 font-medium mb-1">No. Resi Pengiriman</p>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-semibold text-sm text-gray-900">{{ $order->tracking_number }}</span>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}').then(() => { this.textContent='✓'; setTimeout(()=>this.textContent='Salin',1500) })"
                                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 rounded px-2 py-0.5 transition-colors">
                                Salin
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Item Pesanan</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                    <div class="flex items-start gap-4 px-5 py-4">
                        @if ($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->title }}" class="w-14 h-14 object-cover rounded-lg bg-gray-100 shrink-0">
                        @else
                        <div class="w-14 h-14 bg-gray-100 rounded-lg shrink-0 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item->title }}</p>
                            @if ($item->variant_title)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $item->variant_title }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">{{ $item->quantity }} × {{ rupiah($item->price) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 shrink-0">
                            {{ rupiah($item->price * $item->quantity) }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Alamat Pengiriman</h3>
                    <div class="text-sm text-gray-600 space-y-0.5">
                        <p class="font-medium text-gray-900">{{ $order->customer_name }}</p>
                        <p>{{ $order->customer_phone }}</p>
                        <p>{{ $order->shipping_address }}</p>
                        <p>{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Ringkasan Pembayaran</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>{{ rupiah($order->subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Ongkos kirim</span>
                            <span>{{ $order->shipping_cost > 0 ? rupiah($order->shipping_cost) : 'Gratis' }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-100">
                            <span>Total</span>
                            <span>{{ rupiah($order->total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($order->notes)
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Catatan</h3>
                <p class="text-sm text-gray-600">{{ $order->notes }}</p>
            </div>
            @endif

            @if ($order->payment_method === 'bank_transfer')
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Bukti Transfer</h3>

                @if ($order->payment_proof)
                    {{-- Status badge --}}
                    <div class="flex items-center gap-2 text-sm text-green-700 font-medium mb-4">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Bukti transfer sudah dikirim
                    </div>

                    {{-- Thumbnail with lightbox --}}
                    <a href="{{ Storage::url($order->payment_proof) }}" target="_blank" rel="noopener"
                       class="group inline-flex flex-col items-center gap-1 mb-4">
                        <img src="{{ Storage::url($order->payment_proof) }}"
                             alt="Bukti transfer"
                             style="width:80px;height:80px;object-fit:cover;"
                             class="rounded-lg border border-gray-200 group-hover:opacity-75 transition-opacity">
                        <span class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">Lihat foto</span>
                    </a>

                    {{-- Replace form --}}
                    <p class="text-xs text-gray-500 mb-2">Ingin mengganti? Upload file baru:</p>
                    <form method="POST" action="{{ route('account.orders.proof', $order->order_number) }}"
                          enctype="multipart/form-data" x-data="{ fileName: '' }">
                        @csrf
                        @error('proof') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="flex items-center gap-2 max-w-sm">
                            <label class="flex-1 flex items-center gap-2 cursor-pointer border border-dashed border-gray-300 rounded-lg px-3 py-2 hover:border-gray-500 transition-colors text-xs text-gray-500 min-w-0">
                                <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                </svg>
                                <span class="truncate" x-text="fileName || 'Pilih foto'"></span>
                                <input type="file" name="proof" accept="image/*" class="hidden"
                                       x-on:change="fileName = $event.target.files[0]?.name">
                            </label>
                            <button type="submit"
                                    class="shrink-0 theme-btn-primary text-xs font-medium px-4 py-2 rounded-lg transition-colors">
                                Ganti
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-gray-500 mb-4">Belum ada bukti transfer. Silakan upload foto bukti pembayaran kamu.</p>
                    @error('proof') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    <form method="POST" action="{{ route('account.orders.proof', $order->order_number) }}"
                          enctype="multipart/form-data" x-data="{ fileName: '' }">
                        @csrf
                        <label class="flex flex-col items-center gap-2 cursor-pointer border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-gray-400 transition-colors"
                               x-on:dragover.prevent x-on:drop.prevent="fileName = $event.dataTransfer.files[0]?.name; $refs.inp.files = $event.dataTransfer.files">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                            </svg>
                            <span class="text-sm text-gray-500" x-text="fileName || 'Klik atau drag foto bukti transfer di sini'"></span>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP — maks. 5 MB</span>
                            <input type="file" name="proof" accept="image/*" class="hidden" x-ref="inp"
                                   x-on:change="fileName = $event.target.files[0]?.name">
                        </label>
                        <button type="submit"
                                class="mt-3 w-full theme-btn-primary text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            Kirim Bukti Transfer
                        </button>
                    </form>
                @endif
            </div>
            @endif

        </div>
    </div>

</x-account-layout>
@endsection
