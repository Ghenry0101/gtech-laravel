<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\Product;
use App\Services\Storefront\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $carts
    ) {
    }

    public function index(Request $request): View
    {
        $cartItems = $this->carts->activeItems($request->user());

        return view('cart.index', [
            'cartItems' => $cartItems,
            'summary' => $this->carts->summary($cartItems),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'string', 'in:add,buy'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);
        $user = $request->user();

        try {
            $this->carts->addProduct($user, $product, $quantity);
        } catch (CartException $exception) {
            return back()->withErrors([
                'cart' => $exception->getMessage(),
            ]);
        }

        $action = $validated['action'] ?? 'add';
        $redirectRoute = 'cart.index';

        $message = $action === 'buy'
            ? __('Produk siap dibeli. Silakan cek keranjang Anda.')
            : __('Produk ditambahkan ke keranjang.');

        return redirect()
            ->route($redirectRoute)
            ->with('status', $message);
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $this->carts->ensureAccessible($request->user(), $cart);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $updated = $this->carts->updateQuantity($cart, $validated['quantity']);

        if (! $updated) {
            return redirect()->route('cart.index')->with('status', __('Produk dihapus karena stok habis.'));
        }

        return redirect()
            ->route('cart.index')
            ->with('status', __('Keranjang diperbarui.'));
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        $this->carts->ensureAccessible($request->user(), $cart);
        $this->carts->remove($cart);

        return redirect()
            ->route('cart.index')
            ->with('status', __('Produk dihapus dari keranjang.'));
    }
}
