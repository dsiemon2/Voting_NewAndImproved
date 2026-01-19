<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use App\Models\EventVotingConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingTypeController extends Controller
{
    public function index()
    {
        $votingTypes = VotingType::with('placeConfigs')
            ->withCount('events')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.voting-types.index', compact('votingTypes'));
    }

    public function create()
    {
        return view('admin.voting-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:voting_types,name',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:ranked,approval,weighted,rating,cumulative',
            'is_active' => 'boolean',
            'places' => 'required|array|min:1',
            'places.*.place' => 'required|integer|min:1',
            'places.*.points' => 'required|integer|min:1|max:100',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Generate code from name
        $code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $validated['name']));

        DB::transaction(function () use ($validated, $request, $code) {
            $votingType = VotingType::create([
                'code' => $code,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'is_active' => $validated['is_active'],
            ]);

            foreach ($request->places as $placeData) {
                VotingPlaceConfig::create([
                    'voting_type_id' => $votingType->id,
                    'place' => $placeData['place'],
                    'points' => $placeData['points'],
                ]);
            }
        });

        return redirect()->route('admin.voting-types.index')
            ->with('success', 'Voting type created successfully.');
    }

    public function edit(VotingType $votingType)
    {
        $votingType->load(['placeConfigs' => function ($query) {
            $query->orderBy('place');
        }]);

        return view('admin.voting-types.edit', compact('votingType'));
    }

    public function update(Request $request, VotingType $votingType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:voting_types,name,' . $votingType->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'places' => 'required|array|min:1',
            'places.*.place' => 'required|integer|min:1',
            'places.*.points' => 'required|integer|min:1|max:100',
        ]);

        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($votingType, $validated, $request) {
            $votingType->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            // Delete existing place configs and recreate
            $votingType->placeConfigs()->delete();

            foreach ($request->places as $placeData) {
                VotingPlaceConfig::create([
                    'voting_type_id' => $votingType->id,
                    'place' => $placeData['place'],
                    'points' => $placeData['points'],
                ]);
            }
        });

        return redirect()->route('admin.voting-types.index')
            ->with('success', 'Voting type updated successfully.');
    }

    public function destroy(VotingType $votingType)
    {
        $eventsUsingThis = EventVotingConfig::where('voting_type_id', $votingType->id)->count();

        if ($eventsUsingThis > 0) {
            return redirect()->route('admin.voting-types.index')
                ->with('error', 'Cannot delete voting type that is in use by events.');
        }

        DB::transaction(function () use ($votingType) {
            $votingType->placeConfigs()->delete();
            $votingType->delete();
        });

        return redirect()->route('admin.voting-types.index')
            ->with('success', 'Voting type deleted successfully.');
    }

    public function preset(Request $request)
    {
        $presets = [
            'standard' => [
                'name' => 'Standard 3-2-1',
                'description' => 'Classic 3 places with 3, 2, 1 points',
                'places' => [
                    ['place' => 1, 'points' => 3],
                    ['place' => 2, 'points' => 2],
                    ['place' => 3, 'points' => 1],
                ],
            ],
            'extended' => [
                'name' => 'Extended 5-4-3-2-1',
                'description' => '5 places with descending points',
                'places' => [
                    ['place' => 1, 'points' => 5],
                    ['place' => 2, 'points' => 4],
                    ['place' => 3, 'points' => 3],
                    ['place' => 4, 'points' => 2],
                    ['place' => 5, 'points' => 1],
                ],
            ],
            'top-heavy' => [
                'name' => 'Top-Heavy 5-3-1',
                'description' => '3 places with emphasis on 1st place',
                'places' => [
                    ['place' => 1, 'points' => 5],
                    ['place' => 2, 'points' => 3],
                    ['place' => 3, 'points' => 1],
                ],
            ],
        ];

        $presetKey = $request->input('preset');

        if (!isset($presets[$presetKey])) {
            return redirect()->route('admin.voting-types.index')
                ->with('error', 'Invalid preset selected.');
        }

        $preset = $presets[$presetKey];

        // Check if already exists
        if (VotingType::where('name', $preset['name'])->exists()) {
            return redirect()->route('admin.voting-types.index')
                ->with('error', 'A voting type with this name already exists.');
        }

        DB::transaction(function () use ($preset) {
            $votingType = VotingType::create([
                'name' => $preset['name'],
                'description' => $preset['description'],
                'is_active' => true,
            ]);

            foreach ($preset['places'] as $placeData) {
                VotingPlaceConfig::create([
                    'voting_type_id' => $votingType->id,
                    'place' => $placeData['place'],
                    'points' => $placeData['points'],
                ]);
            }
        });

        return redirect()->route('admin.voting-types.index')
            ->with('success', "Voting type '{$preset['name']}' created successfully.");
    }
}
