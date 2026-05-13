<?php

namespace App\Payment\Contracts;

use App\Actions\Payment\PaymentResult;
use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Inisiasi pembayaran dan kembalikan hasilnya.
     *
     * Gateway async (Midtrans, Xendit, dll.) → PaymentResult::async()
     * Gateway manual / redirect → PaymentResult::manual()
     *
     * @throws \Throwable Jika gateway unreachable atau konfigurasi salah.
     *                    Exception dibiarkan bubble up agar controller bisa rollback order.
     */
    public function createPayment(Order $order): PaymentResult;
}
