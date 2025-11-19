<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminBarangController;
use App\Http\Controllers\Admin\AdminShippingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\ProfileAddressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\BiteshipAreaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemReviewController;
use App\Models\Product;
use App\Support\CategoryMenu;
use App\ViewModels\ProductCardViewModel;
use Illuminate\Support\Facades\Schema;

$homePage = function () {
    $categories = CategoryMenu::active();

    if (! Schema::hasTable('products')) {
        return view('home', [
            'categories' => $categories,
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

    return view('home', [
        'categories' => $categories,
        'popular' => ProductCardViewModel::collection($popularProducts),
        'latest' => ProductCardViewModel::collection($latestProducts),
    ]);
};

Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/', $homePage);
Route::get('/home', $homePage)->name('home');

Route::get('/products/{product:slug}', [StorefrontProductController::class, 'show'])
    ->name('products.show');
Route::get('/produks/{categorySlug?}', [StorefrontProductController::class, 'index'])
    ->name('products.index');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/biteship/areas', BiteshipAreaController::class)->name('biteship.areas.search');
});

Route::get('/dashboard', $homePage, function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'profile.complete'])->name('dashboard');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])->name('checkout.shipping-rates');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order:order_number}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order:order_number}/complaints', [ComplaintController::class, 'store'])->name('orders.complaints.store');

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
        Route::post('/barang/products/generate-sku', [AdminProductController::class, 'generateSku'])
            ->name('barang.products.generate-sku');
        Route::resource('barang/products', AdminProductController::class)
            ->names('barang.products')
            ->except(['show']);
        Route::resource('barang/categories', AdminCategoryController::class)
            ->names('barang.categories')
            ->except(['show']);
        Route::resource('barang/brands', AdminBrandController::class)
            ->names('barang.brands')
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
        Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
        Route::patch('/complaints/{complaint}', [AdminComplaintController::class, 'updateStatus'])->name('complaints.update');
    });

require __DIR__.'/auth.php';

Route::post('/midtrans/webhook', MidtransWebhookController::class)->name('midtrans.webhook');
