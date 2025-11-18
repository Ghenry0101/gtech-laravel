    <?php
    namespace App\Http\Controllers;

    use App\Http\Requests\OrderItemReviewRequest;
    use App\Models\OrderItem;
    use App\Models\Review;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Facades\Storage;

    class OrderItemReviewController extends Controller
    {
        public function store(OrderItemReviewRequest $request, OrderItem $orderItem): RedirectResponse
        {
            $this->ensureCanReview($request->user()?->id, $orderItem, ensureReviewExists: false);

            $review = $orderItem->review()->create([
                'rating' => $request->validated('rating'),
                'title' => $request->validated('title'),
                'comment' => $request->validated('comment'),
                'reviewed_at' => now(),
            ]);

            $this->syncImages($review, $request);

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

            $this->syncImages($review, $request);

            return back()
                ->with('status', 'review-updated')
                ->with('status_message', __('Ulasan berhasil diperbarui.'));
        }

        protected function ensureCanReview(?string $userId, OrderItem $orderItem, bool $ensureReviewExists): ?Review
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

        protected function syncImages(Review $review, OrderItemReviewRequest $request): void
        {
            $removeIds = collect($request->validated('remove_images', []))->filter()->all();

            if (! empty($removeIds)) {
                $images = $review->images()->whereIn('id', $removeIds)->get();

                foreach ($images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            $files = $request->file('images', []);
            if (! $files) {
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
