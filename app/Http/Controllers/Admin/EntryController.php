<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $event->load('template');

        $query = $event->entries()
            ->with(['participant', 'division', 'category'])
            ->withCount('votes');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }

        if ($request->filled('participant')) {
            $query->where('participant_id', $request->participant);
        }

        $entries = $query->orderBy('entry_number')->orderBy('name')->paginate(10)->withQueryString();
        $divisions = $event->divisions()->where('is_active', true)->orderBy('display_order')->orderBy('code')->get();
        $participants = $event->participants()->where('is_active', true)->orderBy('name')->get();

        return view('admin.events.entries.index', compact('event', 'entries', 'divisions', 'participants'));
    }

    public function create(Event $event)
    {
        $divisions = $event->divisions()->where('is_active', true)->orderBy('name')->get();
        $participants = $event->participants()->where('is_active', true)->orderBy('name')->get();
        $categories = $event->categories()->where('is_active', true)->orderBy('name')->get();

        return view('admin.events.entries.create', compact('event', 'divisions', 'participants', 'categories'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entry_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'participant_id' => 'nullable|exists:participants,id',
            'division_id' => 'nullable|exists:divisions,id',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active');

        $event->entries()->create($validated);

        return redirect()->route('admin.events.entries.index', $event)
            ->with('success', 'Entry created successfully.');
    }

    public function edit(Event $event, Entry $entry)
    {
        $entry->load('votes');
        $divisions = $event->divisions()->where('is_active', true)->orderBy('name')->get();
        $participants = $event->participants()->where('is_active', true)->orderBy('name')->get();
        $categories = $event->categories()->where('is_active', true)->orderBy('name')->get();

        return view('admin.events.entries.edit', compact('event', 'entry', 'divisions', 'participants', 'categories'));
    }

    public function update(Request $request, Event $event, Entry $entry)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entry_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'participant_id' => 'nullable|exists:participants,id',
            'division_id' => 'nullable|exists:divisions,id',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $entry->update($validated);

        return redirect()->route('admin.events.entries.index', $event)
            ->with('success', 'Entry updated successfully.');
    }

    public function destroy(Event $event, Entry $entry)
    {
        // Delete associated votes first
        $entry->votes()->delete();
        $entry->delete();

        return redirect()->route('admin.events.entries.index', $event)
            ->with('success', 'Entry and associated votes deleted successfully.');
    }
}
