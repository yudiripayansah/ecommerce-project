<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart  = session('cart', []);
        $total = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        return view('theme.templates.cart', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        $product = Product::with('featuredImage')->findOrFail($request->product_id);
        $variant = $request->variant_id
            ? ProductVariant::with('image')->find($request->variant_id)
            : $product->variants()->with('image')->orderBy('position')->first();

        // Validasi stok sebelum ditambahkan ke keranjang
        if ($variant) {
            if ($variant->track_stock && $variant->inventory_quantity < $request->quantity) {
                $available = max(0, $variant->inventory_quantity);
                return back()->withErrors(['stock' => "Stok tidak mencukupi. Tersedia: {$available} item."]);
            }
        } elseif ($product->track_stock && $product->inventory_quantity < $request->quantity) {
            $available = max(0, $product->inventory_quantity);
            return back()->withErrors(['stock' => "Stok tidak mencukupi. Tersedia: {$available} item."]);
        }

        $price = $variant?->price ?? $product->price;
        $key   = $product->id . '-' . ($variant?->id ?? 0);

        $variantTitle = $variant
            ? collect([$variant->option1, $variant->option2, $variant->option3])->filter()->implode(' / ')
            : null;

        // Prefer variant image; fall back to product featured image
        $imageSource = $variant?->image ?? $product->featuredImage;
        $image       = $imageSource
            ? (parse_url($imageSource->url, PHP_URL_PATH) ?? '/storage/' . $imageSource->path)
            : null;

        $cart = session('cart', []);

        // Cek total di keranjang + qty baru tidak melebihi stok
        $currentQty = $cart[$key]['quantity'] ?? 0;
        $newQty     = $currentQty + $request->quantity;

        if ($variant && $variant->track_stock && $variant->inventory_quantity < $newQty) {
            $available = max(0, $variant->inventory_quantity - $currentQty);
            return back()->withErrors(['stock' => "Tidak bisa tambah lagi. Stok tersisa: {$available} item."]);
        } elseif (! $variant && $product->track_stock && $product->inventory_quantity < $newQty) {
            $available = max(0, $product->inventory_quantity - $currentQty);
            return back()->withErrors(['stock' => "Tidak bisa tambah lagi. Stok tersisa: {$available} item."]);
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = min(99, $newQty);
        } else {
            $cart[$key] = [
                'product_id'    => $product->id,
                'variant_id'    => $variant?->id,
                'handle'        => $product->handle,
                'title'         => $product->title,
                'variant_title' => $variantTitle,
                'price'         => (float) $price,
                'quantity'      => $request->quantity,
                'image'         => $image,
            ];
        }

        session(['cart' => $cart]);

        if ($request->boolean('redirect_to_cart')) {
            return redirect()->route('cart');
        }

        return back()->with('cart_success', "\u{201C}{$product->title}\u{201D} ditambahkan ke keranjang.");
    }

    public function update(Request $request, string $key)
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);

        $cart = session('cart', []);

        if (isset($cart[$key])) {
            if ($request->quantity < 1) {
                unset($cart[$key]);
            } else {
                $item = $cart[$key];

                // Validasi stok saat update kuantitas
                if ($item['variant_id']) {
                    $variant = ProductVariant::find($item['variant_id']);
                    if ($variant && $variant->track_stock && $variant->inventory_quantity < $request->quantity) {
                        return back()->withErrors(['stock' => "Stok tersisa {$variant->inventory_quantity} item."]);
                    }
                } else {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->track_stock && $product->inventory_quantity < $request->quantity) {
                        return back()->withErrors(['stock' => "Stok tersisa {$product->inventory_quantity} item."]);
                    }
                }

                $cart[$key]['quantity'] = $request->quantity;
            }
            session(['cart' => $cart]);
        }

        return back();
    }

    public function remove(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return back();
    }

    public function clear()
    {
        session()->forget('cart');

        return back();
    }
}
