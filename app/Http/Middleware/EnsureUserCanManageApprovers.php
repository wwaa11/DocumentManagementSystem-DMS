<?php

namespace App\Http\Middleware;

use App\Services\Admin\ApproverAdminService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageApprovers
{
    public function __construct(private ApproverAdminService $approverAdminService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $this->approverAdminService->canManageApprovers(Auth::user())) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
