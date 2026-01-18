<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Webhook;
use App\Services\VotingService;
use App\Services\EventConfigurationService;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use App\Repositories\Contracts\EntryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoteController extends Controller
{
    public function __construct(
        private VotingService $votingService,
        private EventConfigurationService $configService,
        private DivisionRepositoryInterface $divisionRepository,
        private EntryRepositoryInterface $entryRepository,
    ) {}

    /**
     * Show voting form
     */
    public function index(Event $event)
    {
        $event->load(['votingConfig.votingType.placeConfigs', 'template']);

        $divisions = $this->divisionRepository->getActiveByEvent($event->id);
        $entries = $this->entryRepository->getByEvent($event->id);

        // Group entries by division
        $entriesByDivision = $entries->groupBy('division_id');

        // Get place configurations as simple [place => points] array
        $rawConfigs = $event->votingConfig?->getPlaceConfigs() ?? [];
        $placeConfigs = [];
        foreach ($rawConfigs as $config) {
            $placeConfigs[$config['place']] = $config['points'];
        }

        // Get voting results for display
        $results = $this->votingService->getResults($event);
        $resultsByDivision = $results->groupBy('division_id');

        // Check if user has already voted
        $hasVoted = auth()->check()
            ? $this->votingService->hasUserVoted(auth()->user(), $event)
            : false;

        return view('voting.vote', [
            'event' => $event,
            'divisions' => $divisions,
            'entriesByDivision' => $entriesByDivision,
            'resultsByDivision' => $resultsByDivision,
            'placeConfigs' => $placeConfigs,
            'hasVoted' => $hasVoted,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }

    /**
     * Store votes
     */
    public function store(Request $request, Event $event)
    {
        $event->load(['votingConfig.votingType']);

        $votingType = $event->votingConfig?->votingType;

        try {
            switch ($votingType?->category) {
                case 'ranked':
                    $validated = $request->validate([
                        'votes' => 'required|array',
                        'votes.*' => 'array',
                        'votes.*.*' => 'nullable|integer',
                    ]);

                    // Validate inputs
                    $errors = $this->votingService->validateRankedVoteInputs($event, $validated['votes']);
                    if (!empty($errors)) {
                        throw ValidationException::withMessages($errors);
                    }

                    $this->votingService->castRankedVotes(
                        $event,
                        auth()->user(),
                        $validated['votes']
                    );
                    break;

                case 'approval':
                    $validated = $request->validate([
                        'entries' => 'required|array|min:1',
                        'entries.*' => 'integer|exists:entries,id',
                    ]);

                    $this->votingService->castApprovalVotes(
                        $event,
                        auth()->user(),
                        $validated['entries']
                    );
                    break;

                case 'rating':
                    $validated = $request->validate([
                        'ratings' => 'required|array',
                        'ratings.*' => 'numeric|min:0|max:10',
                    ]);

                    foreach ($validated['ratings'] as $entryId => $rating) {
                        $this->votingService->castRatingVote(
                            $event,
                            auth()->user(),
                            $entryId,
                            $rating
                        );
                    }
                    break;

                default:
                    throw ValidationException::withMessages([
                        'voting' => 'Invalid voting type configuration.',
                    ]);
            }

            // Dispatch webhook for vote.created event
            Webhook::dispatch('vote.created', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'user_id' => auth()->id(),
                'voting_type' => $votingType?->code,
                'voted_at' => now()->toIso8601String(),
            ]);

            return redirect()
                ->route('voting.index', $event)
                ->with('success', 'Votes successfully submitted!');

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['voting' => 'An error occurred while recording your votes. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Thank you page after voting
     */
    public function thankYou(Event $event)
    {
        return view('voting.thank-you', [
            'event' => $event,
        ]);
    }

    /**
     * Public voting (no login required)
     */
    public function publicVote(Event $event)
    {
        if (!$event->is_public) {
            abort(403, 'This event does not allow public voting.');
        }

        $event->load(['votingConfig.votingType.placeConfigs', 'template']);

        $divisions = $this->divisionRepository->getActiveByEvent($event->id);
        $entries = $this->entryRepository->getByEvent($event->id);
        $entriesByDivision = $entries->groupBy('division_id');
        $placeConfigs = $event->votingConfig?->getPlaceConfigs() ?? [];

        return view('voting.public-vote', [
            'event' => $event,
            'divisions' => $divisions,
            'entriesByDivision' => $entriesByDivision,
            'placeConfigs' => $placeConfigs,
            'participantLabel' => $this->configService->getParticipantLabel($event),
            'entryLabel' => $this->configService->getEntryLabel($event),
        ]);
    }

    /**
     * Store public votes
     */
    public function storePublicVote(Request $request, Event $event)
    {
        if (!$event->is_public) {
            abort(403, 'This event does not allow public voting.');
        }

        // For public voting, we create a temporary user or use IP-based tracking
        // This is a simplified implementation
        $request->validate([
            'votes' => 'required|array',
        ]);

        // Store votes with IP tracking instead of user ID
        // Implementation depends on business requirements

        return redirect()
            ->route('public.results', $event)
            ->with('success', 'Thank you for voting!');
    }
}
