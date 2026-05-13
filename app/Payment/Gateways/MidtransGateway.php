<?php

namespace App\Payment\Gateways;

use App\Actions\Payment\PaymentResult;
use App\Models\Order;
use App\Payment\Contracts\PaymentGatewayInterface;
use App\Services\MidtransService;

class MidtransGateway implements PaymentGatewayInterface
{
    public function __construct(private MidtransService $midtrans) {}

    public function createPayment(Order $order): PaymentResult
    {
        // MidtransService membaca server key dari tenant Settings secara otomatis.
        // Tidak ada config hardcoded di sini — tenancy-aware by design.
        $snapToken = $this->midtrans->createSnapToken($order->load('items'));

        $order->update(['snap_token' => $snapToken]);

        return PaymentResult::async($snapToken);
    }
}
