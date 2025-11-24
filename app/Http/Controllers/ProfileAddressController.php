<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileAddressRequest;
use App\Models\Address;
use App\Services\Storefront\ProfileAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileAddressController extends Controller
{
    public function __construct(
        private readonly ProfileAddressService $addresses
    ) {
    }

    public function store(ProfileAddressRequest $request): RedirectResponse
    {
        $this->addresses->create(
            $request->user(),
            $request->validated(),
            $request->boolean('is_default')
        );

        return back()->with('status', 'address-added');
    }

    public function update(ProfileAddressRequest $request, Address $address): RedirectResponse
    {
        $this->addresses->update(
            $request->user(),
            $address,
            $request->validated(),
            $request->boolean('is_default')
        );

        return back()->with('status', 'address-updated');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->addresses->delete($request->user(), $address);

        return back()->with('status', 'address-deleted');
    }
}
