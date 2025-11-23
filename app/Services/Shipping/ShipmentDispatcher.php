<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment as ShipmentModel;
use App\Services\Biteship\BiteshipService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShipmentDispatcher
{
    public function __construct(
        private readonly BiteshipService $biteship,
    ) {
    }

    public static function make(): self
    {
        return new self(BiteshipService::make());
    }

    /**
     * Kirim order ke Biteship untuk mendapatkan nomor resi.
     */
    public function dispatch(Order $order): bool
    {
        $order->loadMissing(['shipment', 'items.product']);

        $shipment = $order->shipment;
        if (! $shipment) {
            return false;
        }

        $destination = $this->resolveDestinationData($shipment, $order);

        if (! $destination) {
            $this->assignFallbackTracking($order, 'missing_destination_snapshot');

            return false;
        }

        if ($shipment->biteship_order_id && $shipment->tracking_id && $shipment->waybill_id) {
            return true;
        }

        if ($shipment->biteship_order_id && (! $shipment->tracking_id || ! $shipment->waybill_id)) {
            $this->assignFallbackTracking($order, 'missing_tracking_existing_biteship');

            return true;
        }

        if (empty($destination['biteship_area_id'])) {
            Log::warning('Shipment destination missing Biteship area id.', [
                'order_id' => $order->id,
                'shipment_id' => $shipment->id,
            ]);
            $this->assignFallbackTracking($order, 'missing_destination_area');

            return false;
        }

        $origin = config('biteship.origin', []);
        if (! $origin) {
            Log::warning('Biteship origin configuration missing. Skip dispatch.', [
                'order_id' => $order->id,
            ]);
        $this->assignFallbackTracking($order, 'missing_origin');

        return false;
    }

        $ratePayload = $shipment->rate_payload ?? [];
        $selectedRate = data_get($ratePayload, 'selected_rate', $ratePayload);

        Log::info('shipment.selected_rate', [
            'order_id' => $order->id,
            'shipment_id' => $shipment->id,
            'selected_rate' => $selectedRate,
        ]);
        $courierType = $this->extractCourierType($shipment->courier_service_code, $selectedRate);
        $courierCompany = data_get($selectedRate, 'courier.company')
            ?? $shipment->courier_name
            ?? Arr::get($selectedRate, 'courier_code');

        $itemsPayload = $order->items->map(function ($item) {
            $weight = (int) ($item->product?->weight ?? 500);

            return [
                'name' => $item->product_name,
                'value' => (int) round($item->price),
                'quantity' => $item->quantity,
                'weight' => $weight * $item->quantity,
            ];
        })->all();

        $payload = [
            'shipper_contact_name' => $origin['contact_name'] ?? null,
            'shipper_contact_phone' => $origin['contact_phone'] ?? null,
            'origin_contact_name' => $origin['contact_name'] ?? null,
            'origin_contact_phone' => $origin['contact_phone'] ?? null,
            'origin_address' => $origin['address'] ?? null,
            'origin_postal_code' => $origin['postal_code'] ?? null,
            'origin_area_id' => $origin['area_id'] ?? null,
            'destination_contact_name' => $destination['recipient_name'] ?? $order->recipient_name,
            'destination_contact_phone' => $destination['phone'] ?? $order->phone,
            'destination_address' => $destination['detail'] ?? ($destination['full_address'] ?? $order->full_address),
            'destination_postal_code' => $destination['postal_code'] ?? null,
            'destination_area_id' => $destination['biteship_area_id'] ?? null,
            'courier_company' => $courierCompany,
            'courier_type' => $courierType,
            'items' => $itemsPayload,
            'order_note' => $order->notes,
            'metadata' => [
                'order_number' => $order->order_number,
            ],
        ];

        $payload['distance'] = data_get($selectedRate, 'distance')
            ?? data_get($selectedRate, 'summary.distance');

        if ($deliveryType = $this->determineDeliveryType($selectedRate, $courierType)) {
            $payload['delivery_type'] = $deliveryType;
        }

        try {
            $response = $this->biteship->createShipment(array_filter($payload));

            $trackingId = $response['tracking_number']
                ?? $response['tracking_id']
                ?? data_get($response, 'courier.waybill_id')
                ?? data_get($response, 'courier.tracking_id')
                ?? data_get($response, 'waybill_id')
                ?? $shipment->tracking_id;
            $waybillId = $response['waybill_id']
                ?? data_get($response, 'courier.waybill_id')
                ?? ($shipment->waybill_id ?: $trackingId);
            $mappedShipmentStatus = $this->mapShipmentStatus(data_get($response, 'status'));
            $normalizedStatus = $this->normalizeStatus($mappedShipmentStatus ?? $shipment->status ?? 'processing');

            $shipment->forceFill([
                'biteship_order_id' => $response['id'] ?? $response['order_id'] ?? $shipment->biteship_order_id,
                'tracking_id' => $trackingId,
                'waybill_id' => $waybillId,
                'status' => $normalizedStatus,
                'rate_payload' => array_merge($shipment->rate_payload ?? [], ['order' => $response]),
                'shipped_at' => $shipment->shipped_at ?? now(),
            ])->save();

            if (! $shipment->tracking_id || ! $shipment->waybill_id) {
                $this->assignFallbackTracking($order, 'biteship_no_tracking');
            }

            return true;
        } catch (\Throwable $throwable) {
            Log::error('Failed creating Biteship shipment.', [
                'order_id' => $order->id,
                'message' => $throwable->getMessage(),
            ]);
            $this->assignFallbackTracking($order, 'biteship_failed');

            return false;
        }
    }

    protected function extractCourierType(?string $serviceCode, array $ratePayload): string
    {
        $raw = data_get($ratePayload, 'raw');
        $courierType = data_get($raw, 'type')
            ?? data_get($raw, 'courier_type')
            ?? data_get($ratePayload, 'courier.type')
            ?? data_get($ratePayload, 'courier_type')
            ?? data_get($ratePayload, 'type')
            ?? data_get($ratePayload, 'service_type')
            ?? 'regular';

        $type = strtolower((string) $courierType);
        $serviceCode = strtolower((string) $serviceCode);

        if (in_array($type, ['regular', 'standard', 'reguler'], true)) {
            if ($serviceCode !== '') {
                if (str_contains($serviceCode, 'same')) {
                    return 'same_day';
                }

                if (str_contains($serviceCode, 'instant')) {
                    return 'instant';
                }
            }

            return 'reg';
        }

        return $type !== '' ? $type : 'reg';
    }

    protected function mapShipmentStatus(?string $status): ?string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'confirmed', 'allocated', 'pending', 'ready_to_pickup', 'waiting_assignment', 'awaiting_pickup' => 'processing',
            'courier_allocated' => 'courier_allocated',
            'picking_up', 'starting_pickup', 'on_pickup', 'courier_pickup' => 'picking_up',
            'picked', 'picked_up' => 'picked',
            'delivering',
            'in_transit',
            'shipped',
            'on_delivery',
            'dropping_off',
            'dropping_off_item',
            'on_the_way',
            'out_for_delivery',
            'reg' => 'on_the_way',
            'delivered', 'complete', 'completed' => 'delivered',
            'failed', 'cancelled', 'canceled' => 'failed',
            default => null,
        };
    }

    protected function normalizeStatus(?string $status): string
    {
        $allowed = [
            'processing',
            'courier_allocated',
            'picking_up',
            'picked',
            'on_the_way',
            'delivering',
            'delivered',
            'failed',
        ];

        $status = strtolower((string) $status);

        return in_array($status, $allowed, true) ? $status : 'processing';
    }

    protected function determineDeliveryType(array $ratePayload, string $courierType): ?string
    {
        $deliveryType = data_get($ratePayload, 'delivery_type');
        $normalizedType = strtolower((string) $courierType);
        $instantCouriers = ['instant', 'same_day', 'sameday', 'on_demand', 'ondemand'];

        if (! in_array($normalizedType, $instantCouriers, true)) {
            return null;
        }

        if (! $deliveryType) {
            return 'now';
        }

        return strtolower((string) $deliveryType);
    }

    protected function assignFallbackTracking(Order $order, string $reason = 'fallback'): void
    {
        $shipment = $order->shipment;

        if (! $shipment) {
            return;
        }

        $needsTracking = blank($shipment->tracking_id);
        $needsWaybill = blank($shipment->waybill_id);

        if (! $needsTracking && ! $needsWaybill) {
            return;
        }

        $code = $this->generateFallbackTrackingCode($order);
        $updates = [
            'status' => $shipment->status ?? 'processing',
            'shipped_at' => $shipment->shipped_at ?? now(),
        ];

        if ($needsTracking) {
            $updates['tracking_id'] = $code;
        }

        if ($needsWaybill) {
            $updates['waybill_id'] = $code;
        }

        $shipment->forceFill($updates)->save();

        Log::info('Assign fallback tracking id', [
            'order_id' => $order->id,
            'tracking_id' => $code,
            'waybill_id' => $updates['waybill_id'] ?? $shipment->waybill_id,
            'reason' => $reason,
        ]);
    }

    protected function generateFallbackTrackingCode(Order $order): string
    {
        $prefix = sprintf('GT-%s-', now()->format('ymd'));
        $random = Str::upper(Str::random(4));
        $idSuffix = Str::upper(substr((string) $order->id, -6));

        return $prefix.$random.$idSuffix;
    }

    protected function resolveDestinationData(ShipmentModel $shipment, Order $order): ?array
    {
        $payload = $shipment->rate_payload ?? [];
        $destination = data_get($payload, 'destination');

        if (is_array($destination) && ! empty($destination)) {
            $destination['full_address'] = $destination['full_address'] ?? data_get($payload, 'full_address');

            return $destination;
        }

        if ($order->recipient_name || $order->full_address) {
            return [
                'recipient_name' => $order->recipient_name,
                'phone' => $order->phone,
                'detail' => $order->full_address,
                'full_address' => $order->full_address,
                'postal_code' => null,
                'biteship_area_id' => null,
            ];
        }

        return null;
    }
}
