<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = Cart::query()
            ->with('product.category')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        $summary = [
            'items' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('subtotal'),
            'total_products' => $cartItems->count(),
        ];

        return view('cart.index', [
            'cartItems' => $cartItems,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'string', 'in:add,buy'],
        ]);

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);

        if ($product->stock < 1) {
            return back()->withErrors([
                'cart' => __('Produk :name sedang habis stok.', ['name' => $product->name]),
            ]);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);
        $quantity = max(1, $quantity);
        $maxQuantity = max($product->stock, 1);
        $quantity = min($quantity, $maxQuantity);

        $user = $request->user();
        $cartEntry = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        $cartEntry->price = $product->effective_price;
        $cartEntry->quantity = $cartEntry->exists ? $cartEntry->quantity + $quantity : $quantity;
        $cartEntry->save();

        $redirectRoute = $validated['action'] === 'buy' ? 'cart.index' : 'cart.index';

        $message = $validated['action'] === 'buy'
            ? __('Produk siap dibeli. Silakan cek keranjang Anda.')
            : __('Produk ditambahkan ke keranjang.');

        return redirect()
            ->route($redirectRoute)
            ->with('status', $message);
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $this->authorizeCart($request, $cart);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productStock = max($cart->product?->stock ?? 0, 0);
        if ($productStock === 0) {
            $cart->delete();

            return redirect()
                ->route('cart.index')
                ->with('status', __('Produk dihapus karena stok habis.'));
        }

        $maxQuantity = max($productStock, 1);
        $cart->quantity = min($validated['quantity'], $maxQuantity);
        $cart->save();

        return redirect()
            ->route('cart.index')
            ->with('status', __('Keranjang diperbarui.'));
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        $this->authorizeCart($request, $cart);
        $cart->delete();

        return redirect()
            ->route('cart.index')
            ->with('status', __('Produk dihapus dari keranjang.'));
    }

    protected function authorizeCart(Request $request, Cart $cart): void
    {
        abort_if($cart->user_id !== $request->user()->id, 403);
        abort_if($cart->status !== 'active', 404);
    }
}
