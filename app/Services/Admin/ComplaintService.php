<?php

namespace App\Services\Admin;

use App\Models\Complaint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComplaintService
{
    /**
     * Fetch the latest complaints with the relationships needed by the admin UI.
     */
    public function paginateLatest(int $perPage = 12): LengthAwarePaginator
    {
        return Complaint::query()
            ->with([
                'order:id,order_number,order_status',
                'user:id,name,email,phone',
                'images:id,complaint_id,path,position',
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Update only the status field of a complaint.
     */
    public function updateStatus(Complaint $complaint, string $status): void
    {
        $complaint->forceFill([
            'status' => $status,
        ])->save();
    }
}

