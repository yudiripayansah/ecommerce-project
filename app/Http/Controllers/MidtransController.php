<?php

namespace App\Http\Controllers;

use App\Mail\Admin\NewOrderMail;
use App\Mail\Customer\OrderCreatedMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function notification(Request $request)
    {
        Config::$serverKey    = Setting::get('midtrans_server_key', config('midtrans.server_key'));
        Config::$isProduction = filter_var(
            Setting::get('midtrans_is_production', config('midtrans.is_production', false)),
            FILTER_VALIDATE_BOOLEAN
        );
        Config::$isSanitized  = config('midtrans.is_sanitized', true);
        Config::$is3ds        = config('midtrans.is_3ds', true);

        try {
            $notif         = new Notification();
            $transactionStatus = $notif->transaction_status;
            $fraudStatus       = $notif->fraud_status;
            $orderId           = $notif->order_id;
            $paymentType       = $notif->payment_type;
            $transactionId     = $notif->transaction_id;

            $order = Order::with('items')->where('order_number', $orderId)->first();

            if (! $order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->update([
                'midtrans_transaction_id' => $transactionId,
                'midtrans_payment_type'   => $paymentType,
            ]);

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    if ($order->status !== 'processing') {
                        $order->update(['status' => 'processing']);
                        CheckoutController::decrementStock($order->items->map(fn ($i) => [
                            'variant_id' => $i->variant_id,
                            'product_id' => $i->product_id,
                            'quantity'   => $i->quantity,
                        ])->all());
                        $this->sendOrderEmails($order);
                    }
                }
            } elseif ($transactionStatus === 'settlement') {
                if ($order->status !== 'processing') {
                    $order->update(['status' => 'processing']);
                    CheckoutController::decrementStock($order->items->map(fn ($i) => [
                        'variant_id' => $i->variant_id,
                        'product_id' => $i->product_id,
                        'quantity'   => $i->quantity,
                    ])->all());
                    $this->sendOrderEmails($order);
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $order->update(['status' => 'cancelled']);
            } elseif ($transactionStatus === 'pending') {
                $order->update(['status' => 'pending']);
            }

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function sendOrderEmails(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === 'processing') {
            try {
                Mail::to($order->customer_email)->send(new OrderCreatedMail($order));

                $adminEmail = Setting::get('contact_email', config('mail.from.address'));
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new NewOrderMail($order));
                }
            } catch (\Throwable) {
                // Email gagal tidak boleh batalkan webhook
            }

            try {
                (new WhatsAppService)->sendPaymentConfirmed($order);
            } catch (\Throwable) {
                // WhatsApp gagal tidak boleh batalkan webhook
            }
        }
    }
}
