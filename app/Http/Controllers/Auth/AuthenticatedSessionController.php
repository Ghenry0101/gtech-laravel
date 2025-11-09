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

        $guard = $request->guard();
        $user = Auth::guard($guard)->user();

        if ($guard === 'admin') {
            $positionRedirects = [
                'product_admin' => 'admin.barang.dashboard',
                'shipping_admin' => 'admin.pengiriman.dashboard',
                'finance_admin' => 'admin.keuangan.dashboard',
            ];

            $targetRoute = $positionRedirects[$user?->position] ?? 'admin.barang.dashboard';

            return redirect()->intended(
                Route::has($targetRoute) ? route($targetRoute) : route('home')
            );
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
