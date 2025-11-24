<?php

namespace App\Services\Storefront;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function profileData(User $user): array
    {
        $user->load(['addresses' => function ($query) {
            $query->orderByDesc('is_default')->orderBy('created_at');
        }]);

        $addresses = $user->addresses;

        return [
            'user' => $user,
            'addresses' => $addresses,
            'needsProfileCompletion' => ! filled($user->phone) || $addresses->isEmpty(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): void
    {
        if ($avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        if (array_key_exists('phone', $data)) {
            $data['phone'] = $this->formatPhoneNumber($data['phone']);
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    public function deleteAccount(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
    }

    /**
     * Normalize phone to 62xxxxxxxx format.
     */
    private function formatPhoneNumber(?string $input): ?string
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

        return $digits !== '' ? '62'.$digits : null;
    }
}

