<?php

namespace App\Services\AI;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\VotingType;
use App\Models\Division;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class AiContextBuilder
{
    /**
     * Build complete system context with all available data
     */
    public static function buildFullContext(?Event $currentEvent = null): string
    {
        $context = "";

        // Add event templates (event types)
        $context .= self::buildEventTemplatesContext();

        // Add voting types
        $context .= self::buildVotingTypesContext();

        // Add all events summary
        $context .= self::buildAllEventsContext();

        // Add voting results summary for ALL events with votes
        $context .= self::buildAllEventsResultsContext();

        // Add current event detailed context
        if ($currentEvent) {
            $context .= self::buildCurrentEventContext($currentEvent);
        }

        return $context;
    }

    /**
     * Build context for all event templates (event types)
     */
    public static function buildEventTemplatesContext(): string
    {
        $templates = EventTemplate::where('is_active', true)->get();

        if ($templates->isEmpty()) {
            return "\n## Event Types:\nNo event types configured.\n";
        }

        $context = "\n## Available Event Types (Templates):\n";

        foreach ($templates as $template) {
            $context .= "\n### {$template->name}\n";
            $context .= "- **Description:** " . ($template->description ?: 'No description') . "\n";
            $context .= "- **Participant Label:** {$template->participant_label}s (e.g., " . self::getExampleNames($template->participant_label) . ")\n";
            $context .= "- **Entry Label:** {$template->entry_label}s (e.g., " . self::getExampleEntries($template->entry_label) . ")\n";

            // Division types
            $divisionTypes = $template->getDivisionTypes();
            if (!empty($divisionTypes)) {
                $context .= "- **Division Types:**\n";
                foreach ($divisionTypes as $dt) {
                    $context .= "  - {$dt['name']} ({$dt['code']})\n";
                }
            }

            // Count events using this template
            $eventCount = Event::where('event_template_id', $template->id)->count();
            $context .= "- **Events using this template:** {$eventCount}\n";
        }

        return $context;
    }

    /**
     * Build context for all voting types
     */
    public static function buildVotingTypesContext(): string
    {
        $votingTypes = VotingType::with('placeConfigs')->where('is_active', true)->get();

        if ($votingTypes->isEmpty()) {
            return "\n## Voting Types:\nNo voting types configured.\n";
        }

        $context = "\n## Available Voting Types:\n";

        foreach ($votingTypes as $vt) {
            $context .= "\n### {$vt->name} ({$vt->code})\n";
            $context .= "- **Category:** " . ucfirst($vt->category) . "\n";
            $context .= "- **Description:** " . ($vt->description ?: 'No description') . "\n";

            // Place configurations (point system)
            $places = $vt->placeConfigs;
            if ($places->isNotEmpty()) {
                $context .= "- **Point System:**\n";
                foreach ($places as $place) {
                    $ordinal = self::getOrdinal($place->place);
                    $context .= "  - {$ordinal} place: {$place->points} points\n";
                }
            }

            // Count events using this voting type
            $eventCount = Event::where('voting_type_id', $vt->id)->count();
            $context .= "- **Events using this voting type:** {$eventCount}\n";
        }

        return $context;
    }

    /**
     * Build summary context for all events
     */
    public static function buildAllEventsContext(): string
    {
        $events = Event::with(['template', 'votingType', 'divisions'])
            ->orderByDesc('is_active')
            ->orderByDesc('event_date')
            ->get();

        if ($events->isEmpty()) {
            return "\n## All Events:\nNo events in the system.\n";
        }

        $activeEvents = $events->where('is_active', true);
        $inactiveEvents = $events->where('is_active', false);

        $context = "\n## All Events in System:\n";
        $context .= "- **Total Events:** {$events->count()}\n";
        $context .= "- **Active Events:** {$activeEvents->count()}\n";
        $context .= "- **Draft/Inactive Events:** {$inactiveEvents->count()}\n";

        // Active events
        if ($activeEvents->isNotEmpty()) {
            $context .= "\n### Active Events:\n";
            foreach ($activeEvents as $event) {
                $context .= self::formatEventSummary($event);
            }
        }

        // Inactive events
        if ($inactiveEvents->isNotEmpty()) {
            $context .= "\n### Draft/Inactive Events:\n";
            foreach ($inactiveEvents as $event) {
                $context .= self::formatEventSummary($event);
            }
        }

        return $context;
    }

    /**
     * Format a single event summary
     */
    protected static function formatEventSummary(Event $event): string
    {
        $templateName = $event->template->name ?? 'Unknown';
        $votingTypeName = $event->votingType->name ?? 'Not set';
        $date = $event->event_date ? $event->event_date->format('M j, Y') : 'No date';
        $participantCount = Participant::where('event_id', $event->id)->count();
        $entryCount = Entry::where('event_id', $event->id)->count();
        $voteCount = Vote::where('event_id', $event->id)->count();
        $divisionCount = $event->divisions->count();

        $summary = "- **{$event->name}** (ID: {$event->id})\n";
        $summary .= "  - Type: {$templateName} | Voting: {$votingTypeName}\n";
        $summary .= "  - Date: {$date} | Status: " . ($event->is_active ? '✅ Active' : '📝 Draft') . "\n";
        $summary .= "  - {$participantCount} participants, {$entryCount} entries, {$voteCount} votes, {$divisionCount} divisions\n";

        return $summary;
    }

    /**
     * Build voting results summary for ALL events that have votes
     */
    public static function buildAllEventsResultsContext(): string
    {
        // Get all events that have votes
        $eventsWithVotes = Event::with('template')
            ->whereHas('votes')
            ->orderByDesc('is_active')
            ->orderByDesc('event_date')
            ->get();

        if ($eventsWithVotes->isEmpty()) {
            return "\n## Voting Results:\nNo events have received votes yet.\n";
        }

        $context = "\n## Voting Results Summary (All Events with Votes):\n";
        $context .= "**{$eventsWithVotes->count()} events have voting results.**\n\n";

        foreach ($eventsWithVotes as $event) {
            $voteCount = Vote::where('event_id', $event->id)->whereNull('deleted_at')->count();
            $status = $event->is_active ? '✅' : '📝';

            $context .= "### {$status} {$event->name} ({$voteCount} votes)\n";

            // Get top 3 results for this event
            $topEntries = DB::table('votes')
                ->select(
                    'entries.name as entry_name',
                    'participants.name as participant_name',
                    'divisions.code as division_code',
                    DB::raw('SUM(votes.final_points) as total_points')
                )
                ->join('entries', 'votes.entry_id', '=', 'entries.id')
                ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
                ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
                ->where('votes.event_id', $event->id)
                ->whereNull('votes.deleted_at')
                ->groupBy('entries.id', 'entries.name', 'participants.name', 'divisions.code')
                ->orderByDesc('total_points')
                ->limit(3)
                ->get();

            if ($topEntries->isNotEmpty()) {
                foreach ($topEntries as $i => $entry) {
                    $rank = $i + 1;
                    $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "{$rank}." };
                    $by = $entry->participant_name ? " by {$entry->participant_name}" : '';
                    $div = $entry->division_code ? " [{$entry->division_code}]" : '';
                    $context .= "{$medal} **{$entry->entry_name}**{$by}{$div} - {$entry->total_points} pts\n";
                }
            } else {
                $context .= "- No ranked results yet\n";
            }
            $context .= "\n";
        }

        $context .= "---\n";
        $context .= "When asked about results, provide the data above. For detailed results, suggest viewing the Results Page.\n";

        return $context;
    }

    /**
     * Build detailed context for current event
     */
    public static function buildCurrentEventContext(Event $currentEvent): string
    {
        $currentEvent->load(['template', 'votingType.placeConfigs', 'divisions', 'participants']);

        $participantLabel = $currentEvent->template->participant_label ?? 'Participant';
        $entryLabel = $currentEvent->template->entry_label ?? 'Entry';

        // Get statistics
        $participantCount = $currentEvent->participants()->count();
        $entryCount = Entry::where('event_id', $currentEvent->id)->count();
        $voteCount = Vote::where('event_id', $currentEvent->id)->count();
        $divisionCount = $currentEvent->divisions->count();

        $context = "\n## Current Event Details:\n";
        $context .= "**You are currently managing: {$currentEvent->name}**\n\n";

        // Basic info
        $context .= "### Event Information:\n";
        $context .= "- **ID:** {$currentEvent->id}\n";
        $context .= "- **Name:** {$currentEvent->name}\n";
        $context .= "- **Description:** " . ($currentEvent->description ?: 'No description') . "\n";
        $context .= "- **Date:** " . ($currentEvent->event_date ? $currentEvent->event_date->format('M j, Y') : 'Not set') . "\n";
        $context .= "- **Status:** " . ($currentEvent->is_active ? '✅ Active (accepting votes)' : '📝 Draft (not accepting votes)') . "\n";
        $context .= "- **Template:** " . ($currentEvent->template->name ?? 'Unknown') . "\n";
        $context .= "- **Voting Type:** " . ($currentEvent->votingType->name ?? 'Not configured') . "\n";

        // Labels
        $context .= "\n### Terminology for this Event:\n";
        $context .= "- Competitors are called: **{$participantLabel}s**\n";
        $context .= "- Submissions are called: **{$entryLabel}s**\n";

        // Voting configuration
        if ($currentEvent->votingType) {
            $context .= "\n### Voting Configuration:\n";
            $context .= "- **Type:** {$currentEvent->votingType->name}\n";
            $places = $currentEvent->votingType->placeConfigs;
            if ($places->isNotEmpty()) {
                $context .= "- **Point System:**\n";
                foreach ($places as $place) {
                    $ordinal = self::getOrdinal($place->place);
                    $context .= "  - {$ordinal} place = {$place->points} points\n";
                }
            }
        }

        // Statistics
        $context .= "\n### Current Statistics:\n";
        $context .= "- **{$participantLabel}s:** {$participantCount}\n";
        $context .= "- **{$entryLabel}s:** {$entryCount}\n";
        $context .= "- **Votes Cast:** {$voteCount}\n";
        $context .= "- **Divisions:** {$divisionCount}\n";

        // Divisions
        if ($currentEvent->divisions->isNotEmpty()) {
            $context .= "\n### Divisions:\n";
            foreach ($currentEvent->divisions as $division) {
                $divEntryCount = Entry::where('division_id', $division->id)->count();
                $context .= "- **{$division->name}** ({$division->code}) - {$divEntryCount} entries\n";
            }
        }

        // Participants list
        if ($participantCount > 0 && $participantCount <= 20) {
            $context .= "\n### {$participantLabel}s:\n";
            $participants = Participant::where('event_id', $currentEvent->id)->take(20)->get();
            foreach ($participants as $p) {
                $pEntryCount = Entry::where('participant_id', $p->id)->count();
                $context .= "- **{$p->name}** (ID: {$p->id}) - {$pEntryCount} entries\n";
            }
        } elseif ($participantCount > 20) {
            $context .= "\n### {$participantLabel}s:\n";
            $context .= "- {$participantCount} {$participantLabel}s registered (too many to list all)\n";
        }

        // Entries list (if not too many)
        if ($entryCount > 0 && $entryCount <= 30) {
            $context .= "\n### {$entryLabel}s:\n";
            $entries = Entry::with(['participant', 'division'])
                ->where('event_id', $currentEvent->id)
                ->take(30)
                ->get();
            foreach ($entries as $e) {
                $by = $e->participant?->name ?? 'Unknown';
                $div = $e->division ? " [{$e->division->code}]" : '';
                $context .= "- **{$e->name}** by {$by}{$div} (Entry #: {$e->entry_number})\n";
            }
        } elseif ($entryCount > 30) {
            $context .= "\n### {$entryLabel}s:\n";
            $context .= "- {$entryCount} {$entryLabel}s submitted (too many to list all)\n";
        }

        // Top results if votes exist
        if ($voteCount > 0) {
            $context .= "\n### Current Standings (Top 5):\n";
            $topEntries = DB::table('votes')
                ->select('entries.name', 'participants.name as participant_name', DB::raw('SUM(votes.final_points) as total'))
                ->join('entries', 'votes.entry_id', '=', 'entries.id')
                ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
                ->where('votes.event_id', $currentEvent->id)
                ->whereNull('votes.deleted_at')
                ->groupBy('entries.id', 'entries.name', 'participants.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            foreach ($topEntries as $i => $entry) {
                $rank = $i + 1;
                $by = $entry->participant_name ? " by {$entry->participant_name}" : '';
                $context .= "{$rank}. **{$entry->name}**{$by} - {$entry->total} points\n";
            }
        }

        return $context;
    }

    /**
     * Get ordinal suffix for a number
     */
    protected static function getOrdinal(int $number): string
    {
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        return $number . $suffixes[$number % 10];
    }

    /**
     * Get example names for participant label
     */
    protected static function getExampleNames(string $label): string
    {
        return match (strtolower($label)) {
            'chef' => 'Gordon, Julia, Marco',
            'photographer' => 'Ansel, Annie, Steve',
            'performer' => 'Taylor, Bruno, Adele',
            'artist' => 'Pablo, Frida, Vincent',
            default => 'John, Jane, Alex',
        };
    }

    /**
     * Get example entries for entry label
     */
    protected static function getExampleEntries(string $label): string
    {
        return match (strtolower($label)) {
            'dish', 'entry' => 'Tomato Bisque, Grilled Salmon, Apple Pie',
            'photo' => 'Sunset Beach, Mountain Vista, City Lights',
            'performance' => 'Swan Lake, Jazz Improv, Stand-up Set',
            'artwork' => 'Abstract #1, Portrait Study, Landscape',
            default => 'Submission A, Submission B, Submission C',
        };
    }
}
