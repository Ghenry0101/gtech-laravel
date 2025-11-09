<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            $user = Auth::guard('admin')->user();
        }

        if (!$user) {
            return redirect()->route('login');
        }

        $roleName = null;

        if ($user instanceof Admin) {
            $roleName = $user->position;
        }

        if (! $roleName || !in_array($roleName, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
