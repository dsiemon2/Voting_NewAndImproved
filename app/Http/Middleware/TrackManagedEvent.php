<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackManagedEvent
{
    /**
     * Track which event the user is currently managing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If we're on an event-specific route, save the event ID to session and cookie
        if ($request->route('event')) {
            $eventId = $request->route('event');

            // Handle both model binding and numeric IDs
            if (is_object($eventId)) {
                $eventId = $eventId->id;
            }

            if (is_numeric($eventId)) {
                session()->put('managing_event_id', (int) $eventId);
                session()->save();

                // Set a cookie so JavaScript can read the event context
                $response->withCookie(cookie('managing_event_id', (string) $eventId, 60 * 24, '/', null, false, false));

                Log::info('TrackManagedEvent: Set managing_event_id to ' . $eventId);
            }
        }

        // Clear event context when viewing the events list
        if ($request->routeIs('admin.events.index')) {
            session()->forget('managing_event_id');
            session()->save();

            // Clear the cookie
            $response->withCookie(cookie()->forget('managing_event_id'));

            Log::info('TrackManagedEvent: Cleared managing_event_id');
        }

        return $response;
    }
}
