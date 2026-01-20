<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Entry;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventVotingConfig;
use App\Models\Participant;
use App\Models\User;
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected VotingService $votingService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->votingService = app(VotingService::class);

        // Create or get a role for testing
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'Member'],
            ['description' => 'Regular member', 'is_system' => false]
        );

        $this->user = User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * Test voting with legacy division codes (P1, P2, A1, A2)
     */
    public function test_voting_with_legacy_division_codes(): void
    {
        // Create template with P/A division types
        $template = EventTemplate::create([
            'name' => 'Test Food Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Dish',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro chefs'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Home cooks'],
            ],
        ]);

        // Create voting type
        $votingType = VotingType::create([
            'code' => 'standard-ranked',
            'name' => 'Standard Ranked',
            'category' => 'ranked',
            'description' => '3-2-1 point system',
        ]);

        // Add place configs
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3, 'label' => '1st']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2, 'label' => '2nd']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd']);

        // Create event
        $event = Event::create([
            'name' => 'Test Cookoff',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        // Create voting config
        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        // Create divisions with legacy codes (P1, P2, A1, A2)
        $p1 = Division::create(['event_id' => $event->id, 'code' => 'P1', 'name' => 'Professional 1', 'type' => 'Professional']);
        $p2 = Division::create(['event_id' => $event->id, 'code' => 'P2', 'name' => 'Professional 2', 'type' => 'Professional']);
        $a1 = Division::create(['event_id' => $event->id, 'code' => 'A1', 'name' => 'Amateur 1', 'type' => 'Amateur']);

        // Create entries
        $participant = Participant::create(['name' => 'Test Chef', 'event_id' => $event->id]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p1->id, 'participant_id' => $participant->id, 'name' => 'Pro Dish 1', 'entry_number' => 1]);
        Entry::create(['event_id' => $event->id, 'division_id' => $p2->id, 'participant_id' => $participant->id, 'name' => 'Pro Dish 2', 'entry_number' => 2]);
        Entry::create(['event_id' => $event->id, 'division_id' => $a1->id, 'participant_id' => $participant->id, 'name' => 'Amateur Dish', 'entry_number' => 101]);

        // Test voting
        $votes = [
            'P' => [1 => '1', 2 => '2'],  // 1st place: entry 1, 2nd place: entry 2
            'A' => [1 => '101'],           // 1st place: entry 101
        ];

        $result = $this->votingService->castRankedVotes($event->fresh(), $this->user, $votes);
        $this->assertTrue($result);

        // Verify votes were recorded
        $this->assertDatabaseCount('votes', 3);
    }

    /**
     * Test voting with non-legacy division codes (T, V, H)
     */
    public function test_voting_with_non_legacy_division_codes(): void
    {
        // Create template with P/A division types
        $template = EventTemplate::create([
            'name' => 'Test Chili Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Chili',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro chefs'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Home cooks'],
            ],
        ]);

        $votingType = VotingType::create([
            'code' => 'extended-ranked',
            'name' => 'Extended Ranked',
            'category' => 'ranked',
            'description' => '5-4-3-2-1 point system',
        ]);

        // Add place configs
        for ($i = 1; $i <= 5; $i++) {
            VotingPlaceConfig::create([
                'voting_type_id' => $votingType->id,
                'place' => $i,
                'points' => 6 - $i,
            ]);
        }

        $event = Event::create([
            'name' => 'Texas Chili Test',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        // Create divisions with non-legacy codes (T, V, H)
        $t = Division::create(['event_id' => $event->id, 'code' => 'T', 'name' => 'Traditional', 'type' => 'Professional']);
        $v = Division::create(['event_id' => $event->id, 'code' => 'V', 'name' => 'Verde', 'type' => 'Professional']);
        $h = Division::create(['event_id' => $event->id, 'code' => 'H', 'name' => 'Homestyle', 'type' => 'Amateur']);

        // Create entries
        $participant = Participant::create(['name' => 'Test Chef', 'event_id' => $event->id]);
        Entry::create(['event_id' => $event->id, 'division_id' => $t->id, 'participant_id' => $participant->id, 'name' => 'Traditional Chili 1', 'entry_number' => 1]);
        Entry::create(['event_id' => $event->id, 'division_id' => $t->id, 'participant_id' => $participant->id, 'name' => 'Traditional Chili 2', 'entry_number' => 2]);
        Entry::create(['event_id' => $event->id, 'division_id' => $v->id, 'participant_id' => $participant->id, 'name' => 'Verde Chili', 'entry_number' => 3]);
        Entry::create(['event_id' => $event->id, 'division_id' => $h->id, 'participant_id' => $participant->id, 'name' => 'Home Chili', 'entry_number' => 101]);

        // Test voting - should work with entry_number strategy
        $votes = [
            'P' => [1 => '1', 2 => '2', 3 => '3'],  // Professional entries
            'A' => [1 => '101'],                      // Amateur entry
        ];

        $result = $this->votingService->castRankedVotes($event->fresh(), $this->user, $votes);
        $this->assertTrue($result);

        // Verify votes were recorded
        $this->assertDatabaseCount('votes', 4);
    }

    /**
     * Test all voting type categories
     */
    public function test_ranked_voting_type(): void
    {
        $event = $this->createTestEvent('ranked');

        $votes = ['P' => [1 => '1', 2 => '2', 3 => '3']];
        $result = $this->votingService->castRankedVotes($event, $this->user, $votes);

        $this->assertTrue($result);
        $this->assertDatabaseCount('votes', 3);
    }

    public function test_approval_voting_type(): void
    {
        $event = $this->createTestEvent('approval');
        $entries = Entry::where('event_id', $event->id)->pluck('id')->toArray();

        $result = $this->votingService->castApprovalVotes($event, $this->user, $entries);

        $this->assertTrue($result);
    }

    public function test_rating_voting_type(): void
    {
        $event = $this->createTestEvent('rating');
        $entry = Entry::where('event_id', $event->id)->first();

        $result = $this->votingService->castRatingVote($event, $this->user, $entry->id, 4.5);

        $this->assertTrue($result);
    }

    /**
     * Test invalid entry number is rejected
     */
    public function test_invalid_entry_number_throws_exception(): void
    {
        $event = $this->createTestEvent('ranked');

        $votes = ['P' => [1 => '999']];  // Non-existent entry

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->votingService->castRankedVotes($event, $this->user, $votes);
    }

    /**
     * Test duplicate voting is prevented
     */
    public function test_duplicate_voting_prevented(): void
    {
        $event = $this->createTestEvent('ranked');

        $votes = ['P' => [1 => '1', 2 => '2']];

        // First vote should succeed
        $this->votingService->castRankedVotes($event, $this->user, $votes);

        // Second vote should fail (user already voted)
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->votingService->castRankedVotes($event->fresh(), $this->user, $votes);
    }

    /**
     * Test results are sorted correctly by points
     */
    public function test_results_sorted_by_points_descending(): void
    {
        $event = $this->createTestEvent('ranked');

        // Cast votes: entry 3 gets 1st (3pts), entry 1 gets 2nd (2pts), entry 2 gets 3rd (1pt)
        $votes = ['P' => [1 => '3', 2 => '1', 3 => '2']];
        $this->votingService->castRankedVotes($event, $this->user, $votes);

        $results = $this->votingService->getResults($event->fresh());

        // Results should be sorted by total_points descending
        $this->assertGreaterThanOrEqual($results[1]->total_points ?? 0, $results[0]->total_points);
    }

    /**
     * Helper method to create a test event with all required data
     */
    private function createTestEvent(string $votingCategory): Event
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
            'code' => 'test-voting-type-' . $counter,
            'name' => 'Test Voting Type ' . $counter,
            'category' => $votingCategory,
            'description' => 'Test',
        ]);

        // Add place configs for ranked voting
        if ($votingCategory === 'ranked') {
            VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3, 'label' => '1st']);
            VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2, 'label' => '2nd']);
            VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd']);
        }

        $event = Event::create([
            'name' => 'Test Event ' . $counter,
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        // Create divisions with legacy codes
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
}
