<?php

namespace App\Http\Controllers\Account;

use App\Actions\Payment\UploadPaymentProofAction;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private UploadPaymentProofAction $uploadProof) {}

    public function index()
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $orders = $customer->orders()->latest()->paginate(10);

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

        if (! in_array($order->status, ['pending', 'processing']) || $order->payment_method !== 'bank_transfer') {
            abort(403);
        }

        $request->validate(['proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);

        $this->uploadProof->handle($order, $request->file('proof'));

        return back()->with('success', 'Bukti transfer berhasil dikirim.');
    }
}
