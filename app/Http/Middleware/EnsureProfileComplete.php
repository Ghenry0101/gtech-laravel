<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $roleName = $user->role?->name;
        $adminRoles = ['admin_barang', 'admin_pengiriman', 'admin_keuangan'];

        if (in_array($roleName, $adminRoles, true)) {
            return $next($request);
        }

        if ($request->routeIs([
            'profile.*',
            'profile.addresses.*',
            'logout',
        ])) {
            return $next($request);
        }

        $profileComplete = $user->addresses()->exists();

        if (! $profileComplete) {
            return redirect()
                ->route('profile.edit')
                ->with('must_complete_profile', true);
        }

        return $next($request);
    }
}
