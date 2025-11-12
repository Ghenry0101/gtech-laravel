<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminBarangController;
use App\Http\Controllers\Admin\AdminShippingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\ProfileAddressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\BiteshipAreaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemReviewController;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

$homePage = function () {
    if (! Schema::hasTable('products')) {
        return view('home', [
            'popular' => [],
            'latest' => [],
        ]);
    }

    $popularProducts = Product::query()
        ->where('is_active', true)
        ->orderByDesc('stock')
        ->orderByDesc('updated_at')
        ->take(8)
        ->get();

    $latestProducts = Product::query()
        ->where('is_active', true)
        ->latest()
        ->take(8)
        ->get();

    $transformProduct = static function (Product $product): array {
        $imagePath = $product->product_image ? 'storage/'.$product->product_image : 'images/PC.png';
        $hasDiscount = $product->hasDiscountConfigured();
        $isDiscountActive = $product->hasDiscountActive();
        $plannedPrice = $product->discount_price;

        if ($plannedPrice === null && $product->discount_percent !== null) {
            $plannedPrice = round($product->price - ($product->price * $product->discount_percent / 100), 2);
        }

        return [
            'title' => $product->name,
            'slug' => $product->slug,
            'status' => $product->stock > 0 ? 'available' : 'unavailable',
            'price' => $isDiscountActive && $plannedPrice !== null
                ? (float) $product->effective_price
                : (float) ($plannedPrice !== null ? $plannedPrice : $product->price),
            'original_price' => (float) $product->price,
            'planned_price' => $plannedPrice,
            'has_discount' => $hasDiscount,
            'is_discount_active' => $isDiscountActive,
            'discount_percent' => $product->discount_percent,
            'discount_start' => optional($product->discount_start)?->format('d M Y H:i'),
            'discount_end' => optional($product->discount_end)?->format('d M Y H:i'),
            'image_path' => $imagePath,
            'stock' => $product->stock,
        ];
    };

    return view('home', [
        'popular' => $popularProducts->map($transformProduct)->all(),
        'latest' => $latestProducts->map($transformProduct)->all(),
    ]);
};

Route::get('/', $homePage);
Route::get('/home', $homePage)->name('home');

Route::get('/products/{product:slug}', [StorefrontProductController::class, 'show'])
    ->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/biteship/areas', BiteshipAreaController::class)->name('biteship.areas.search');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'profile.complete'])->name('dashboard');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])->name('checkout.shipping-rates');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order:order_number}/complete', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/addresses', [ProfileAddressController::class, 'store'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [ProfileAddressController::class, 'update'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileAddressController::class, 'destroy'])->name('profile.addresses.destroy');

    Route::post('/order-items/{orderItem}/review', [OrderItemReviewController::class, 'store'])->name('order-items.review.store');
    Route::put('/order-items/{orderItem}/review', [OrderItemReviewController::class, 'update'])->name('order-items.review.update');
});

Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::patch('/users/{user}/role', [RoleController::class, 'assignRole']);

Route::middleware(['auth', 'role:admin_barang'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/barang', [AdminBarangController::class, 'index'])->name('barang.dashboard');
        Route::resource('barang/products', AdminProductController::class)
            ->names('barang.products')
            ->except(['show']);
        Route::resource('barang/categories', AdminCategoryController::class)
            ->names('barang.categories')
            ->except(['show']);
    });

Route::middleware(['auth', 'role:admin_pengiriman'])
    ->prefix('admin/pengiriman')
    ->name('admin.shipping.')
    ->group(function () {
        Route::get('/', [AdminShippingController::class, 'index'])->name('dashboard');
        Route::get('/orders/{order:order_number}', [AdminShippingController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order:order_number}/shipment', [AdminShippingController::class, 'updateShipment'])->name('orders.shipment.update');
        Route::patch('/orders/{order:order_number}/status', [AdminShippingController::class, 'updateStatus'])->name('orders.status.update');
    });

require __DIR__.'/auth.php';

Route::post('/midtrans/webhook', MidtransWebhookController::class)->name('midtrans.webhook');
