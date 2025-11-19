<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOwner($request, $order);
        $this->ensureComplainable($order);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:150'],
            'issue_detail' => ['required', 'string', 'min:10'],
        ]);

        $order->complaints()->create([
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'issue_detail' => $data['issue_detail'],
            'status' => 'pending',
        ]);

        return back()
            ->with('status', 'complaint-submitted')
            ->with('status_message', __('Komplain Anda sudah kami terima. Tim pengiriman akan menindaklanjuti secepatnya.'));
    }

    protected function ensureOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }

    protected function ensureComplainable(Order $order): void
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
