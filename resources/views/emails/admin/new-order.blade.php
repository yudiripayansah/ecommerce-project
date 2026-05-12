<x-mail::message>
# Order Baru Masuk

Halo Admin,

Ada order baru yang masuk di **{{ store_name() }}**.

<x-mail::panel>
**No. Order:** {{ $order->order_number }}

**Pelanggan:** {{ $order->customer_name }}

**Email:** {{ $order->customer_email }}

**Telepon:** {{ $order->customer_phone }}

**Tanggal:** {{ $order->created_at->format('d M Y, H:i') }}

**Pembayaran:** {{ $order->payment_method === 'bank_transfer' ? 'Transfer Bank' : 'COD' }}

**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}
</x-mail::panel>

**Alamat Pengiriman:**

{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}

<x-mail::table>
| Produk | Qty | Harga |
|:-------|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->title }}{{ $item->variant_title ? ' — ' . $item->variant_title : '' }} | {{ $item->quantity }} | Rp {{ number_format($item->price, 0, ',', '.') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="url('/admin/orders/' . $order->id)">
Lihat Detail Order
</x-mail::button>

Salam,<br>
{{ store_name() }}
</x-mail::message>
