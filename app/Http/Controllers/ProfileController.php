<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['addresses' => function ($query) {
            $query->orderByDesc('is_default')->orderBy('created_at');
        }]);

        $addresses = $user->addresses;
        $needsProfileCompletion = ! filled($user->phone) || $addresses->isEmpty();

        return view('profile.edit', [
            'user' => $user,
            'addresses' => $addresses,
            'needsProfileCompletion' => $needsProfileCompletion,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (array_key_exists('phone', $data)) {
            $data['phone'] = $this->formatPhoneNumber($data['phone']);
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Normalize phone to 62xxxxxxxx format.
     */
    protected function formatPhoneNumber(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $input);

        if ($digits === '') {
            return null;
        }

        $digits = ltrim($digits, '0');

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        return $digits !== '' ? '62' . $digits : null;
    }
}
