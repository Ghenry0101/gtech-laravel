<?php

namespace App\Services\Biteship;

use App\Models\Address;
use App\Models\Cart;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $baseUrl = null,
        private readonly array $origin = [],
        private readonly array $defaultCouriers = [],
        private readonly bool $verifySsl = true,
        private readonly bool $mockRatesEnabled = false,
        private readonly array $mockRates = [],
    ) {
    }

    public static function make(): self
    {
        $config = config('biteship');

        return new self(
            apiKey: $config['api_key'] ?? null,
            baseUrl: $config['base_url'] ?? null,
            origin: $config['origin'] ?? [],
            defaultCouriers: $config['default_couriers'] ?? [],
            verifySsl: (bool) ($config['verify_ssl'] ?? true),
            mockRatesEnabled: (bool) ($config['mock_rates_enabled'] ?? false),
            mockRates: $config['mock_rates'] ?? [],
        );
    }

    /**
     * Fetch available shipping rates from Biteship.
     *
     * @param  Collection<int, Cart>  $cartItems
     * @return array<int, array<string, mixed>>
     *
     * @throws RequestException
     */
    public function getRates(Address $address, Collection $cartItems): array
    {
        if ($cartItems->isEmpty()) {
            return [];
        }

        try {
            $client = $this->client();
            $destinationAreaId = $this->resolveDestinationAreaId($address);

            $payload = [
                'origin_area_id' => $this->origin['area_id'] ?? null,
                'destination_area_id' => $destinationAreaId,
                'destination_postal_code' => $address->postal_code,
                'couriers' => implode(',', $this->defaultCouriers),
                'items' => $this->buildItemsPayload($cartItems),
            ];

            if (empty($payload['origin_area_id'])) {
                $payload['origin_postal_code'] = $this->origin['postal_code'] ?? null;
            }

            if (! $destinationAreaId) {
                unset($payload['destination_area_id']);
            }

            $response = $client->post('/rates/couriers', array_filter($payload))->throw();

            $data = $response->json('data', $response->json('pricing', []));

            return collect($data)
                ->map(function (array $rate): array {
                    $courier = $rate['courier'] ?? [];
                    $duration = $rate['duration'] ?? [];

                    return [
                        'courier_code' => $courier['code'] ?? null,
                        'courier_company' => $courier['company'] ?? null,
                        'courier_service_code' => $courier['service_code'] ?? null,
                        'courier_service_name' => $courier['service_name'] ?? null,
                        'courier_description' => $courier['description'] ?? null,
                        'cost' => (int) ($rate['price'] ?? 0),
                        'estimation' => $this->formatDuration($duration),
                        'duration_min' => $duration['min'] ?? null,
                        'duration_max' => $duration['max'] ?? null,
                        'raw' => $rate,
                    ];
                })
                ->filter(fn (array $item) => $item['courier_code'] && $item['courier_service_code'])
                ->values()
                ->all();
        } catch (RequestException $exception) {
            if ($this->shouldUseMockRates($exception)) {
                return $this->generateMockRates($cartItems);
            }

            throw $exception;
        }
    }

    /**
     * Search Biteship areas for address autocomplete.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchAreas(string $query, int $limit = 10): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $limit = max(1, min($limit, 20));

        $response = $this->client()
            ->get('/maps/areas', [
                'countries' => 'ID',
                'input' => $query,
                'limit' => $limit,
                'type' => 'single',
            ])
            ->throw();

        return collect($response->json('areas', []))
            ->map(fn (array $area) => $this->formatAreaData($area))
            ->filter(fn (array $area) => $area['id'] !== null)
            ->values()
            ->all();
    }

    public function getAreaDetail(string $areaId): ?array
    {
        $response = $this->client()
            ->get("/maps/areas/{$areaId}", [
                'type' => 'single',
            ])
            ->throw();

        $area = collect($response->json('areas', []))->first();

        return $area ? $this->formatAreaData($area) : null;
    }

    /**
     * Create shipment order after payment success.
     *
     * @throws RequestException
     */
    public function createShipment(array $payload): array
    {
        return $this->client()
            ->post('/orders', $payload)
            ->throw()
            ->json();
    }

    protected function client(): PendingRequest
    {
        if (empty($this->apiKey) || empty($this->baseUrl)) {
            throw new \RuntimeException('Biteship API configuration is incomplete.');
        }

        return Http::baseUrl($this->baseUrl)
            ->withOptions([
                'verify' => $this->verifySsl,
            ])
            ->withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
    }

    protected function buildItemsPayload(Collection $cartItems): array
    {
        return $cartItems->map(function (Cart $cart): array {
            $product = $cart->product;
            $weight = (int) ($product?->weight ?? 500);

            return [
                'id' => $product?->id,
                'name' => $product?->name ?? 'Produk',
                'description' => $product?->description,
                'value' => (int) round($cart->price),
                'quantity' => $cart->quantity,
                'weight' => $weight * max($cart->quantity, 1),
                'length' => (int) ($product?->length ?? 10),
                'width' => (int) ($product?->width ?? 10),
                'height' => (int) ($product?->height ?? 10),
            ];
        })->all();
    }

    protected function resolveDestinationAreaId(Address $address): ?string
    {
        if ($address->biteship_area_id) {
            return $address->biteship_area_id;
        }

        if (! $address->postal_code) {
            return null;
        }

        try {
            $response = $this->client()->get('/areas', [
                'countries' => 'ID',
                'input' => $address->district ?? $address->city ?? $address->province,
                'type' => 'single',
                'postal_code' => $address->postal_code,
                'limit' => 5,
            ])->throw();
        } catch (RequestException $e) {
            Log::warning('Failed to resolve Biteship area id', [
                'address_id' => $address->id,
                'postal_code' => $address->postal_code,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $firstMatch = collect($response->json('areas', $response->json('data', [])))
            ->first(fn (array $area) => ($area['postal_code'] ?? null) === $address->postal_code);

        if (! $firstMatch) {
            return null;
        }

        $address->forceFill([
            'biteship_area_id' => $firstMatch['id'] ?? null,
        ])->saveQuietly();

        return $address->biteship_area_id;
    }

    protected function shouldUseMockRates(?RequestException $exception): bool
    {
        if (! $this->mockRatesEnabled) {
            return false;
        }

        if ($exception === null) {
            return true;
        }

        $response = $exception->response;
        if ($response === null) {
            return true;
        }

        $body = strtolower($response->body() ?? '');

        return $response->status() === 402
            || Str::contains($body, 'no sufficient balance')
            || Str::contains($body, 'insufficient balance')
            || Str::contains($body, 'top up');
    }

    protected function generateMockRates(Collection $cartItems): array
    {
        $weightGrams = $this->calculateCartWeight($cartItems);
        $weightKg = max(1, (int) ceil($weightGrams / 1000));

        return collect($this->mockRates)
            ->flatMap(function (array $courier) use ($weightKg) {
                $services = $courier['services'] ?? [];

                return collect($services)->map(function (array $service) use ($courier, $weightKg) {
                    $base = (int) ($service['base_price'] ?? 0);
                    $perKg = (int) ($service['per_kg'] ?? 0);
                    $price = $base + ($perKg * $weightKg);

                    return [
                        'courier_code' => $courier['courier_code'] ?? null,
                        'courier_company' => $courier['courier_company'] ?? null,
                        'courier_service_code' => $service['service_code'] ?? null,
                        'courier_service_name' => $service['service_name'] ?? null,
                        'courier_description' => 'Simulasi ongkir (mock)',
                        'cost' => $price,
                        'estimation' => $service['estimation'] ?? null,
                        'duration_min' => null,
                        'duration_max' => null,
                        'raw' => [
                            'mock' => true,
                            'weight_kg' => $weightKg,
                            'base_price' => $base,
                            'per_kg' => $perKg,
                        ],
                    ];
                });
            })
            ->filter(fn (array $item) => $item['courier_code'] && $item['courier_service_code'])
            ->values()
            ->all();
    }

    protected function calculateCartWeight(Collection $cartItems): int
    {
        return (int) $cartItems->sum(function (Cart $cart) {
            $weight = (int) ($cart->product?->weight ?? 1000); // default 1kg

            return max(1, $cart->quantity) * max(100, $weight);
        });
    }

    protected function formatDuration(?array $duration): ?string
    {
        if (! $duration) {
            return null;
        }

        $min = $duration['min'] ?? null;
        $max = $duration['max'] ?? null;
        $unit = $duration['unit'] ?? 'day';

        if ($min && $max && $min !== $max) {
            return "{$min}-{$max} {$unit}";
        }

        if ($min) {
            return "{$min} {$unit}";
        }

        return null;
    }

    protected function formatAreaData(array $area): array
    {
        $postal = $area['postal_code'] ?? null;

        return [
            'id' => $area['id'] ?? null,
            'label' => $area['name'] ?? null,
            'province' => $area['administrative_division_level_1_name'] ?? null,
            'city' => $area['administrative_division_level_2_name'] ?? null,
            'district' => $area['administrative_division_level_3_name'] ?? null,
            'postal_code' => $postal !== null ? (string) $postal : null,
            'raw' => $area,
        ];
    }
}
