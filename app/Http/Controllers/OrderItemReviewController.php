<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderItemReviewRequest;
use App\Models\OrderItem;
use App\Services\Storefront\OrderItemReviewService;
use Illuminate\Http\RedirectResponse;

class OrderItemReviewController extends Controller
{
    public function __construct(
        private readonly OrderItemReviewService $reviews
    ) {
    }

    public function store(OrderItemReviewRequest $request, OrderItem $orderItem): RedirectResponse
    {
        $data = $request->validated();

        $this->reviews->createReview(
            $request->user(),
            $orderItem,
            $data,
            $data['remove_images'] ?? [],
            $request->file('images', [])
        );

        return back()
            ->with('status', 'review-saved')
            ->with('status_message', __('Terima kasih! Ulasan Anda tersimpan.'));
    }

    public function update(OrderItemReviewRequest $request, OrderItem $orderItem): RedirectResponse
    {
        $data = $request->validated();

        $this->reviews->updateReview(
            $request->user(),
            $orderItem,
            $data,
            $data['remove_images'] ?? [],
            $request->file('images', [])
        );

        return back()
            ->with('status', 'review-updated')
            ->with('status_message', __('Ulasan berhasil diperbarui.'));
    }
}
