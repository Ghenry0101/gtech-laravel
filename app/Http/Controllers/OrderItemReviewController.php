<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderItemReviewRequest;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class OrderItemReviewController extends Controller
{
    public function store(OrderItemReviewRequest $request, OrderItem $orderItem): RedirectResponse
    {
        $this->ensureCanReview($request->user()?->id, $orderItem, ensureReviewExists: false);

        $orderItem->review()->create([
            'rating' => $request->validated('rating'),
            'title' => $request->validated('title'),
            'comment' => $request->validated('comment'),
            'reviewed_at' => now(),
        ]);

        return back()
            ->with('status', 'review-saved')
            ->with('status_message', __('Terima kasih! Ulasan Anda tersimpan.'));
    }

    public function update(OrderItemReviewRequest $request, OrderItem $orderItem): RedirectResponse
    {
        $review = $this->ensureCanReview($request->user()?->id, $orderItem, ensureReviewExists: true);

        $review->fill([
            'rating' => $request->validated('rating'),
            'title' => $request->validated('title'),
            'comment' => $request->validated('comment'),
            'reviewed_at' => now(),
        ])->save();

        return back()
            ->with('status', 'review-updated')
            ->with('status_message', __('Ulasan berhasil diperbarui.'));
    }

    protected function ensureCanReview(?int $userId, OrderItem $orderItem, bool $ensureReviewExists): ?Review
    {
        $order = $orderItem->order;

        abort_unless($userId && $order && $order->user_id === $userId, 403);
        abort_unless($order->order_status === 'completed', 422, __('Pesanan belum selesai untuk diulas.'));

        $review = $orderItem->review;

        if ($ensureReviewExists) {
            abort_unless($review, 404);
        } else {
            abort_if($review, 409, __('Ulasan sudah tersedia untuk produk ini.'));
        }

        return $review;
    }
}
