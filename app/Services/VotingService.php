<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\Entry;
use App\Models\Division;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\EntryRepositoryInterface;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VotingService
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
        private EventRepositoryInterface $eventRepository,
        private EntryRepositoryInterface $entryRepository,
        private DivisionRepositoryInterface $divisionRepository,
    ) {}

    /**
     * Cast ranked votes for an event
     */
    public function castRankedVotes(Event $event, User $user, array $votes): bool
    {
        $config = $event->votingConfig;

        // Validate voting is open
        if (!$event->isVotingOpen()) {
            throw ValidationException::withMessages([
                'voting' => 'Voting is not currently open for this event.',
            ]);
        }

        // Check if user has already voted
        if ($this->hasUserVoted($user, $event)) {
            throw ValidationException::withMessages([
                'voting' => 'You have already voted in this event.',
            ]);
        }

        // Get user's weight multiplier
        $weightMultiplier = $user->getWeightForEvent($event);

        return DB::transaction(function () use ($event, $user, $votes, $config, $weightMultiplier) {
            foreach ($votes as $typeCode => $placeVotes) {
                // Validate no duplicate entries within a division type
                $selectedEntries = array_filter(array_values($placeVotes));
                if (count($selectedEntries) !== count(array_unique($selectedEntries))) {
                    throw ValidationException::withMessages([
                        'votes' => 'You cannot select the same entry for multiple places.',
                    ]);
                }

                foreach ($placeVotes as $place => $entryInput) {
                    if (empty($entryInput)) continue;

                    // Find entry by type code and number
                    $entry = $this->findEntryByTypeAndNumber($event, $typeCode, $entryInput);
                    if (!$entry) {
                        throw ValidationException::withMessages([
                            'votes' => "Invalid entry selection: {$entryInput}",
                        ]);
                    }

                    // Get points from config (with possible override)
                    $basePoints = $config->getPointsForPlace($place);

                    $this->voteRepository->castVote([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'entry_id' => $entry->id,
                        'division_id' => $entry->division_id,
                        'place' => $place,
                        'base_points' => $basePoints,
                        'weight_multiplier' => $weightMultiplier,
                        'voter_ip' => request()->ip(),
                    ]);
                }
            }

            // Update vote summaries
            $this->voteRepository->updateVoteSummaries($event->id);

            return true;
        });
    }

    /**
     * Cast approval votes (equal weight, multiple selections)
     */
    public function castApprovalVotes(Event $event, User $user, array $entryIds): bool
    {
        $config = $event->votingConfig;
        $votingType = $config->votingType;
        $settings = $votingType->settings ?? [];

        // Check max selections
        $maxSelections = $settings['max_selections'] ?? null;
        if ($maxSelections && count($entryIds) > $maxSelections) {
            throw ValidationException::withMessages([
                'entries' => "Maximum {$maxSelections} selections allowed.",
            ]);
        }

        $pointsPerVote = $settings['points_per_vote'] ?? 1;

        return DB::transaction(function () use ($event, $user, $entryIds, $pointsPerVote) {
            foreach ($entryIds as $entryId) {
                $this->voteRepository->castVote([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'entry_id' => $entryId,
                    'place' => null,
                    'base_points' => $pointsPerVote,
                    'weight_multiplier' => 1.0,
                    'voter_ip' => request()->ip(),
                ]);
            }

            $this->voteRepository->updateVoteSummaries($event->id);

            return true;
        });
    }

    /**
     * Cast rating vote (star rating, score voting)
     */
    public function castRatingVote(Event $event, User $user, int $entryId, float $rating): bool
    {
        $config = $event->votingConfig;
        $settings = $config->votingType->settings ?? [];

        $minRating = $settings['min_rating'] ?? 0;
        $maxRating = $settings['max_rating'] ?? 10;

        if ($rating < $minRating || $rating > $maxRating) {
            throw ValidationException::withMessages([
                'rating' => "Rating must be between {$minRating} and {$maxRating}.",
            ]);
        }

        return $this->voteRepository->castVote([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'entry_id' => $entryId,
            'rating' => $rating,
            'base_points' => $rating,
            'weight_multiplier' => 1.0,
            'voter_ip' => request()->ip(),
        ]);
    }

    /**
     * Get results for an event
     */
    public function getResults(Event $event): Collection
    {
        $places = $this->getPlacesForEvent($event);
        return $this->voteRepository->getResultsByEvent($event->id, $places);
    }

    /**
     * Get results by division
     */
    public function getResultsByDivision(Event $event, Division $division): Collection
    {
        $places = $this->getPlacesForEvent($event);
        return $this->voteRepository->getResultsByDivision($event->id, $division->id, $places);
    }

    /**
     * Get places array for an event's voting configuration
     */
    private function getPlacesForEvent(Event $event): array
    {
        $placeConfigs = $event->votingConfig?->getPlaceConfigs() ?? [];
        if (empty($placeConfigs)) {
            return [1, 2, 3]; // Default fallback
        }
        return array_map(fn($config) => (int) $config['place'], $placeConfigs);
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(Event $event, ?int $divisionId = null, int $limit = 10): Collection
    {
        return $this->voteRepository->getLeaderboard($event->id, $divisionId, $limit);
    }

    /**
     * Check if user has voted
     */
    public function hasUserVoted(User $user, Event $event, ?Division $division = null): bool
    {
        return $this->voteRepository->hasUserVoted($user->id, $event->id, $division?->id);
    }

    /**
     * Get user's votes for an event
     */
    public function getUserVotes(User $user, Event $event): Collection
    {
        return $this->voteRepository->getUserVotesForEvent($user->id, $event->id);
    }

    /**
     * Validate vote inputs for ranked voting
     */
    public function validateRankedVoteInputs(Event $event, array $votes): array
    {
        $errors = [];
        $hasAtLeastOneVote = false;

        foreach ($votes as $typeCode => $placeVotes) {
            $selectedEntries = [];

            foreach ($placeVotes as $place => $entryInput) {
                if (empty($entryInput)) continue;

                $hasAtLeastOneVote = true;

                // Check for duplicates
                if (in_array($entryInput, $selectedEntries)) {
                    $errors["votes.{$typeCode}"] = "Cannot select the same entry for multiple places.";
                }
                $selectedEntries[] = $entryInput;

                // Validate entry exists
                $entry = $this->findEntryByTypeAndNumber($event, $typeCode, $entryInput);
                if (!$entry) {
                    $errors["votes.{$typeCode}.{$place}"] = "Invalid entry number: {$entryInput}";
                }
            }
        }

        if (!$hasAtLeastOneVote) {
            $errors['votes'] = 'Please make at least one selection.';
        }

        return $errors;
    }

    /**
     * Find entry by division type code and user input number
     *
     * Supports multiple lookup strategies:
     * 1. Legacy format: typeCode + number = division code (P1, A1, etc.)
     * 2. Entry number within type: Find entry by entry_number within divisions of matching type
     * 3. Direct entry number: Find entry by entry_number regardless of division type
     */
    private function findEntryByTypeAndNumber(Event $event, string $typeCode, mixed $input): ?Entry
    {
        if (!is_numeric($input)) {
            return null;
        }

        $userInput = (int) $input;

        // Strategy 1: Try legacy format (typeCode + number = P1, A1, etc.)
        $divisionCode = strtoupper($typeCode) . $userInput;
        $entry = Entry::where('event_id', $event->id)
            ->whereHas('division', function ($query) use ($divisionCode) {
                $query->where('code', $divisionCode);
            })
            ->first();

        if ($entry) {
            return $entry;
        }

        // Strategy 2: Find by entry_number within divisions of the given type
        // Get the type name from the template's division_types by code
        $template = $event->template;
        $divisionTypes = $template->getDivisionTypes();
        $typeName = null;

        foreach ($divisionTypes as $dt) {
            if (strtoupper($dt['code']) === strtoupper($typeCode)) {
                $typeName = $dt['name'];
                break;
            }
        }

        if ($typeName) {
            $entry = Entry::where('event_id', $event->id)
                ->where('entry_number', $userInput)
                ->whereHas('division', function ($query) use ($typeName) {
                    $query->where('type', $typeName);
                })
                ->first();

            if ($entry) {
                return $entry;
            }
        }

        // Strategy 3: Direct entry number lookup (for events without division types)
        $entry = Entry::where('event_id', $event->id)
            ->where('entry_number', $userInput)
            ->first();

        return $entry;
    }
}
