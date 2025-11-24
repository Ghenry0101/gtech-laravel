<?php

namespace App\Http\Controllers;

use App\Services\Webhooks\BiteshipWebhookService;
use Illuminate\Http\Request;

class BiteshipWebhookController extends Controller
{
    public function __construct(
        private readonly BiteshipWebhookService $webhook
    ) {
    }

    public function install(Request $request)
    {
        return $this->webhook->handle($request);
    }
}
