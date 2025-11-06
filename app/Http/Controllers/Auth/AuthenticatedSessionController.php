<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
        $roleName = $user?->role?->name;
        $roleRedirects = [
            'admin_barang' => 'admin.barang.dashboard',
            'admin_pengiriman' => 'admin.pengiriman.dashboard',
            'admin_keuangan' => 'admin.keuangan.dashboard',
        ];

        $targetRoute = $roleRedirects[$roleName] ?? 'home';

        if (! Route::has($targetRoute)) {
            $targetRoute = 'home';
        }

        return redirect()->intended(route($targetRoute));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
