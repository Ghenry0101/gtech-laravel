<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileAddressRequest;
use App\Models\Address;
use App\Services\Biteship\BiteshipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProfileAddressController extends Controller
{
    public function store(ProfileAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->mergeAreaData($request->validated());
        $isDefault = $request->boolean('is_default') || ! $user->addresses()->exists();

        $addressData = array_merge($data, ['is_default' => $isDefault]);
        $address = $user->addresses()->create($addressData);

        if ($isDefault) {
            $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        return back()->with('status', 'address-added');
    }

    public function update(ProfileAddressRequest $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $data = $this->mergeAreaData($request->validated());
        $data['is_default'] = $request->boolean('is_default');

        $address->update($data);

        if ($data['is_default']) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        } elseif (! $request->user()->addresses()->where('is_default', true)->exists()) {
            $address->update(['is_default' => true]);
        }

        return back()->with('status', 'address-updated');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);
        $wasDefault = (bool) $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->oldest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('status', 'address-deleted');
    }

    protected function ensureOwner(Request $request, Address $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 403);
    }

    protected function mergeAreaData(array $data): array
    {
        $area = $this->fetchBiteshipArea($data['biteship_area_id'] ?? '');

        $data['biteship_area_id'] = $area['id'];
        $data['province'] = $area['province'];
        $data['city'] = $area['city'];
        $data['district'] = $area['district'];
        $data['postal_code'] = $area['postal_code'];

        return $data;
    }

    protected function fetchBiteshipArea(string $areaId): array
    {
        if (blank($areaId)) {
            throw ValidationException::withMessages([
                'biteship_area_id' => __('Silakan pilih kecamatan/kota dari pencarian Biteship.'),
            ]);
        }

        try {
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
