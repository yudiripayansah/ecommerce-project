<?php

namespace App\Actions\Order;

use App\Mail\Admin\NewOrderMail;
use App\Mail\Customer\OrderCreatedMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderNotificationsAction
{
    public function __construct(private WhatsAppService $whatsApp) {}

    /**
     * Kirim notifikasi email (customer + admin) dan WhatsApp.
     * Semua exception ditangkap agar tidak membatalkan alur utama.
     */
    public function handle(Order $order): void
    {
        $this->sendEmails($order);
        $this->sendWhatsApp($order);
    }

    private function sendEmails(Order $order): void
    {
        try {
            Mail::to($order->customer_email)->send(new OrderCreatedMail($order));

            $adminEmail = Setting::get('store_email', config('mail.from.address'));
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new NewOrderMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order notification email failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWhatsApp(Order $order): void
    {
        try {
            $this->whatsApp->sendOrderCreated($order);
        } catch (\Throwable $e) {
            Log::warning('Order WhatsApp notification failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
