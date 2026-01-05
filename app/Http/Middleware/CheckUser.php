<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $checkRoleList = in_array(Auth::user()->role, ['dev', 'pac', 'lab', 'heartstream', 'register']);
        if (Auth::check() && $checkRoleList) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
