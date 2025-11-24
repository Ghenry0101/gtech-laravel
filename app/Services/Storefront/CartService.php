<?php

namespace App\Services\Storefront;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function activeItems(User $user): Collection
    {
        return Cart::query()
            ->with('product.category')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    public function summary(Collection $cartItems): array
    {
        return [
            'items' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('subtotal'),
            'total_products' => $cartItems->count(),
        ];
    }

    /**
     * @throws CartException
     */
    public function addProduct(User $user, Product $product, int $quantity): void
    {
        if ($product->stock < 1) {
            throw new CartException(__('Produk :name sedang habis stok.', ['name' => $product->name]));
        }

        $quantity = $this->clampQuantity($quantity, $product->stock);

        $cartEntry = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        $cartEntry->price = $product->effective_price;
        $cartEntry->quantity = $cartEntry->exists ? $cartEntry->quantity + $quantity : $quantity;
        $cartEntry->save();
    }

    /**
     * Update quantity and return true when cart remains, false when removed.
     */
    public function updateQuantity(Cart $cart, int $requestedQuantity): bool
    {
        $productStock = max($cart->product?->stock ?? 0, 0);
        if ($productStock === 0) {
            $cart->delete();

            return false;
        }

        $cart->quantity = $this->clampQuantity($requestedQuantity, $productStock);
        $cart->save();

        return true;
    }

    public function remove(Cart $cart): void
    {
        $cart->delete();
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function ensureAccessible(User $user, Cart $cart): void
    {
        abort_if($cart->user_id !== $user->id, 403);
        abort_if($cart->status !== 'active', 404);
    }

    private function clampQuantity(int $quantity, int $stock): int
    {
        $quantity = max(1, $quantity);
        $maxQuantity = max(1, $stock);

        return min($quantity, $maxQuantity);
    }
}

