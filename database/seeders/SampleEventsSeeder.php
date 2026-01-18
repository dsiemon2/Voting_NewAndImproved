<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\VotingType;
use App\Models\State;
use App\Models\Division;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\User;

class SampleEventsSeeder extends Seeder
{
    public function run(): void
    {
        $foodTemplate = EventTemplate::where('name', 'Food Competition')->first();
        $votingType = VotingType::where('code', 'ranked_321')->first();
        $pennsylvania = State::where('code', 'PA')->first();
        $adminUser = User::first();

        if (!$foodTemplate || !$votingType || !$pennsylvania) {
            $this->command->info('Required data not found. Please run DatabaseSeeder first.');
            return;
        }

        // Create Soup Cookoff 2025
        $soupCookoff = $this->createSoupCookoff($foodTemplate, $votingType, $pennsylvania, $adminUser);

        // Create Great Bakeoff 2025
        $greatBakeoff = $this->createGreatBakeoff($foodTemplate, $votingType, $pennsylvania, $adminUser);

        $this->command->info('Sample events created successfully!');
    }

    private function createSoupCookoff($template, $votingType, $state, $user): Event
    {
        $event = Event::firstOrCreate(
            ['name' => 'The Soup Cookoff 2025'],
            [
                'event_template_id' => $template->id,
                'voting_type_id' => $votingType->id,
                'description' => 'The Famous Soup Cookoff 2025 - Compete for the best soup!',
                'event_date' => '2025-11-30',
                'location' => 'Best Western - Harrisburg, PA',
                'state_id' => $state->id,
                'is_active' => true,
                'is_public' => true,
                'created_by' => $user?->id,
            ]
        );

        // Create divisions
        $proDivision = Division::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'P1'],
            [
                'name' => 'Professional',
                'description' => 'Professional chefs and restaurants',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $amateurDivision = Division::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'A1'],
            [
                'name' => 'Amateur',
                'description' => 'Home cooks and hobbyists',
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        // Create Professional Participants/Entries
        $proParticipants = [
            ['name' => 'Harvest Seasonal Grill', 'entry' => 'Butternut Squash Bisque'],
            ['name' => 'The Hershey Pantry', 'entry' => 'Tomato Basil Soup'],
            ['name' => "O'Reilly's Taproom & Kitchen", 'entry' => 'Irish Potato Soup'],
            ['name' => "Ted's Bar & Grill", 'entry' => 'French Onion Soup'],
            ['name' => 'Euro Bites', 'entry' => 'Hungarian Goulash'],
            ['name' => 'Spikefish Catering Co.', 'entry' => 'Seafood Chowder'],
            ['name' => "Local's Market Dillsburg", 'entry' => 'Chicken Noodle Soup'],
            ['name' => 'Rookies Craft Burger Bar', 'entry' => 'Loaded Baked Potato Soup'],
        ];

        $entryNum = 1;
        foreach ($proParticipants as $data) {
            $participant = Participant::firstOrCreate(
                ['event_id' => $event->id, 'name' => $data['name'], 'division_id' => $proDivision->id],
                [
                    'organization' => $data['name'],
                    'is_active' => true,
                ]
            );

            Entry::firstOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id, 'name' => $data['entry']],
                [
                    'division_id' => $proDivision->id,
                    'entry_number' => 'P' . str_pad($entryNum++, 2, '0', STR_PAD_LEFT),
                    'description' => 'Delicious ' . $data['entry'] . ' by ' . $data['name'],
                    'is_active' => true,
                ]
            );
        }

        // Create Amateur Participants/Entries
        $amateurParticipants = [
            ['name' => 'Doris Deardorff', 'entry' => 'Grandma\'s Chicken Soup'],
            ['name' => 'Sebastian Allen', 'entry' => 'Spicy Tortilla Soup'],
            ['name' => 'Molly Dykins', 'entry' => 'Creamy Mushroom Soup'],
            ['name' => 'Steven Grazier', 'entry' => 'Beef Stew'],
            ['name' => 'Natalie Boggs', 'entry' => 'Minestrone'],
            ['name' => 'Josephine Harris', 'entry' => 'Split Pea Soup'],
            ['name' => 'Gaye Davis', 'entry' => 'Broccoli Cheddar'],
            ['name' => 'Stacy Prazenica', 'entry' => 'Corn Chowder'],
            ['name' => 'Erin Hocker', 'entry' => 'Lentil Soup'],
            ['name' => 'Darryl Denson', 'entry' => 'Clam Chowder'],
        ];

        $entryNum = 1;
        foreach ($amateurParticipants as $data) {
            $participant = Participant::firstOrCreate(
                ['event_id' => $event->id, 'name' => $data['name'], 'division_id' => $amateurDivision->id],
                [
                    'is_active' => true,
                ]
            );

            Entry::firstOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id, 'name' => $data['entry']],
                [
                    'division_id' => $amateurDivision->id,
                    'entry_number' => 'A' . str_pad($entryNum++, 2, '0', STR_PAD_LEFT),
                    'description' => $data['entry'] . ' prepared by ' . $data['name'],
                    'is_active' => true,
                ]
            );
        }

        return $event;
    }

    private function createGreatBakeoff($template, $votingType, $state, $user): Event
    {
        $event = Event::firstOrCreate(
            ['name' => 'The Great Bakeoff 2025'],
            [
                'event_template_id' => $template->id,
                'voting_type_id' => $votingType->id,
                'description' => 'The Great Bakeoff 2025 - Show off your baking skills!',
                'event_date' => '2025-12-15',
                'location' => 'Best Western - Harrisburg, PA',
                'state_id' => $state->id,
                'is_active' => true,
                'is_public' => true,
                'created_by' => $user?->id,
            ]
        );

        // Create divisions
        $cakesDivision = Division::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'CAKE'],
            [
                'name' => 'Cakes',
                'description' => 'Layer cakes, bundt cakes, and specialty cakes',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $piesDivision = Division::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'PIE'],
            [
                'name' => 'Pies',
                'description' => 'Fruit pies, cream pies, and specialty pies',
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        $cookiesDivision = Division::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'COOKIE'],
            [
                'name' => 'Cookies & Bars',
                'description' => 'Cookies, brownies, and bar desserts',
                'display_order' => 3,
                'is_active' => true,
            ]
        );

        // Cakes entries
        $cakesEntries = [
            ['name' => 'Laura Faust', 'entry' => 'Red Velvet Layer Cake'],
            ['name' => 'Christine Waters', 'entry' => 'German Chocolate Cake'],
            ['name' => 'Fred Stewart', 'entry' => 'Carrot Cake with Cream Cheese'],
            ['name' => 'Kathleen Matoska', 'entry' => 'Lemon Pound Cake'],
        ];

        $entryNum = 1;
        foreach ($cakesEntries as $data) {
            $participant = Participant::firstOrCreate(
                ['event_id' => $event->id, 'name' => $data['name'], 'division_id' => $cakesDivision->id],
                ['is_active' => true]
            );

            Entry::firstOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id, 'name' => $data['entry']],
                [
                    'division_id' => $cakesDivision->id,
                    'entry_number' => 'C' . str_pad($entryNum++, 2, '0', STR_PAD_LEFT),
                    'description' => $data['entry'] . ' by ' . $data['name'],
                    'is_active' => true,
                ]
            );
        }

        // Pies entries
        $piesEntries = [
            ['name' => 'Matthew Risko', 'entry' => 'Apple Crumb Pie'],
            ['name' => 'Chris Yellowdy', 'entry' => 'Pecan Pie'],
            ['name' => 'Mark Seibert', 'entry' => 'Cherry Pie'],
            ['name' => 'Chris Brett', 'entry' => 'Pumpkin Pie'],
        ];

        $entryNum = 1;
        foreach ($piesEntries as $data) {
            $participant = Participant::firstOrCreate(
                ['event_id' => $event->id, 'name' => $data['name'], 'division_id' => $piesDivision->id],
                ['is_active' => true]
            );

            Entry::firstOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id, 'name' => $data['entry']],
                [
                    'division_id' => $piesDivision->id,
                    'entry_number' => 'P' . str_pad($entryNum++, 2, '0', STR_PAD_LEFT),
                    'description' => $data['entry'] . ' by ' . $data['name'],
                    'is_active' => true,
                ]
            );
        }

        // Cookies entries
        $cookiesEntries = [
            ['name' => 'Steve Gilbert', 'entry' => 'Double Chocolate Chip Cookies'],
            ['name' => 'Jacob Kreider', 'entry' => 'Peanut Butter Blossoms'],
            ['name' => 'Randy Allen', 'entry' => 'Snickerdoodles'],
            ['name' => 'Diane Allen', 'entry' => 'Lemon Bars'],
        ];

        $entryNum = 1;
        foreach ($cookiesEntries as $data) {
            $participant = Participant::firstOrCreate(
                ['event_id' => $event->id, 'name' => $data['name'], 'division_id' => $cookiesDivision->id],
                ['is_active' => true]
            );

            Entry::firstOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id, 'name' => $data['entry']],
                [
                    'division_id' => $cookiesDivision->id,
                    'entry_number' => 'K' . str_pad($entryNum++, 2, '0', STR_PAD_LEFT),
                    'description' => $data['entry'] . ' by ' . $data['name'],
                    'is_active' => true,
                ]
            );
        }

        return $event;
    }
}
