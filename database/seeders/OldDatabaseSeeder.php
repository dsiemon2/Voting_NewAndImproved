<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Models\Division;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\EventVotingConfig;

class OldDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Import users from old database
        $this->importUsers();

        // 2. Update existing events with proper sample data
        $this->setupSoupCookoff();
        $this->setupGreatBakeoff();
    }

    private function importUsers(): void
    {
        $oldUsers = [
            [
                'first_name' => 'Gwen',
                'last_name' => 'Forlizzi',
                'email' => 'thesoupcookoff@gmail.com',
                'password' => '$2y$10$OXqQoM3580UiOumMnS1Une4LgHm9j7GOz0Qxlze0nCvlzxy/0vutq',
                'role_id' => 1, // Administrator
                'is_active' => true,
            ],
            [
                'first_name' => 'Daniel',
                'last_name' => 'Siemon',
                'email' => 'dsiemon2@gmail.com',
                'password' => '$2y$10$2NUfBaLOMo7xiaHcXX7xWuQ2YD3Av/vSuEGL.ldeGGduFf2QY5iJ6',
                'role_id' => 1, // Administrator
                'is_active' => true,
            ],
            [
                'first_name' => 'Bryan',
                'last_name' => 'Siemon',
                'email' => 'admin@soupcookoff.com',
                'password' => '$2y$10$3DCT8/Z5JAcwPkXWcA1Jou/LtMwZZOW5TxpEH9nWrm4hp5u3ZS8dC',
                'role_id' => 1, // Administrator
                'is_active' => false,
            ],
            [
                'first_name' => 'Daniel',
                'last_name' => 'Siemon',
                'email' => 'dsiemon3@gmail.com',
                'password' => '$2y$10$49C8pLrTWvMagLIvVmbJROiKSgUXGBfMz6jCYIvsU21RDkYwcqeDK',
                'role_id' => 2, // Member
                'is_active' => false,
            ],
        ];

        foreach ($oldUsers as $userData) {
            if (!User::where('email', $userData['email'])->exists()) {
                User::create($userData);
                $this->command->info("Created user: {$userData['email']}");
            } else {
                $this->command->info("User exists: {$userData['email']}");
            }
        }
    }

    private function setupSoupCookoff(): void
    {
        $event = Event::where('name', 'like', '%Soup Cookoff%')->first();
        if (!$event) {
            $this->command->warn("Soup Cookoff event not found");
            return;
        }

        $this->command->info("Setting up Soup Cookoff (ID: {$event->id})");

        // Ensure voting config exists
        EventVotingConfig::firstOrCreate(
            ['event_id' => $event->id],
            ['voting_type_id' => 1] // Standard 3-2-1
        );

        // Clear existing data to start fresh
        $event->entries()->forceDelete();
        $event->participants()->forceDelete();
        $event->divisions()->forceDelete();

        // Professional divisions (P1-P13)
        $proDivisions = [];
        for ($i = 1; $i <= 13; $i++) {
            $div = Division::create([
                'event_id' => $event->id,
                'code' => 'P' . $i,
                'name' => 'Professional ' . $i,
                'type' => 'Professional',
                'display_order' => $i,
                'is_active' => true,
            ]);
            $proDivisions[$i] = $div;
        }

        // Amateur divisions (A1-A13)
        $amaDivisions = [];
        for ($i = 1; $i <= 13; $i++) {
            $div = Division::create([
                'event_id' => $event->id,
                'code' => 'A' . $i,
                'name' => 'Amateur ' . $i,
                'type' => 'Amateur',
                'display_order' => 13 + $i,
                'is_active' => true,
            ]);
            $amaDivisions[$i] = $div;
        }

        // Sample Professional Chefs and Soups
        $proChefs = [
            1 => ['Chef Mario', 'Tuscan Bean Soup'],
            2 => ['Chef Gordon', 'Lobster Bisque'],
            3 => ['Chef Julia', 'French Onion'],
            4 => ['Chef Wolfgang', 'Butternut Squash'],
            5 => ['Chef Emeril', 'Gumbo Ya-Ya'],
            6 => ['Chef Thomas', 'Potato Leek'],
            7 => ['Chef Jacques', 'Bouillabaisse'],
            8 => ['Chef Alain', 'Consomme'],
            9 => ['Chef Daniel', 'Wild Mushroom'],
            10 => ['Chef Jean-Georges', 'Carrot Ginger'],
            11 => ['Chef Eric', 'Clam Chowder'],
            12 => ['Chef Grant', 'Tomato Basil'],
            13 => ['Chef Masa', 'Miso Ramen'],
        ];

        foreach ($proChefs as $num => $data) {
            $participant = Participant::create([
                'event_id' => $event->id,
                'division_id' => $proDivisions[$num]->id,
                'name' => $data[0],
                'is_active' => true,
            ]);
            Entry::create([
                'event_id' => $event->id,
                'division_id' => $proDivisions[$num]->id,
                'participant_id' => $participant->id,
                'name' => $data[1],
                'entry_number' => $num,
                'is_active' => true,
            ]);
        }

        // Sample Amateur Chefs and Soups
        $amaChefs = [
            1 => ['Home Cook Sarah', 'Grandma\'s Chicken Noodle'],
            2 => ['Home Cook Mike', 'Hearty Beef Stew'],
            3 => ['Home Cook Lisa', 'Vegetable Minestrone'],
            4 => ['Home Cook Tom', 'Creamy Tomato'],
            5 => ['Home Cook Amy', 'Split Pea Ham'],
            6 => ['Home Cook John', 'Loaded Potato'],
            7 => ['Home Cook Beth', 'Broccoli Cheddar'],
            8 => ['Home Cook Dave', 'Chili Con Carne'],
            9 => ['Home Cook Kim', 'Thai Coconut'],
            10 => ['Home Cook Bob', 'Classic Minestrone'],
            11 => ['Home Cook Sue', 'Corn Chowder'],
            12 => ['Home Cook Jim', 'Italian Wedding'],
            13 => ['Home Cook Pat', 'Lentil Vegetable'],
        ];

        foreach ($amaChefs as $num => $data) {
            $participant = Participant::create([
                'event_id' => $event->id,
                'division_id' => $amaDivisions[$num]->id,
                'name' => $data[0],
                'is_active' => true,
            ]);
            Entry::create([
                'event_id' => $event->id,
                'division_id' => $amaDivisions[$num]->id,
                'participant_id' => $participant->id,
                'name' => $data[1],
                'entry_number' => 100 + $num, // Amateur entries start at 101
                'is_active' => true,
            ]);
        }

        $this->command->info("Created 26 divisions, 26 participants, 26 entries for Soup Cookoff");
    }

    private function setupGreatBakeoff(): void
    {
        $event = Event::where('name', 'like', '%Bakeoff%')->first();
        if (!$event) {
            $this->command->warn("Great Bakeoff event not found");
            return;
        }

        $this->command->info("Setting up Great Bakeoff (ID: {$event->id})");

        // Ensure voting config exists
        EventVotingConfig::firstOrCreate(
            ['event_id' => $event->id],
            ['voting_type_id' => 1] // Standard 3-2-1
        );

        // Clear existing data
        $event->entries()->forceDelete();
        $event->participants()->forceDelete();
        $event->divisions()->forceDelete();

        // Professional divisions (P1-P10)
        $proDivisions = [];
        for ($i = 1; $i <= 10; $i++) {
            $div = Division::create([
                'event_id' => $event->id,
                'code' => 'P' . $i,
                'name' => 'Professional ' . $i,
                'type' => 'Professional',
                'display_order' => $i,
                'is_active' => true,
            ]);
            $proDivisions[$i] = $div;
        }

        // Amateur divisions (A1-A10)
        $amaDivisions = [];
        for ($i = 1; $i <= 10; $i++) {
            $div = Division::create([
                'event_id' => $event->id,
                'code' => 'A' . $i,
                'name' => 'Amateur ' . $i,
                'type' => 'Amateur',
                'display_order' => 10 + $i,
                'is_active' => true,
            ]);
            $amaDivisions[$i] = $div;
        }

        // Sample Professional Bakers
        $proBakers = [
            1 => ['Baker Pierre', 'Croissants'],
            2 => ['Baker Marie', 'Macarons'],
            3 => ['Baker Hans', 'Black Forest Cake'],
            4 => ['Baker Antonio', 'Tiramisu'],
            5 => ['Baker Sophie', 'Tarte Tatin'],
            6 => ['Baker Klaus', 'Strudel'],
            7 => ['Baker Rosa', 'Cannoli'],
            8 => ['Baker Yuki', 'Matcha Roll Cake'],
            9 => ['Baker Carlos', 'Churros'],
            10 => ['Baker Emma', 'Victoria Sponge'],
        ];

        foreach ($proBakers as $num => $data) {
            $participant = Participant::create([
                'event_id' => $event->id,
                'division_id' => $proDivisions[$num]->id,
                'name' => $data[0],
                'is_active' => true,
            ]);
            Entry::create([
                'event_id' => $event->id,
                'division_id' => $proDivisions[$num]->id,
                'participant_id' => $participant->id,
                'name' => $data[1],
                'entry_number' => $num,
                'is_active' => true,
            ]);
        }

        // Sample Amateur Bakers
        $amaBakers = [
            1 => ['Home Baker Jane', 'Chocolate Chip Cookies'],
            2 => ['Home Baker Mark', 'Apple Pie'],
            3 => ['Home Baker Linda', 'Banana Bread'],
            4 => ['Home Baker Steve', 'Brownies'],
            5 => ['Home Baker Nancy', 'Lemon Bars'],
            6 => ['Home Baker Paul', 'Cinnamon Rolls'],
            7 => ['Home Baker Carol', 'Carrot Cake'],
            8 => ['Home Baker Kevin', 'Pecan Pie'],
            9 => ['Home Baker Diana', 'Red Velvet Cupcakes'],
            10 => ['Home Baker Brian', 'Blueberry Muffins'],
        ];

        foreach ($amaBakers as $num => $data) {
            $participant = Participant::create([
                'event_id' => $event->id,
                'division_id' => $amaDivisions[$num]->id,
                'name' => $data[0],
                'is_active' => true,
            ]);
            Entry::create([
                'event_id' => $event->id,
                'division_id' => $amaDivisions[$num]->id,
                'participant_id' => $participant->id,
                'name' => $data[1],
                'entry_number' => 100 + $num, // Amateur entries start at 101
                'is_active' => true,
            ]);
        }

        $this->command->info("Created 20 divisions, 20 participants, 20 entries for Great Bakeoff");
    }
}
