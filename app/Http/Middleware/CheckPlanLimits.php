<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $plan = $user->currentPlan();

        // If no plan system is set up, allow everything
        if (!$plan) {
            return $next($request);
        }

        // Check specific feature if provided
        if ($feature && !$plan->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Your plan does not include this feature. Please upgrade to access {$feature}.",
                    'upgrade_url' => route('subscription.pricing'),
                ], 403);
            }

            return redirect()->route('subscription.pricing')
                ->with('error', "Your plan does not include this feature. Please upgrade to access {$feature}.");
        }

        return $next($request);
    }
}
