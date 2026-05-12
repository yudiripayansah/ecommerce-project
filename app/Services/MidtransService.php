<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey        = config('midtrans.server_key');
        Config::$isProduction     = config('midtrans.is_production');
        Config::$isSanitized      = config('midtrans.is_sanitized');
        Config::$is3ds            = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email'      => $order->customer_email,
                'phone'      => $order->customer_phone,
            ],
            'shipping_address' => [
                'first_name' => $order->customer_name,
                'phone'      => $order->customer_phone,
                'address'    => $order->shipping_address,
                'city'       => $order->shipping_city,
                'postal_code'=> $order->shipping_postal_code,
                'country_code' => 'IDN',
            ],
            'item_details' => $this->buildItemDetails($order),
        ];

        return Snap::getSnapToken($params);
    }

    private function buildItemDetails(Order $order): array
    {
        $items = $order->items->map(fn ($item) => [
            'id'       => (string) $item->id,
            'price'    => (int) $item->price,
            'quantity' => $item->quantity,
            'name'     => mb_substr($item->title . ($item->variant_title ? ' - ' . $item->variant_title : ''), 0, 50),
        ])->toArray();

        if ($order->shipping_cost > 0) {
            $items[] = [
                'id'       => 'shipping',
                'price'    => (int) $order->shipping_cost,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim',
            ];
        }

        return $items;
    }
}
