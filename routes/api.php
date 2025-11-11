<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

Route::post('/payment/notification', [CheckoutController::class, 'callback'])
    ->name('midtrans.callback');
