<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ValidateStockAction;
use App\Actions\Inventory\DecrementStockAction;
use App\Actions\Payment\CreatePaymentAction;
use App\Actions\Payment\UploadPaymentProofAction;
use App\Jobs\SendOrderNotificationsJob;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Checkout\CheckoutOrchestratorService;
use App\Services\MidtransService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private ValidateStockAction $validateStock,
        private CreatePaymentAction $createPayment,
        private DecrementStockAction $decrementStock,
        private UploadPaymentProofAction $uploadProof,
        private CheckoutOrchestratorService $checkoutOrchestrator,
    ) {}

    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart');
        }

        $total             = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $bankAccounts      = $this->bankAccounts();
        $midtransClientKey = Setting::get('midtrans_client_key', config('midtrans.client_key'));
        $midtransSnapUrl   = MidtransService::snapUrl();

        /** @var \App\Models\Customer|null $customer */
        $customer       = auth('customer')->user();
        $addresses      = collect();
        $defaultAddress = null;

        if ($customer) {
            $addresses      = $customer->addresses()->orderByDesc('is_default')->orderBy('id')->get();
            $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        }

        $provinces = (new RajaOngkirService)->getProvinces();

        return view('theme.templates.checkout', compact(
            'cart', 'total', 'bankAccounts',
            'customer', 'addresses', 'defaultAddress',
            'midtransClientKey', 'midtransSnapUrl',
            'provinces'
        ));
    }

    public function process(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart');
        }

        $validated = $request->validate([
            'customer_name'        => 'required|string|max:255',
            'customer_email'       => 'required|email|max:255',
            'customer_phone'       => 'required|string|max:20',
            'shipping_address'     => 'required|string|max:500',
            'shipping_city'        => 'required|string|max:100',
            'shipping_province'    => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_cost'        => 'nullable|integer|min:0',
            'shipping_service'     => 'nullable|string|max:100',
            'payment_method'       => 'required|in:cod,bank_transfer,midtrans',
            'notes'                => 'nullable|string|max:1000',
        ]);

        // ── Step 1: Validate available stock (on-hand minus reserved) ─────────
        if ($error = $this->validateStock->handle($cart)) {
            return back()->withErrors(['stock' => $error])->withInput();
        }

        // ── Step 2: Create customer + order + items in one idempotent transaction
        try {
            $idempotencyKey = (string) ($request->header('Idempotency-Key')
                ?? $request->input('idempotency_key')
                ?? '');

            if ($idempotencyKey === '') {
                // Session-scoped fallback so normal browser retries reuse the same key
                // without being too strict across payload edits.
                $idempotencyKey = (string) session('checkout_idempotency_key', '');

                if ($idempotencyKey === '') {
                    $idempotencyKey = (string) \Illuminate\Support\Str::uuid();
                    session(['checkout_idempotency_key' => $idempotencyKey]);
                }
            }

            $order = $this->checkoutOrchestrator->handle($cart, $validated, $idempotencyKey);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // ── Step 3: Initiate payment ───────────────────────────────────────────
        try {
            $payment = $this->createPayment->handle($order);
        } catch (\Throwable) {
            $order->delete();
            return response()->json(['error' => 'Gagal menghubungi payment gateway. Silakan coba lagi.'], 422);
        }

        // ── Step 4a: Midtrans — reservation already handled by orchestrator ────
        if ($payment->isAsync) {
            session()->forget('cart');
            session()->forget('checkout_idempotency_key');
            session(['order_success' => $order->order_number]);

            return response()->json($payment->toArray($order->order_number, route('checkout.success')));
        }

        // ── Step 4b: Bank transfer — reservation already handled by orchestrator
        if ($order->payment_method === 'bank_transfer') {
            session()->forget('cart');
            session()->forget('checkout_idempotency_key');
            session(['order_success' => $order->order_number]);
            SendOrderNotificationsJob::dispatch($order->id, tenant()->getTenantKey());

            return redirect()->route('checkout.success');
        }

        // ── Step 4c: COD — immediate stock deduction ───────────────────────────
        $this->decrementStock->handle($cart, $order);
        session()->forget('cart');
        session()->forget('checkout_idempotency_key');
        session(['order_success' => $order->order_number]);
        SendOrderNotificationsJob::dispatch($order->id, tenant()->getTenantKey());

        return redirect()->route('checkout.success');
    }

    public function success()
    {
        $orderNumber = session('order_success');

        if (! $orderNumber) {
            return redirect()->route('home');
        }

        $order        = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
        $bankAccounts = $this->bankAccounts();

        return view('theme.templates.checkout-success', compact('order', 'bankAccounts'));
    }

    public function uploadProof(Request $request)
    {
        $orderNumber = session('order_success');

        if (! $orderNumber) {
            return redirect()->route('home');
        }

        $request->validate(['proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $this->uploadProof->handle($order, $request->file('proof'));

        return back()->with('proof_uploaded', true);
    }

    private function bankAccounts(): array
    {
        return json_decode(Setting::get('bank_accounts', '[]'), true) ?: [];
    }
}
