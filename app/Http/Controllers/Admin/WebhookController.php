<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Display webhooks management page.
     */
    public function index()
    {
        $webhooks = Webhook::withCount('logs')->orderBy('name')->get();

        return view('admin.webhooks.index', [
            'webhooks' => $webhooks,
            'availableEvents' => Webhook::EVENTS,
        ]);
    }

    /**
     * Store a new webhook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array|min:1',
            'events.*' => 'in:' . implode(',', array_keys(Webhook::EVENTS)),
            'headers' => 'nullable|array',
            'is_active' => 'boolean',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'timeout' => 'nullable|integer|min:5|max:120',
        ]);

        $webhook = Webhook::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook created successfully.',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Update a webhook.
     */
    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array|min:1',
            'events.*' => 'in:' . implode(',', array_keys(Webhook::EVENTS)),
            'headers' => 'nullable|array',
            'is_active' => 'boolean',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'timeout' => 'nullable|integer|min:5|max:120',
        ]);

        $webhook->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook updated successfully.',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Delete a webhook.
     */
    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully.',
        ]);
    }

    /**
     * Toggle webhook active status.
     */
    public function toggle(Webhook $webhook)
    {
        $webhook->update(['is_active' => !$webhook->is_active]);

        return response()->json([
            'success' => true,
            'message' => $webhook->is_active ? 'Webhook enabled.' : 'Webhook disabled.',
            'is_active' => $webhook->is_active,
        ]);
    }

    /**
     * Test a webhook with sample data.
     */
    public function test(Webhook $webhook)
    {
        $testEvent = $webhook->events[0] ?? 'test.event';

        $log = $webhook->trigger($testEvent, [
            'test' => true,
            'message' => 'This is a test webhook delivery.',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => $log?->isSuccessful() ?? false,
            'message' => $log?->isSuccessful() ? 'Webhook test successful!' : 'Webhook test failed.',
            'log' => $log,
        ]);
    }

    /**
     * Get webhook logs.
     */
    public function logs(Webhook $webhook)
    {
        $logs = $webhook->logs()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Clear webhook logs.
     */
    public function clearLogs(Webhook $webhook)
    {
        $webhook->logs()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logs cleared successfully.',
        ]);
    }

    /**
     * Get all webhooks (API endpoint).
     */
    public function list()
    {
        return response()->json([
            'webhooks' => Webhook::withCount('logs')->orderBy('name')->get(),
            'available_events' => Webhook::EVENTS,
        ]);
    }
}
