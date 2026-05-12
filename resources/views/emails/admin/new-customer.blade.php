<x-mail::message>
# Pelanggan Baru Terdaftar

Halo Admin,

Ada pelanggan baru yang baru saja mendaftar di **{{ store_name() }}**.

<x-mail::panel>
**Nama:** {{ $customer->name }}

**Email:** {{ $customer->email }}

**Telepon:** {{ $customer->phone ?? '-' }}

**Tanggal Daftar:** {{ $customer->created_at->format('d M Y, H:i') }}
</x-mail::panel>

<x-mail::button :url="url('/admin/customers/' . $customer->id)">
Lihat Detail Pelanggan
</x-mail::button>

Salam,<br>
{{ store_name() }}
</x-mail::message>
