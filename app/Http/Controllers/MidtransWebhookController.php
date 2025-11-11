<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, CheckoutController $checkoutController): JsonResponse
    {
        return $checkoutController->callback($request);
    }
}
