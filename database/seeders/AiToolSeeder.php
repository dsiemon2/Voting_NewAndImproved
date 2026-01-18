<?php

namespace Database\Seeders;

use App\Models\AiTool;
use Illuminate\Database\Seeder;

class AiToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tools = [
            // Event Information
            [
                'code' => 'get_event_results',
                'name' => 'Get Event Results',
                'description' => 'Retrieve voting results for the current or specified event',
                'category' => 'event_info',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'event_id' => ['type' => 'integer', 'description' => 'Event ID (optional, uses current if not specified)'],
                    ],
                    'required' => [],
                ],
                'display_order' => 1,
            ],
            [
                'code' => 'get_event_stats',
                'name' => 'Get Event Statistics',
                'description' => 'Get voting statistics including total votes, unique voters, and entry counts',
                'category' => 'event_info',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'event_id' => ['type' => 'integer', 'description' => 'Event ID (optional)'],
                    ],
                    'required' => [],
                ],
                'display_order' => 2,
            ],
            [
                'code' => 'list_events',
                'name' => 'List Events',
                'description' => 'List all events or filter by active status',
                'category' => 'event_info',
                'requires_event' => false,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'active' => ['type' => 'boolean', 'description' => 'Filter by active status'],
                    ],
                    'required' => [],
                ],
                'display_order' => 3,
            ],

            // Entries & Participants
            [
                'code' => 'list_entries',
                'name' => 'List Entries',
                'description' => 'List all entries for the current event, optionally filtered by division',
                'category' => 'entries',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'division_id' => ['type' => 'integer', 'description' => 'Filter by division ID'],
                    ],
                    'required' => [],
                ],
                'display_order' => 1,
            ],
            [
                'code' => 'list_participants',
                'name' => 'List Participants',
                'description' => 'List all participants for the current event',
                'category' => 'entries',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                    'required' => [],
                ],
                'display_order' => 2,
            ],
            [
                'code' => 'list_divisions',
                'name' => 'List Divisions',
                'description' => 'List all divisions for the current event',
                'category' => 'entries',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                    'required' => [],
                ],
                'display_order' => 3,
            ],
            [
                'code' => 'search_entries',
                'name' => 'Search Entries',
                'description' => 'Search entries by name or participant name',
                'category' => 'entries',
                'requires_event' => false,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Search query (min 2 characters)'],
                        'event_id' => ['type' => 'integer', 'description' => 'Limit search to specific event'],
                    ],
                    'required' => ['query'],
                ],
                'display_order' => 4,
            ],

            // Voting
            [
                'code' => 'get_leaderboard',
                'name' => 'Get Leaderboard',
                'description' => 'Get top entries by votes/points',
                'category' => 'voting',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => ['type' => 'integer', 'description' => 'Number of results (default 10)'],
                        'division_id' => ['type' => 'integer', 'description' => 'Filter by division'],
                    ],
                    'required' => [],
                ],
                'display_order' => 1,
            ],
            [
                'code' => 'get_vote_count',
                'name' => 'Get Vote Count',
                'description' => 'Get total vote count and unique voters for the event',
                'category' => 'voting',
                'requires_event' => true,
                'requires_auth' => false,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                    'required' => [],
                ],
                'display_order' => 2,
            ],
        ];

        foreach ($tools as $tool) {
            AiTool::updateOrCreate(
                ['code' => $tool['code']],
                $tool
            );
        }
    }
}
