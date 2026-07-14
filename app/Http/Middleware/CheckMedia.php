<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMedia
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedRoles = ['admin', 'dev', 'media', 'media-head'];

        if (Auth::check() && in_array(Auth::user()->role, $allowedRoles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
