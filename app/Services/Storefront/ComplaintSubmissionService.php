<?php

namespace App\Services\Storefront;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class ComplaintSubmissionService
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function submit(User $user, Order $order, array $data, array $files = []): Complaint
    {
        $this->ensureOwner($user, $order);
        $this->ensureComplainable($order);

        $complaint = $order->complaints()->create([
            'user_id' => $user->id,
            'reason' => $data['reason'],
            'issue_detail' => $data['issue_detail'],
            'status' => 'pending',
        ]);

        foreach ($files as $index => $uploadedFile) {
            $path = $uploadedFile->store('complaints', 'public');

            $complaint->images()->create([
                'path' => $path,
                'position' => $index,
            ]);
        }

        return $complaint;
    }

    private function ensureOwner(User $user, Order $order): void
    {
        abort_unless($order->user_id === $user->id, 404);
    }

    private function ensureComplainable(Order $order): void
    {
        abort_unless(
            in_array($order->order_status, ['shipped', 'completed'], true),
            422,
            __('Komplain baru bisa diajukan setelah pesanan dikirim.')
        );

        abort_if(
            $order->complaints()->where('status', 'pending')->exists(),
            422,
            __('Anda sudah memiliki komplain yang sedang diproses.')
        );
    }
}

