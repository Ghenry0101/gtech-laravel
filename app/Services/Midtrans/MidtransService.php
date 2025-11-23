<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $paymentMethod = $this->paymentMethods[$paymentMethodKey] ?? null;

        if (! $paymentMethod) {
            throw new \InvalidArgumentException("Midtrans payment method [{$paymentMethodKey}] is not configured.");
        }

        $enabledPayments = $this->resolveEnabledPayments($paymentMethodKey, $paymentMethod);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $this->convertToGrossAmount($order->total_amount),
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'credit_card' => [
                'secure' => true,
            ],
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        if (! empty($enabledPayments)) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        // Jika metode yang dipilih adalah bank transfer, set default bank (misalnya BCA).
        if ($paymentMethodKey === 'bank_transfer') {
            $payload['bank_transfer'] = [
                'bank' => 'bca',
            ];
        }

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

    /**
     * Create transaction using Core API (without Snap UI) and persist instructions to payment.
     *
     * @param  array<int, array<string, mixed>>  $itemDetails
     * @param  array<string, mixed>  $customerDetails
     */
    public function createCoreCharge(
        Order $order,
        Payment $payment,
        array $itemDetails,
        array $customerDetails,
        string $paymentMethodKey,
        ?string $bank = null,
    ): array {
        $paymentMethod = $this->paymentMethods[$paymentMethodKey] ?? null;

        if (! $paymentMethod) {
            throw new \InvalidArgumentException("Midtrans payment method [{$paymentMethodKey}] is not configured.");
        }

        $paymentType = $this->resolvePaymentType($paymentMethodKey, $paymentMethod);

        $payload = [
            'payment_type' => $paymentType,
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $this->convertToGrossAmount($order->total_amount),
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'expiry' => $this->buildExpiry($order),
        ];

        $this->appendPaymentSpecificPayload($payload, $paymentType, $paymentMethod, $order, $bank);

        Log::info('midtrans.charge.request', [
            'order' => $order->order_number,
            'payment_type' => $paymentType,
            'payload' => $payload,
        ]);

        $response = $this->coreClient()
            ->post('/v2/charge', $payload)
            ->throw()
            ->json();

        Log::info('midtrans.charge.response', [
            'order' => $order->order_number,
            'payment_type' => $paymentType,
            'response' => $response,
        ]);

        $this->updatePaymentFromResponse($payment, $response, $order);

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

    public function getPaymentMethod(?string $key): ?array
    {
        if ($key === null) {
            return null;
        }

        return $this->paymentMethods[$key] ?? null;
    }

    public function snapScriptUrl(): string
    {
        return rtrim($this->snapBaseUrl, '/').'/snap/snap.js';
    }

    public function updatePaymentFromResponse(Payment $payment, array $response, ?Order $order = null): Payment
    {
        $instructions = $this->extractPaymentInstructions($response);
        $status = $this->normalizePaymentStatus($response['transaction_status'] ?? $payment->payment_status);

        $payment->forceFill([
            'midtrans_order_id' => $response['order_id'] ?? $order?->order_number ?? $payment->midtrans_order_id,
            'transaction_id' => $response['transaction_id'] ?? $payment->transaction_id,
            'payment_type' => $response['payment_type'] ?? $payment->payment_type,
            'payment_status' => $status,
            'bank' => $instructions['bank'] ?? $payment->bank,
            'va_number' => $instructions['va_number'] ?? $payment->va_number,
            'gross_amount' => $response['gross_amount'] ?? $payment->gross_amount,
            'fraud_status' => $response['fraud_status'] ?? $payment->fraud_status,
            'paid_at' => $this->isPaidStatus($response['transaction_status'] ?? null)
                ? ($payment->paid_at ?? now())
                : $payment->paid_at,
            'snap_token' => null,
            'snap_redirect_url' => null,
            'payload' => $response ?: $payment->payload,
            'qr_string' => $instructions['qr_string'] ?? $payment->qr_string,
            'payment_link' => $instructions['payment_link'] ?? $payment->payment_link,
            'payment_code' => $instructions['payment_code'] ?? $payment->payment_code,
        ])->save();

        return $payment;
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
     * Determine enabled payment channels for selected method.
     */
    protected function resolveEnabledPayments(string $paymentMethodKey, array $paymentMethod): array
    {
        $configured = array_values(array_filter($paymentMethod['enabled_payments'] ?? []));

        if (! empty($configured)) {
            return $configured;
        }

        $defaults = match ($paymentMethodKey) {
            'bank_transfer' => ['bank_transfer'],
            'qris' => ['qris'],
            'ewallet' => ['gopay', 'shopeepay'],
            default => array_filter([$paymentMethodKey]),
        };

        return array_values($defaults);
    }

    /**
     * Build payment_type for Core API.
     */
    protected function resolvePaymentType(string $paymentMethodKey, array $paymentMethod = []): string
    {
        if (! empty($paymentMethod['core_type'])) {
            return (string) $paymentMethod['core_type'];
        }

        return match ($paymentMethodKey) {
            'ewallet' => (string) ($paymentMethod['channel'] ?? 'gopay'),
            default => $paymentMethodKey,
        };
    }

    protected function appendPaymentSpecificPayload(
        array &$payload,
        string $paymentType,
        array $paymentMethod,
        Order $order,
        ?string $bankOverride = null,
    ): void {
        if ($paymentType === 'bank_transfer') {
            $payload['bank_transfer'] = [
                'bank' => $this->resolveBank($paymentMethod, $bankOverride),
            ];
        }

        if ($paymentType === 'gopay') {
            $payload['gopay'] = [
                'enable_callback' => true,
                'callback_url' => route('orders.show', $order),
            ];
        }

        if ($paymentType === 'shopeepay') {
            $payload['shopeepay'] = [
                'callback_url' => route('orders.show', $order),
            ];
        }
    }

    protected function resolveBank(array $paymentMethod, ?string $bankOverride = null): string
    {
        if ($bankOverride) {
            return strtolower($bankOverride);
        }

        if (! empty($paymentMethod['bank'])) {
            return (string) $paymentMethod['bank'];
        }

        $banks = array_values(array_filter($paymentMethod['banks'] ?? []));

        return $banks[0] ?? 'bca';
    }

    protected function extractPaymentInstructions(array $response): array
    {
        $actions = $response['actions'] ?? [];
        $primaryAction = $this->extractPrimaryActionUrl($actions);
        $vaNumber = data_get($response, 'va_numbers.0.va_number')
            ?? ($response['permata_va_number'] ?? null)
            ?? ($response['bill_key'] ?? null);
        $bank = data_get($response, 'va_numbers.0.bank');

        if (! $bank && isset($response['permata_va_number'])) {
            $bank = 'permata';
        }

        return [
            'bank' => $bank,
            'va_number' => $vaNumber,
            'payment_link' => $primaryAction,
            'qr_string' => $response['qr_string'] ?? null,
            'payment_code' => $response['payment_code'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $actions
     */
    protected function extractPrimaryActionUrl(array $actions): ?string
    {
        $preferredNames = ['deeplink', 'redirect', 'mobile', 'web', 'qr'];

        foreach ($preferredNames as $name) {
            foreach ($actions as $action) {
                if (! empty($action['url']) && ! empty($action['name']) && str_contains($action['name'], $name)) {
                    return $action['url'];
                }
            }
        }

        foreach ($actions as $action) {
            if (! empty($action['url'])) {
                return $action['url'];
            }
        }

        return null;
    }

    protected function normalizePaymentStatus(?string $status): string
    {
        return match ($status) {
            'settlement', 'capture' => 'settlement',
            'cancel', 'deny', 'failure' => 'cancel',
            'expire', 'expired' => 'expire',
            default => 'pending',
        };
    }

    protected function isPaidStatus(?string $status): bool
    {
        return in_array($status, ['capture', 'settlement'], true);
    }

    protected function buildExpiry(Order $order): array
    {
        $baseTime = $order->order_time ?? $order->created_at ?? Carbon::now('Asia/Jakarta');
        $startTime = $baseTime->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s O');

        return [
            'start_time' => $startTime,
            'duration' => 24,
            'unit' => 'hour',
        ];
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
