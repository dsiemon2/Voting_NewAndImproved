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
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $memberRole = Role::firstOrCreate(
            ['name' => 'Member'],
            ['description' => 'Regular member', 'is_system' => false]
        );

        $this->user = User::factory()->create([
            'role_id' => $memberRole->id,
        ]);

        $this->event = $this->createTestEvent();
    }

    /**
     * Test voting API endpoint
     */
    public function test_cast_vote_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/vote", [
                'votes' => [
                    'P' => [1 => 1, 2 => 2],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test voting API requires authentication
     */
    public function test_cast_vote_requires_authentication(): void
    {
        $response = $this->postJson("/api/voting/{$this->event->id}/vote", [
            'votes' => ['P' => [1 => 1]],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test has voted API endpoint
     */
    public function test_has_voted_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/voting/{$this->event->id}/has-voted");

        $response->assertStatus(200)
            ->assertJson(['has_voted' => false]);
    }

    /**
     * Test has voted returns true after voting
     */
    public function test_has_voted_returns_true_after_voting(): void
    {
        // Cast a vote
        Vote::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'entry_id' => Entry::where('event_id', $this->event->id)->first()->id,
            'place' => 1,
            'base_points' => 3,
            'weight_multiplier' => 1.0,
            'final_points' => 3,
            'voter_ip' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/voting/{$this->event->id}/has-voted");

        $response->assertStatus(200)
            ->assertJson(['has_voted' => true]);
    }

    /**
     * Test my votes API endpoint
     */
    public function test_my_votes_api_endpoint(): void
    {
        $entry = Entry::where('event_id', $this->event->id)->first();

        Vote::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'entry_id' => $entry->id,
            'place' => 1,
            'base_points' => 3,
            'weight_multiplier' => 1.0,
            'final_points' => 3,
            'voter_ip' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/voting/{$this->event->id}/my-votes");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /**
     * Test validate vote API endpoint
     */
    public function test_validate_vote_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/validate", [
                'votes' => [
                    'P' => [1 => 1, 2 => 2],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => true]);
    }

    /**
     * Test validate vote with invalid entry
     */
    public function test_validate_vote_with_invalid_entry(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/validate", [
                'votes' => [
                    'P' => [1 => 999],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }

    /**
     * Test results API endpoint
     */
    public function test_results_api_endpoint(): void
    {
        // Add some votes
        $entries = Entry::where('event_id', $this->event->id)->take(2)->get();

        Vote::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'entry_id' => $entries[0]->id,
            'division_id' => $entries[0]->division_id,
            'place' => 1,
            'base_points' => 3,
            'weight_multiplier' => 1.0,
            'final_points' => 3,
            'voter_ip' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/results/{$this->event->id}");

        $response->assertStatus(200);
    }

    /**
     * Test event API endpoint
     */
    public function test_event_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/events/{$this->event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'is_active',
            ]);
    }

    /**
     * Test events list API endpoint
     */
    public function test_events_list_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/events');

        $response->assertStatus(200);
    }

    /**
     * Test duplicate vote is rejected via API
     */
    public function test_duplicate_vote_rejected_via_api(): void
    {
        // First vote
        $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/vote", [
                'votes' => ['P' => [1 => 1]],
            ]);

        // Second vote should fail
        $response = $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/vote", [
                'votes' => ['P' => [1 => 2]],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test API returns JSON response
     */
    public function test_api_returns_json(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/events/{$this->event->id}");

        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'application/json')
        );
    }

    /**
     * Test invalid event ID returns 404
     */
    public function test_invalid_event_returns_404(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/events/99999');

        $response->assertStatus(404);
    }

    /**
     * Test vote validation requires votes array
     */
    public function test_vote_validation_requires_votes_array(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/voting/{$this->event->id}/vote", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['votes']);
    }

    // -------------------- Helper Methods --------------------

    private function createTestEvent(): Event
    {
        $template = EventTemplate::create([
            'name' => 'API Test Template',
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional'],
            ],
        ]);

        $votingType = VotingType::create([
            'code' => 'api-test-ranked',
            'name' => 'API Test Ranked',
            'category' => 'ranked',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3, 'label' => '1st']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2, 'label' => '2nd']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd']);

        $event = Event::create([
            'name' => 'API Test Event',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

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
}
