<?php

namespace App\Services\AI;

use App\Models\AiTool;
use App\Models\Event;
use App\Models\Entry;
use App\Models\Participant;
use App\Models\Division;
use App\Models\Vote;
use App\Services\VotingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AiToolExecutor
{
    protected ?Event $currentEvent = null;
    protected VotingService $votingService;

    public function __construct(VotingService $votingService)
    {
        $this->votingService = $votingService;
    }

    /**
     * Set the current event context.
     */
    public function setEvent(?Event $event): self
    {
        $this->currentEvent = $event;
        return $this;
    }

    /**
     * Execute a tool by code.
     */
    public function execute(string $toolCode, array $params = []): array
    {
        $tool = AiTool::where('code', $toolCode)->where('is_active', true)->first();

        if (!$tool) {
            return ['success' => false, 'error' => "Tool '{$toolCode}' not found or inactive."];
        }

        if ($tool->requires_event && !$this->currentEvent) {
            return ['success' => false, 'error' => "This tool requires an event context. Please select an event first."];
        }

        if ($tool->requires_auth && !Auth::check()) {
            return ['success' => false, 'error' => "This tool requires authentication."];
        }

        // Execute the appropriate handler
        return match ($toolCode) {
            'get_event_results' => $this->getEventResults($params),
            'get_event_stats' => $this->getEventStats($params),
            'list_events' => $this->listEvents($params),
            'list_entries' => $this->listEntries($params),
            'list_participants' => $this->listParticipants($params),
            'list_divisions' => $this->listDivisions($params),
            'search_entries' => $this->searchEntries($params),
            'get_leaderboard' => $this->getLeaderboard($params),
            'get_vote_count' => $this->getVoteCount($params),
            default => ['success' => false, 'error' => "No handler implemented for '{$toolCode}'."],
        };
    }

    /**
     * Get results for current or specified event.
     */
    protected function getEventResults(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $results = $this->votingService->getResults($event);

        return [
            'success' => true,
            'event' => $event->name,
            'results' => $results->map(function ($r) {
                return [
                    'rank' => $r->rank ?? null,
                    'entry' => $r->entry_name,
                    'participant' => $r->participant_name,
                    'division' => $r->division_name,
                    'points' => $r->total_points,
                    'votes' => $r->vote_count,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get statistics for current or specified event.
     */
    protected function getEventStats(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        return [
            'success' => true,
            'event' => $event->name,
            'stats' => [
                'total_votes' => Vote::where('event_id', $event->id)->count(),
                'unique_voters' => Vote::where('event_id', $event->id)->distinct('user_id')->count('user_id'),
                'total_entries' => Entry::where('event_id', $event->id)->count(),
                'total_participants' => Participant::where('event_id', $event->id)->count(),
                'total_divisions' => Division::where('event_id', $event->id)->count(),
                'total_points' => Vote::where('event_id', $event->id)->sum('final_points'),
            ],
        ];
    }

    /**
     * List all events.
     */
    protected function listEvents(array $params): array
    {
        $query = Event::with('template');

        if (isset($params['active'])) {
            $query->where('is_active', $params['active']);
        }

        $events = $query->orderByDesc('created_at')->limit(20)->get();

        return [
            'success' => true,
            'count' => $events->count(),
            'events' => $events->map(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'template' => $e->template?->name,
                    'date' => $e->event_date?->format('Y-m-d'),
                    'active' => $e->is_active,
                    'entries' => $e->entries()->count(),
                    'votes' => $e->votes()->count(),
                ];
            })->toArray(),
        ];
    }

    /**
     * List entries for current event.
     */
    protected function listEntries(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $query = Entry::with(['participant', 'division'])
            ->where('event_id', $event->id);

        if (isset($params['division_id'])) {
            $query->where('division_id', $params['division_id']);
        }

        $entries = $query->orderBy('entry_number')->get();

        return [
            'success' => true,
            'event' => $event->name,
            'count' => $entries->count(),
            'entries' => $entries->map(function ($e) {
                return [
                    'id' => $e->id,
                    'number' => $e->entry_number,
                    'name' => $e->name,
                    'participant' => $e->participant?->name,
                    'division' => $e->division?->name,
                ];
            })->toArray(),
        ];
    }

    /**
     * List participants for current event.
     */
    protected function listParticipants(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $participants = Participant::where('event_id', $event->id)
            ->orderBy('name')
            ->get();

        return [
            'success' => true,
            'event' => $event->name,
            'count' => $participants->count(),
            'participants' => $participants->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'email' => $p->email,
                    'entries' => $p->entries()->count(),
                ];
            })->toArray(),
        ];
    }

    /**
     * List divisions for current event.
     */
    protected function listDivisions(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $divisions = Division::where('event_id', $event->id)
            ->orderBy('display_order')
            ->get();

        return [
            'success' => true,
            'event' => $event->name,
            'count' => $divisions->count(),
            'divisions' => $divisions->map(function ($d) {
                return [
                    'id' => $d->id,
                    'code' => $d->code,
                    'name' => $d->name,
                    'type' => $d->type,
                    'entries' => $d->entries()->count(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Search entries by name or participant.
     */
    protected function searchEntries(array $params): array
    {
        $query = $params['query'] ?? '';
        if (strlen($query) < 2) {
            return ['success' => false, 'error' => 'Search query must be at least 2 characters.'];
        }

        $event = $this->resolveEvent($params);

        $entries = Entry::with(['participant', 'division', 'event'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('participant', function ($pq) use ($query) {
                        $pq->where('name', 'like', "%{$query}%");
                    });
            });

        if ($event) {
            $entries->where('event_id', $event->id);
        }

        $results = $entries->limit(20)->get();

        return [
            'success' => true,
            'query' => $query,
            'count' => $results->count(),
            'entries' => $results->map(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'participant' => $e->participant?->name,
                    'division' => $e->division?->name,
                    'event' => $e->event?->name,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get leaderboard for current event.
     */
    protected function getLeaderboard(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $limit = $params['limit'] ?? 10;
        $divisionId = $params['division_id'] ?? null;

        $leaderboard = $this->votingService->getLeaderboard($event, $divisionId, $limit);

        return [
            'success' => true,
            'event' => $event->name,
            'leaderboard' => $leaderboard->toArray(),
        ];
    }

    /**
     * Get vote count for current event.
     */
    protected function getVoteCount(array $params): array
    {
        $event = $this->resolveEvent($params);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }

        $count = Vote::where('event_id', $event->id)->count();
        $uniqueVoters = Vote::where('event_id', $event->id)->distinct('user_id')->count('user_id');

        return [
            'success' => true,
            'event' => $event->name,
            'total_votes' => $count,
            'unique_voters' => $uniqueVoters,
        ];
    }

    /**
     * Resolve event from params or current context.
     */
    protected function resolveEvent(array $params): ?Event
    {
        if (isset($params['event_id'])) {
            return Event::find($params['event_id']);
        }

        return $this->currentEvent;
    }

    /**
     * Get available tools for AI function calling format.
     */
    public static function getToolDefinitions(): array
    {
        $tools = AiTool::where('is_active', true)->get();

        return $tools->map(function ($tool) {
            return [
                'type' => 'function',
                'function' => [
                    'name' => $tool->code,
                    'description' => $tool->description,
                    'parameters' => $tool->parameters ?? [
                        'type' => 'object',
                        'properties' => [],
                        'required' => [],
                    ],
                ],
            ];
        })->toArray();
    }
}
