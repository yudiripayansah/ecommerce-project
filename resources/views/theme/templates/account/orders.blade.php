@extends('theme.layouts.app')

@section('title', 'Riwayat Pesanan — ' . store_name())

@section('content')
<x-account-layout>

    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Riwayat Pesanan</h2>

        @if ($orders->isEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                </svg>
                <p class="text-gray-500 text-sm">Kamu belum punya pesanan.</p>
                <a href="{{ route('collections.show', 'all') }}" class="mt-4 inline-block theme-btn-primary text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    Mulai belanja
                </a>
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($orders as $order)
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
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'delivered'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                        default      => ucfirst($order->status),
                    };
                @endphp
                <a href="{{ route('account.orders.show', $order->order_number) }}"
                   class="block bg-white border border-gray-200 rounded-xl px-6 py-4 hover:border-gray-300 hover:shadow-sm transition-all">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-gray-900">
                                {{ rupiah($order->total) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $orders->links('theme.snippets.pagination') }}</div>
        @endif
    </div>

</x-account-layout>
@endsection
