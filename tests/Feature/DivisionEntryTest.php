<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Entry;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Participant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Event $event;
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
            'name' => 'Test Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Amateur'],
            ],
        ]);

        $this->event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $this->template->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test division can be created
     */
    public function test_division_can_be_created(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'code' => 'P1',
            'type' => 'Professional',
        ]);
    }

    /**
     * Test division belongs to event
     */
    public function test_division_belongs_to_event(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        $this->assertEquals($this->event->id, $division->event->id);
    }

    /**
     * Test entry can be created
     */
    public function test_entry_can_be_created(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Test Entry',
            'entry_number' => 1,
        ]);

        $this->assertDatabaseHas('entries', [
            'id' => $entry->id,
            'name' => 'Test Entry',
            'entry_number' => 1,
        ]);
    }

    /**
     * Test entry belongs to division
     */
    public function test_entry_belongs_to_division(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Test Entry',
            'entry_number' => 1,
        ]);

        $this->assertEquals($division->id, $entry->division->id);
    }

    /**
     * Test entry belongs to participant
     */
    public function test_entry_belongs_to_participant(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Test Entry',
            'entry_number' => 1,
        ]);

        $this->assertEquals($participant->id, $entry->participant->id);
    }

    /**
     * Test division has many entries
     */
    public function test_division_has_many_entries(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 1',
            'entry_number' => 1,
        ]);

        Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 2',
            'entry_number' => 2,
        ]);

        $this->assertEquals(2, $division->entries()->count());
    }

    /**
     * Test participant has many entries
     */
    public function test_participant_has_many_entries(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 1',
            'entry_number' => 1,
        ]);

        Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 2',
            'entry_number' => 2,
        ]);

        $this->assertEquals(2, $participant->entries()->count());
    }

    /**
     * Test event has many divisions
     */
    public function test_event_has_many_divisions(): void
    {
        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        Division::create([
            'event_id' => $this->event->id,
            'code' => 'A1',
            'name' => 'Amateur 1',
            'type' => 'Amateur',
        ]);

        $this->assertEquals(2, $this->event->divisions()->count());
    }

    /**
     * Test event has many entries
     */
    public function test_event_has_many_entries(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Entry 1',
            'entry_number' => 1,
        ]);

        $this->assertEquals(1, $this->event->entries()->count());
    }

    /**
     * Test division can be soft deleted
     */
    public function test_division_can_be_updated(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Original Name',
        ]);

        $division->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test entry can be updated
     */
    public function test_entry_can_be_updated(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Original Entry',
            'entry_number' => 1,
        ]);

        $entry->update(['name' => 'Updated Entry']);

        $this->assertDatabaseHas('entries', [
            'id' => $entry->id,
            'name' => 'Updated Entry',
        ]);
    }

    /**
     * Test professional entry numbering convention
     */
    public function test_professional_entry_numbering(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        // Professional entries: 1-99
        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Pro Entry',
            'entry_number' => 13,
        ]);

        $this->assertEquals(13, $entry->entry_number);
        $this->assertTrue($entry->entry_number < 100);
    }

    /**
     * Test amateur entry numbering convention
     */
    public function test_amateur_entry_numbering(): void
    {
        $division = Division::create([
            'event_id' => $this->event->id,
            'code' => 'A1',
            'name' => 'Amateur 1',
            'type' => 'Amateur',
        ]);

        $participant = Participant::create([
            'name' => 'Test Chef',
            'event_id' => $this->event->id,
        ]);

        // Amateur entries: 101-199
        $entry = Entry::create([
            'event_id' => $this->event->id,
            'division_id' => $division->id,
            'participant_id' => $participant->id,
            'name' => 'Amateur Entry',
            'entry_number' => 113,
        ]);

        $this->assertEquals(113, $entry->entry_number);
        $this->assertTrue($entry->entry_number >= 101 && $entry->entry_number < 200);
    }

    /**
     * Test division active scope
     */
    public function test_division_active_scope(): void
    {
        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Active Division',
            'is_active' => true,
        ]);

        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P2',
            'name' => 'Inactive Division',
            'is_active' => false,
        ]);

        $activeDivisions = $this->event->divisions()->where('is_active', true)->get();

        $this->assertEquals(1, $activeDivisions->count());
        $this->assertEquals('Active Division', $activeDivisions->first()->name);
    }

    /**
     * Test participant can be created
     */
    public function test_participant_can_be_created(): void
    {
        $participant = Participant::create([
            'name' => 'John Doe',
            'event_id' => $this->event->id,
            'email' => 'john@example.com',
            'phone' => '555-1234',
        ]);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test participant belongs to event
     */
    public function test_participant_belongs_to_event(): void
    {
        $participant = Participant::create([
            'name' => 'John Doe',
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals($this->event->id, $participant->event->id);
    }

    /**
     * Test event has many participants
     */
    public function test_event_has_many_participants(): void
    {
        Participant::create(['name' => 'Participant 1', 'event_id' => $this->event->id]);
        Participant::create(['name' => 'Participant 2', 'event_id' => $this->event->id]);
        Participant::create(['name' => 'Participant 3', 'event_id' => $this->event->id]);

        $this->assertEquals(3, $this->event->participants()->count());
    }

    /**
     * Test division display order
     */
    public function test_division_display_order(): void
    {
        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P3',
            'name' => 'Third',
            'display_order' => 3,
        ]);

        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'First',
            'display_order' => 1,
        ]);

        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P2',
            'name' => 'Second',
            'display_order' => 2,
        ]);

        $orderedDivisions = $this->event->divisions()->orderBy('display_order')->get();

        $this->assertEquals('First', $orderedDivisions[0]->name);
        $this->assertEquals('Second', $orderedDivisions[1]->name);
        $this->assertEquals('Third', $orderedDivisions[2]->name);
    }
}
