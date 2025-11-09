<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    private const POSITIONS = [
        'product_admin' => 'Admin Barang',
        'shipping_admin' => 'Admin Pengiriman',
        'finance_admin' => 'Admin Keuangan',
    ];

    public function index()
    {
        $positions = collect(self::POSITIONS)->map(function ($label, $key) {
            return [
                'key' => $key,
                'label' => $label,
            ];
        })->values();

        return response()->json($positions);
    }

    public function store()
    {
        abort(405, 'Admin positions are predefined.');
    }

    public function assignRole(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'position' => ['required', Rule::in(array_keys(self::POSITIONS))],
        ]);

        $admin->update(['position' => $validated['position']]);

        return response()->json([
            'message' => "Posisi admin diperbarui menjadi {$validated['position']}",
            'admin' => $admin->fresh(),
        ]);
    }
}
