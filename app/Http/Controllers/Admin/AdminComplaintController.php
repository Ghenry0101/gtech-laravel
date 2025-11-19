<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminComplaintController extends Controller
{
    public function index(): View
    {
        $complaints = Complaint::query()
            ->with([
                'order:id,order_number,order_status',
                'user:id,name,email,phone',
            ])
            ->latest()
            ->paginate(12);

        return view('admin.pengiriman.complaints.index', [
            'complaints' => $complaints,
        ]);
    }

    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(Complaint::STATUSES)],
        ]);

        $complaint->forceFill([
            'status' => $data['status'],
        ])->save();

        return back()
            ->with('status', 'complaint-updated')
            ->with('status_message', __('Status komplain diperbarui.'));
    }
}
