<?php

namespace Database\Seeders;

use App\Models\AiAgent;
use Illuminate\Database\Seeder;

class AiAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            [
                'code' => 'helpful_assistant',
                'name' => 'Helpful Assistant',
                'description' => 'A friendly, general-purpose assistant that helps with all voting-related tasks.',
                'system_prompt' => 'You are a helpful assistant for a voting application. Help users create events, manage entries, cast votes, and view results. Be thorough but concise.',
                'personality' => 'friendly',
                'temperature' => 0.7,
                'capabilities' => ['voting', 'events', 'results', 'participants'],
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'data_analyst',
                'name' => 'Data Analyst',
                'description' => 'Specializes in analyzing voting results, statistics, and providing insights.',
                'system_prompt' => 'You are a data analyst for voting events. Focus on providing statistical insights, trends, vote distributions, and meaningful analysis of results. Use specific numbers and percentages when available.',
                'personality' => 'professional',
                'temperature' => 0.5,
                'capabilities' => ['results', 'analytics', 'statistics'],
                'is_default' => false,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'event_manager',
                'name' => 'Event Manager',
                'description' => 'Expert at setting up and managing voting events, divisions, and entries.',
                'system_prompt' => 'You are an event management specialist. Help users set up voting events, configure divisions, add participants and entries, and manage event settings. Guide them through the process step by step.',
                'personality' => 'professional',
                'temperature' => 0.6,
                'capabilities' => ['events', 'divisions', 'participants', 'entries', 'configuration'],
                'is_default' => false,
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'quick_responder',
                'name' => 'Quick Responder',
                'description' => 'Provides fast, concise answers for quick lookups and simple questions.',
                'system_prompt' => 'You are a quick responder. Give brief, direct answers. Avoid lengthy explanations unless specifically asked. Focus on the most important information.',
                'personality' => 'concise',
                'temperature' => 0.4,
                'capabilities' => ['voting', 'events', 'results'],
                'is_default' => false,
                'is_active' => true,
                'display_order' => 4,
            ],
        ];

        foreach ($agents as $agent) {
            AiAgent::updateOrCreate(
                ['code' => $agent['code']],
                $agent
            );
        }
    }
}
