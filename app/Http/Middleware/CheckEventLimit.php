<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEventLimit
{
    /**
     * Handle an incoming request.
     * Check if user can create more events based on their plan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Only check on create routes (POST to events.store)
        if ($request->isMethod('POST') && $request->routeIs('admin.events.store')) {
            if (!$user->canCreateEvent()) {
                $plan = $user->currentPlan();
                $limit = $plan ? $plan->max_events : 1;

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "You've reached your plan limit of {$limit} active events. Please upgrade your plan to create more events.",
                        'upgrade_url' => route('subscription.pricing'),
                    ], 403);
                }

                return redirect()->route('admin.events.index')
                    ->with('error', "You've reached your plan limit of {$limit} active events. Please upgrade your plan to create more events.");
            }
        }

        return $next($request);
    }
}
