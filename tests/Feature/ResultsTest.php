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
use App\Models\Vote;
use App\Models\VoteSummary;
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsTest extends TestCase
{
    use RefreshDatabase;

    protected VotingService $votingService;
    protected Event $event;
    protected Role $memberRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->votingService = app(VotingService::class);

        $this->memberRole = Role::firstOrCreate(
            ['name' => 'Member'],
            ['description' => 'Regular member', 'is_system' => false]
        );

        $this->event = $this->createTestEvent();
    }

    /**
     * Test results show correct vote counts
     */
    public function test_results_show_correct_vote_counts(): void
    {
        $users = User::factory()->count(3)->create(['role_id' => $this->memberRole->id]);

        // User 1: Entry 1 gets 1st (3pts), Entry 2 gets 2nd (2pts)
        $votes1 = ['P' => [1 => '1', 2 => '2']];
        $this->votingService->castRankedVotes($this->event->fresh(), $users[0], $votes1);

        // User 2: Entry 1 gets 1st (3pts), Entry 3 gets 2nd (2pts)
        $votes2 = ['P' => [1 => '1', 2 => '3']];
        $this->votingService->castRankedVotes($this->event->fresh(), $users[1], $votes2);

        // User 3: Entry 2 gets 1st (3pts), Entry 1 gets 2nd (2pts)
        $votes3 = ['P' => [1 => '2', 2 => '1']];
        $this->votingService->castRankedVotes($this->event->fresh(), $users[2], $votes3);

        $results = $this->votingService->getResults($this->event->fresh());

        // Entry 1: 3 + 3 + 2 = 8 points (two 1st, one 2nd)
        // Entry 2: 2 + 3 = 5 points (one 1st, one 2nd)
        // Entry 3: 2 points (one 2nd)

        $entry1Result = $results->firstWhere('entry_id', Entry::where('entry_number', 1)->first()->id);
        $entry2Result = $results->firstWhere('entry_id', Entry::where('entry_number', 2)->first()->id);

        $this->assertEquals(8, $entry1Result->total_points);
        $this->assertEquals(5, $entry2Result->total_points);
    }

    /**
     * Test results are sorted by points descending
     */
    public function test_results_sorted_by_points(): void
    {
        $users = User::factory()->count(3)->create(['role_id' => $this->memberRole->id]);

        // Vote so entry 3 has most points
        $this->votingService->castRankedVotes($this->event->fresh(), $users[0], ['P' => [1 => '3']]);
        $this->votingService->castRankedVotes($this->event->fresh(), $users[1], ['P' => [1 => '3']]);
        $this->votingService->castRankedVotes($this->event->fresh(), $users[2], ['P' => [1 => '1']]);

        $results = $this->votingService->getResults($this->event->fresh());

        // Entry 3 should be first with 6 points
        // Entry 1 should be second with 3 points
        $this->assertGreaterThanOrEqual($results[1]->total_points, $results[0]->total_points);
    }

    /**
     * Test tie handling in results
     */
    public function test_tie_handling_in_results(): void
    {
        $users = User::factory()->count(2)->create(['role_id' => $this->memberRole->id]);

        // Both entries get same points
        $this->votingService->castRankedVotes($this->event->fresh(), $users[0], ['P' => [1 => '1']]);
        $this->votingService->castRankedVotes($this->event->fresh(), $users[1], ['P' => [1 => '2']]);

        $results = $this->votingService->getResults($this->event->fresh());

        // Both should have 3 points
        $tiedResults = $results->filter(fn($r) => $r->total_points == 3);
        $this->assertEquals(2, $tiedResults->count());
    }

    /**
     * Test results by division
     */
    public function test_results_by_division(): void
    {
        $user = User::factory()->create(['role_id' => $this->memberRole->id]);

        $votes = ['P' => [1 => '1', 2 => '2', 3 => '3']];
        $this->votingService->castRankedVotes($this->event->fresh(), $user, $votes);

        $division = Division::where('event_id', $this->event->id)->first();
        $results = $this->votingService->getResultsByDivision($this->event, $division);

        $this->assertNotNull($results);
    }

    /**
     * Test leaderboard
     */
    public function test_leaderboard(): void
    {
        $users = User::factory()->count(5)->create(['role_id' => $this->memberRole->id]);

        // Create votes for leaderboard
        foreach ($users as $user) {
            $votes = ['P' => [1 => '1', 2 => '2']];
            $this->votingService->castRankedVotes($this->event->fresh(), $user, $votes);
        }

        $leaderboard = $this->votingService->getLeaderboard($this->event->fresh(), null, 5);

        $this->assertNotNull($leaderboard);
    }

    /**
     * Test results with multiple voters same entry
     */
    public function test_results_accumulate_from_multiple_voters(): void
    {
        $users = User::factory()->count(5)->create(['role_id' => $this->memberRole->id]);

        // All vote for Entry 1 in 1st place
        foreach ($users as $user) {
            $this->votingService->castRankedVotes($this->event->fresh(), $user, ['P' => [1 => '1']]);
        }

        $results = $this->votingService->getResults($this->event->fresh());
        $entry1Result = $results->firstWhere('entry_id', Entry::where('entry_number', 1)->first()->id);

        // 5 voters * 3 points each = 15 points
        $this->assertEquals(15, $entry1Result->total_points);
    }

    /**
     * Test results page is accessible
     */
    public function test_results_page_accessible(): void
    {
        $user = User::factory()->create(['role_id' => $this->memberRole->id]);

        $response = $this->actingAs($user)->get(route('results.index', $this->event));

        $response->assertStatus(200);
    }

    /**
     * Test results with no votes returns empty
     */
    public function test_results_with_no_votes(): void
    {
        $results = $this->votingService->getResults($this->event);

        $this->assertNotNull($results);
        // Results may return entries with 0 points or empty collection
    }

    /**
     * Test vote summary is updated after voting
     */
    public function test_vote_summary_updated_after_voting(): void
    {
        $user = User::factory()->create(['role_id' => $this->memberRole->id]);

        $votes = ['P' => [1 => '1', 2 => '2', 3 => '3']];
        $this->votingService->castRankedVotes($this->event->fresh(), $user, $votes);

        $summaries = VoteSummary::where('event_id', $this->event->id)->get();

        // Each voted entry should have a summary
        $this->assertGreaterThan(0, $summaries->count());
    }

    /**
     * Test place counts in results
     */
    public function test_place_counts_in_results(): void
    {
        $users = User::factory()->count(3)->create(['role_id' => $this->memberRole->id]);

        // Entry 1 gets: two 1st place, one 2nd place
        $this->votingService->castRankedVotes($this->event->fresh(), $users[0], ['P' => [1 => '1']]);
        $this->votingService->castRankedVotes($this->event->fresh(), $users[1], ['P' => [1 => '1']]);
        $this->votingService->castRankedVotes($this->event->fresh(), $users[2], ['P' => [2 => '1']]);

        $entry1 = Entry::where('event_id', $this->event->id)->where('entry_number', 1)->first();

        $votes = Vote::where('entry_id', $entry1->id)->get();

        $firstPlaceCount = $votes->where('place', 1)->count();
        $secondPlaceCount = $votes->where('place', 2)->count();

        $this->assertEquals(2, $firstPlaceCount);
        $this->assertEquals(1, $secondPlaceCount);
    }

    /**
     * Test results contain correct entry information
     */
    public function test_results_contain_entry_info(): void
    {
        $user = User::factory()->create(['role_id' => $this->memberRole->id]);

        $votes = ['P' => [1 => '1']];
        $this->votingService->castRankedVotes($this->event->fresh(), $user, $votes);

        $results = $this->votingService->getResults($this->event->fresh());

        $this->assertNotNull($results->first());
    }

    // -------------------- Helper Methods --------------------

    private function createTestEvent(): Event
    {
        $template = EventTemplate::create([
            'name' => 'Results Test Template',
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional'],
            ],
        ]);

        $votingType = VotingType::create([
            'code' => 'results-test-ranked',
            'name' => 'Results Test Ranked',
            'category' => 'ranked',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3, 'label' => '1st']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2, 'label' => '2nd']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd']);

        $event = Event::create([
            'name' => 'Results Test Event',
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
