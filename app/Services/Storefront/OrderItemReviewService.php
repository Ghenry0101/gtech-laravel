<?php

namespace App\Services\Storefront;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OrderItemReviewService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int|string>  $removeImages
     * @param  array<int, UploadedFile>  $files
     */
    public function createReview(User $user, OrderItem $orderItem, array $data, array $removeImages = [], array $files = []): Review
    {
        $this->ensureCanReview($user, $orderItem, ensureReviewExists: false);

        // Reviews double as user-facing testimonials, so we centralize rating/comment/image persistence here for easier walkthroughs in demos.
        $review = $orderItem->review()->create([
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'reviewed_at' => now(),
        ]);

        $this->syncImages($review, $removeImages, $files);

        return $review;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int|string>  $removeImages
     * @param  array<int, UploadedFile>  $files
     */
    public function updateReview(User $user, OrderItem $orderItem, array $data, array $removeImages = [], array $files = []): Review
    {
        $review = $this->ensureCanReview($user, $orderItem, ensureReviewExists: true);

        $review->fill([
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'reviewed_at' => now(),
        ])->save();

        $this->syncImages($review, $removeImages, $files);

        return $review;
    }

    private function ensureCanReview(User $user, OrderItem $orderItem, bool $ensureReviewExists): ?Review
    {
        $order = $orderItem->order;

        abort_unless($order && $order->user_id === $user->id, 403);
        abort_unless($order->order_status === 'completed', 422, __('Pesanan belum selesai untuk diulas.'));

        $review = $orderItem->review;

        if ($ensureReviewExists) {
            abort_unless($review, 404);
        } else {
            abort_if($review, 409, __('Ulasan sudah tersedia untuk produk ini.'));
        }

        return $review;
    }

    /**
     * @param  array<int, int|string>  $removeImages
     * @param  array<int, UploadedFile>  $files
     */
    private function syncImages(Review $review, array $removeImages, array $files): void
    {
        $removeIds = collect($removeImages)->filter()->all();

        if (! empty($removeIds)) {
            $images = $review->images()->whereIn('id', $removeIds)->get();

            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        if (empty($files)) {
            return;
        }

        $existingCount = $review->images()->count();
        $availableSlots = max(0, 5 - $existingCount);

        if ($availableSlots === 0) {
            return;
        }

        $files = array_slice($files, 0, $availableSlots);

        foreach ($files as $uploadedFile) {
            $path = $uploadedFile->store('reviews', 'public');

            $review->images()->create([
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
            ]);
        }
    }
}
