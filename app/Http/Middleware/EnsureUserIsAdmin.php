<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $checkRoleList = in_array(Auth::user()->role, ['admin', 'dev'], true);
        if (Auth::check() && $checkRoleList) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
