<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Division;
use App\Services\VotingService;
use App\Services\EventConfigurationService;
use App\Repositories\Contracts\DivisionRepositoryInterface;

class ResultsController extends Controller
{
    public function __construct(
        private VotingService $votingService,
        private EventConfigurationService $configService,
        private DivisionRepositoryInterface $divisionRepository,
    ) {}

    /**
     * Show results for an event
     */
    public function index(Event $event)
    {
        $event->load(['votingConfig.votingType.placeConfigs', 'template']);

        $divisions = $this->divisionRepository->getActiveByEvent($event->id);
        $results = $this->votingService->getResults($event);

        // Group results by division, sorted by points within each
        $resultsByDivision = $results->groupBy('division_id')->map(function ($group) {
            return $group->sortByDesc('total_points')->values();
        });

        // Group divisions by type for display
        $divisionsByType = $divisions->groupBy(function($division) {
            return $division->type ?? 'Other';
        });

        // Get place configurations as simple [place => points] array
        $rawConfigs = $event->votingConfig?->getPlaceConfigs() ?? [];
        $placeConfigs = [];
        foreach ($rawConfigs as $config) {
            $placeConfigs[$config['place']] = $config['points'];
        }

        return view('results.index', [
            'event' => $event,
            'divisions' => $divisions,
            'divisionsByType' => $divisionsByType,
            'resultsByDivision' => $resultsByDivision,
            'placeConfigs' => $placeConfigs,
            'showVoteCounts' => $event->votingConfig?->show_vote_counts ?? true,
            'showPercentages' => $event->votingConfig?->show_percentages ?? true,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }

    /**
     * Show results for a specific division
     */
    public function byDivision(Event $event, Division $division)
    {
        $event->load(['votingConfig.votingType', 'template']);

        $results = $this->votingService->getResultsByDivision($event, $division);

        return view('results.division', [
            'event' => $event,
            'division' => $division,
            'results' => $results,
            'showVoteCounts' => $event->votingConfig?->show_vote_counts ?? true,
            'showPercentages' => $event->votingConfig?->show_percentages ?? true,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }

    /**
     * Live results (auto-refresh)
     */
    public function live(Event $event)
    {
        if (!$event->votingConfig?->show_live_results) {
            abort(403, 'Live results are not enabled for this event.');
        }

        $divisions = $this->divisionRepository->getActiveByEvent($event->id);
        $results = $this->votingService->getResults($event);
        $resultsByDivision = $results->groupBy('division_id')->map(function ($group) {
            return $group->sortByDesc('total_points')->values();
        });

        return view('results.live', [
            'event' => $event,
            'divisions' => $divisions,
            'resultsByDivision' => $resultsByDivision,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }

    /**
     * Get results as JSON for polling/live updates
     */
    public function poll(Event $event)
    {
        $results = $this->votingService->getResults($event);

        // Get vote count for change detection
        $voteCount = $event->votes()->count();

        return response()->json([
            'success' => true,
            'vote_count' => $voteCount,
            'last_updated' => now()->toIso8601String(),
            'results' => $results->map(function ($result) {
                return [
                    'entry_id' => $result->entry_id,
                    'entry_name' => $result->entry_name,
                    'entry_number' => $result->entry_number,
                    'division_id' => $result->division_id,
                    'division_name' => $result->division_name,
                    'participant_name' => $result->participant_name,
                    'total_points' => (float) $result->total_points,
                    'vote_count' => (int) $result->vote_count,
                    'first_place_count' => (int) ($result->first_place_count ?? 0),
                    'second_place_count' => (int) ($result->second_place_count ?? 0),
                    'third_place_count' => (int) ($result->third_place_count ?? 0),
                ];
            })->values(),
        ]);
    }

    /**
     * Public results
     */
    public function publicResults(Event $event)
    {
        if (!$event->is_public) {
            abort(403, 'This event does not have public results.');
        }

        $divisions = $this->divisionRepository->getActiveByEvent($event->id);
        $results = $this->votingService->getResults($event);
        $resultsByDivision = $results->groupBy('division_id')->map(function ($group) {
            return $group->sortByDesc('total_points')->values();
        });

        return view('results.public', [
            'event' => $event,
            'divisions' => $divisions,
            'resultsByDivision' => $resultsByDivision,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }
}
