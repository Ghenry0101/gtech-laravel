<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookController extends Controller
{
    /**
     * Biteship webhook endpoint.
     *
     * - Empty payload: installation ping, always reply "ok" without signature check.
     * - Payload present: validate signature (if configured), persist tracking events, and sync shipment metadata.
     */
    public function install(Request $request)
    {
        if ($this->isEmptyPayload($request)) {
            return $this->plainOk();
        }

        if ($this->shouldValidateSignature($request) && ! $this->isValidSignature($request)) {
            Log::warning('Biteship webhook signature invalid.');

            return response('invalid signature', 401)->header('Content-Type', 'text/plain');
        }

        $payload = $request->json()->all();
        $data = $payload['data'] ?? $payload;

        Log::info('biteship.webhook.received', ['payload' => $data]);

        $shipment = $this->resolveShipment($data);

        if (! $shipment) {
            Log::warning('Biteship webhook: shipment not found', [
                'biteship_order_id' => $data['id'] ?? $data['order_id'] ?? null,
                'tracking_id' => $this->extractTrackingId($data),
                'waybill_id' => $this->extractWaybillId($data),
            ]);

            return $this->plainOk();
        }

        DB::transaction(function () use ($shipment, $data): void {
            [$latestStatus, $latestRecordedAt, $deliveredRecordedAt] = $this->storeTrackingEvents($shipment, $data);

            $updates = $this->buildShipmentUpdates(
                shipment: $shipment,
                payload: $data,
                latestStatus: $latestStatus,
                latestRecordedAt: $latestRecordedAt,
                deliveredRecordedAt: $deliveredRecordedAt,
            );

            if (! empty($updates)) {
                $shipment->forceFill($updates)->save();
            }

            $this->syncOrderStatus($shipment, $updates['status'] ?? $latestStatus);
        });

        return $this->plainOk();
    }

    protected function isEmptyPayload(Request $request): bool
    {
        $content = trim((string) $request->getContent());

        return in_array($content, ['', '{}', '[]'], true) && empty($request->all());
    }

    protected function plainOk()
    {
        return response('ok', 200)->header('Content-Type', 'text/plain');
    }

    protected function shouldValidateSignature(Request $request): bool
    {
        return ! $this->isEmptyPayload($request) && (bool) config('biteship.webhook_secret');
    }

    protected function isValidSignature(Request $request): bool
    {
        $signature = $this->extractSignature($request);
        $secret = (string) config('biteship.webhook_secret');

        if (! $signature || $secret === '') {
            return false;
        }

        $normalizedSignature = str_ireplace('sha256=', '', trim($signature));
        $expected = hash_hmac('sha256', (string) $request->getContent(), $secret);

        return hash_equals($expected, $normalizedSignature);
    }

    protected function extractSignature(Request $request): ?string
    {
        $candidates = [
            $request->header('x-biteship-signature'),
            $request->header('biteship-signature'),
            $request->header('x-signature'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    protected function resolveShipment(array $payload): ?Shipment
    {
        $biteshipId = $payload['id'] ?? $payload['order_id'] ?? null;
        $trackingId = $this->extractTrackingId($payload);
        $waybillId = $this->extractWaybillId($payload);
        $orderNumber = data_get($payload, 'metadata.order_number') ?? data_get($payload, 'order_number');

        if (! $biteshipId && ! $trackingId && ! $waybillId && ! $orderNumber) {
            return null;
        }

        $shipmentQuery = Shipment::query()
            ->when($biteshipId, fn ($query) => $query->orWhere('biteship_order_id', $biteshipId))
            ->when($trackingId, fn ($query) => $query->orWhere('tracking_id', $trackingId))
            ->when($waybillId, fn ($query) => $query->orWhere('waybill_id', $waybillId));

        $shipment = $shipmentQuery->first();

        if (! $shipment && $orderNumber) {
            $order = Order::query()
                ->where('order_number', $orderNumber)
                ->with('shipment')
                ->first();

            $shipment = $order?->shipment;
        }

        return $shipment;
    }

    /**
     * @return array{0: string|null, 1: Carbon|null, 2: Carbon|null}
     */
    protected function storeTrackingEvents(Shipment $shipment, array $payload): array
    {
        $events = $this->extractEvents($payload);
        $latestStatus = null;
        $latestRecordedAt = null;
        $deliveredRecordedAt = null;

        foreach ($events as $event) {
            $mappedStatus = $this->mapStatus($event['status'] ?? null);

            if (! $mappedStatus) {
                continue;
            }

            $recordedAt = $this->parseRecordedAt($event);
            $recordedAtString = $recordedAt->toDateTimeString();

            $exists = $shipment->trackings()
                ->where('recorded_at', $recordedAtString)
                ->exists();

            if ($exists) {
                continue;
            }

            $description = trim((string) ($event['description'] ?? $event['note'] ?? $event['status'] ?? ''));

            $shipment->trackings()->create([
                'status' => $mappedStatus,
                'description' => $description !== '' ? $description : null,
                'recorded_at' => $recordedAt,
            ]);

            $latestStatus = $mappedStatus;
            $latestRecordedAt = $recordedAt;

            if ($mappedStatus === 'delivered') {
                $deliveredRecordedAt = $recordedAt;
            }
        }

        return [$latestStatus, $latestRecordedAt, $deliveredRecordedAt];
    }

    /**
     * @return array<array{status: string|null, description: string|null, recorded_at: string|null}>
     */
    protected function extractEvents(array $payload): array
    {
        $history = data_get($payload, 'history');

        if ($history !== null) {
            return collect(Arr::wrap($history))
                ->filter(fn ($item) => is_array($item))
                ->map(function (array $item): array {
                    return [
                        'status' => $item['status'] ?? null,
                        'description' => $item['description'] ?? ($item['note'] ?? null),
                        'recorded_at' => $item['recorded_at'] ?? ($item['updated_at'] ?? ($item['created_at'] ?? null)),
                    ];
                })
                ->values()
                ->all();
        }

        $status = $payload['status'] ?? null;
        $description = $payload['description'] ?? ($payload['note'] ?? null);
        $recordedAt = $payload['recorded_at'] ?? ($payload['updated_at'] ?? ($payload['created_at'] ?? null));

        if ($status || $description || $recordedAt) {
            return [[
                'status' => $status,
                'description' => $description,
                'recorded_at' => $recordedAt,
            ]];
        }

        return [];
    }

    protected function parseRecordedAt(array $event): Carbon
    {
        $recordedAt = $event['recorded_at'] ?? null;

        if ($recordedAt) {
            try {
                return Carbon::parse($recordedAt);
            } catch (\Throwable $e) {
                // Fallback below.
            }
        }

        return now();
    }

    protected function buildShipmentUpdates(
        Shipment $shipment,
        array $payload,
        ?string $latestStatus,
        ?Carbon $latestRecordedAt,
        ?Carbon $deliveredRecordedAt
    ): array {
        $trackingId = $this->extractTrackingId($payload);
        $waybillId = $this->extractWaybillId($payload);
        $status = $latestStatus ?? $this->mapStatus($payload['status'] ?? null);

        $updates = [
            'biteship_order_id' => $payload['id'] ?? $payload['order_id'] ?? $shipment->biteship_order_id,
            'tracking_id' => $trackingId ?? $shipment->tracking_id,
            'waybill_id' => $waybillId ?? $shipment->waybill_id,
            'driver_name' => $this->extractDriverField($payload, ['driver_name', 'name']),
            'driver_phone' => $this->extractDriverField($payload, ['driver_phone', 'phone', 'phone_number']),
            'driver_plate_number' => $this->extractDriverField($payload, ['driver_plate_number', 'vehicle_number', 'plate_number']),
            'status' => $status ?? $shipment->status,
        ];

        if ($status && $status !== 'processing' && ! $shipment->shipped_at) {
            $updates['shipped_at'] = $latestRecordedAt ?? now();
        }

        if ($status === 'delivered') {
            $updates['delivered_at'] = $deliveredRecordedAt ?? $shipment->delivered_at ?? now();
        }

        return array_filter($updates, fn ($value) => $value !== null);
    }

    protected function syncOrderStatus(Shipment $shipment, ?string $shipmentStatus): void
    {
        $order = $shipment->order;

        if (! $order) {
            return;
        }

        $mappedOrderStatus = match ($shipmentStatus) {
            'courier_allocated', 'picking_up', 'picked', 'delivering' => 'shipped',
            'delivered' => 'completed',
            default => null,
        };

        if ($mappedOrderStatus && $order->order_status !== 'canceled' && $order->order_status !== $mappedOrderStatus) {
            $order->forceFill([
                'order_status' => $mappedOrderStatus,
            ])->save();
        }
    }

    protected function extractTrackingId(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'tracking_id'),
            data_get($payload, 'tracking_number'),
            data_get($payload, 'waybill_id'),
            data_get($payload, 'courier.tracking_id'),
            data_get($payload, 'courier.tracking_number'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function extractWaybillId(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'waybill_id'),
            data_get($payload, 'courier.waybill_id'),
            data_get($payload, 'courier.tracking_id'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function extractDriverField(array $payload, array $keys): ?string
    {
        $sources = [
            'driver',
            'courier.driver',
            'courier',
        ];

        foreach ($sources as $source) {
            foreach ($keys as $key) {
                $value = data_get($payload, "{$source}.{$key}");
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }
        }

        foreach ($keys as $key) {
            $value = data_get($payload, $key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function mapStatus(?string $status): ?string
    {
        $normalized = strtolower((string) $status);

        return match ($normalized) {
            'confirmed', 'allocated' => 'courier_allocated',
            'courier_allocated' => 'courier_allocated',
            'picking_up', 'starting_pickup', 'on_pickup' => 'picking_up',
            'picked', 'picked_up' => 'picked',
            'delivering', 'on_delivery' => 'delivering',
            'delivered' => 'delivered',
            default => null,
        };
    }
}
