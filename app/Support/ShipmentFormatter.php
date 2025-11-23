<?php

namespace App\Support;

use App\Models\Shipment;

class ShipmentFormatter
{
    /**
     * Format delivery estimation into a localized human readable label, e.g. "3-5 hari".
     */
    public static function formatEstimation(?Shipment $shipment): ?string
    {
        $defaultLabel = '5 hari';

        if (! $shipment) {
            return $defaultLabel;
        }

        $payload = $shipment->rate_payload ?? [];
        $selectedRate = is_array($payload) ? ($payload['selected_rate'] ?? $payload) : [];

        $min = self::toPositiveInt(data_get($selectedRate, 'duration_min'));
        $max = self::toPositiveInt(data_get($selectedRate, 'duration_max'));

        if ($min === null || $max === null) {
            $min = $min ?? self::toPositiveInt(data_get($selectedRate, 'raw.duration.min'));
            $max = $max ?? self::toPositiveInt(data_get($selectedRate, 'raw.duration.max'));
        }

        if ($min !== null && $max !== null) {
            return $min === $max ? "{$max} hari" : "{$min}-{$max} hari";
        }

        if ($max !== null) {
            return "{$max} hari";
        }

        if ($min !== null) {
            return "{$min} hari";
        }

        $label = data_get($selectedRate, 'estimation') ?? data_get($payload, 'estimation');
        if ($label) {
            $label = trim((string) $label);

            if ($label !== '') {
                $label = str_ireplace(['day', 'days'], 'hari', $label);
                $label = str_ireplace('haris', 'hari', $label);

                return $label;
            }
        }

        if ($shipment->estimation_days) {
            $days = max(1, (int) $shipment->estimation_days);

            return "{$days} hari";
        }

        return $defaultLabel;
    }

    protected static function toPositiveInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $int = (int) $value;

            return $int > 0 ? $int : null;
        }

        return null;
    }
}
