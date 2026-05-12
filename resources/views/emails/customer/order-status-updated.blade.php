<x-mail::message>
# Status Pesanan Diperbarui

Halo {{ $order->customer_name }},

Status pesanan Anda telah diperbarui.

<x-mail::panel>
**No. Order:** {{ $order->order_number }}

**Status Sebelumnya:** {{ ucfirst($oldStatus) }}

**Status Terbaru:** {{ ucfirst($order->status) }}
</x-mail::panel>

@if ($order->status === 'processing')
Pesanan Anda sedang kami siapkan untuk pengiriman.
@elseif ($order->status === 'shipped')
Pesanan Anda telah dikirim.{{ $order->tracking_number ? ' No. resi: **' . $order->tracking_number . '**' : '' }}
@elseif ($order->status === 'delivered')
Pesanan Anda telah sampai. Terima kasih telah berbelanja di **{{ config('app.name') }}**!
@elseif ($order->status === 'cancelled')
Pesanan Anda telah dibatalkan. Jika ada pertanyaan, silakan hubungi kami.
@endif

<x-mail::button :url="url('/account/orders/' . $order->order_number)">
Lihat Detail Pesanan
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
