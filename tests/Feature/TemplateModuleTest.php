<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Module;
use App\Models\EventModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

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
    }

    /**
     * Test template can be created
     */
    public function test_template_can_be_created(): void
    {
        $template = EventTemplate::create([
            'name' => 'Food Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional'],
                ['code' => 'A', 'name' => 'Amateur'],
            ],
        ]);

        $this->assertDatabaseHas('event_templates', [
            'id' => $template->id,
            'name' => 'Food Competition',
            'participant_label' => 'Chef',
        ]);
    }

    /**
     * Test template division types are stored correctly
     */
    public function test_template_division_types_stored(): void
    {
        $template = EventTemplate::create([
            'name' => 'Photo Contest',
            'participant_label' => 'Photographer',
            'entry_label' => 'Photo',
            'division_types' => [
                ['code' => 'N', 'name' => 'Nature', 'description' => 'Nature photos'],
                ['code' => 'P', 'name' => 'Portrait', 'description' => 'Portrait photos'],
                ['code' => 'S', 'name' => 'Street', 'description' => 'Street photos'],
            ],
        ]);

        $divisionTypes = $template->getDivisionTypes();

        $this->assertCount(3, $divisionTypes);
        $this->assertEquals('N', $divisionTypes[0]['code']);
        $this->assertEquals('Portrait', $divisionTypes[1]['name']);
    }

    /**
     * Test template has many events
     */
    public function test_template_has_many_events(): void
    {
        $template = EventTemplate::create([
            'name' => 'Test Template',
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        Event::create([
            'name' => 'Event 1',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Event 2',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        $this->assertEquals(2, $template->events()->count());
    }

    /**
     * Test module can be created
     */
    public function test_module_can_be_created(): void
    {
        $module = Module::firstOrCreate(
            ['code' => 'voting-test'],
            [
                'name' => 'Voting Test',
                'description' => 'Enable voting functionality',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $this->assertDatabaseHas('modules', [
            'code' => 'voting-test',
            'name' => 'Voting Test',
        ]);
    }

    /**
     * Test event can override template modules
     */
    public function test_event_module_override(): void
    {
        $template = EventTemplate::create([
            'name' => 'Test Template Override',
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        $module = Module::firstOrCreate(
            ['code' => 'judging-override'],
            [
                'name' => 'Judging Override',
                'description' => 'Judging functionality',
                'is_active' => true,
            ]
        );

        $event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        // Create module override
        EventModule::create([
            'event_id' => $event->id,
            'module_id' => $module->id,
            'is_enabled' => false,
            'custom_label' => 'Custom Judging Label',
        ]);

        $override = $event->moduleOverrides()->first();

        $this->assertNotNull($override);
        $this->assertFalse($override->is_enabled);
        $this->assertEquals('Custom Judging Label', $override->custom_label);
    }

    /**
     * Test template participant label accessor
     */
    public function test_template_participant_label(): void
    {
        $template = EventTemplate::create([
            'name' => 'Chef Competition',
            'participant_label' => 'Master Chef',
            'entry_label' => 'Dish',
        ]);

        $this->assertEquals('Master Chef', $template->participant_label);
    }

    /**
     * Test template entry label accessor
     */
    public function test_template_entry_label(): void
    {
        $template = EventTemplate::create([
            'name' => 'Art Contest',
            'participant_label' => 'Artist',
            'entry_label' => 'Artwork',
        ]);

        $this->assertEquals('Artwork', $template->entry_label);
    }

    /**
     * Test event inherits template labels
     */
    public function test_event_inherits_template_labels(): void
    {
        $template = EventTemplate::create([
            'name' => 'Singing Competition',
            'participant_label' => 'Singer',
            'entry_label' => 'Performance',
        ]);

        $event = Event::create([
            'name' => 'Talent Show',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        $this->assertEquals('Singer', $event->participant_label);
        $this->assertEquals('Performance', $event->entry_label);
    }

    /**
     * Test template can have default modules
     */
    public function test_template_default_modules(): void
    {
        static $moduleCounter = 0;
        $moduleCounter++;

        $template = EventTemplate::create([
            'name' => 'Test Template Modules ' . $moduleCounter,
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        $votingModule = Module::firstOrCreate(
            ['code' => 'voting-mod-' . $moduleCounter],
            ['name' => 'Voting ' . $moduleCounter, 'is_active' => true]
        );

        $resultsModule = Module::firstOrCreate(
            ['code' => 'results-mod-' . $moduleCounter],
            ['name' => 'Results ' . $moduleCounter, 'is_active' => true]
        );

        $template->modules()->attach($votingModule->id, ['is_enabled_by_default' => true]);
        $template->modules()->attach($resultsModule->id, ['is_enabled_by_default' => false]);

        $defaultModules = $template->modules()
            ->wherePivot('is_enabled_by_default', true)
            ->get();

        $this->assertEquals(1, $defaultModules->count());
        $this->assertStringStartsWith('Voting', $defaultModules->first()->name);
    }

    /**
     * Test division types for different templates
     */
    public function test_food_competition_division_types(): void
    {
        $template = EventTemplate::create([
            'name' => 'Food Competition',
            'participant_label' => 'Chef',
            'entry_label' => 'Entry',
            'division_types' => [
                ['code' => 'P', 'name' => 'Professional', 'description' => 'Pro chefs'],
                ['code' => 'A', 'name' => 'Amateur', 'description' => 'Home cooks'],
            ],
        ]);

        $types = $template->getDivisionTypes();

        $this->assertEquals('P', $types[0]['code']);
        $this->assertEquals('Professional', $types[0]['name']);
        $this->assertEquals('A', $types[1]['code']);
        $this->assertEquals('Amateur', $types[1]['name']);
    }

    /**
     * Test talent show division types
     */
    public function test_talent_show_division_types(): void
    {
        $template = EventTemplate::create([
            'name' => 'Talent Show',
            'participant_label' => 'Performer',
            'entry_label' => 'Act',
            'division_types' => [
                ['code' => 'V', 'name' => 'Vocal', 'description' => 'Singing'],
                ['code' => 'I', 'name' => 'Instrumental', 'description' => 'Musical instruments'],
                ['code' => 'D', 'name' => 'Dance', 'description' => 'Dancing'],
            ],
        ]);

        $types = $template->getDivisionTypes();

        $this->assertCount(3, $types);
        $this->assertEquals('Vocal', $types[0]['name']);
        $this->assertEquals('Instrumental', $types[1]['name']);
        $this->assertEquals('Dance', $types[2]['name']);
    }

    /**
     * Test module configuration storage
     */
    public function test_module_configuration_storage(): void
    {
        static $configCounter = 0;
        $configCounter++;

        $template = EventTemplate::create([
            'name' => 'Test Template Config ' . $configCounter,
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
        ]);

        $module = Module::firstOrCreate(
            ['code' => 'config-mod-' . $configCounter],
            ['name' => 'Configurable Module ' . $configCounter, 'is_active' => true]
        );

        $event = Event::create([
            'name' => 'Test Event',
            'event_template_id' => $template->id,
            'is_active' => true,
        ]);

        EventModule::create([
            'event_id' => $event->id,
            'module_id' => $module->id,
            'is_enabled' => true,
            'configuration' => [
                'setting1' => 'value1',
                'setting2' => true,
            ],
        ]);

        $override = $event->moduleOverrides()->first();

        $this->assertEquals('value1', $override->configuration['setting1']);
        $this->assertTrue($override->configuration['setting2']);
    }

    /**
     * Test admin can access templates management
     */
    public function test_admin_can_access_templates(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/templates');
        $response->assertStatus(200);
    }

    /**
     * Test template without division types
     */
    public function test_template_without_division_types(): void
    {
        $template = EventTemplate::create([
            'name' => 'Simple Event',
            'participant_label' => 'Participant',
            'entry_label' => 'Entry',
            'division_types' => null,
        ]);

        $types = $template->getDivisionTypes();

        $this->assertIsArray($types);
        $this->assertEmpty($types);
    }
}
