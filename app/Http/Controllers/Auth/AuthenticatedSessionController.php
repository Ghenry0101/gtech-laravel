<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $roleName = $user?->role?->posisi;
        if (! $roleName && $user?->role?->display_name) {
            $roleName = Str::slug($user->role->display_name, '_');
        }

        if (! $roleName && $user?->role_id) {
            $roleName = match ((int) $user->role_id) {
                1 => 'admin_barang',
                3 => 'admin_pengiriman',
                4 => 'admin_keuangan',
                default => 'user',
            };
        }
        $roleRedirects = [
            'admin_barang' => 'admin.barang.dashboard',
            'admin_pengiriman' => 'admin.shipping.dashboard',
            'admin_keuangan' => 'dashboard',
            'user' => 'dashboard',
        ];

        $targetRoute = $roleRedirects[$roleName] ?? 'home';

        if (! Route::has($targetRoute)) {
            $targetRoute = 'home';
        }

        $targetUrl = route($targetRoute);
        $isAdminRole = is_string($roleName) && str_starts_with($roleName, 'admin_');

        return $isAdminRole
            ? redirect($targetUrl)
            : redirect()->intended($targetUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
