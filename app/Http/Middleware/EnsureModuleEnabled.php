<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Services\EventConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private EventConfigurationService $configService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        $event = $request->route('event');

        if (!$event) {
            abort(404, 'Event not found.');
        }

        // If event is passed as ID, resolve it
        if (is_numeric($event)) {
            $event = Event::with(['template.modules', 'moduleOverrides'])->find($event);
        }

        if (!$event) {
            abort(404, 'Event not found.');
        }

        // Check if module is enabled for this event
        if (!$this->configService->hasModule($event, $moduleCode)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "The {$moduleCode} module is not enabled for this event."
                ], 403);
            }

            abort(403, "The {$moduleCode} module is not enabled for this event.");
        }

        return $next($request);
    }
}
