<?php

namespace App\Repositories\Eloquent;

use App\Models\Vote;
use App\Models\VoteSummary;
use App\Repositories\Contracts\VoteRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VoteRepository extends BaseRepository implements VoteRepositoryInterface
{
    public function __construct(Vote $model)
    {
        parent::__construct($model);
    }

    public function castVote(array $voteData): bool
    {
        return DB::transaction(function () use ($voteData) {
            // For ranked voting, check if user already voted for this entry at this place
            // Each place vote should be a separate record
            if (isset($voteData['place'])) {
                $existingVote = $this->model
                    ->where('user_id', $voteData['user_id'])
                    ->where('event_id', $voteData['event_id'])
                    ->where('entry_id', $voteData['entry_id'])
                    ->where('place', $voteData['place'])
                    ->first();

                if ($existingVote) {
                    // Update existing vote for same entry at same place
                    return $existingVote->update([
                        'base_points' => $voteData['base_points'],
                        'weight_multiplier' => $voteData['weight_multiplier'] ?? 1.0,
                    ]);
                }
            } else {
                // For non-ranked voting (approval, rating), check by entry only
                $existingVote = $this->model
                    ->where('user_id', $voteData['user_id'])
                    ->where('event_id', $voteData['event_id'])
                    ->where('entry_id', $voteData['entry_id'])
                    ->whereNull('place')
                    ->first();

                if ($existingVote) {
                    return $existingVote->update([
                        'base_points' => $voteData['base_points'],
                        'weight_multiplier' => $voteData['weight_multiplier'] ?? 1.0,
                        'rating' => $voteData['rating'] ?? null,
                    ]);
                }
            }

            return (bool) $this->model->create($voteData);
        });
    }

    public function getVotesByEvent(int $eventId): Collection
    {
        return $this->model
            ->with(['entry', 'division', 'user'])
            ->where('event_id', $eventId)
            ->get();
    }

    public function getVotesByDivision(int $eventId, int $divisionId): Collection
    {
        return $this->model
            ->with(['entry', 'user'])
            ->where('event_id', $eventId)
            ->where('division_id', $divisionId)
            ->get();
    }

    public function getResultsByEvent(int $eventId, array $places = [1, 2, 3]): Collection
    {
        $selectColumns = [
            'entries.id as entry_id',
            'entries.name as entry_name',
            'entries.entry_number',
            'divisions.id as division_id',
            'divisions.name as division_name',
            'divisions.code as division_code',
            'participants.name as participant_name',
            DB::raw('SUM(votes.final_points) as total_points'),
            DB::raw('COUNT(votes.id) as vote_count'),
        ];

        // Dynamically add place count columns based on provided places
        foreach ($places as $place) {
            $selectColumns[] = DB::raw("SUM(CASE WHEN votes.place = {$place} THEN 1 ELSE 0 END) as place_{$place}_count");
        }

        return DB::table('votes')
            ->select($selectColumns)
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'votes.division_id', '=', 'divisions.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $eventId)
            ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'divisions.id', 'divisions.name', 'divisions.code', 'participants.name')
            ->orderByDesc('total_points')
            ->get();
    }

    public function getResultsByDivision(int $eventId, int $divisionId, array $places = [1, 2, 3]): Collection
    {
        $selectColumns = [
            'entries.id as entry_id',
            'entries.name as entry_name',
            'entries.entry_number',
            'participants.name as participant_name',
            DB::raw('SUM(votes.final_points) as total_points'),
            DB::raw('COUNT(votes.id) as vote_count'),
        ];

        // Dynamically add place count columns based on provided places
        foreach ($places as $place) {
            $selectColumns[] = DB::raw("SUM(CASE WHEN votes.place = {$place} THEN 1 ELSE 0 END) as place_{$place}_count");
        }

        return DB::table('votes')
            ->select($selectColumns)
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $eventId)
            ->where('votes.division_id', $divisionId)
            ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'participants.name')
            ->orderByDesc('total_points')
            ->get();
    }

    public function hasUserVoted(int $userId, int $eventId, ?int $divisionId = null): bool
    {
        $query = $this->model
            ->where('user_id', $userId)
            ->where('event_id', $eventId);

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        return $query->exists();
    }

    public function getUserVotesForEvent(int $userId, int $eventId): Collection
    {
        return $this->model
            ->with(['entry', 'division'])
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->get();
    }

    public function calculateTotalPoints(int $eventId, int $entryId): float
    {
        return (float) $this->model
            ->where('event_id', $eventId)
            ->where('entry_id', $entryId)
            ->sum('final_points');
    }

    public function getLeaderboard(int $eventId, ?int $divisionId = null, int $limit = 10): Collection
    {
        $query = DB::table('votes')
            ->select([
                'entries.id',
                'entries.name',
                'entries.entry_number',
                'participants.name as participant_name',
                DB::raw('SUM(votes.final_points) as total_points'),
            ])
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $eventId);

        if ($divisionId) {
            $query->where('votes.division_id', $divisionId);
        }

        return $query
            ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'participants.name')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
    }

    public function updateVoteSummaries(int $eventId): void
    {
        $results = $this->getResultsByEvent($eventId);

        foreach ($results as $result) {
            VoteSummary::updateOrCreate(
                [
                    'event_id' => $eventId,
                    'entry_id' => $result->entry_id,
                    'division_id' => $result->division_id,
                ],
                [
                    'total_points' => $result->total_points,
                    'vote_count' => $result->vote_count,
                    'first_place_count' => $result->place_1_count ?? 0,
                    'second_place_count' => $result->place_2_count ?? 0,
                    'third_place_count' => $result->place_3_count ?? 0,
                ]
            );
        }

        // Update rankings within each division
        $divisions = VoteSummary::where('event_id', $eventId)
            ->select('division_id')
            ->distinct()
            ->pluck('division_id');

        foreach ($divisions as $divisionId) {
            $summaries = VoteSummary::where('event_id', $eventId)
                ->where('division_id', $divisionId)
                ->orderByDesc('total_points')
                ->get();

            $rank = 1;
            foreach ($summaries as $summary) {
                $summary->update(['ranking' => $rank++]);
            }
        }
    }
}
