<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Storefront\ComplaintSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintSubmissionService $complaints
    ) {
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:150'],
            'issue_detail' => ['required', 'string', 'min:10'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [File::image()->max(10240)],
        ]);

        $this->complaints->submit(
            $request->user(),
            $order,
            $data,
            $request->file('images', [])
        );

        return back()
            ->with('status', 'complaint-submitted')
            ->with('status_message', __('Komplain Anda sudah kami terima. Tim pengiriman akan menindaklanjuti secepatnya.'));
    }
}
