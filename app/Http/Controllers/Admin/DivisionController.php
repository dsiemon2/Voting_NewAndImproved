<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(Event $event)
    {
        $event->load('template');

        $divisions = $event->divisions()
            ->withCount('entries')
            ->orderBy('display_order')
            ->orderBy('code')
            ->paginate(10);

        return view('admin.events.divisions.index', compact('event', 'divisions'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active');

        // Auto-generate name if not provided
        if (empty($validated['name'])) {
            $number = preg_replace('/[^0-9]/', '', $validated['code']);
            $validated['name'] = $validated['type'] . ' ' . $number;
        }

        // Set display order if not provided
        if (empty($validated['display_order'])) {
            $validated['display_order'] = $event->divisions()->max('display_order') + 1;
        }

        $event->divisions()->create($validated);

        return redirect()->route('admin.events.divisions.index', $event)
            ->with('success', 'Division created successfully.');
    }

    public function update(Request $request, Event $event, Division $division)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Auto-generate name if not provided
        if (empty($validated['name'])) {
            $number = preg_replace('/[^0-9]/', '', $validated['code']);
            $validated['name'] = $validated['type'] . ' ' . $number;
        }

        $division->update($validated);

        return redirect()->route('admin.events.divisions.index', $event)
            ->with('success', 'Division updated successfully.');
    }

    public function destroy(Event $event, Division $division)
    {
        // Check if division has entries
        if ($division->entries()->count() > 0) {
            return redirect()->route('admin.events.divisions.index', $event)
                ->with('error', 'Cannot delete division with existing entries. Remove entries first.');
        }

        $division->delete();

        return redirect()->route('admin.events.divisions.index', $event)
            ->with('success', 'Division deleted successfully.');
    }
}
