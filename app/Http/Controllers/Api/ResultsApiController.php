<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Division;
use App\Services\VotingService;
use Illuminate\Http\Request;

class ResultsApiController extends Controller
{
    public function __construct(
        private VotingService $votingService
    ) {}

    public function index(Event $event)
    {
        $results = $this->votingService->getResults($event);
        return response()->json($results);
    }

    public function byDivision(Event $event, Division $division)
    {
        $results = $this->votingService->getResultsByDivision($event, $division);
        return response()->json($results);
    }

    public function leaderboard(Request $request, Event $event)
    {
        $limit = $request->get('limit', 10);
        $divisionId = $request->get('division_id');

        $leaderboard = $this->votingService->getLeaderboard($event, $divisionId, $limit);
        return response()->json($leaderboard);
    }

    public function summary(Event $event)
    {
        $results = $this->votingService->getResults($event);

        return response()->json([
            'total_votes' => $event->votes()->count(),
            'total_entries' => $event->entries()->count(),
            'total_participants' => $event->participants()->count(),
            'total_divisions' => $event->divisions()->count(),
            'results_count' => $results->count(),
        ]);
    }
}
