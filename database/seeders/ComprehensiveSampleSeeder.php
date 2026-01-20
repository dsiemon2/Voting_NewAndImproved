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
use App\Models\EventVotingConfig;
use App\Models\User;

/**
 * Comprehensive Sample Seeder
 *
 * Creates sample events for all 6 templates using all 7 voting types.
 * Does NOT modify existing events (Soup Cookoff 2025, Great Bakeoff 2025).
 */
class ComprehensiveSampleSeeder extends Seeder
{
    private ?State $defaultState;
    private ?User $adminUser;
    private array $templates = [];
    private array $votingTypes = [];

    public function run(): void
    {
        $this->loadDependencies();

        if (empty($this->templates) || empty($this->votingTypes)) {
            $this->command->error('Required templates or voting types not found. Run migrations first.');
            return;
        }

        $this->command->info('Creating comprehensive sample events...');
        $this->command->newLine();

        // Food Competition - add one more with Extended Ranked (existing 2 use Standard)
        $this->createFoodCompetitionEvents();

        // Photo Contest - 2 events with different voting types
        $this->createPhotoContestEvents();

        // General Vote - 2 events
        $this->createGeneralVoteEvents();

        // Employee Recognition - 1 event
        $this->createEmployeeRecognitionEvents();

        // Art Competition - 2 events
        $this->createArtCompetitionEvents();

        // Talent Show - 2 events
        $this->createTalentShowEvents();

        $this->command->newLine();
        $this->command->info('Comprehensive sample seeding complete!');
        $this->displaySummary();
    }

    private function loadDependencies(): void
    {
        $this->defaultState = State::where('code', 'PA')->first()
            ?? State::where('code', 'NY')->first()
            ?? State::first();
        $this->adminUser = User::whereHas('role', fn($q) => $q->where('name', 'Administrator'))->first()
            ?? User::first();

        // Load all templates by name
        $templateNames = ['Food Competition', 'Photo Contest', 'General Vote', 'Employee Recognition', 'Art Competition', 'Talent Show'];
        foreach ($templateNames as $name) {
            $template = EventTemplate::where('name', $name)->first();
            if ($template) {
                $this->templates[$name] = $template;
            }
        }

        // Load all voting types by code
        $votingCodes = ['ranked_321', 'ranked_54321', 'ranked_531', 'equal_weight', 'approval_limited', 'weighted_judged', 'star_rating'];
        foreach ($votingCodes as $code) {
            $type = VotingType::where('code', $code)->first();
            if ($type) {
                $this->votingTypes[$code] = $type;
            }
        }
    }

    // =========================================================================
    // FOOD COMPETITION EVENTS
    // =========================================================================
    private function createFoodCompetitionEvents(): void
    {
        $template = $this->templates['Food Competition'] ?? null;
        if (!$template) return;

        // Chili Cook-Off with Extended Ranked (5-4-3-2-1)
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_54321'] ?? null,
            name: 'Texas Chili Championship 2025',
            description: 'The hottest chili competition in the Lone Star State! Five places of glory await.',
            eventDate: '2025-10-15',
            location: 'Austin Convention Center, TX',
            stateCode: 'TX',
            divisions: [
                ['code' => 'T', 'name' => 'Traditional', 'type' => 'Professional', 'entries' => [
                    ['participant' => 'Tex-Mex Masters', 'entry' => 'Authentic Texas Red'],
                    ['participant' => 'Cowboy Kitchen', 'entry' => 'Lone Star Beef Chili'],
                    ['participant' => 'Rodeo Grille', 'entry' => 'Smoky Mesquite Chili'],
                    ['participant' => 'Austin Eats', 'entry' => 'Hill Country Heat'],
                    ['participant' => 'Dallas Diner', 'entry' => 'Big D Bowl of Red'],
                ]],
                ['code' => 'V', 'name' => 'Verde/Green', 'type' => 'Professional', 'entries' => [
                    ['participant' => 'Green Chile Co', 'entry' => 'Hatch Green Chili'],
                    ['participant' => 'Southwest Spice', 'entry' => 'Tomatillo Verde'],
                    ['participant' => 'Border Grill', 'entry' => 'Jalapeño Dream'],
                    ['participant' => 'Pepper Palace', 'entry' => 'Serrano Sensation'],
                ]],
                ['code' => 'H', 'name' => 'Homestyle', 'type' => 'Amateur', 'entries' => [
                    ['participant' => 'Maria Garcia', 'entry' => 'Grandma\'s Secret Recipe'],
                    ['participant' => 'John Smith', 'entry' => 'Backyard BBQ Chili'],
                    ['participant' => 'Sarah Johnson', 'entry' => 'Sunday Slow Cooker'],
                    ['participant' => 'Mike Williams', 'entry' => 'Game Day Chili'],
                    ['participant' => 'Lisa Brown', 'entry' => 'Family Tradition'],
                ]],
            ]
        );

        // BBQ Competition with Top-Heavy (5-3-1)
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_531'] ?? null,
            name: 'Memphis BBQ Showdown 2025',
            description: 'Low and slow competition featuring the best pitmasters. Top-heavy scoring rewards excellence!',
            eventDate: '2025-09-20',
            location: 'Beale Street, Memphis, TN',
            stateCode: 'TN',
            divisions: [
                ['code' => 'B', 'name' => 'Brisket', 'type' => 'Professional', 'entries' => [
                    ['participant' => 'Smoky Mountain BBQ', 'entry' => '18-Hour Brisket'],
                    ['participant' => 'Delta Smoke', 'entry' => 'Mississippi Smoked'],
                    ['participant' => 'Pitmaster Pete', 'entry' => 'Competition Brisket'],
                    ['participant' => 'Blues City BBQ', 'entry' => 'Memphis Magic'],
                ]],
                ['code' => 'R', 'name' => 'Ribs', 'type' => 'Professional', 'entries' => [
                    ['participant' => 'Rib Kings', 'entry' => 'Fall-Off-Bone Ribs'],
                    ['participant' => 'Smoke House', 'entry' => 'Dry Rub Perfection'],
                    ['participant' => 'Southern Pride', 'entry' => 'Memphis Style Ribs'],
                    ['participant' => 'Q Masters', 'entry' => 'Competition Spare Ribs'],
                ]],
                ['code' => 'P', 'name' => 'Pulled Pork', 'type' => 'Amateur', 'entries' => [
                    ['participant' => 'Bob Thompson', 'entry' => 'Carolina Style Pull'],
                    ['participant' => 'Jim Davis', 'entry' => 'Smoky Shoulder'],
                    ['participant' => 'Tim Wilson', 'entry' => 'Backyard Champion'],
                ]],
            ]
        );

        $this->command->info('  ✓ Food Competition: 2 new events created');
    }

    // =========================================================================
    // PHOTO CONTEST EVENTS
    // =========================================================================
    private function createPhotoContestEvents(): void
    {
        $template = $this->templates['Photo Contest'] ?? null;
        if (!$template) return;

        // Nature Photography with Equal Weight voting
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['equal_weight'] ?? null,
            name: 'National Geographic Amateur Photo Contest 2025',
            description: 'Capture the beauty of our world. Equal weight voting - every great photo counts!',
            eventDate: '2025-08-01',
            location: 'Online Submission',
            stateCode: 'NY',
            divisions: [
                ['code' => 'N', 'name' => 'Nature', 'type' => 'Nature', 'entries' => [
                    ['participant' => 'Alex Rivera', 'entry' => 'Mountain Sunrise'],
                    ['participant' => 'Emma Chen', 'entry' => 'Ocean Waves'],
                    ['participant' => 'David Kim', 'entry' => 'Forest Mist'],
                    ['participant' => 'Sophie Martin', 'entry' => 'Desert Bloom'],
                    ['participant' => 'James Wilson', 'entry' => 'Northern Lights'],
                    ['participant' => 'Maria Santos', 'entry' => 'Rainforest Canopy'],
                ]],
                ['code' => 'W', 'name' => 'Wildlife', 'type' => 'Nature', 'entries' => [
                    ['participant' => 'Tom Anderson', 'entry' => 'Eagle in Flight'],
                    ['participant' => 'Lisa Park', 'entry' => 'Safari Sunset'],
                    ['participant' => 'Chris Brown', 'entry' => 'Underwater World'],
                    ['participant' => 'Anna White', 'entry' => 'Arctic Fox'],
                    ['participant' => 'Mike Johnson', 'entry' => 'Butterfly Garden'],
                ]],
                ['code' => 'P', 'name' => 'Portrait', 'type' => 'Portrait', 'entries' => [
                    ['participant' => 'Rachel Green', 'entry' => 'Elder Wisdom'],
                    ['participant' => 'Daniel Lee', 'entry' => 'Street Musician'],
                    ['participant' => 'Nicole Adams', 'entry' => 'Child\'s Wonder'],
                    ['participant' => 'Kevin Moore', 'entry' => 'Market Vendor'],
                ]],
            ]
        );

        // Street Photography with Limited Approval (Top 3)
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['approval_limited'] ?? null,
            name: 'Urban Streets Photo Awards 2025',
            description: 'Street photography celebrating urban life. Pick your top 3 favorites!',
            eventDate: '2025-07-15',
            location: 'New York City, NY',
            stateCode: 'NY',
            divisions: [
                ['code' => 'S', 'name' => 'Street Life', 'type' => 'Street', 'entries' => [
                    ['participant' => 'Marco Rossi', 'entry' => 'Rush Hour'],
                    ['participant' => 'Yuki Tanaka', 'entry' => 'Neon Dreams'],
                    ['participant' => 'Carlos Mendez', 'entry' => 'Subway Stories'],
                    ['participant' => 'Elena Volkov', 'entry' => 'City Rain'],
                    ['participant' => 'Ahmad Hassan', 'entry' => 'Market Day'],
                ]],
                ['code' => 'A', 'name' => 'Architecture', 'type' => 'Street', 'entries' => [
                    ['participant' => 'Peter Schmidt', 'entry' => 'Glass and Steel'],
                    ['participant' => 'Laura Chen', 'entry' => 'Historic Facades'],
                    ['participant' => 'Robert Taylor', 'entry' => 'Bridge at Dusk'],
                    ['participant' => 'Amy Wilson', 'entry' => 'Skyline Reflection'],
                ]],
            ]
        );

        $this->command->info('  ✓ Photo Contest: 2 events created');
    }

    // =========================================================================
    // GENERAL VOTE EVENTS
    // =========================================================================
    private function createGeneralVoteEvents(): void
    {
        $template = $this->templates['General Vote'] ?? null;
        if (!$template) return;

        // Best Local Business with 5-Star Rating
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['star_rating'] ?? null,
            name: 'Best of Portland Awards 2025',
            description: 'Vote for your favorite local businesses using our 5-star rating system!',
            eventDate: '2025-12-01',
            location: 'Portland, OR',
            stateCode: 'OR',
            divisions: [
                ['code' => 'R', 'name' => 'Restaurants', 'type' => null, 'entries' => [
                    ['participant' => 'Pine Street Bistro', 'entry' => 'Pine Street Bistro'],
                    ['participant' => 'Hawthorne Cafe', 'entry' => 'Hawthorne Cafe'],
                    ['participant' => 'Pearl District Grille', 'entry' => 'Pearl District Grille'],
                    ['participant' => 'Alberta Arts Kitchen', 'entry' => 'Alberta Arts Kitchen'],
                    ['participant' => 'Division Street Diner', 'entry' => 'Division Street Diner'],
                ]],
                ['code' => 'S', 'name' => 'Shops', 'type' => null, 'entries' => [
                    ['participant' => 'Powell\'s Books', 'entry' => 'Powell\'s City of Books'],
                    ['participant' => 'Made Here PDX', 'entry' => 'Made Here PDX'],
                    ['participant' => 'Tender Loving Empire', 'entry' => 'Tender Loving Empire'],
                    ['participant' => 'Bridge City Comics', 'entry' => 'Bridge City Comics'],
                ]],
                ['code' => 'C', 'name' => 'Coffee Shops', 'type' => null, 'entries' => [
                    ['participant' => 'Stumptown Coffee', 'entry' => 'Stumptown Coffee'],
                    ['participant' => 'Heart Coffee', 'entry' => 'Heart Coffee Roasters'],
                    ['participant' => 'Coava Coffee', 'entry' => 'Coava Coffee Roasters'],
                    ['participant' => 'Case Study Coffee', 'entry' => 'Case Study Coffee'],
                ]],
            ]
        );

        // Community Choice with Standard Ranked
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_321'] ?? null,
            name: 'Community Park Naming Vote 2025',
            description: 'Help us name our new community park! Rank your top 3 choices.',
            eventDate: '2025-06-15',
            location: 'Springfield, IL',
            stateCode: 'IL',
            divisions: [
                ['code' => 'N', 'name' => 'Name Options', 'type' => null, 'entries' => [
                    ['participant' => 'Historical Society', 'entry' => 'Lincoln Heritage Park'],
                    ['participant' => 'Garden Club', 'entry' => 'Prairie Meadows'],
                    ['participant' => 'Youth Council', 'entry' => 'Adventure Commons'],
                    ['participant' => 'Veterans Group', 'entry' => 'Freedom Fields'],
                    ['participant' => 'Nature Alliance', 'entry' => 'Wildflower Gardens'],
                    ['participant' => 'Arts Committee', 'entry' => 'Inspiration Point'],
                ]],
            ]
        );

        $this->command->info('  ✓ General Vote: 2 events created');
    }

    // =========================================================================
    // EMPLOYEE RECOGNITION EVENTS
    // =========================================================================
    private function createEmployeeRecognitionEvents(): void
    {
        $template = $this->templates['Employee Recognition'] ?? null;
        if (!$template) return;

        // Employee of the Year with Weighted Judges
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['weighted_judged'] ?? null,
            name: 'TechCorp Employee Excellence Awards 2025',
            description: 'Recognizing outstanding employees. Peer votes + judge panel scoring.',
            eventDate: '2025-12-20',
            location: 'San Francisco, CA',
            stateCode: 'CA',
            divisions: [
                ['code' => 'L', 'name' => 'Leadership', 'type' => null, 'entries' => [
                    ['participant' => 'Sarah Mitchell', 'entry' => 'Mentorship Excellence'],
                    ['participant' => 'David Chen', 'entry' => 'Team Transformation'],
                    ['participant' => 'Jennifer Lopez', 'entry' => 'Crisis Leadership'],
                    ['participant' => 'Michael Brown', 'entry' => 'Department Growth'],
                ]],
                ['code' => 'I', 'name' => 'Innovation', 'type' => null, 'entries' => [
                    ['participant' => 'Alex Park', 'entry' => 'AI Integration Project'],
                    ['participant' => 'Emma Rodriguez', 'entry' => 'Process Automation'],
                    ['participant' => 'Chris Taylor', 'entry' => 'Customer Experience Redesign'],
                    ['participant' => 'Lisa Wang', 'entry' => 'Sustainability Initiative'],
                ]],
                ['code' => 'T', 'name' => 'Team Player', 'type' => null, 'entries' => [
                    ['participant' => 'Ryan Johnson', 'entry' => 'Cross-Team Collaboration'],
                    ['participant' => 'Amanda Smith', 'entry' => 'Onboarding Champion'],
                    ['participant' => 'Kevin Lee', 'entry' => 'Knowledge Sharing'],
                    ['participant' => 'Maria Garcia', 'entry' => 'Culture Ambassador'],
                ]],
                ['code' => 'R', 'name' => 'Rising Star', 'type' => null, 'entries' => [
                    ['participant' => 'Jordan Williams', 'entry' => 'First Year Impact'],
                    ['participant' => 'Taylor Martinez', 'entry' => 'Rapid Growth'],
                    ['participant' => 'Casey Thompson', 'entry' => 'Fresh Perspective'],
                ]],
            ]
        );

        // Quarterly Recognition with Extended Ranked
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_54321'] ?? null,
            name: 'StartupXYZ Q4 Recognition Awards',
            description: 'Quarterly peer recognition program. Top 5 contributors honored!',
            eventDate: '2025-12-31',
            location: 'Austin, TX',
            stateCode: 'TX',
            divisions: [
                ['code' => 'A', 'name' => 'All Stars', 'type' => null, 'entries' => [
                    ['participant' => 'Dev Team - Alice', 'entry' => 'Backend Overhaul'],
                    ['participant' => 'Design Team - Bob', 'entry' => 'UX Redesign'],
                    ['participant' => 'Sales Team - Carol', 'entry' => 'Record Quarter'],
                    ['participant' => 'Support Team - Dan', 'entry' => '100% Satisfaction'],
                    ['participant' => 'Marketing - Eve', 'entry' => 'Viral Campaign'],
                    ['participant' => 'Product - Frank', 'entry' => 'Feature Launch'],
                    ['participant' => 'HR Team - Grace', 'entry' => 'Culture Initiative'],
                ]],
            ]
        );

        $this->command->info('  ✓ Employee Recognition: 2 events created');
    }

    // =========================================================================
    // ART COMPETITION EVENTS
    // =========================================================================
    private function createArtCompetitionEvents(): void
    {
        $template = $this->templates['Art Competition'] ?? null;
        if (!$template) return;

        // Fine Art with Standard Ranked
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_321'] ?? null,
            name: 'Santa Fe Art Festival 2025',
            description: 'Celebrating Southwestern art and artists. Traditional 3-2-1 scoring.',
            eventDate: '2025-07-04',
            location: 'Santa Fe Plaza, NM',
            stateCode: 'NM',
            divisions: [
                ['code' => 'P', 'name' => 'Painting', 'type' => 'Painting', 'entries' => [
                    ['participant' => 'Isabella Torres', 'entry' => 'Desert Sunset'],
                    ['participant' => 'William Begay', 'entry' => 'Spirit of the Land'],
                    ['participant' => 'Catherine Ortiz', 'entry' => 'Adobe Dreams'],
                    ['participant' => 'Robert Lujan', 'entry' => 'Mountain Majesty'],
                    ['participant' => 'Elena Chavez', 'entry' => 'Fiesta Colors'],
                ]],
                ['code' => 'S', 'name' => 'Sculpture', 'type' => 'Sculpture', 'entries' => [
                    ['participant' => 'Marcus Stone', 'entry' => 'Bronze Buffalo'],
                    ['participant' => 'Patricia Clay', 'entry' => 'Pueblo Woman'],
                    ['participant' => 'Joseph Eagle', 'entry' => 'Spirit Dancer'],
                    ['participant' => 'Anna Silversmith', 'entry' => 'Canyon Form'],
                ]],
                ['code' => 'M', 'name' => 'Mixed Media', 'type' => 'Mixed', 'entries' => [
                    ['participant' => 'Diana Moon', 'entry' => 'Layered Memories'],
                    ['participant' => 'Thomas Ray', 'entry' => 'Found Object Journey'],
                    ['participant' => 'Laura Wind', 'entry' => 'Textile Traditions'],
                ]],
            ]
        );

        // Student Art with Top-Heavy scoring
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['ranked_531'] ?? null,
            name: 'Young Artists Showcase 2025',
            description: 'High school art competition. Top-heavy scoring rewards standout pieces!',
            eventDate: '2025-05-15',
            location: 'Chicago Art Institute, IL',
            stateCode: 'IL',
            divisions: [
                ['code' => 'D', 'name' => 'Drawing', 'type' => 'Drawing', 'entries' => [
                    ['participant' => 'Emma Wilson (Lincoln HS)', 'entry' => 'Self Portrait'],
                    ['participant' => 'James Park (Oak Academy)', 'entry' => 'City Skyline'],
                    ['participant' => 'Sophie Chen (Arts Magnet)', 'entry' => 'Nature Study'],
                    ['participant' => 'Lucas Martinez (Central HS)', 'entry' => 'Abstract Emotions'],
                ]],
                ['code' => 'P', 'name' => 'Photography', 'type' => 'Photography', 'entries' => [
                    ['participant' => 'Mia Johnson (Tech High)', 'entry' => 'Urban Decay'],
                    ['participant' => 'Noah Brown (West Academy)', 'entry' => 'Light and Shadow'],
                    ['participant' => 'Ava Davis (North Prep)', 'entry' => 'Motion Blur'],
                    ['participant' => 'Ethan Lee (East High)', 'entry' => 'Macro World'],
                ]],
                ['code' => 'G', 'name' => 'Digital Art', 'type' => 'Digital', 'entries' => [
                    ['participant' => 'Olivia Taylor (STEM Academy)', 'entry' => 'Cyberpunk City'],
                    ['participant' => 'Liam Anderson (Digital Arts)', 'entry' => 'Fantasy Landscape'],
                    ['participant' => 'Isabella Garcia (Creative HS)', 'entry' => 'Character Design'],
                ]],
            ]
        );

        $this->command->info('  ✓ Art Competition: 2 events created');
    }

    // =========================================================================
    // TALENT SHOW EVENTS
    // =========================================================================
    private function createTalentShowEvents(): void
    {
        $template = $this->templates['Talent Show'] ?? null;
        if (!$template) return;

        // Community Talent with Limited Approval
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['approval_limited'] ?? null,
            name: 'Nashville Rising Stars 2025',
            description: 'Discover the next big talent! Vote for your top 3 favorite acts.',
            eventDate: '2025-08-10',
            location: 'Ryman Auditorium, TN',
            stateCode: 'TN',
            divisions: [
                ['code' => 'V', 'name' => 'Vocal', 'type' => 'Vocal', 'entries' => [
                    ['participant' => 'Melody Harper', 'entry' => 'Original Ballad'],
                    ['participant' => 'Jackson Rivers', 'entry' => 'Country Soul'],
                    ['participant' => 'Aria Stone', 'entry' => 'Pop Medley'],
                    ['participant' => 'Marcus Cole', 'entry' => 'R&B Showcase'],
                    ['participant' => 'Luna Vega', 'entry' => 'Classical Crossover'],
                ]],
                ['code' => 'I', 'name' => 'Instrumental', 'type' => 'Instrumental', 'entries' => [
                    ['participant' => 'Django Bennett', 'entry' => 'Guitar Virtuoso'],
                    ['participant' => 'Clara Keys', 'entry' => 'Piano Prodigy'],
                    ['participant' => 'Max Drummer', 'entry' => 'Percussion Solo'],
                    ['participant' => 'Violet Strings', 'entry' => 'Fiddle Fire'],
                ]],
                ['code' => 'D', 'name' => 'Dance', 'type' => 'Dance', 'entries' => [
                    ['participant' => 'The Movement Crew', 'entry' => 'Hip Hop Fusion'],
                    ['participant' => 'Grace Ballet Academy', 'entry' => 'Contemporary Dreams'],
                    ['participant' => 'Street Beats', 'entry' => 'Break Dance Battle'],
                    ['participant' => 'Rhythm Nation', 'entry' => 'Latin Heat'],
                ]],
            ]
        );

        // School Talent Show with Equal Weight
        $this->createEvent(
            template: $template,
            votingType: $this->votingTypes['equal_weight'] ?? null,
            name: 'Springfield High Annual Talent Show',
            description: 'Every vote counts equally! Support your classmates\' amazing talents.',
            eventDate: '2025-04-25',
            location: 'Springfield High Auditorium, MA',
            stateCode: 'MA',
            divisions: [
                ['code' => 'S', 'name' => 'Solo Acts', 'type' => 'Solo', 'entries' => [
                    ['participant' => 'Jessica Miller (Senior)', 'entry' => 'Acoustic Guitar Set'],
                    ['participant' => 'Brandon Lee (Junior)', 'entry' => 'Magic Show'],
                    ['participant' => 'Samantha Cruz (Senior)', 'entry' => 'Stand-up Comedy'],
                    ['participant' => 'Tyler James (Sophomore)', 'entry' => 'Beatbox Performance'],
                    ['participant' => 'Michelle Park (Junior)', 'entry' => 'K-Pop Dance Cover'],
                ]],
                ['code' => 'G', 'name' => 'Group Acts', 'type' => 'Group', 'entries' => [
                    ['participant' => 'The Harmonics', 'entry' => 'A Cappella Mashup'],
                    ['participant' => 'Drama Club', 'entry' => 'One-Act Play'],
                    ['participant' => 'Step Squad', 'entry' => 'Step Routine'],
                    ['participant' => 'Jazz Band', 'entry' => 'Swing Revival'],
                ]],
                ['code' => 'V', 'name' => 'Variety', 'type' => 'Variety', 'entries' => [
                    ['participant' => 'Alex & Jordan', 'entry' => 'Comedy Duo'],
                    ['participant' => 'Science Club', 'entry' => 'Science Demo Show'],
                    ['participant' => 'Cheerleading Team', 'entry' => 'Cheer Routine'],
                ]],
            ]
        );

        $this->command->info('  ✓ Talent Show: 2 events created');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    private function createEvent(
        EventTemplate $template,
        ?VotingType $votingType,
        string $name,
        string $description,
        string $eventDate,
        string $location,
        string $stateCode,
        array $divisions
    ): ?Event {
        if (!$votingType) {
            $this->command->warn("    Skipping '{$name}' - voting type not found");
            return null;
        }

        // Check if event already exists
        if (Event::where('name', $name)->exists()) {
            $this->command->line("    Skipping '{$name}' - already exists");
            return null;
        }

        $state = State::where('code', $stateCode)->first() ?? $this->defaultState;

        $event = Event::create([
            'event_template_id' => $template->id,
            'voting_type_id' => $votingType->id,
            'name' => $name,
            'description' => $description,
            'event_date' => $eventDate,
            'location' => $location,
            'state_id' => $state?->id,
            'is_active' => true,
            'is_public' => true,
            'created_by' => $this->adminUser?->id,
        ]);

        // Create voting config
        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $votingType->id,
            'max_votes_per_entry' => 1,
            'allow_self_voting' => false,
            'show_live_results' => false,
            'show_vote_counts' => true,
            'show_percentages' => true,
        ]);

        // Create divisions, participants, and entries
        $divisionOrder = 1;
        $entryNumber = 1;
        $amateurEntryNumber = 101;

        foreach ($divisions as $divData) {
            $division = Division::create([
                'event_id' => $event->id,
                'code' => $divData['code'],
                'name' => $divData['name'],
                'type' => $divData['type'],
                'display_order' => $divisionOrder++,
                'is_active' => true,
            ]);

            foreach ($divData['entries'] as $entryData) {
                $participant = Participant::create([
                    'event_id' => $event->id,
                    'division_id' => $division->id,
                    'name' => $entryData['participant'],
                    'is_active' => true,
                ]);

                // Determine entry number based on division type
                $isAmateur = str_contains(strtolower($divData['type'] ?? ''), 'amateur')
                    || str_contains(strtolower($divData['name']), 'amateur')
                    || str_contains(strtolower($divData['name']), 'homestyle');

                $number = $isAmateur ? $amateurEntryNumber++ : $entryNumber++;

                Entry::create([
                    'event_id' => $event->id,
                    'division_id' => $division->id,
                    'participant_id' => $participant->id,
                    'name' => $entryData['entry'],
                    'entry_number' => $number,
                    'is_active' => true,
                ]);
            }
        }

        return $event;
    }

    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('=== Summary ===');

        $events = Event::with(['template', 'votingType'])
            ->withCount(['divisions', 'participants', 'entries'])
            ->orderBy('event_template_id')
            ->orderBy('id')
            ->get();

        $this->command->table(
            ['ID', 'Event Name', 'Template', 'Voting Type', 'Divisions', 'Participants', 'Entries'],
            $events->map(fn($e) => [
                $e->id,
                \Illuminate\Support\Str::limit($e->name, 35),
                $e->template->name ?? 'N/A',
                \Illuminate\Support\Str::limit($e->votingType->name ?? 'N/A', 20),
                $e->divisions_count,
                $e->participants_count,
                $e->entries_count,
            ])
        );

        $this->command->newLine();
        $this->command->info('Total Events: ' . $events->count());

        // Show voting type coverage
        $votingTypeCoverage = $events->groupBy('voting_type_id')->map->count();
        $this->command->info('Voting Types Used: ' . $votingTypeCoverage->count() . ' of ' . VotingType::count());

        // Show template coverage
        $templateCoverage = $events->groupBy('event_template_id')->map->count();
        $this->command->info('Templates Used: ' . $templateCoverage->count() . ' of ' . EventTemplate::count());
    }
}
