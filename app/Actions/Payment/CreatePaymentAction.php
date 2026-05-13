<?php

namespace App\Actions\Payment;

use App\Models\Order;
use App\Payment\PaymentGatewayManager;

class CreatePaymentAction
{
    public function __construct(private PaymentGatewayManager $manager) {}

    /**
     * Inisiasi pembayaran sesuai metode yang dipilih customer.
     *
     * Untuk menambah gateway baru (Xendit, DOKU, dll.):
     *   1. Buat class yang implement PaymentGatewayInterface
     *   2. Register di AppServiceProvider: $manager->register('xendit', ...)
     *   3. Selesai — tidak ada kode di sini yang perlu diubah
     *
     * @throws \Throwable Jika gateway gagal atau method tidak terdaftar.
     */
    public function handle(Order $order): PaymentResult
    {
        // COD dan bank transfer tidak melalui gateway — langsung manual.
        if (! $this->manager->has($order->payment_method)) {
            return PaymentResult::manual();
        }

        return $this->manager->for($order->payment_method)->createPayment($order);
    }
}
