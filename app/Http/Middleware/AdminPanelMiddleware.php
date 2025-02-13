<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ?User $user */
        $user = auth()->guard('web')->user();
        
        if (!$user instanceof User || !$user->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
