<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Services\Biteship\BiteshipService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
        $order->loadMissing(['shipment', 'address', 'items.product']);

        $shipment = $order->shipment;
        $address = $order->address;

        if (! $shipment || ! $address || $shipment->biteship_order_id) {
            return false;
        }

        $origin = config('biteship.origin', []);
        if (! $origin) {
            Log::warning('Biteship origin configuration missing. Skip dispatch.', [
                'order_id' => $order->id,
            ]);

            return false;
        }

        $ratePayload = $shipment->rate_payload ?? [];
        $courierType = $this->extractCourierType($shipment->courier_service_code, $ratePayload);
        $courierCompany = data_get($ratePayload, 'courier.company')
            ?? $shipment->courier_name
            ?? Arr::get($ratePayload, 'courier_code');

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
            'destination_contact_name' => $address->recipient_name,
            'destination_contact_phone' => $address->phone,
            'destination_address' => $address->detail,
            'destination_postal_code' => $address->postal_code,
            'destination_area_id' => $address->biteship_area_id,
            'courier_company' => $courierCompany,
            'courier_type' => $courierType,
            'items' => $itemsPayload,
            'order_note' => $order->notes,
            'metadata' => [
                'order_number' => $order->order_number,
            ],
        ];

        $payload['distance'] = data_get($ratePayload, 'distance')
            ?? data_get($ratePayload, 'summary.distance');

        if ($deliveryType = $this->determineDeliveryType($ratePayload, $courierType)) {
            $payload['delivery_type'] = $deliveryType;
        }

        try {
            $response = $this->biteship->createShipment(array_filter($payload));

            $shipment->forceFill([
                'biteship_order_id' => $response['id'] ?? $response['order_id'] ?? $shipment->biteship_order_id,
                'tracking_id' => $response['tracking_number'] ?? $response['tracking_id'] ?? $shipment->tracking_id,
                'status' => $response['status'] ?? 'processing',
                'rate_payload' => array_merge($shipment->rate_payload ?? [], ['order' => $response]),
                'shipped_at' => $shipment->shipped_at ?? now(),
            ])->save();

            return true;
        } catch (\Throwable $throwable) {
            Log::error('Failed creating Biteship shipment.', [
                'order_id' => $order->id,
                'message' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    protected function extractCourierType(?string $serviceCode, array $ratePayload): string
    {
        $courierType = data_get($ratePayload, 'courier.type')
            ?? data_get($ratePayload, 'courier_type')
            ?? data_get($ratePayload, 'type')
            ?? data_get($ratePayload, 'service_type')
            ?? 'regular';

        $type = strtolower((string) $courierType);
        $serviceCode = strtolower((string) $serviceCode);

        if ($type === 'regular' && $serviceCode !== '') {
            if (str_contains($serviceCode, 'same')) {
                return 'same_day';
            }

            if (str_contains($serviceCode, 'instant')) {
                return 'instant';
            }
        }

        return $courierType;
    }

    protected function determineDeliveryType(array $ratePayload, string $courierType): ?string
    {
        $deliveryType = data_get($ratePayload, 'delivery_type');
        $normalizedType = strtolower((string) $courierType);

        if (! $deliveryType && in_array($normalizedType, ['instant', 'same_day', 'sameday', 'on_demand', 'ondemand'], true)) {
            $deliveryType = 'now';
        }

        return $deliveryType ? strtolower((string) $deliveryType) : null;
    }
}
