<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests
{
    /**
     * Handle an incoming request.
     * This is a placeholder - we're using Blade, not Inertia
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
