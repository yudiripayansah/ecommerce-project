<?php

namespace App\Http\Controllers;

use App\Mail\Admin\NewOrderMail;
use App\Mail\Customer\OrderCreatedMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\MidtransService;
use App\Services\RajaOngkirService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart');
        }

        $total        = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $bankAccounts = $this->bankAccounts();
        $midtransClientKey = config('midtrans.client_key');
        $midtransSnapUrl   = config('midtrans.snap_url');

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

        // Validasi stok semua item sebelum membuat order
        foreach ($cart as $item) {
            if ($item['variant_id']) {
                $variant = ProductVariant::find($item['variant_id']);
                if ($variant && $variant->track_stock && $variant->inventory_quantity < $item['quantity']) {
                    $msg = "Stok \"{$item['title']}" . ($item['variant_title'] ? " ({$item['variant_title']})" : '') . "\" tidak mencukupi.";
                    return back()->withErrors(['stock' => $msg])->withInput();
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock && $product->inventory_quantity < $item['quantity']) {
                    return back()->withErrors(['stock' => "Stok \"{$item['title']}\" tidak mencukupi."])->withInput();
                }
            }
        }

        $customer = Customer::updateOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name'  => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
            ]
        );

        $subtotal     = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $shippingCost = (int) ($validated['shipping_cost'] ?? 0);
        $total        = $subtotal + $shippingCost;

        $order = Order::create([
            'customer_id'          => $customer->id,
            'order_number'         => 'ORD-' . strtoupper(uniqid()),
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
            'total'                => $total,
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

        // Midtrans: buat snap token lalu kembalikan sebagai JSON
        if ($validated['payment_method'] === 'midtrans') {
            try {
                $snapToken = (new MidtransService)->createSnapToken($order->load('items'));
                $order->update(['snap_token' => $snapToken]);

                session()->forget('cart');
                session(['order_success' => $order->order_number]);

                return response()->json([
                    'snap_token'   => $snapToken,
                    'order_number' => $order->order_number,
                    'success_url'  => route('checkout.success'),
                ]);
            } catch (\Throwable) {
                $order->delete();
                return response()->json(['error' => 'Gagal menghubungi payment gateway. Silakan coba lagi.'], 422);
            }
        }

        self::decrementStock($cart);

        session()->forget('cart');
        session(['order_success' => $order->order_number]);

        Mail::to($order->customer_email)->send(new OrderCreatedMail($order));

        $adminEmail = Setting::get('contact_email', config('mail.from.address'));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewOrderMail($order));
        }

        try {
            (new WhatsAppService)->sendOrderCreated($order->load('items'));
        } catch (\Throwable) {
            // WhatsApp gagal tidak boleh batalkan order
        }

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

        $request->validate([
            'proof' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');
        $order->update(['payment_proof' => $path]);

        return back()->with('proof_uploaded', true);
    }

    private function bankAccounts(): array
    {
        $raw = Setting::get('bank_accounts', '[]');
        return json_decode($raw, true) ?: [];
    }

    public static function decrementStock(array $cart): void
    {
        foreach ($cart as $item) {
            if ($item['variant_id']) {
                $variant = ProductVariant::find($item['variant_id']);
                $variant?->decrementStock($item['quantity']);
            } else {
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    $product->decrement('inventory_quantity', $item['quantity']);
                }
            }
        }
    }
}
