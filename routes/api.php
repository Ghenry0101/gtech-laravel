<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\BiteshipWebhookController;

Route::post('/payment/notification', [CheckoutController::class, 'callback'])
    ->name('midtrans.callback');

// Biteship webhook endpoint (installation ping returns "ok"; signature enforced for real events)
Route::post('/webhook/biteship', [BiteshipWebhookController::class, 'install'])->name('biteship.webhook.install');
