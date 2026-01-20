<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Entry;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventVotingConfig;
use App\Models\Participant;
use App\Models\User;
use App\Models\Role;
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use App\Models\VoterWeightClass;
use App\Models\UserVoterClass;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Carbon\Carbon;

class VotingEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected VotingService $votingService;
    protected User $user;
    protected Role $memberRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->votingService = app(VotingService::class);

        $this->memberRole = Role::firstOrCreate(
            ['name' => 'Member'],
            ['description' => 'Regular member', 'is_system' => false]
        );

        $this->user = User::factory()->create(['role_id' => $this->memberRole->id]);
    }

    /**
     * Test voting fails when event voting time has not started
     */
    public function test_voting_fails_before_voting_start_time(): void
    {
        $event = $this->createTestEvent([
            'voting_starts_at' => Carbon::now()->addDay(),
            'voting_ends_at' => Carbon::now()->addDays(2),
        ]);

        $votes = ['P' => [1 => '1']];

        $this->expectException(ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test voting fails after voting end time
     */
    public function test_voting_fails_after_voting_end_time(): void
    {
        $event = $this->createTestEvent([
            'voting_starts_at' => Carbon::now()->subDays(2),
            'voting_ends_at' => Carbon::now()->subDay(),
        ]);

        $votes = ['P' => [1 => '1']];

        $this->expectException(ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test voting fails when event is inactive
     */
    public function test_voting_fails_when_event_inactive(): void
    {
        $event = $this->createTestEvent([
            'is_active' => false,
        ]);

        $votes = ['P' => [1 => '1']];

        $this->expectException(ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test same entry cannot be selected for multiple places
     */
    public function test_same_entry_cannot_be_multiple_places(): void
    {
        $event = $this->createTestEvent();

        // Try to vote for same entry in 1st and 2nd place
        $votes = ['P' => [1 => '1', 2 => '1', 3 => '3']];

        $this->expectException(ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test empty votes are allowed but must have at least one selection
     */
    public function test_empty_votes_validation(): void
    {
        $event = $this->createTestEvent();

        // All empty votes
        $votes = ['P' => [1 => '', 2 => '', 3 => '']];

        $errors = $this->votingService->validateRankedVoteInputs($event, $votes);

        $this->assertArrayHasKey('votes', $errors);
    }

    /**
     * Test approval voting with max selections limit
     */
    public function test_approval_voting_respects_max_selections(): void
    {
        $event = $this->createApprovalEvent(3); // max 3 selections

        $entries = Entry::where('event_id', $event->id)->pluck('id')->toArray();

        // Try to vote for more than allowed
        $this->expectException(ValidationException::class);
        $this->votingService->castApprovalVotes($event, $this->user, $entries);
    }

    /**
     * Test approval voting within limit succeeds
     */
    public function test_approval_voting_within_limit_succeeds(): void
    {
        $event = $this->createApprovalEvent(5); // max 5 selections

        $entries = Entry::where('event_id', $event->id)->take(3)->pluck('id')->toArray();

        $result = $this->votingService->castApprovalVotes($event, $this->user, $entries);

        $this->assertTrue($result);
    }

    /**
     * Test rating voting with invalid low rating
     */
    public function test_rating_voting_rejects_rating_below_minimum(): void
    {
        $event = $this->createRatingEvent(0, 10); // min 0, max 10
        $entry = Entry::where('event_id', $event->id)->first();

        $this->expectException(ValidationException::class);
        $this->votingService->castRatingVote($event, $this->user, $entry->id, -1);
    }

    /**
     * Test rating voting with invalid high rating
     */
    public function test_rating_voting_rejects_rating_above_maximum(): void
    {
        $event = $this->createRatingEvent(0, 10); // min 0, max 10
        $entry = Entry::where('event_id', $event->id)->first();

        $this->expectException(ValidationException::class);
        $this->votingService->castRatingVote($event, $this->user, $entry->id, 15);
    }

    /**
     * Test rating voting with valid rating
     */
    public function test_rating_voting_accepts_valid_rating(): void
    {
        $event = $this->createRatingEvent(0, 10);
        $entry = Entry::where('event_id', $event->id)->first();

        $result = $this->votingService->castRatingVote($event, $this->user, $entry->id, 7.5);

        $this->assertTrue($result);
    }

    /**
     * Test non-numeric entry input is rejected
     */
    public function test_non_numeric_entry_input_rejected(): void
    {
        $event = $this->createTestEvent();

        $votes = ['P' => [1 => 'abc']];

        $this->expectException(ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test partial votes are allowed
     */
    public function test_partial_votes_are_allowed(): void
    {
        $event = $this->createTestEvent();

        // Only vote for 1st place
        $votes = ['P' => [1 => '1']];

        $result = $this->votingService->castRankedVotes($event, $this->user, $votes);

        $this->assertTrue($result);
        $this->assertDatabaseCount('votes', 1);
    }

    /**
     * Test votes across multiple division types
     */
    public function test_votes_across_multiple_division_types(): void
    {
        $event = $this->createMultiTypeEvent();

        $votes = [
            'P' => [1 => '1', 2 => '2'],
            'A' => [1 => '101'],
        ];

        $result = $this->votingService->castRankedVotes($event, $this->user, $votes);

        $this->assertTrue($result);
        $this->assertDatabaseCount('votes', 3);
    }

    /**
     * Test user has voted check
     */
    public function test_user_has_voted_check(): void
    {
        $event = $this->createTestEvent();

        $this->assertFalse($this->votingService->hasUserVoted($this->user, $event));

        $votes = ['P' => [1 => '1']];
        $this->votingService->castRankedVotes($event, $this->user, $votes);

        $this->assertTrue($this->votingService->hasUserVoted($this->user, $event->fresh()));
    }

    /**
     * Test get user votes
     */
    public function test_get_user_votes(): void
    {
        $event = $this->createTestEvent();

        $votes = ['P' => [1 => '1', 2 => '2']];
        $this->votingService->castRankedVotes($event, $this->user, $votes);

        $userVotes = $this->votingService->getUserVotes($this->user, $event);

        $this->assertEquals(2, $userVotes->count());
    }

    // -------------------- Helper Methods --------------------

    private function createTestEvent(array $overrides = []): Event
    {
        static $counter = 0;
        $counter++;

        $template = EventTemplate::create([
            'name' => 'Test Template ' . $counter,
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro category'],
            ],
        ]);

        $votingType = VotingType::create([
            'code' => 'test-ranked-' . $counter,
            'name' => 'Test Ranked ' . $counter,
            'category' => 'ranked',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3]);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2]);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1]);

        $event = Event::create(array_merge([
            'name' => 'Test Event ' . $counter,
            'event_template_id' => $template->id,
            'is_active' => true,
        ], $overrides));

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        // Create divisions
        $p1 = Division::create(['event_id' => $event->id, 'code' => 'P1', 'name' => 'Pro 1', 'type' => 'Professional']);
        $p2 = Division::create(['event_id' => $event->id, 'code' => 'P2', 'name' => 'Pro 2', 'type' => 'Professional']);
        $p3 = Division::create(['event_id' => $event->id, 'code' => 'P3', 'name' => 'Pro 3', 'type' => 'Professional']);

        // Create entries
        $participant = Participant::create(['name' => 'Test Participant', 'event_id' => $event->id]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p1->id, 'participant_id' => $participant->id, 'name' => 'Entry 1', 'entry_number' => 1]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p2->id, 'participant_id' => $participant->id, 'name' => 'Entry 2', 'entry_number' => 2]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p3->id, 'participant_id' => $participant->id, 'name' => 'Entry 3', 'entry_number' => 3]);

        return $event->fresh();
    }

    private function createMultiTypeEvent(): Event
    {
        static $counter = 0;
        $counter++;

        $template = EventTemplate::create([
            'name' => 'Multi Type Template ' . $counter,
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Amateur'],
            ],
        ]);

        $votingType = VotingType::create([
            'code' => 'multi-ranked-' . $counter,
            'name' => 'Multi Ranked ' . $counter,
            'category' => 'ranked',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3]);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2]);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1]);

        $event = Event::create([
            'name' => 'Multi Type Event ' . $counter,
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        // Professional divisions
        $p1 = Division::create(['event_id' => $event->id, 'code' => 'P1', 'name' => 'Pro 1', 'type' => 'Professional']);
        $p2 = Division::create(['event_id' => $event->id, 'code' => 'P2', 'name' => 'Pro 2', 'type' => 'Professional']);

        // Amateur divisions
        $a1 = Division::create(['event_id' => $event->id, 'code' => 'A1', 'name' => 'Amateur 1', 'type' => 'Amateur']);

        $participant = Participant::create(['name' => 'Test Participant', 'event_id' => $event->id]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p1->id, 'participant_id' => $participant->id, 'name' => 'Pro Entry 1', 'entry_number' => 1]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p2->id, 'participant_id' => $participant->id, 'name' => 'Pro Entry 2', 'entry_number' => 2]);
        Entry::create(['event_id' => $event->id, 'division_id' => $a1->id, 'participant_id' => $participant->id, 'name' => 'Amateur Entry 1', 'entry_number' => 101]);

        return $event->fresh();
    }

    private function createApprovalEvent(int $maxSelections = 3): Event
    {
        static $counter = 0;
        $counter++;

        $template = EventTemplate::create([
            'name' => 'Approval Template ' . $counter,
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        $votingType = VotingType::create([
            'code' => 'approval-test-' . $counter,
            'name' => 'Approval Test ' . $counter,
            'category' => 'approval',
            'settings' => ['max_selections' => $maxSelections, 'points_per_vote' => 1],
        ]);

        $event = Event::create([
            'name' => 'Approval Event ' . $counter,
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        $division = Division::create(['event_id' => $event->id, 'code' => 'D1', 'name' => 'Division 1']);

        $participant = Participant::create(['name' => 'Participant', 'event_id' => $event->id]);

        // Create more entries than max selections
        for ($i = 1; $i <= 5; $i++) {
            Entry::create([
                'event_id' => $event->id,
                'division_id' => $division->id,
                'participant_id' => $participant->id,
                'name' => "Entry $i",
                'entry_number' => $i,
            ]);
        }

        return $event->fresh();
    }

    private function createRatingEvent(int $minRating = 0, int $maxRating = 10): Event
    {
        static $counter = 0;
        $counter++;

        $template = EventTemplate::create([
            'name' => 'Rating Template ' . $counter,
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        $votingType = VotingType::create([
            'code' => 'rating-test-' . $counter,
            'name' => 'Rating Test ' . $counter,
            'category' => 'rating',
            'settings' => ['min_rating' => $minRating, 'max_rating' => $maxRating],
        ]);

        $event = Event::create([
            'name' => 'Rating Event ' . $counter,
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        $division = Division::create(['event_id' => $event->id, 'code' => 'D1', 'name' => 'Division 1']);
        $participant = Participant::create(['name' => 'Participant', 'event_id' => $event->id]);
        Entry::create([
            'event_id' => $event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 1',
            'entry_number' => 1,
        ]);

        return $event->fresh();
    }
}
