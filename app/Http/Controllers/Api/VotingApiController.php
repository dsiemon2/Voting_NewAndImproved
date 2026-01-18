<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\VotingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VotingApiController extends Controller
{
    public function __construct(
        private VotingService $votingService
    ) {}

    public function castVote(Request $request, Event $event)
    {
        try {
            $validated = $request->validate([
                'votes' => 'required|array',
                'votes.*' => 'array',
                'votes.*.*' => 'nullable|integer',
            ]);

            $this->votingService->castRankedVotes(
                $event,
                auth()->user(),
                $validated['votes']
            );

            return response()->json([
                'success' => true,
                'message' => 'Vote recorded successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function myVotes(Event $event)
    {
        $votes = $this->votingService->getUserVotes(auth()->user(), $event);
        return response()->json($votes);
    }

    public function hasVoted(Event $event)
    {
        $hasVoted = $this->votingService->hasUserVoted(auth()->user(), $event);
        return response()->json(['has_voted' => $hasVoted]);
    }

    public function validateVote(Request $request, Event $event)
    {
        $validated = $request->validate([
            'votes' => 'required|array',
        ]);

        $errors = $this->votingService->validateRankedVoteInputs($event, $validated['votes']);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }
}
