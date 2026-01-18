<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $event->load('template');

        $query = $event->participants()->with('division')->withCount('entries');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }

        $participants = $query->orderBy('name')->paginate(10)->withQueryString();
        $divisions = $event->divisions()->where('is_active', true)->orderBy('display_order')->orderBy('code')->get();

        return view('admin.events.participants.index', compact('event', 'participants', 'divisions'));
    }

    public function create(Event $event)
    {
        $divisions = $event->divisions()->where('is_active', true)->orderBy('name')->get();
        return view('admin.events.participants.create', compact('event', 'divisions'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'division_id' => 'nullable|exists:divisions,id',
            'organization' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active');

        $event->participants()->create($validated);

        return redirect()->route('admin.events.participants.index', $event)
            ->with('success', 'Participant created successfully.');
    }

    public function edit(Event $event, Participant $participant)
    {
        $participant->load('entries');
        $divisions = $event->divisions()->where('is_active', true)->orderBy('name')->get();
        return view('admin.events.participants.edit', compact('event', 'participant', 'divisions'));
    }

    public function update(Request $request, Event $event, Participant $participant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'division_id' => 'nullable|exists:divisions,id',
            'organization' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $participant->update($validated);

        return redirect()->route('admin.events.participants.index', $event)
            ->with('success', 'Participant updated successfully.');
    }

    public function destroy(Event $event, Participant $participant)
    {
        // Delete associated entries first (cascade)
        $participant->entries()->delete();
        $participant->delete();

        return redirect()->route('admin.events.participants.index', $event)
            ->with('success', 'Participant and associated entries deleted successfully.');
    }
}
