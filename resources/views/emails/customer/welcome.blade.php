<x-mail::message>
# Selamat Datang, {{ $customer->name }}!

Terima kasih telah mendaftar di **{{ config('app.name') }}**. Akun Anda telah berhasil dibuat.

<x-mail::panel>
**Email:** {{ $customer->email }}
</x-mail::panel>

Sekarang Anda dapat mulai berbelanja dan menikmati berbagai produk pilihan kami.

<x-mail::button :url="url('/')">
Mulai Belanja
</x-mail::button>

Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
