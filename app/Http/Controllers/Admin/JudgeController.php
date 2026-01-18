<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventJudge;
use App\Models\User;
use Illuminate\Http\Request;

class JudgeController extends Controller
{
    public function index(Event $event)
    {
        $judges = $event->judges()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get users who can be added as judges (not already assigned)
        $existingJudgeIds = $event->judges()->pluck('user_id')->toArray();
        $availableUsers = User::where('is_active', true)
            ->whereNotIn('id', $existingJudgeIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.events.judges.index', compact('event', 'judges', 'availableUsers'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'vote_weight' => 'required|numeric|min:0.01|max:99.99',
            'title' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'can_see_results' => 'boolean',
            'can_vote_own_division' => 'boolean',
        ]);

        // Check if user is already a judge for this event
        if ($event->judges()->where('user_id', $validated['user_id'])->exists()) {
            return redirect()->route('admin.events.judges.index', $event)
                ->with('error', 'This user is already a judge for this event.');
        }

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active');
        $validated['can_see_results'] = $request->has('can_see_results');
        $validated['can_vote_own_division'] = $request->has('can_vote_own_division');

        EventJudge::create($validated);

        return redirect()->route('admin.events.judges.index', $event)
            ->with('success', 'Judge added successfully.');
    }

    public function update(Request $request, Event $event, EventJudge $judge)
    {
        $validated = $request->validate([
            'vote_weight' => 'required|numeric|min:0.01|max:99.99',
            'title' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'can_see_results' => 'boolean',
            'can_vote_own_division' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['can_see_results'] = $request->has('can_see_results');
        $validated['can_vote_own_division'] = $request->has('can_vote_own_division');

        $judge->update($validated);

        return redirect()->route('admin.events.judges.index', $event)
            ->with('success', 'Judge updated successfully.');
    }

    public function destroy(Event $event, EventJudge $judge)
    {
        $judgeName = $judge->user->full_name;
        $judge->delete();

        return redirect()->route('admin.events.judges.index', $event)
            ->with('success', "Judge '{$judgeName}' removed successfully.");
    }
}
