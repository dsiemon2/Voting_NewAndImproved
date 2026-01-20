<?php

namespace Tests\Unit;

use App\Imports\EventDataImport;
use App\Models\Division;
use App\Models\Entry;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Participant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Full system access', 'is_system' => true]
        );

        $template = EventTemplate::create([
            'name' => 'Import Test Template',
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional'],
                ['code' => 'A', 'name' => 'Amateur'],
            ],
        ]);

        $this->event = Event::create([
            'name' => 'Import Test Event',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test import can create divisions
     */
    public function test_import_creates_divisions(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => 'P2', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);

        $this->assertEquals(2, Division::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import creates participants
     */
    public function test_import_creates_participants(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => 'P2', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);

        $this->assertEquals(2, Participant::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import creates entries
     */
    public function test_import_creates_entries(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1', 'entry2' => 'Dish 2']),
            collect(['division' => 'P2', 'participant' => 'Chef Jane', 'entry1' => 'Dish 3']),
        ]);

        $importer->collection($rows);

        $this->assertEquals(3, Entry::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import returns correct stats
     */
    public function test_import_returns_stats(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => 'A1', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);
        $stats = $importer->getStats();

        $this->assertEquals(2, $stats['divisions']);
        $this->assertEquals(2, $stats['participants']);
        $this->assertEquals(2, $stats['entries']);
        $this->assertEmpty($stats['errors']);
    }

    /**
     * Test import skips empty rows
     */
    public function test_import_skips_empty_rows(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => '', 'participant' => '', 'entry1' => '']),
            collect(['division' => 'P2', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);

        $this->assertEquals(2, Participant::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import determines type from division code
     */
    public function test_import_determines_type_from_code(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Pro Chef', 'entry1' => 'Pro Dish']),
            collect(['division' => 'A1', 'participant' => 'Amateur', 'entry1' => 'Home Dish']),
        ]);

        $importer->collection($rows);

        $proDivision = Division::where('event_id', $this->event->id)->where('code', 'P1')->first();
        $amateurDivision = Division::where('event_id', $this->event->id)->where('code', 'A1')->first();

        $this->assertEquals('Professional', $proDivision->type);
        $this->assertEquals('Amateur', $amateurDivision->type);
    }

    /**
     * Test import does not duplicate existing divisions
     */
    public function test_import_does_not_duplicate_divisions(): void
    {
        // Create existing division
        Division::create([
            'event_id' => $this->event->id,
            'code' => 'P1',
            'name' => 'Professional 1',
            'type' => 'Professional',
        ]);

        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => 'P1', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);

        // Should still have only 1 division (the existing one)
        $this->assertEquals(1, Division::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import assigns sequential entry numbers
     */
    public function test_import_assigns_sequential_entry_numbers(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef 1', 'entry1' => 'Dish A']),
            collect(['division' => 'P2', 'participant' => 'Chef 2', 'entry1' => 'Dish B']),
            collect(['division' => 'P3', 'participant' => 'Chef 3', 'entry1' => 'Dish C']),
        ]);

        $importer->collection($rows);

        $entryNumbers = Entry::where('event_id', $this->event->id)
            ->orderBy('entry_number')
            ->pluck('entry_number')
            ->toArray();

        $this->assertEquals([1, 2, 3], $entryNumbers);
    }

    /**
     * Test import divisions-only mode
     */
    public function test_import_divisions_only_mode(): void
    {
        $importer = new EventDataImport($this->event, 'divisions');

        $rows = new Collection([
            collect(['division' => 'P1', 'participant' => 'Chef John', 'entry1' => 'Dish 1']),
            collect(['division' => 'P2', 'participant' => 'Chef Jane', 'entry1' => 'Dish 2']),
        ]);

        $importer->collection($rows);

        $this->assertEquals(2, Division::where('event_id', $this->event->id)->count());
        $this->assertEquals(0, Participant::where('event_id', $this->event->id)->count());
        $this->assertEquals(0, Entry::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import handles multiple entries per row
     */
    public function test_import_handles_multiple_entries(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect([
                'division' => 'P1',
                'participant' => 'Chef Multi',
                'entry1' => 'Dish A',
                'entry2' => 'Dish B',
                'entry3' => 'Dish C',
            ]),
        ]);

        $importer->collection($rows);

        $this->assertEquals(3, Entry::where('event_id', $this->event->id)->count());
    }

    /**
     * Test import handles alternative column names
     */
    public function test_import_handles_alternative_column_names(): void
    {
        $importer = new EventDataImport($this->event, 'combined');

        $rows = new Collection([
            collect(['division' => 'P1', 'chef' => 'Chef Name', 'entry1' => 'Dish 1']),
        ]);

        $importer->collection($rows);

        $participant = Participant::where('event_id', $this->event->id)->first();
        $this->assertEquals('Chef Name', $participant->name);
    }
}
