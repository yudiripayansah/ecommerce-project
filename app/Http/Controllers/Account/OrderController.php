<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $orders = $customer->orders()
            ->latest()
            ->paginate(10);

        return view('theme.templates.account.orders', compact('orders'));
    }

    public function show(string $order)
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $order = $customer->orders()
            ->where('order_number', $order)
            ->with('items')
            ->firstOrFail();

        return view('theme.templates.account.order-detail', compact('order'));
    }

    public function uploadProof(Request $request, string $order)
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $order = $customer->orders()
            ->where('order_number', $order)
            ->firstOrFail();

        $request->validate([
            'proof' => ['required', 'file', 'image', 'max:5120'],
        ]);

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof')->store(tenant_storage_prefix() . 'payment-proofs', 'public');
        $order->update(['payment_proof' => $path]);

        return back()->with('success', 'Bukti transfer berhasil dikirim.');
    }
}
