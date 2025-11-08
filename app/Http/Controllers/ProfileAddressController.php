<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileAddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileAddressController extends Controller
{
    public function store(ProfileAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $isDefault = $request->boolean('is_default') || ! $user->addresses()->exists();

        $payload = $this->mapPayload($data);
        $payload['is_default'] = $isDefault;

        $address = $user->addresses()->create($payload);

        if ($isDefault) {
            $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        return back()->with('status', 'address-added');
    }

    public function update(ProfileAddressRequest $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $data = $request->validated();
        $data['is_default'] = $request->boolean('is_default');

        $payload = $this->mapPayload($data);
        $payload['is_default'] = $data['is_default'];

        $address->update($payload);

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

    /**
     * Normalize payload keys to match database columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mapPayload(array $data): array
    {
        $data['phone'] = $data['recipient_phone'];
        unset($data['recipient_phone']);

        return $data;
    }
}
