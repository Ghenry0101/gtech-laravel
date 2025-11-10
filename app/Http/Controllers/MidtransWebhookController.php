<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Biteship\BiteshipService;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        $midtrans = MidtransService::make();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Invalid Midtrans signature received.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::query()
            ->where('order_number', $payload['order_id'] ?? null)
            ->with(['payment', 'shipment', 'items.product', 'address'])
            ->first();

        if (! $order) {
            Log::warning('Midtrans webhook for unknown order.', ['payload' => $payload]);

            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::transaction(function () use ($order, $payload) {
            $payment = $order->payment;

            if ($payment) {
                $payment->forceFill([
                    'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                    'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                    'bank' => data_get($payload, 'va_numbers.0.bank', $payment->bank),
                    'va_number' => data_get($payload, 'va_numbers.0.va_number', $payment->va_number),
                    'payment_status' => $payload['transaction_status'] ?? $payment->payment_status,
                    'fraud_status' => $payload['fraud_status'] ?? $payment->fraud_status,
                    'gross_amount' => $payload['gross_amount'] ?? $payment->gross_amount,
                    'paid_at' => in_array($payload['transaction_status'] ?? null, ['capture', 'settlement'], true)
                        ? now()
                        : $payment->paid_at,
                ])->save();
            }

            $this->updateOrderStatus($order, $payload);
        });

        if ($this->shouldCreateShipment($payload)) {
            $this->createShipmentOnBiteship($order);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    protected function updateOrderStatus(Order $order, array $payload): void
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        if (in_array($status, ['capture', 'settlement'], true) && ($fraud === null || $fraud === 'accept')) {
            $order->forceFill([
                'order_status' => 'processing',
                'paid_at' => $order->paid_at ?? now(),
            ])->save();
        }

        if (in_array($status, ['cancel', 'deny', 'expire'], true)) {
            $order->forceFill([
                'order_status' => 'canceled',
            ])->save();
        }
    }

    protected function shouldCreateShipment(array $payload): bool
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        if ($status === 'capture') {
            return $fraud === null || $fraud === 'accept';
        }

        return $status === 'settlement';
    }

    protected function createShipmentOnBiteship(Order $order): void
    {
        $shipment = $order->shipment;
        $address = $order->address;

        if (! $shipment || $shipment->biteship_order_id || ! $address) {
            return;
        }

        $biteship = BiteshipService::make();
        $origin = config('biteship.origin', []);

        $items = $order->items->map(function ($item) {
            $weight = (int) ($item->product?->weight ?? 500);

            return [
                'name' => $item->product_name,
                'value' => (int) round($item->price),
                'quantity' => $item->quantity,
                'weight' => $weight * $item->quantity,
            ];
        })->all();

        $payload = [
            'shipper_contact_name' => $origin['contact_name'],
            'shipper_contact_phone' => $origin['contact_phone'],
            'origin_contact_name' => $origin['contact_name'],
            'origin_contact_phone' => $origin['contact_phone'],
            'origin_address' => $origin['address'],
            'origin_postal_code' => $origin['postal_code'],
            'origin_area_id' => $origin['area_id'],
            'destination_contact_name' => $address->recipient_name,
            'destination_contact_phone' => $address->phone,
            'destination_address' => $address->detail,
            'destination_postal_code' => $address->postal_code,
            'destination_area_id' => $address->biteship_area_id,
            'courier_code' => $shipment->courier_code,
            'courier_service_code' => $shipment->courier_service_code,
            'delivery_type' => 'now',
            'items' => $items,
            'order_note' => $order->notes,
            'metadata' => [
                'order_number' => $order->order_number,
            ],
        ];

        try {
            $response = $biteship->createShipment($payload);

            $shipment->forceFill([
                'biteship_order_id' => $response['id'] ?? $response['order_id'] ?? $shipment->biteship_order_id,
                'tracking_id' => $response['tracking_number'] ?? $response['tracking_id'] ?? $shipment->tracking_id,
                'status' => $response['status'] ?? 'processing',
                'rate_payload' => array_merge($shipment->rate_payload ?? [], ['order' => $response]),
                'shipped_at' => $shipment->shipped_at ?? now(),
            ])->save();
        } catch (\Throwable $throwable) {
            Log::error('Failed to push shipment to Biteship.', [
                'order_id' => $order->id,
                'message' => $throwable->getMessage(),
            ]);
        }
    }
}
