<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $roleName = $user->role?->posisi;

        if (! $roleName || !in_array($roleName, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
