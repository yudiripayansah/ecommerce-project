<?php

namespace App\Payment;

use App\Payment\Contracts\PaymentGatewayInterface;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    /**
     * Daftarkan gateway untuk payment method tertentu.
     *
     * Dipanggil dari AppServiceProvider::register().
     * Contoh: $manager->register('midtrans', $app->make(MidtransGateway::class));
     */
    public function register(string $method, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$method] = $gateway;
    }

    /**
     * Ambil gateway yang sesuai untuk payment method yang diminta.
     *
     * @throws \InvalidArgumentException Jika method tidak terdaftar.
     */
    public function for(string $method): PaymentGatewayInterface
    {
        return $this->gateways[$method]
            ?? throw new \InvalidArgumentException("Payment gateway tidak terdaftar: [{$method}]");
    }

    /**
     * Cek apakah suatu payment method ditangani oleh gateway (online).
     * COD dan bank_transfer bukan gateway — mereka manual.
     */
    public function has(string $method): bool
    {
        return isset($this->gateways[$method]);
    }
}
