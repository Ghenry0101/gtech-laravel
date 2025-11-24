<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\Admin\ComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintService $complaints
    ) {
    }

    public function index(): View
    {
        $complaints = $this->complaints->paginateLatest();

        return view('admin.pengiriman.complaints.index', [
            'complaints' => $complaints,
        ]);
    }

    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(Complaint::STATUSES)],
        ]);

        $this->complaints->updateStatus($complaint, $data['status']);

        return back()
            ->with('status', 'complaint-updated')
            ->with('status_message', __('Status komplain diperbarui.'));
    }
}
