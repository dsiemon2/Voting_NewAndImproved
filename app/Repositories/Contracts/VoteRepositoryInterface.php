<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface VoteRepositoryInterface extends BaseRepositoryInterface
{
    public function castVote(array $voteData): bool;

    public function getVotesByEvent(int $eventId): Collection;

    public function getVotesByDivision(int $eventId, int $divisionId): Collection;

    public function getResultsByEvent(int $eventId, array $places = [1, 2, 3]): Collection;

    public function getResultsByDivision(int $eventId, int $divisionId, array $places = [1, 2, 3]): Collection;

    public function hasUserVoted(int $userId, int $eventId, ?int $divisionId = null): bool;

    public function getUserVotesForEvent(int $userId, int $eventId): Collection;

    public function calculateTotalPoints(int $eventId, int $entryId): float;

    public function getLeaderboard(int $eventId, ?int $divisionId = null, int $limit = 10): Collection;

    public function updateVoteSummaries(int $eventId): void;
}
