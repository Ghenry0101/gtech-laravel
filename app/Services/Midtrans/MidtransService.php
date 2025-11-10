<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct(
        private readonly ?string $serverKey = null,
        private readonly ?string $clientKey = null,
        private readonly string $snapBaseUrl = 'https://app.sandbox.midtrans.com',
        private readonly string $coreApiBaseUrl = 'https://api.sandbox.midtrans.com',
        private readonly array $paymentMethods = [],
        private readonly bool $verifySsl = true,
    ) {
    }

    public static function make(): self
    {
        $config = config('midtrans');

        return new self(
            serverKey: $config['server_key'] ?? null,
            clientKey: $config['client_key'] ?? null,
            snapBaseUrl: $config['snap_base_url'] ?? 'https://app.sandbox.midtrans.com',
            coreApiBaseUrl: $config['core_api_base_url'] ?? 'https://api.sandbox.midtrans.com',
            paymentMethods: $config['payment_methods'] ?? [],
            verifySsl: (bool) ($config['verify_ssl'] ?? true),
        );
    }

    public function getClientKey(): ?string
    {
        return $this->clientKey;
    }

    /**
     * Create Snap transaction and return token with redirect URL.
     *
     * @param  array<int, array<string, mixed>>  $itemDetails
     * @param  array<string, mixed>  $customerDetails
     */
    public function createSnapTransaction(
        Order $order,
        Payment $payment,
        array $itemDetails,
        array $customerDetails,
        string $paymentMethodKey,
    ): array {
        $enabledPayments = $this->paymentMethods[$paymentMethodKey]['enabled_payments'] ?? [];

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $this->convertToGrossAmount($order->grand_total),
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => $enabledPayments,
            'credit_card' => [
                'secure' => true,
            ],
        ];

        $response = $this->snapClient()
            ->post('/snap/v1/transactions', $payload)
            ->throw()
            ->json();

        $payment->forceFill([
            'midtrans_order_id' => $order->order_number,
            'snap_token' => $response['token'] ?? null,
            'snap_redirect_url' => $response['redirect_url'] ?? null,
        ])->save();

        return $response;
    }

    public function verifySignature(array $payload): bool
    {
        $signature = $payload['signature_key'] ?? null;

        if (! $signature || empty($this->serverKey)) {
            return false;
        }

        $generated = hash(
            'sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            $this->serverKey
        );

        return hash_equals($generated, $signature);
    }

    public function getPaymentMethod(string $key): ?array
    {
        return $this->paymentMethods[$key] ?? null;
    }

    public function snapScriptUrl(): string
    {
        return rtrim($this->snapBaseUrl, '/').'/snap/snap.js';
    }

    protected function snapClient(): PendingRequest
    {
        return $this->authorizedClient()
            ->baseUrl($this->snapBaseUrl);
    }

    public function coreClient(): PendingRequest
    {
        return $this->authorizedClient()
            ->baseUrl($this->coreApiBaseUrl);
    }

    protected function authorizedClient(): PendingRequest
    {
        if (empty($this->serverKey)) {
            throw new \RuntimeException('Midtrans server key is not configured.');
        }

        return Http::withBasicAuth($this->serverKey, '')
            ->withOptions([
                'verify' => $this->verifySsl,
            ])
            ->asJson()
            ->acceptJson()
            ->timeout(30);
    }

    protected function convertToGrossAmount(float $value): int
    {
        return (int) round($value);
    }

    /**
     * Prepare item details compatible with Midtrans.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function normalizeItemDetails(array $items): array
    {
        return collect($items)
            ->map(function (array $item, int $index) {
                $price = (int) round($item['price'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 1);

                return [
                    'id' => (string) ($item['id'] ?? Str::uuid()),
                    'price' => $price,
                    'quantity' => max(1, $quantity),
                    'name' => (string) ($item['name'] ?? "Item {$index}"),
                ];
            })
            ->all();
    }
}
