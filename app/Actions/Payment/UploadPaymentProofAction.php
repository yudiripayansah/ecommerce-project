<?php

namespace App\Actions\Payment;

use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadPaymentProofAction
{
    /**
     * Simpan bukti transfer ke storage dan update order.
     * File lama otomatis dihapus jika ada.
     */
    public function handle(Order $order, UploadedFile $file): void
    {
        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $file->store(tenant_storage_prefix() . 'payment-proofs', 'public');
        $order->update(['payment_proof' => $path]);
    }
}
