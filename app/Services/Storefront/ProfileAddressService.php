<?php

namespace App\Services\Storefront;

use App\Models\Address;
use App\Models\User;
use App\Services\Biteship\BiteshipService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProfileAddressService
{
    public function create(User $user, array $data, bool $isDefault): Address
    {
        $data = $this->mergeAreaData($data);
        $isDefault = $isDefault || ! $user->addresses()->exists();

        $address = $user->addresses()->create(array_merge($data, ['is_default' => $isDefault]));

        if ($isDefault) {
            $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        return $address;
    }

    public function update(User $user, Address $address, array $data, bool $isDefault): Address
    {
        $this->ensureOwner($user, $address);

        $data = $this->mergeAreaData($data);
        $data['is_default'] = $isDefault;

        $address->update($data);

        if ($data['is_default']) {
            $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        } elseif (! $user->addresses()->where('is_default', true)->exists()) {
            $address->update(['is_default' => true]);
        }

        return $address;
    }

    public function delete(User $user, Address $address): void
    {
        $this->ensureOwner($user, $address);
        $wasDefault = (bool) $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $next = $user->addresses()->oldest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }
    }

    private function ensureOwner(User $user, Address $address): void
    {
        abort_if($address->user_id !== $user->id, 403);
    }

    /**
     * Central place that syncs the user's manual form input with Biteship's canonical area data.
     * This makes the client demo easier because every downstream feature (checkout, dashboard) trusts the same snapshot.
     */
    private function mergeAreaData(array $data): array
    {
        $area = $this->fetchBiteshipArea($data['biteship_area_id'] ?? '');

        $data['biteship_area_id'] = $area['id'];
        $data['province'] = $area['province'];
        $data['city'] = $area['city'];
        $data['district'] = $area['district'];
        $data['postal_code'] = $area['postal_code'];

        return $data;
    }

    private function fetchBiteshipArea(string $areaId): array
    {
        if (blank($areaId)) {
            throw ValidationException::withMessages([
                'biteship_area_id' => __('Silakan pilih kecamatan/kota dari pencarian Biteship.'),
            ]);
        }

        try {
            // Direct API call ensures the stored snapshot already conforms to Biteship so shipping rates never reject the address during demos.
            $area = BiteshipService::make()->getAreaDetail($areaId);
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch Biteship area detail.', [
                'area_id' => $areaId,
                'message' => $throwable->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'biteship_area_id' => __('Gagal memverifikasi wilayah Biteship. Coba ulangi pencarian.'),
            ]);
        }

        if (! $area) {
            throw ValidationException::withMessages([
                'biteship_area_id' => __('Wilayah Biteship tidak ditemukan. Silakan pilih ulang.'),
            ]);
        }

        return $area;
    }
}
