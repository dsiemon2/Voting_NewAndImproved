<?php

namespace Database\Seeders;

use App\Models\AiKnowledgeDocument;
use App\Models\AiPromptTemplate;
use App\Models\AiTool;
use Illuminate\Database\Seeder;

class AiDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedKnowledgeDocuments();
        $this->seedPromptTemplates();
        $this->seedTools();
    }

    protected function seedKnowledgeDocuments(): void
    {
        $documents = [
            [
                'title' => 'Voting System Overview',
                'category' => 'general',
                'content' => 'This is a comprehensive voting and competition management system. It supports multiple event types including food competitions, photo contests, talent shows, and more. Each event can have divisions (categories), participants (competitors), and entries (submissions). Votes are cast using configurable point systems like Standard Ranked (3-2-1), Extended Ranked (5-4-3-2-1), or Top-Heavy (5-3-1). The system tracks all events, participants, entries, votes, and results in real-time.',
                'keywords' => ['voting', 'competition', 'event', 'overview', 'system'],
                'priority' => 100,
            ],
            [
                'title' => 'Event Templates (Event Types)',
                'category' => 'events',
                'content' => 'Event templates define the type of competition. Available templates include: 1) Food Competition - for cooking contests with Chefs creating Dishes/Entries, divisions like Professional (P) and Amateur (A). 2) Photo Contest - for photography with Photographers submitting Photos, divisions like Nature (N), Portrait (P), Street (S). 3) Talent Show - for performances with Performers doing Performances, divisions like Vocal (V), Instrumental (I), Dance (D). 4) Art Competition - for artwork with Artists creating Artworks. 5) General Vote - flexible template for any voting scenario. Each template has custom labels for participants and entries.',
                'keywords' => ['template', 'food', 'photo', 'talent', 'art', 'event type', 'competition type'],
                'priority' => 95,
            ],
            [
                'title' => 'Voting Types and Point Systems',
                'category' => 'voting',
                'content' => 'Voting types determine how points are awarded. Available types: 1) Standard Ranked (3-2-1) - 3 places, 1st=3pts, 2nd=2pts, 3rd=1pt. 2) Extended Ranked (5-4-3-2-1) - 5 places, 1st=5pts down to 5th=1pt. 3) Top-Heavy (5-3-1) - 3 places with larger gaps, 1st=5pts, 2nd=3pts, 3rd=1pt. 4) Simple Vote - 1 point per vote, no ranking. 5) Rating - voters rate entries on a scale. Each event can use a different voting type. Points are summed to determine winners.',
                'keywords' => ['vote', 'points', 'ranking', 'winner', 'results', 'voting type', 'standard', 'extended', 'top-heavy'],
                'priority' => 95,
            ],
            [
                'title' => 'How Voting Works',
                'category' => 'voting',
                'content' => 'To vote: 1) Go to an active event voting page. 2) Enter entry numbers for your choices (1st place, 2nd place, etc.). 3) Submit your vote. Points are calculated based on the voting type configured for that event. Results update in real-time. Each voter can only vote once per event to prevent duplicate voting.',
                'keywords' => ['vote', 'how to vote', 'submit', 'cast vote'],
                'priority' => 90,
            ],
            [
                'title' => 'Divisions Explained',
                'category' => 'events',
                'content' => 'Divisions categorize entries within an event. Common divisions: Food Competition - Professional (P), Amateur (A). Photo Contest - Nature (N), Portrait (P), Street (S). Talent Show - Vocal (V), Instrumental (I), Dance (D). Entry numbers are typically prefixed by division code (P1, P2 for Professional; A1, A2 for Amateur). Divisions allow for category-specific judging and separate winners per division.',
                'keywords' => ['division', 'category', 'professional', 'amateur', 'nature', 'portrait'],
                'priority' => 85,
            ],
            [
                'title' => 'Managing Participants (Competitors)',
                'category' => 'events',
                'content' => 'Participants are the competitors in an event. Labels vary by template: Food Competition = Chefs, Photo Contest = Photographers, Talent Show = Performers, Art Competition = Artists. To add a participant, say "add participant" or "add chef". Each participant can have multiple entries. Participants can be updated or deleted (soft delete - recoverable). Deleting a participant also archives their entries and votes.',
                'keywords' => ['participant', 'chef', 'photographer', 'competitor', 'performer', 'artist', 'add', 'delete', 'update'],
                'priority' => 85,
            ],
            [
                'title' => 'Managing Entries (Submissions)',
                'category' => 'events',
                'content' => 'Entries are submissions from participants. Labels vary by template: Food Competition = Dishes/Entries, Photo Contest = Photos, Talent Show = Performances, Art Competition = Artworks. To add an entry, say "add entry". Each entry has a unique entry number, name, optional description, and is linked to a participant and optionally a division. Entries can be updated or deleted (soft delete - recoverable). Deleting an entry also archives its votes.',
                'keywords' => ['entry', 'dish', 'photo', 'performance', 'submission', 'add', 'delete', 'update'],
                'priority' => 85,
            ],
            [
                'title' => 'Creating Events',
                'category' => 'troubleshooting',
                'content' => 'To create an event, say "create event". Steps: 1) Select a template/event type (Food Competition, Photo Contest, etc.). 2) Name your event. 3) Add description (optional). 4) Set the event date (optional). 5) Choose a voting type (Standard Ranked, Extended Ranked, etc.). After creation, add divisions, then participants, then entries. Activate the event when ready for voting.',
                'keywords' => ['create', 'new', 'setup', 'event', 'how to'],
                'priority' => 80,
            ],
            [
                'title' => 'Updating and Deleting Data',
                'category' => 'troubleshooting',
                'content' => 'To update data: "update event" (change name, date, status), "update participant" (change name, email, division), "update entry" (change name, description, division, entry number). To delete data: "delete participant" or "delete entry". All deletes are soft deletes - data is archived and can be recovered. You will always be asked to confirm before deletion. Note: You cannot manage user accounts through the AI - that is done in the admin panel.',
                'keywords' => ['update', 'edit', 'delete', 'remove', 'modify', 'change'],
                'priority' => 80,
            ],
            [
                'title' => 'User Management Restrictions',
                'category' => 'security',
                'content' => 'For security reasons, user account management (adding, editing, or deleting system users) is NOT available through the AI assistant. User management must be done through the admin panel. The AI can only manage event-related data: events, participants (competitors), entries (submissions), divisions, and votes. If asked about user management, direct users to the admin panel.',
                'keywords' => ['user', 'account', 'security', 'admin', 'cannot', 'restriction'],
                'priority' => 100,
            ],
            [
                'title' => 'Viewing Results and Statistics',
                'category' => 'general',
                'content' => 'To view results, say "show results" or ask about standings/winners. Results show entries ranked by total points, including vote counts per place. To view statistics, say "show statistics" for overall system stats or event-specific stats. You can also ask about specific participants, entries, divisions, or events.',
                'keywords' => ['results', 'statistics', 'stats', 'standings', 'winner', 'leaderboard'],
                'priority' => 75,
            ],
            [
                'title' => 'Switching Events',
                'category' => 'navigation',
                'content' => 'To switch to a different event, say "manage event [event name]" or "switch to event [event name]". You can use partial names - the system will find matching events. If you just say "manage event" without a name, you will see a list of all available events to choose from. The system shows both active and draft/inactive events.',
                'keywords' => ['manage', 'switch', 'select', 'change event', 'go to', 'open event'],
                'priority' => 85,
            ],
            [
                'title' => 'Entry Numbers Convention',
                'category' => 'events',
                'content' => 'Entry numbers follow a convention based on divisions. For Food Competitions: Professional entries use numbers 1-99 (P1=1, P2=2, P13=13), Amateur entries use numbers 101-199 (A1=101, A2=102, A13=113). Entry numbers must be unique within each event. When voting, users enter these numbers to cast their votes for specific entries.',
                'keywords' => ['entry number', 'number', 'P1', 'A1', 'professional', 'amateur', 'convention'],
                'priority' => 80,
            ],
            [
                'title' => 'Event Status and Activation',
                'category' => 'events',
                'content' => 'Events have two statuses: Active (accepting votes) and Draft/Inactive (not accepting votes). To activate an event, say "activate event" or "update event" and change the status. Only active events can receive votes. You can deactivate an event at any time to stop voting. Event status can be changed through the update event wizard.',
                'keywords' => ['active', 'inactive', 'draft', 'status', 'activate', 'deactivate', 'voting'],
                'priority' => 80,
            ],
            [
                'title' => 'Soft Delete and Recovery',
                'category' => 'troubleshooting',
                'content' => 'When you delete a participant or entry, it is soft deleted - meaning the data is archived but not permanently removed. This allows for recovery if needed. The deletion history is tracked with timestamps and who performed the deletion. When a participant is deleted, all their entries and associated votes are also archived. Contact an administrator if you need to recover deleted data.',
                'keywords' => ['soft delete', 'archive', 'recover', 'restore', 'history', 'undo'],
                'priority' => 75,
            ],
        ];

        foreach ($documents as $doc) {
            AiKnowledgeDocument::updateOrCreate(
                ['title' => $doc['title']],
                $doc
            );
        }
    }

    protected function seedPromptTemplates(): void
    {
        $templates = [
            [
                'name' => 'Default Assistant',
                'context' => 'general',
                'system_prompt' => 'You are a helpful AI assistant for a voting and competition management system. You help users manage voting events, participants, entries, and view results. Be friendly, concise, and accurate.',
                'instructions' => "Always use markdown formatting. Be concise. If asked to modify data, tell users to use specific commands like 'create event' or 'add participant'.",
                'is_default' => true,
            ],
            [
                'name' => 'Event Manager',
                'context' => 'event_management',
                'system_prompt' => 'You are an event management assistant. Help users set up and manage voting events, add participants, create divisions, and configure voting settings.',
                'instructions' => 'Focus on event setup tasks. Guide users through the process step by step.',
                'is_default' => true,
            ],
            [
                'name' => 'Results Analyst',
                'context' => 'results',
                'system_prompt' => 'You are a results and analytics assistant. Help users understand voting results, standings, and statistics.',
                'instructions' => 'Provide clear summaries of results. Use rankings and statistics when available.',
                'is_default' => true,
            ],
        ];

        foreach ($templates as $template) {
            AiPromptTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }

    protected function seedTools(): void
    {
        $tools = [
            // Create tools (wizards)
            [
                'code' => 'create_event',
                'name' => 'Create Event',
                'description' => 'Create a new voting event with template, name, and settings',
                'category' => 'create',
                'requires_event' => false,
                'display_order' => 1,
            ],
            [
                'code' => 'add_participant',
                'name' => 'Add Participant',
                'description' => 'Register a new participant/competitor to the event',
                'category' => 'create',
                'requires_event' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'add_entry',
                'name' => 'Add Entry',
                'description' => 'Create a new entry/submission for a participant',
                'category' => 'create',
                'requires_event' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'add_division',
                'name' => 'Add Division',
                'description' => 'Set up a new category/division for the event',
                'category' => 'create',
                'requires_event' => true,
                'display_order' => 4,
            ],

            // Update tools
            [
                'code' => 'update_event',
                'name' => 'Update Event',
                'description' => 'Modify event details like name, date, or status',
                'category' => 'update',
                'requires_event' => false,
                'display_order' => 1,
            ],
            [
                'code' => 'update_participant',
                'name' => 'Update Participant',
                'description' => 'Edit participant information',
                'category' => 'update',
                'requires_event' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'update_entry',
                'name' => 'Update Entry',
                'description' => 'Modify entry details',
                'category' => 'update',
                'requires_event' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'activate_event',
                'name' => 'Activate Event',
                'description' => 'Set an event to active status for voting',
                'category' => 'update',
                'requires_event' => true,
                'display_order' => 4,
            ],

            // Delete tools
            [
                'code' => 'delete_participant',
                'name' => 'Remove Participant',
                'description' => 'Remove a participant from the event (soft delete with confirmation)',
                'category' => 'delete',
                'requires_event' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'delete_entry',
                'name' => 'Remove Entry',
                'description' => 'Delete an entry from the event (soft delete with confirmation)',
                'category' => 'delete',
                'requires_event' => true,
                'display_order' => 2,
            ],

            // Navigation tools
            [
                'code' => 'manage_event',
                'name' => 'Manage Event',
                'description' => 'Switch to managing a different event by name',
                'category' => 'navigation',
                'requires_event' => false,
                'display_order' => 1,
            ],

            // Query tools
            [
                'code' => 'show_results',
                'name' => 'Show Results',
                'description' => 'Display current voting standings and winners',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 1,
            ],
            [
                'code' => 'show_events',
                'name' => 'Show Events',
                'description' => 'List all events (active and inactive)',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 2,
            ],
            [
                'code' => 'show_participants',
                'name' => 'Show Participants',
                'description' => 'List participants in the current event',
                'category' => 'query',
                'requires_event' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'show_entries',
                'name' => 'Show Entries',
                'description' => 'List entries in the current event',
                'category' => 'query',
                'requires_event' => true,
                'display_order' => 4,
            ],
            [
                'code' => 'show_divisions',
                'name' => 'Show Divisions',
                'description' => 'List divisions/categories in the current event',
                'category' => 'query',
                'requires_event' => true,
                'display_order' => 5,
            ],
            [
                'code' => 'show_statistics',
                'name' => 'Show Statistics',
                'description' => 'Display voting statistics and metrics',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 6,
            ],
            [
                'code' => 'show_event_types',
                'name' => 'Show Event Types',
                'description' => 'List available event templates/types',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 7,
            ],
            [
                'code' => 'show_voting_types',
                'name' => 'Show Voting Types',
                'description' => 'List available voting types and point systems',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 8,
            ],
            [
                'code' => 'show_help',
                'name' => 'Get Help',
                'description' => 'Show available commands and how to use the assistant',
                'category' => 'query',
                'requires_event' => false,
                'display_order' => 9,
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
