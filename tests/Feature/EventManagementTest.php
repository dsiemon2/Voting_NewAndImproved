<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Division;
use App\Models\VotingType;
use App\Models\EventVotingConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected EventTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Full system access', 'is_system' => true]
        );

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->template = EventTemplate::create([
            'name' => 'Test Food Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro chefs'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Home cooks'],
            ],
        ]);
    }

    /**
     * Test event can be created
     */
    public function test_event_can_be_created(): void
    {
        $event = Event::create([
            'name' => 'Test Cookoff',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Test Cookoff',
            'is_active' => true,
        ]);
    }

    /**
     * Test event can be updated
     */
    public function test_event_can_be_updated(): void
    {
        $event = Event::create([
            'name' => 'Original Name',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $event->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test event can be soft deleted
     */
    public function test_event_can_be_soft_deleted(): void
    {
        $event = Event::create([
            'name' => 'To Be Deleted',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $event->delete();

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    /**
     * Test event voting is open when no time constraints
     */
    public function test_voting_is_open_when_no_time_constraints(): void
    {
        $event = Event::create([
            'name' => 'Open Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'voting_starts_at' => null,
            'voting_ends_at' => null,
        ]);

        $this->assertTrue($event->isVotingOpen());
    }

    /**
     * Test voting is closed when inactive
     */
    public function test_voting_is_closed_when_inactive(): void
    {
        $event = Event::create([
            'name' => 'Inactive Event',
            'event_template_id' => $this->template->id,
            'is_active' => false,
        ]);

        $this->assertFalse($event->isVotingOpen());
    }

    /**
     * Test voting is closed before start time
     */
    public function test_voting_is_closed_before_start_time(): void
    {
        $event = Event::create([
            'name' => 'Future Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'voting_starts_at' => Carbon::now()->addDay(),
            'voting_ends_at' => Carbon::now()->addDays(2),
        ]);

        $this->assertFalse($event->isVotingOpen());
        $this->assertEquals('scheduled', $event->getVotingStatus());
    }

    /**
     * Test voting is open during voting period
     */
    public function test_voting_is_open_during_voting_period(): void
    {
        $event = Event::create([
            'name' => 'Active Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'voting_starts_at' => Carbon::now()->subHour(),
            'voting_ends_at' => Carbon::now()->addHour(),
        ]);

        $this->assertTrue($event->isVotingOpen());
        $this->assertEquals('open', $event->getVotingStatus());
    }

    /**
     * Test voting is closed after end time
     */
    public function test_voting_is_closed_after_end_time(): void
    {
        $event = Event::create([
            'name' => 'Past Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'voting_starts_at' => Carbon::now()->subDays(2),
            'voting_ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($event->isVotingOpen());
        $this->assertEquals('ended', $event->getVotingStatus());
    }

    /**
     * Test event has voting schedule
     */
    public function test_event_has_voting_schedule(): void
    {
        $eventWithSchedule = Event::create([
            'name' => 'Scheduled Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'voting_starts_at' => Carbon::now(),
            'voting_ends_at' => Carbon::now()->addDay(),
        ]);

        $eventWithoutSchedule = Event::create([
            'name' => 'Unscheduled Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $this->assertTrue($eventWithSchedule->hasVotingSchedule());
        $this->assertFalse($eventWithoutSchedule->hasVotingSchedule());
    }

    /**
     * Test event can be duplicated
     */
    public function test_event_can_be_duplicated(): void
    {
        $this->actingAs($this->adminUser);

        $originalEvent = Event::create([
            'name' => 'Original Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'description' => 'Test description',
        ]);

        // Add divisions to original
        Division::create([
            'event_id' => $originalEvent->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        $duplicatedEvent = $originalEvent->duplicate('Duplicated Event');

        $this->assertDatabaseHas('events', [
            'id' => $duplicatedEvent->id,
            'name' => 'Duplicated Event',
            'is_active' => false, // Starts as inactive
        ]);

        // Check divisions were copied
        $this->assertEquals(1, $duplicatedEvent->divisions()->count());
    }

    /**
     * Test event has template relationship
     */
    public function test_event_has_template_relationship(): void
    {
        $event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $this->assertNotNull($event->template);
        $this->assertEquals($this->template->id, $event->template->id);
    }

    /**
     * Test event participant label from template
     */
    public function test_event_uses_template_participant_label(): void
    {
        $event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $this->assertEquals('Chef', $event->participant_label);
    }

    /**
     * Test event entry label from template
     */
    public function test_event_uses_template_entry_label(): void
    {
        $event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $this->assertEquals('Entry', $event->entry_label);
    }

    /**
     * Test event scopes work correctly
     */
    public function test_active_scope_filters_correctly(): void
    {
        Event::create([
            'name' => 'Active Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Inactive Event',
            'event_template_id' => $this->template->id,
            'is_active' => false,
        ]);

        $activeEvents = Event::active()->get();

        $this->assertEquals(1, $activeEvents->count());
        $this->assertEquals('Active Event', $activeEvents->first()->name);
    }

    /**
     * Test public scope filters correctly
     */
    public function test_public_scope_filters_correctly(): void
    {
        Event::create([
            'name' => 'Public Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'is_public' => true,
        ]);

        Event::create([
            'name' => 'Private Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
            'is_public' => false,
        ]);

        $publicEvents = Event::public()->get();

        $this->assertEquals(1, $publicEvents->count());
        $this->assertEquals('Public Event', $publicEvents->first()->name);
    }

    /**
     * Test event with voting config
     */
    public function test_event_has_voting_config(): void
    {
        $votingType = VotingType::create([
            'code' => 'standard-ranked-test',
            'name' => 'Standard Ranked',
            'category' => 'ranked',
        ]);

        $event = Event::create([
            'name' => 'Event with Config',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
        ]);

        $event->refresh();

        $this->assertNotNull($event->votingConfig);
        $this->assertEquals($votingType->id, $event->votingConfig->voting_type_id);
    }

    /**
     * Test hasDivisions method
     */
    public function test_has_divisions_method(): void
    {
        $eventWithDivisions = Event::create([
            'name' => 'Event with Divisions',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        Division::create([
            'event_id' => $eventWithDivisions->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
            'is_active' => true,
        ]);

        $eventWithoutDivisions = Event::create([
            'name' => 'Event without Divisions',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);

        $this->assertTrue($eventWithDivisions->hasDivisions());
        $this->assertFalse($eventWithoutDivisions->hasDivisions());
    }
}
