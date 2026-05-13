<?php

namespace App\Actions\Payment;

/**
 * Value object yang merepresentasikan hasil pembuatan payment.
 *
 * isAsync = true  → stok dan notifikasi ditangani oleh webhook (Midtrans).
 * isAsync = false → stok dikurangi dan notifikasi dikirim langsung (COD / bank transfer).
 *
 * Desain ini memudahkan penambahan gateway baru (Xendit, DOKU, dll.)
 * tanpa mengubah controller — cukup tambahkan case baru di CreatePaymentAction.
 */
final class PaymentResult
{
    private function __construct(
        public readonly bool   $isAsync,
        public readonly ?string $snapToken = null,
    ) {}

    public static function async(string $snapToken): self
    {
        return new self(isAsync: true, snapToken: $snapToken);
    }

    public static function manual(): self
    {
        return new self(isAsync: false);
    }

    /**
     * Payload untuk JSON response ke frontend (dipakai untuk Midtrans Snap).
     */
    public function toArray(string $orderNumber, string $successUrl): array
    {
        return [
            'snap_token'   => $this->snapToken,
            'order_number' => $orderNumber,
            'success_url'  => $successUrl,
        ];
    }
}
