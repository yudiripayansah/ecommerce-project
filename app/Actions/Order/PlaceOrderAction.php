<?php

namespace App\Actions\Order;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaceOrderAction
{
    /**
     * Buat customer (upsert), order, dan order items dalam satu transaksi.
     *
     * @param  array<string, mixed>  $cart      Isi session cart
     * @param  array<string, mixed>  $validated Data tervalidasi dari request
     */
    public function handle(array $cart, array $validated): Order
    {
        return DB::transaction(function () use ($cart, $validated) {
            $customer = Customer::updateOrCreate(
                ['email' => $validated['customer_email']],
                [
                    'name'  => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                ]
            );

            // Re-fetch prices from DB — never trust session/client values for money
            $cart         = $this->withFreshPrices($cart);
            $subtotal     = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
            $shippingCost = (int) ($validated['shipping_cost'] ?? 0);

            $order = Order::create([
                'customer_id'          => $customer->id,
                'order_number'         => 'ORD-' . strtoupper(Str::random(12)),
                'customer_name'        => $validated['customer_name'],
                'customer_email'       => $validated['customer_email'],
                'customer_phone'       => $validated['customer_phone'],
                'shipping_address'     => $validated['shipping_address'],
                'shipping_city'        => $validated['shipping_city'],
                'shipping_province'    => $validated['shipping_province'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'payment_method'       => $validated['payment_method'],
                'notes'                => $validated['notes'] ?? null,
                'status'               => 'pending',
                'subtotal'             => $subtotal,
                'shipping_cost'        => $shippingCost,
                'total'                => $subtotal + $shippingCost,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'] ?? null,
                    'variant_id'    => $item['variant_id'] ?: null,
                    'title'         => $item['title'],
                    'variant_title' => $item['variant_title'] ?: null,
                    'price'         => $item['price'],
                    'quantity'      => $item['quantity'],
                    'image'         => $item['image'] ?: null,
                ]);
            }

            return $order;
        });
    }

    /**
     * Replace session-stored prices with current DB prices.
     * Bulk-fetched in 2 queries to keep checkout fast.
     */
    private function withFreshPrices(array $cart): array
    {
        $variantIds = array_values(array_filter(array_column($cart, 'variant_id')));
        $productIds = array_values(array_filter(array_column($cart, 'product_id')));

        $variantPrices = $variantIds
            ? ProductVariant::whereIn('id', $variantIds)->pluck('price', 'id')
            : collect();

        $productPrices = $productIds
            ? Product::whereIn('id', $productIds)->pluck('price', 'id')
            : collect();

        return array_map(function (array $item) use ($variantPrices, $productPrices) {
            $price = $item['variant_id']
                ? (float) ($variantPrices[$item['variant_id']] ?? $item['price'])
                : (float) ($productPrices[$item['product_id']] ?? $item['price']);

            return array_merge($item, ['price' => $price]);
        }, $cart);
    }
}
