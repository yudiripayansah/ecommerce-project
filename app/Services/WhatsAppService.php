<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private const API_URL = 'https://api.fonnte.com/send';

    public function sendOrderCreated(Order $order): void
    {
        $customerPhone = $this->normalizePhone($order->customer_phone);
        $adminPhone    = $this->normalizePhone(
            Setting::get('fonnte_admin_whatsapp', config('services.fonnte.admin_whatsapp', ''))
        );

        $customerMsg = $this->orderCreatedCustomerMessage($order);
        $adminMsg    = $this->orderCreatedAdminMessage($order);

        if ($customerPhone) {
            $this->send($customerPhone, $customerMsg);
        }

        if ($adminPhone) {
            $this->send($adminPhone, $adminMsg);
        }
    }

    public function sendShipped(Order $order): void
    {
        $customerPhone = $this->normalizePhone($order->customer_phone);

        if ($customerPhone) {
            $tracking = $order->tracking_number ?? '—';
            $message  = "Halo {$order->customer_name}! 🚚\n\n"
                . "Pesanan *{$order->order_number}* sudah dikirim!\n\n"
                . "*No. Resi:* {$tracking}\n\n"
                . "Silakan pantau pengiriman Anda. Terima kasih sudah berbelanja! 🛍️";

            $this->send($customerPhone, $message);
        }
    }

    public function sendPaymentConfirmed(Order $order): void
    {
        $customerPhone = $this->normalizePhone($order->customer_phone);

        if ($customerPhone) {
            $this->send($customerPhone, $this->paymentConfirmedMessage($order));
        }
    }

    private function orderCreatedCustomerMessage(Order $order): string
    {
        $items = $order->items->map(function ($item) {
            $variant = $item->variant_title ? " ({$item->variant_title})" : '';
            return "• {$item->title}{$variant} x{$item->quantity} — " . rupiah($item->price * $item->quantity);
        })->join("\n");

        $payment = match ($order->payment_method) {
            'midtrans'      => 'Bayar Online (Midtrans)',
            'bank_transfer' => 'Transfer Bank',
            'cod'           => 'Bayar di Tempat (COD)',
            default         => $order->payment_method,
        };

        return "Halo {$order->customer_name}! 👋\n\n"
            . "Pesanan Anda telah kami terima.\n\n"
            . "*No. Pesanan:* {$order->order_number}\n"
            . "*Metode Bayar:* {$payment}\n\n"
            . "*Produk:*\n{$items}\n\n"
            . "*Total:* " . rupiah($order->total) . "\n\n"
            . "Terima kasih sudah berbelanja! 🛍️";
    }

    private function orderCreatedAdminMessage(Order $order): string
    {
        $items = $order->items->map(function ($item) {
            $variant = $item->variant_title ? " ({$item->variant_title})" : '';
            return "• {$item->title}{$variant} x{$item->quantity}";
        })->join("\n");

        $payment = match ($order->payment_method) {
            'midtrans'      => 'Bayar Online',
            'bank_transfer' => 'Transfer Bank',
            'cod'           => 'COD',
            default         => $order->payment_method,
        };

        return "🛒 *Pesanan Baru!*\n\n"
            . "*No. Pesanan:* {$order->order_number}\n"
            . "*Pelanggan:* {$order->customer_name}\n"
            . "*Telepon:* {$order->customer_phone}\n"
            . "*Pembayaran:* {$payment}\n\n"
            . "*Item:*\n{$items}\n\n"
            . "*Total:* " . rupiah($order->total);
    }

    private function paymentConfirmedMessage(Order $order): string
    {
        return "Halo {$order->customer_name}! ✅\n\n"
            . "Pembayaran untuk pesanan *{$order->order_number}* telah dikonfirmasi.\n\n"
            . "Pesanan Anda sedang kami proses dan akan segera dikirim. 🚀\n\n"
            . "Terima kasih telah berbelanja!";
    }

    private function send(string $phone, string $message): void
    {
        // Per-tenant token takes priority, falls back to global env config
        $token = Setting::get('fonnte_token', config('services.fonnte.token'));

        if (! $token) {
            Log::warning('WhatsApp: FONNTE_TOKEN tidak dikonfigurasi untuk tenant ini');
            return;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(10)
                ->post(self::API_URL, [
                    'target'  => $phone,
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp: Fonnte gagal', [
                    'phone'    => $phone,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp: Exception saat kirim pesan — ' . $e->getMessage());
        }
    }

    private function normalizePhone(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
