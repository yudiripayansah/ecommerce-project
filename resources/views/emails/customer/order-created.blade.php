<x-mail::message>
# Pesanan Anda Diterima

Halo {{ $order->customer_name }},

Terima kasih! Pesanan Anda telah kami terima dan sedang diproses.

<x-mail::panel>
**No. Order:** {{ $order->order_number }}

**Tanggal:** {{ $order->created_at->format('d M Y, H:i') }}

**Pembayaran:** {{ $order->payment_method === 'bank_transfer' ? 'Transfer Bank' : 'COD (Bayar di Tempat)' }}

**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}
</x-mail::panel>

**Detail Pesanan:**

<x-mail::table>
| Produk | Qty | Harga |
|:-------|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->title }}{{ $item->variant_title ? ' — ' . $item->variant_title : '' }} | {{ $item->quantity }} | Rp {{ number_format($item->price, 0, ',', '.') }} |
@endforeach
</x-mail::table>

**Alamat Pengiriman:**

{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}

@if ($order->payment_method === 'bank_transfer')
Segera lakukan pembayaran dan unggah bukti transfer agar pesanan Anda segera diproses.
@endif

<x-mail::button :url="url('/account/orders/' . $order->order_number)">
Lihat Detail Pesanan
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
