<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event');

        if (!$event) {
            abort(404, 'Event not found.');
        }

        // If event is passed as ID, resolve it
        if (is_numeric($event)) {
            $event = \App\Models\Event::find($event);
        }

        if (!$event || !$event->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Event is not active'], 403);
            }

            abort(403, 'This event is not currently active.');
        }

        return $next($request);
    }
}
