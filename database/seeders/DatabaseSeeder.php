<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use App\Models\EventTemplate;
use App\Models\State;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedVotingPlaceConfigs();
        $this->seedEventTemplates();
        $this->seedStates();

        // Call additional seeders
        $this->call([
            AiAgentSeeder::class,
            WebhookSeeder::class,
            AiToolSeeder::class,
            TwilioSettingsSeeder::class,
        ]);
    }

    private function seedAdminUser(): void
    {
        $adminRole = Role::where('name', 'Administrator')->first();

        if ($adminRole) {
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password'),
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedVotingPlaceConfigs(): void
    {
        // Get the voting types that were seeded in the migration
        $ranked321 = VotingType::where('code', 'ranked_321')->first();
        $ranked54321 = VotingType::where('code', 'ranked_54321')->first();
        $ranked531 = VotingType::where('code', 'ranked_531')->first();

        // Add place configs for Standard 3-2-1
        if ($ranked321) {
            $places = [
                ['place' => 1, 'points' => 3],
                ['place' => 2, 'points' => 2],
                ['place' => 3, 'points' => 1],
            ];
            foreach ($places as $placeData) {
                VotingPlaceConfig::firstOrCreate(
                    ['voting_type_id' => $ranked321->id, 'place' => $placeData['place']],
                    ['points' => $placeData['points']]
                );
            }
        }

        // Add place configs for Extended 5-4-3-2-1
        if ($ranked54321) {
            $places = [
                ['place' => 1, 'points' => 5],
                ['place' => 2, 'points' => 4],
                ['place' => 3, 'points' => 3],
                ['place' => 4, 'points' => 2],
                ['place' => 5, 'points' => 1],
            ];
            foreach ($places as $placeData) {
                VotingPlaceConfig::firstOrCreate(
                    ['voting_type_id' => $ranked54321->id, 'place' => $placeData['place']],
                    ['points' => $placeData['points']]
                );
            }
        }

        // Add place configs for Top-Heavy 5-3-1
        if ($ranked531) {
            $places = [
                ['place' => 1, 'points' => 5],
                ['place' => 2, 'points' => 3],
                ['place' => 3, 'points' => 1],
            ];
            foreach ($places as $placeData) {
                VotingPlaceConfig::firstOrCreate(
                    ['voting_type_id' => $ranked531->id, 'place' => $placeData['place']],
                    ['points' => $placeData['points']]
                );
            }
        }
    }

    private function seedEventTemplates(): void
    {
        $templates = [
            [
                'name' => 'Food Competition',
                'description' => 'Perfect for cook-offs, BBQ competitions, chili contests, and culinary events',
                'icon' => 'fa-utensils',
                'participant_label' => 'Chef',
                'entry_label' => 'Entry',
                'modules' => ['voting', 'results', 'divisions', 'participants', 'entries', 'import', 'pdf'],
            ],
            [
                'name' => 'Photo Contest',
                'description' => 'Ideal for photography competitions and visual arts',
                'icon' => 'fa-camera',
                'participant_label' => 'Photographer',
                'entry_label' => 'Photo',
                'modules' => ['voting', 'results', 'categories', 'participants', 'entries', 'judging'],
            ],
            [
                'name' => 'General Vote',
                'description' => 'Simple voting for any type of contest or election',
                'icon' => 'fa-check-square',
                'participant_label' => 'Nominee',
                'entry_label' => 'Entry',
                'modules' => ['voting', 'results', 'entries'],
            ],
            [
                'name' => 'Talent Show',
                'description' => 'For talent competitions and performance events',
                'icon' => 'fa-star',
                'participant_label' => 'Performer',
                'entry_label' => 'Performance',
                'modules' => ['voting', 'results', 'categories', 'participants', 'entries', 'judging', 'pdf'],
            ],
        ];

        foreach ($templates as $templateData) {
            $moduleCodes = $templateData['modules'];
            unset($templateData['modules']);

            $template = EventTemplate::firstOrCreate(
                ['name' => $templateData['name']],
                array_merge($templateData, ['is_active' => true])
            );

            // Attach modules using 'code' column
            $moduleIds = Module::whereIn('code', $moduleCodes)->pluck('id');
            $template->modules()->sync($moduleIds);
        }
    }

    private function seedStates(): void
    {
        $states = [
            ['name' => 'Alabama', 'code' => 'AL'],
            ['name' => 'Alaska', 'code' => 'AK'],
            ['name' => 'Arizona', 'code' => 'AZ'],
            ['name' => 'Arkansas', 'code' => 'AR'],
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'Colorado', 'code' => 'CO'],
            ['name' => 'Connecticut', 'code' => 'CT'],
            ['name' => 'Delaware', 'code' => 'DE'],
            ['name' => 'Florida', 'code' => 'FL'],
            ['name' => 'Georgia', 'code' => 'GA'],
            ['name' => 'Hawaii', 'code' => 'HI'],
            ['name' => 'Idaho', 'code' => 'ID'],
            ['name' => 'Illinois', 'code' => 'IL'],
            ['name' => 'Indiana', 'code' => 'IN'],
            ['name' => 'Iowa', 'code' => 'IA'],
            ['name' => 'Kansas', 'code' => 'KS'],
            ['name' => 'Kentucky', 'code' => 'KY'],
            ['name' => 'Louisiana', 'code' => 'LA'],
            ['name' => 'Maine', 'code' => 'ME'],
            ['name' => 'Maryland', 'code' => 'MD'],
            ['name' => 'Massachusetts', 'code' => 'MA'],
            ['name' => 'Michigan', 'code' => 'MI'],
            ['name' => 'Minnesota', 'code' => 'MN'],
            ['name' => 'Mississippi', 'code' => 'MS'],
            ['name' => 'Missouri', 'code' => 'MO'],
            ['name' => 'Montana', 'code' => 'MT'],
            ['name' => 'Nebraska', 'code' => 'NE'],
            ['name' => 'Nevada', 'code' => 'NV'],
            ['name' => 'New Hampshire', 'code' => 'NH'],
            ['name' => 'New Jersey', 'code' => 'NJ'],
            ['name' => 'New Mexico', 'code' => 'NM'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'North Carolina', 'code' => 'NC'],
            ['name' => 'North Dakota', 'code' => 'ND'],
            ['name' => 'Ohio', 'code' => 'OH'],
            ['name' => 'Oklahoma', 'code' => 'OK'],
            ['name' => 'Oregon', 'code' => 'OR'],
            ['name' => 'Pennsylvania', 'code' => 'PA'],
            ['name' => 'Rhode Island', 'code' => 'RI'],
            ['name' => 'South Carolina', 'code' => 'SC'],
            ['name' => 'South Dakota', 'code' => 'SD'],
            ['name' => 'Tennessee', 'code' => 'TN'],
            ['name' => 'Texas', 'code' => 'TX'],
            ['name' => 'Utah', 'code' => 'UT'],
            ['name' => 'Vermont', 'code' => 'VT'],
            ['name' => 'Virginia', 'code' => 'VA'],
            ['name' => 'Washington', 'code' => 'WA'],
            ['name' => 'West Virginia', 'code' => 'WV'],
            ['name' => 'Wisconsin', 'code' => 'WI'],
            ['name' => 'Wyoming', 'code' => 'WY'],
        ];

        foreach ($states as $state) {
            State::firstOrCreate(['code' => $state['code']], $state);
        }
    }
}
