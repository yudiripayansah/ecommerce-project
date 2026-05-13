<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMidtransWebhookJob;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function notification(): JsonResponse
    {
        try {
            $this->configureMidtrans();

            // Validate signature synchronously — Midtrans expects a fast response.
            // If invalid, Notification constructor throws; Midtrans will retry.
            $notif = new Notification();

            $payload = [
                'transaction_status' => $notif->transaction_status,
                'fraud_status'       => $notif->fraud_status,
                'order_id'           => $notif->order_id,
                'payment_type'       => $notif->payment_type,
                'transaction_id'     => $notif->transaction_id,
            ];

            ProcessMidtransWebhookJob::dispatch($payload, tenant()->getTenantKey());

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Midtrans notification error', [
                'error'  => $e->getMessage(),
                'tenant' => tenant()?->getTenantKey(),
            ]);

            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function configureMidtrans(): void
    {
        Config::$serverKey    = Setting::get('midtrans_server_key', config('midtrans.server_key'));
        Config::$isProduction = filter_var(
            Setting::get('midtrans_is_production', config('midtrans.is_production', false)),
            FILTER_VALIDATE_BOOLEAN
        );
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds       = config('midtrans.is_3ds', true);
    }
}
