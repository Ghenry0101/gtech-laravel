<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // GET /api/roles → ambil semua role
    public function index()
    {
        return response()->json(Role::all());
    }

    // POST /api/roles → tambah role baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'posisi' => 'required|string|max:100|unique:roles,posisi',
            'display_name' => 'nullable|string',
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update(['role_id' => $validated['role_id']]);
        $user->load('role');

        return response()->json([
            'message' => "Role user berhasil diubah menjadi {$user->role->posisi}",
            'user' => $user
        ]);
    }
}
