<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\VotingType;
use App\Models\Entry;
use App\Models\Participant;
use App\Models\Division;
use App\Models\Vote;
use App\Services\AI\WizardStateMachine;
use App\Services\AI\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    protected WizardStateMachine $wizard;
    protected AiService $aiService;

    // Intent patterns for detecting user intentions (used for rule-based routing)
    const INTENT_PATTERNS = [
        // Create Intents - trigger add wizards
        'add_event' => ['create event', 'create an event', 'new event', 'add event', 'add an event',
                        'start event', 'make event', 'set up event', 'setup event'],
        'add_participant' => ['add participant', 'add a participant', 'new participant', 'add chef', 'add a chef',
                             'new chef', 'register chef', 'add photographer', 'register participant', 'add performer'],
        'add_entry' => ['add entry', 'add an entry', 'new entry', 'submit entry', 'create entry',
                        'add dish', 'add photo', 'add a dish', 'add a photo'],
        'add_division' => ['add division', 'add a division', 'new division', 'create division', 'add category'],

        // Update Intents - trigger update wizards
        'update_event' => ['update event', 'edit event', 'change event', 'modify event', 'rename event',
                          'activate event', 'deactivate event'],
        'update_participant' => ['update participant', 'edit participant', 'change participant', 'modify participant',
                                'update chef', 'edit chef', 'rename participant'],
        'update_entry' => ['update entry', 'edit entry', 'change entry', 'modify entry',
                          'rename entry', 'edit dish', 'update dish'],

        // Delete Intents - trigger delete wizards
        'delete_participant' => ['delete participant', 'remove participant', 'delete chef', 'remove chef',
                                'delete a participant', 'remove a participant'],
        'delete_entry' => ['delete entry', 'remove entry', 'delete an entry', 'remove an entry',
                          'delete dish', 'remove dish'],

        // Event Management Intents - switch context
        'manage_event' => ['manage event', 'switch to event', 'select event', 'work on event',
                          'go to event', 'open event', 'use event', 'switch to ', 'manage '],

        // Clear event context - return to main menu
        'clear_event' => ['go back to management', 'return to management', 'back to management',
                         'clear event', 'deselect event', 'no event', 'back to menu', 'main menu',
                         'exit event', 'leave event', 'close event', 'stop managing'],
    ];

    public function __construct()
    {
        $this->wizard = new WizardStateMachine();
        $this->aiService = new AiService();
    }

    /**
     * Process a chat message and return a response
     * Uses hybrid approach: Rules for actions, AI for conversations
     */
    public function chat(Request $request)
    {
        $message = $request->input('message', '');
        $messageLower = strtolower($message);
        $currentEventId = $request->input('event_id');
        $wizardState = $request->input('wizard_state'); // Client sends wizard state
        $conversationHistory = $request->input('conversation_history', []); // For AI context

        // Get the current event if one is being managed
        $currentEvent = null;
        if ($currentEventId) {
            $currentEvent = Event::with(['template', 'divisions', 'participants'])->find($currentEventId);
        }

        // PRIORITY 1: Handle active wizard (rule-based)
        if ($wizardState && isset($wizardState['type'])) {
            return $this->processWizardStep($wizardState, $message, $currentEvent);
        }

        // PRIORITY 2: Detect action intents that should start wizards (rule-based)
        $intent = $this->detectIntent($messageLower);

        // Handle "manage event <name>" - switches the current event context
        if ($intent === 'manage_event') {
            return $this->handleManageEvent($message, $currentEvent);
        }

        // Handle "go back to management" - clears event context
        if ($intent === 'clear_event') {
            return $this->handleClearEvent($currentEvent);
        }

        if (str_starts_with($intent, 'add_') || str_starts_with($intent, 'update_') || str_starts_with($intent, 'delete_')) {
            return $this->startWizard($intent, $currentEvent);
        }

        // PRIORITY 3: Use AI service for natural language responses
        if ($this->aiService->isAvailable()) {
            $aiResponse = $this->aiService->chat($message, $currentEvent, $conversationHistory);

            if ($aiResponse['success']) {
                return response()->json([
                    'message' => $aiResponse['message'],
                    'type' => 'ai',
                    'visualAids' => $aiResponse['visualAids'],
                    'suggestedActions' => $this->getSuggestedActions($currentEvent),
                    'provider' => $aiResponse['provider'] ?? null,
                    'model' => $aiResponse['model'] ?? null,
                ]);
            }
        }

        // FALLBACK: Rule-based responses if AI unavailable
        return $this->getRuleBasedResponse($message, $currentEvent);
    }

    /**
     * Fallback rule-based response when AI is unavailable
     */
    protected function getRuleBasedResponse(string $message, ?Event $currentEvent)
    {
        $messageLower = strtolower($message);

        // Check for specific query patterns
        if (str_contains($messageLower, 'result') || str_contains($messageLower, 'winner') || str_contains($messageLower, 'standing')) {
            return $this->getResults($message, $currentEvent);
        }

        // Check if asking for ALL events vs just active
        if (str_contains($messageLower, 'all event') || str_contains($messageLower, 'list event') || str_contains($messageLower, 'every event')) {
            return $this->getAllEvents($currentEvent);
        }

        if (str_contains($messageLower, 'active') || str_contains($messageLower, 'event')) {
            return $this->getAllEvents($currentEvent); // Changed to show all by default
        }

        if (str_contains($messageLower, 'stat') || str_contains($messageLower, 'vote count')) {
            return $this->getVotingStats($message, $currentEvent);
        }

        if (str_contains($messageLower, 'help') || str_contains($messageLower, 'what can')) {
            return $this->getHelp($currentEvent);
        }

        if (str_contains($messageLower, 'participant') || str_contains($messageLower, 'chef')) {
            return $this->getParticipantInfo($message, $currentEvent);
        }

        if (str_contains($messageLower, 'entry') || str_contains($messageLower, 'entries')) {
            return $this->getEntryInfo($message, $currentEvent);
        }

        if (str_contains($messageLower, 'division')) {
            return $this->getDivisionInfo($currentEvent);
        }

        if (str_contains($messageLower, 'how') && str_contains($messageLower, 'vote')) {
            return $this->getVotingHelp($currentEvent);
        }

        // Event templates/types query
        if (str_contains($messageLower, 'template') || str_contains($messageLower, 'event type') || str_contains($messageLower, 'competition type')) {
            return $this->getEventTemplatesInfo();
        }

        // Voting types query
        if (str_contains($messageLower, 'voting type') || str_contains($messageLower, 'point system') || str_contains($messageLower, 'points')) {
            return $this->getVotingTypesInfo();
        }

        return $this->getDefaultResponse($currentEvent);
    }

    /**
     * Process a wizard step using client-provided state
     */
    protected function processWizardStep(array $wizardState, string $input, ?Event $currentEvent)
    {
        $wizardType = $wizardState['type'];
        $currentStep = $wizardState['currentStep'] ?? 0;
        $collectedData = $wizardState['collectedData'] ?? [];
        $eventId = $wizardState['eventId'] ?? $currentEvent?->id;

        // Handle cancel
        if (strtolower(trim($input)) === 'cancel') {
            return response()->json([
                'message' => "Wizard cancelled. How else can I help you?",
                'type' => 'text',
                'wizardState' => null,
            ]);
        }

        // Handle skip
        if (strtolower(trim($input)) === 'skip') {
            $input = null;
        }

        $wizardConfig = WizardStateMachine::WIZARDS[$wizardType] ?? null;
        if (!$wizardConfig) {
            return response()->json([
                'message' => "Unknown wizard type.",
                'type' => 'error',
                'wizardState' => null,
            ]);
        }

        $handler = app($wizardConfig['handler']);
        $handler->setEventId($eventId);

        $stepName = $wizardConfig['steps'][$currentStep];
        $validation = $handler->validateStep($stepName, $input, $collectedData);

        if (!$validation['valid']) {
            return response()->json([
                'message' => $validation['error'],
                'type' => 'text',
                'wizardState' => [
                    'type' => $wizardType,
                    'currentStep' => $currentStep,
                    'totalSteps' => count($wizardConfig['steps']),
                    'stepName' => $stepName,
                    'collectedData' => $collectedData,
                    'eventId' => $eventId,
                    'options' => $handler->getOptionsForStep($stepName, $collectedData),
                    'canSkip' => $handler->canSkipStep($stepName),
                ],
            ]);
        }

        // Store validated value and advance
        $collectedData[$stepName] = $validation['value'];
        $nextStep = $currentStep + 1;

        // Check if wizard is complete
        if ($nextStep >= count($wizardConfig['steps'])) {
            $result = $handler->execute($collectedData);
            return response()->json([
                'message' => $result['message'],
                'type' => 'text',
                'wizardState' => null,
                'suggestedActions' => $result['suggestedActions'] ?? [],
                'complete' => true,
            ]);
        }

        // Get next step prompt
        $nextStepName = $wizardConfig['steps'][$nextStep];
        $prompt = $handler->getPromptForStep($nextStepName, $collectedData);

        return response()->json([
            'message' => $prompt,
            'type' => 'wizard',
            'wizardState' => [
                'type' => $wizardType,
                'currentStep' => $nextStep,
                'totalSteps' => count($wizardConfig['steps']),
                'stepName' => $nextStepName,
                'collectedData' => $collectedData,
                'eventId' => $eventId,
                'options' => $handler->getOptionsForStep($nextStepName, $collectedData),
                'canSkip' => $handler->canSkipStep($nextStepName),
            ],
        ]);
    }

    /**
     * Detect user intent from message
     */
    protected function detectIntent(string $message): string
    {
        foreach (self::INTENT_PATTERNS as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    return $intent;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Start a wizard for the given action intent
     */
    protected function startWizard(string $intent, ?Event $currentEvent)
    {
        $wizardType = $intent; // e.g., 'add_event', 'add_participant', 'update_event', 'delete_entry'

        // Define which wizards require an event context
        $requiresEventContext = [
            'add_participant', 'add_entry', 'add_division',
            'update_participant', 'update_entry',
            'delete_participant', 'delete_entry',
        ];

        // For event-specific wizards, require an event context
        if (in_array($intent, $requiresEventContext) && !$currentEvent) {
            $action = match(true) {
                str_starts_with($intent, 'add_') => 'add',
                str_starts_with($intent, 'update_') => 'update',
                str_starts_with($intent, 'delete_') => 'delete',
                default => 'manage'
            };
            $entity = str_replace(['add_', 'update_', 'delete_'], '', $intent);

            return response()->json([
                'message' => "To {$action} a {$entity}, you need to be managing an event first.\n\n" .
                            "Would you like to:\n" .
                            "• **Create a new event** - Say \"create event\"\n" .
                            "• **Select an existing event** - Go to the Events page and click on an event",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                    ['label' => 'Show active events', 'prompt' => 'show active events'],
                ],
            ]);
        }

        $wizardConfig = WizardStateMachine::WIZARDS[$wizardType] ?? null;
        if (!$wizardConfig) {
            return response()->json([
                'message' => "Unknown wizard type: {$wizardType}",
                'type' => 'error',
            ]);
        }

        $handler = app($wizardConfig['handler']);
        $handler->setEventId($currentEvent?->id);

        $firstStep = $wizardConfig['steps'][0];
        $prompt = $handler->getPromptForStep($firstStep, []);

        return response()->json([
            'message' => $prompt,
            'type' => 'wizard',
            'wizardState' => [
                'type' => $wizardType,
                'currentStep' => 0,
                'totalSteps' => count($wizardConfig['steps']),
                'stepName' => $firstStep,
                'collectedData' => [],
                'eventId' => $currentEvent?->id,
                'options' => $handler->getOptionsForStep($firstStep, []),
                'canSkip' => $handler->canSkipStep($firstStep),
            ],
        ]);
    }

    /**
     * Get default response with capabilities
     */
    protected function getDefaultResponse(?Event $currentEvent)
    {
        $contextMsg = $currentEvent
            ? "You're currently managing **{$currentEvent->name}**.\n\n"
            : "";

        $participantLabel = $currentEvent && $currentEvent->template
            ? ($currentEvent->template->participant_label ?? 'participant')
            : 'participant';
        $entryLabel = $currentEvent && $currentEvent->template
            ? ($currentEvent->template->entry_label ?? 'entry')
            : 'entry';

        $actionMsg = $currentEvent
            ? "**Create:**\n" .
              "• \"Add a participant\" - Register a new {$participantLabel}\n" .
              "• \"Add an entry\" - Create a new {$entryLabel}\n" .
              "• \"Add a division\" - Set up a new division\n\n" .
              "**Update:**\n" .
              "• \"Update event\" - Change event details or status\n" .
              "• \"Update participant\" - Edit {$participantLabel} info\n" .
              "• \"Update entry\" - Edit {$entryLabel} details\n\n" .
              "**Delete:**\n" .
              "• \"Delete participant\" - Remove a {$participantLabel}\n" .
              "• \"Delete entry\" - Remove an {$entryLabel}\n\n"
            : "**Actions I can do:**\n" .
              "• \"Create an event\" - Set up a new voting event\n" .
              "• \"Update event\" - Modify an existing event\n\n";

        return response()->json([
            'message' => "{$contextMsg}{$actionMsg}" .
                        "**Questions I can answer:**\n" .
                        "• \"What events are active?\"\n" .
                        "• \"Show me the results\"\n" .
                        "• \"How do I vote?\"\n" .
                        "• \"Show entries\" / \"Show participants\"\n" .
                        "• \"Voting statistics\"\n\n" .
                        "What would you like to do?",
            'type' => 'text',
            'suggestedActions' => $this->getSuggestedActions($currentEvent),
        ]);
    }

    /**
     * Get contextual suggested actions
     */
    protected function getSuggestedActions(?Event $currentEvent): array
    {
        if (!$currentEvent) {
            return [
                ['label' => 'Create an event', 'prompt' => 'create event'],
                ['label' => 'Active events', 'prompt' => 'show active events'],
                ['label' => 'View results', 'prompt' => 'show results'],
            ];
        }

        $actions = [];

        // Check what the event needs
        $participantCount = $currentEvent->participants()->count();
        $entryCount = Entry::where('event_id', $currentEvent->id)->count();
        $voteCount = Vote::where('event_id', $currentEvent->id)->count();

        $label = $currentEvent->template ? ($currentEvent->template->participant_label ?? 'participant') : 'participant';

        if ($participantCount === 0) {
            $actions[] = ['label' => "Add a {$label}", 'prompt' => 'add a participant'];
        }

        if ($participantCount > 0 && $entryCount === 0) {
            $actions[] = ['label' => 'Add an entry', 'prompt' => 'add an entry'];
        }

        if ($voteCount > 0) {
            $actions[] = ['label' => 'View results', 'prompt' => 'show results'];
        }

        $actions[] = ['label' => 'View stats', 'prompt' => 'show statistics'];

        return array_slice($actions, 0, 3);
    }

    // ===== Query Handlers =====

    private function getActiveEvents($currentEvent = null)
    {
        $events = Event::with('template')
            ->where('is_active', true)
            ->take(10)
            ->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => "There are no active events at the moment.\n\nWould you like to **create a new event**?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        $contextMsg = $currentEvent
            ? "You're currently managing **{$currentEvent->name}**.\n\n"
            : "";

        $eventList = $events->map(function($event) use ($currentEvent) {
            $template = $event->template->name ?? 'General';
            $date = $event->event_date ? $event->event_date->format('M j, Y') : 'TBD';
            $current = ($currentEvent && $currentEvent->id === $event->id) ? ' ← current' : '';
            return "• **{$event->name}** ({$template}) - {$date}{$current}";
        })->join("\n");

        return response()->json([
            'message' => "{$contextMsg}Here are the active events:\n\n{$eventList}\n\nWould you like to see results for any of these?",
            'type' => 'text',
            'data' => $events,
        ]);
    }

    private function getAllEvents($currentEvent = null)
    {
        $events = Event::with(['template', 'votingType'])
            ->orderByDesc('is_active')
            ->orderByDesc('event_date')
            ->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => "There are no events in the system yet.\n\nWould you like to **create a new event**?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        $activeEvents = $events->where('is_active', true);
        $inactiveEvents = $events->where('is_active', false);

        $contextMsg = $currentEvent
            ? "You're currently managing **{$currentEvent->name}**.\n\n"
            : "";

        $message = "{$contextMsg}**All Events in System:** ({$events->count()} total)\n\n";

        // Active events
        if ($activeEvents->isNotEmpty()) {
            $message .= "### ✅ Active Events ({$activeEvents->count()}):\n";
            foreach ($activeEvents as $event) {
                $template = $event->template->name ?? 'General';
                $date = $event->event_date ? $event->event_date->format('M j, Y') : 'TBD';
                $votingType = $event->votingType->name ?? 'Not set';
                $participantCount = Participant::where('event_id', $event->id)->count();
                $entryCount = Entry::where('event_id', $event->id)->count();
                $voteCount = Vote::where('event_id', $event->id)->count();
                $current = ($currentEvent && $currentEvent->id === $event->id) ? ' ← **current**' : '';

                $message .= "• **{$event->name}**{$current}\n";
                $message .= "  - Type: {$template} | Voting: {$votingType} | Date: {$date}\n";
                $message .= "  - {$participantCount} participants, {$entryCount} entries, {$voteCount} votes\n";
            }
        }

        // Inactive/Draft events
        if ($inactiveEvents->isNotEmpty()) {
            $message .= "\n### 📝 Draft/Inactive Events ({$inactiveEvents->count()}):\n";
            foreach ($inactiveEvents as $event) {
                $template = $event->template->name ?? 'General';
                $date = $event->event_date ? $event->event_date->format('M j, Y') : 'TBD';
                $participantCount = Participant::where('event_id', $event->id)->count();
                $entryCount = Entry::where('event_id', $event->id)->count();

                $message .= "• **{$event->name}**\n";
                $message .= "  - Type: {$template} | Date: {$date}\n";
                $message .= "  - {$participantCount} participants, {$entryCount} entries\n";
            }
        }

        $message .= "\nWould you like to see details or results for any of these events?";

        return response()->json([
            'message' => $message,
            'type' => 'text',
            'data' => $events,
        ]);
    }

    private function getUpcomingEvents()
    {
        $events = Event::with('template')
            ->where('event_date', '>', now())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => "No upcoming events scheduled yet.\n\nWould you like to **create a new event**?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        $eventList = $events->map(function($event) {
            $date = $event->event_date ? $event->event_date->format('M j, Y') : 'TBD';
            return "• **{$event->name}** - {$date}";
        })->join("\n");

        return response()->json([
            'message' => "Upcoming events:\n\n{$eventList}",
            'type' => 'text',
        ]);
    }

    private function getResults($message, $currentEvent = null)
    {
        $messageLower = strtolower($message);

        // Check if user is asking for all/other events' results
        $showAll = str_contains($messageLower, 'all') ||
                   str_contains($messageLower, 'every') ||
                   str_contains($messageLower, 'each') ||
                   str_contains($messageLower, 'other') ||
                   str_contains($messageLower, 'different') ||
                   str_contains($messageLower, 'which event') ||
                   str_contains($messageLower, 'what event') ||
                   str_contains($messageLower, 'any event');

        // If showing all events, show comprehensive results
        if ($showAll) {
            return $this->getAllEventsResults($currentEvent);
        }

        // Check if user is asking for a SPECIFIC event's results by name
        $specificEvent = $this->findEventInMessage($message);
        if ($specificEvent) {
            return $this->getSingleEventResults($specificEvent);
        }

        // If no current event, show all results
        if (!$currentEvent) {
            return $this->getAllEventsResults($currentEvent);
        }

        // Show results for current event
        return $this->getSingleEventResults($currentEvent);
    }

    /**
     * Find an event mentioned in the user's message
     */
    private function findEventInMessage(string $message): ?Event
    {
        $messageLower = strtolower($message);

        // Get all events and check if any name is mentioned
        $events = Event::all();

        foreach ($events as $event) {
            $eventNameLower = strtolower($event->name);

            // Check for exact match or partial match
            if (str_contains($messageLower, $eventNameLower)) {
                return $event->load(['template', 'divisions']);
            }

            // Check for key words from event name (at least 2 significant words)
            $eventWords = array_filter(explode(' ', $eventNameLower), function($word) {
                return strlen($word) > 3 && !in_array($word, ['the', 'and', 'for', '2025', '2024', '2026']);
            });

            if (count($eventWords) >= 2) {
                $matchCount = 0;
                foreach ($eventWords as $word) {
                    if (str_contains($messageLower, $word)) {
                        $matchCount++;
                    }
                }
                // If at least 2 significant words match, consider it a match
                if ($matchCount >= 2) {
                    return $event->load(['template', 'divisions']);
                }
            }
        }

        return null;
    }

    /**
     * Get results from all events that have votes
     */
    private function getAllEventsResults($currentEvent = null)
    {
        // Get all events with votes
        $eventsWithVotes = Event::with(['template', 'divisions'])
            ->whereHas('votes')
            ->orderByDesc('is_active')
            ->orderByDesc('event_date')
            ->get();

        if ($eventsWithVotes->isEmpty()) {
            return response()->json([
                'message' => "No voting results found in any events yet.\n\nBe the first to vote!",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Show all events', 'prompt' => 'show all events'],
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        $message = "**Voting Results Summary:**\n\n";

        foreach ($eventsWithVotes as $event) {
            $voteCount = Vote::where('event_id', $event->id)->whereNull('deleted_at')->count();
            $entryLabel = $event->template->entry_label ?? 'Entry';
            $status = $event->is_active ? '✅' : '📝';

            $message .= "### {$status} {$event->name}\n";
            $message .= "*{$voteCount} votes cast*\n\n";

            // Get top 3 results for this event
            $results = DB::table('votes')
                ->select(
                    'entries.name as entry_name',
                    'participants.name as participant_name',
                    'divisions.name as division_name',
                    'divisions.code as division_code',
                    DB::raw('SUM(votes.final_points) as total_points')
                )
                ->join('entries', 'votes.entry_id', '=', 'entries.id')
                ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
                ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
                ->where('votes.event_id', $event->id)
                ->whereNull('votes.deleted_at')
                ->groupBy('entries.id', 'entries.name', 'participants.name', 'divisions.name', 'divisions.code')
                ->orderByDesc('total_points')
                ->take(3)
                ->get();

            if ($results->isNotEmpty()) {
                $rank = 1;
                foreach ($results as $r) {
                    $medal = match($rank) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => "{$rank}."
                    };
                    $participant = $r->participant_name ? " by {$r->participant_name}" : '';
                    $division = $r->division_code ? " [{$r->division_code}]" : '';
                    $message .= "{$medal} **{$r->entry_name}**{$participant}{$division} - {$r->total_points} pts\n";
                    $rank++;
                }
            } else {
                $message .= "*No results yet*\n";
            }

            $message .= "\n";
        }

        $message .= "---\n";
        $message .= "Say **\"results for [event name]\"** for detailed results of a specific event.";

        return response()->json([
            'message' => $message,
            'type' => 'text',
            'suggestedActions' => [
                ['label' => 'Show all events', 'prompt' => 'show all events'],
            ],
        ]);
    }

    /**
     * Get detailed results for a single event
     */
    private function getSingleEventResults(Event $event)
    {
        $event->load(['template', 'divisions']);
        $voteCount = Vote::where('event_id', $event->id)->whereNull('deleted_at')->count();

        if ($voteCount === 0) {
            $eventsWithVotes = Event::whereHas('votes')->with('template')->take(3)->get();

            if ($eventsWithVotes->isEmpty()) {
                return response()->json([
                    'message' => "No votes have been cast yet for **{$event->name}**.\n\nBe the first to vote!",
                    'type' => 'text',
                ]);
            }

            $otherEvents = $eventsWithVotes->map(fn($e) => "• **{$e->name}**")->join("\n");
            return response()->json([
                'message' => "No votes have been cast yet for **{$event->name}**.\n\nThese events have voting results:\n{$otherEvents}",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Show all results', 'prompt' => 'show all results'],
                ],
            ]);
        }

        $entryLabel = $event->template->entry_label ?? 'Entry';
        $participantLabel = $event->template->participant_label ?? 'Participant';

        $message = "**Results for {$event->name}** ({$voteCount} votes)\n\n";

        // Check if event has divisions and if they have meaningful groupings (division type)
        $divisionTypes = $event->divisions->pluck('type')->unique()->filter()->values();

        if ($divisionTypes->count() >= 2) {
            // Group by division TYPE (e.g., Professional vs Amateur)
            foreach ($divisionTypes as $divType) {
                $typeResults = DB::table('votes')
                    ->select(
                        'entries.name as entry_name',
                        'entries.entry_number',
                        'participants.name as participant_name',
                        'divisions.code as division_code',
                        DB::raw('SUM(votes.final_points) as total_points')
                    )
                    ->join('entries', 'votes.entry_id', '=', 'entries.id')
                    ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
                    ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
                    ->where('votes.event_id', $event->id)
                    ->where('divisions.type', $divType)
                    ->whereNull('votes.deleted_at')
                    ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'participants.name', 'divisions.code')
                    ->orderByDesc('total_points')
                    ->take(5)
                    ->get();

                if ($typeResults->isNotEmpty()) {
                    $message .= "### {$divType}\n";

                    $rank = 1;
                    foreach ($typeResults as $r) {
                        $medal = match($rank) {
                            1 => '🥇',
                            2 => '🥈',
                            3 => '🥉',
                            default => "{$rank}."
                        };
                        $participant = $r->participant_name ? " by {$r->participant_name}" : '';
                        $divCode = $r->division_code ? " [{$r->division_code}]" : '';
                        $message .= "{$medal} **{$r->entry_name}**{$participant}{$divCode} - {$r->total_points} pts\n";
                        $rank++;
                    }
                    $message .= "\n";
                }
            }
        } else {
            // No divisions - show all results
            $results = DB::table('votes')
                ->select(
                    'entries.name as entry_name',
                    'entries.entry_number',
                    'participants.name as participant_name',
                    DB::raw('SUM(votes.final_points) as total_points'),
                    DB::raw('COUNT(votes.id) as vote_count')
                )
                ->join('entries', 'votes.entry_id', '=', 'entries.id')
                ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
                ->where('votes.event_id', $event->id)
                ->whereNull('votes.deleted_at')
                ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'participants.name')
                ->orderByDesc('total_points')
                ->take(10)
                ->get();

            $rank = 1;
            foreach ($results as $r) {
                $medal = match($rank) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => "{$rank}."
                };
                $participant = $r->participant_name ? " by {$r->participant_name}" : '';
                $entryNum = $r->entry_number ? " (#{$r->entry_number})" : '';
                $message .= "{$medal} **{$r->entry_name}**{$participant}{$entryNum} - {$r->total_points} pts\n";
                $rank++;
            }
        }

        $message .= "\n---\n";
        $message .= "View full results at: [Results Page](/results/{$event->id})";

        return response()->json([
            'message' => $message,
            'type' => 'text',
            'event' => $event->name,
            'discussedEvent' => ['id' => $event->id, 'name' => $event->name],
            'suggestedActions' => [
                ['label' => 'Show all events results', 'prompt' => 'show all results'],
                ['label' => "Stats for {$event->name}", 'prompt' => "statistics for {$event->name}"],
            ],
        ]);
    }

    private function getVotingHelp($currentEvent = null)
    {
        $eventInfo = $currentEvent
            ? "\n\n**Current Event:** {$currentEvent->name}\nGo to the voting page for this event to cast your vote!"
            : "\n\nWould you like to see active events you can vote on?";

        return response()->json([
            'message' => "**How to Vote:**\n\n1. Go to an active event's voting page\n2. Enter the entry numbers for your choices (1st, 2nd, 3rd place)\n3. Click **Submit Vote**\n\n**Voting Rules:**\n• Each user can vote once per event\n• Points are awarded based on placement (e.g., 1st = 3pts, 2nd = 2pts, 3rd = 1pt)\n• Results update in real-time{$eventInfo}",
            'type' => 'text',
        ]);
    }

    private function getEntryInfo($message, $currentEvent = null)
    {
        $event = $currentEvent ?? Event::where('is_active', true)->first() ?? Event::latest()->first();

        if (!$event) {
            return response()->json([
                'message' => "No events found.",
                'type' => 'text',
            ]);
        }

        $entryCount = Entry::where('event_id', $event->id)->count();
        $participantCount = Participant::where('event_id', $event->id)->count();

        $entries = Entry::with('participant', 'division')
            ->where('event_id', $event->id)
            ->take(5)
            ->get();

        if ($entries->isEmpty()) {
            $label = $event->template->entry_label ?? 'entry';
            return response()->json([
                'message' => "**{$event->name}** doesn't have any entries yet.\n\nWould you like to add one?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => "Add an {$label}", 'prompt' => 'add an entry'],
                ],
            ]);
        }

        $entryList = $entries->map(function($e) {
            $participant = $e->participant->name ?? 'Unknown';
            $division = $e->division->name ?? '';
            return "• **{$e->name}** by {$participant}" . ($division ? " ({$division})" : '');
        })->join("\n");

        return response()->json([
            'message' => "**{$event->name}** has {$entryCount} entries from {$participantCount} participants.\n\nSample entries:\n{$entryList}\n\nWant to see the full list or results?",
            'type' => 'text',
        ]);
    }

    private function getParticipantInfo($message, $currentEvent = null)
    {
        $event = $currentEvent ?? Event::where('is_active', true)->first() ?? Event::latest()->first();

        if (!$event) {
            return response()->json([
                'message' => "No events found.",
                'type' => 'text',
            ]);
        }

        $label = $event->template->participant_label ?? 'Participant';
        $participants = Participant::where('event_id', $event->id)->take(10)->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'message' => "**{$event->name}** doesn't have any {$label}s yet.\n\nWould you like to add one?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => "Add a {$label}", 'prompt' => 'add a participant'],
                ],
            ]);
        }

        $list = $participants->map(function($p) {
            return "• **{$p->name}**" . ($p->email ? " ({$p->email})" : '');
        })->join("\n");

        $total = Participant::where('event_id', $event->id)->count();
        $moreText = $total > 10 ? "\n\n*... and " . ($total - 10) . " more*" : "";

        return response()->json([
            'message' => "**{$label}s in {$event->name}:**\n\n{$list}{$moreText}",
            'type' => 'text',
        ]);
    }

    private function getDivisionInfo($currentEvent = null)
    {
        $event = $currentEvent ?? Event::where('is_active', true)->first() ?? Event::latest()->first();

        if (!$event) {
            return response()->json([
                'message' => "No events found.",
                'type' => 'text',
            ]);
        }

        $divisions = Division::where('event_id', $event->id)->get();

        if ($divisions->isEmpty()) {
            return response()->json([
                'message' => "**{$event->name}** doesn't have any divisions yet.\n\nWould you like to add one?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Add a division', 'prompt' => 'add a division'],
                ],
            ]);
        }

        $list = $divisions->map(function($d) {
            $entryCount = Entry::where('division_id', $d->id)->count();
            return "• **{$d->name}** ({$d->code}) - {$entryCount} entries";
        })->join("\n");

        return response()->json([
            'message' => "**Divisions in {$event->name}:**\n\n{$list}",
            'type' => 'text',
        ]);
    }

    private function getVotingStats($message, $currentEvent = null)
    {
        // Check if user mentioned a specific event by name
        $specificEvent = $this->findEventInMessage($message);
        $event = $specificEvent ?? $currentEvent;

        if ($event) {
            $eventVotes = Vote::where('event_id', $event->id)->whereNull('deleted_at')->count();
            $eventEntries = Entry::where('event_id', $event->id)->count();
            $eventParticipants = Participant::where('event_id', $event->id)->count();

            $totalVotes = Vote::whereNull('deleted_at')->count();
            $totalEvents = Event::count();
            $activeEvents = Event::where('is_active', true)->count();

            return response()->json([
                'message' => "**Stats for {$event->name}:**\n\n• Votes Cast: **{$eventVotes}**\n• Entries: **{$eventEntries}**\n• Participants: **{$eventParticipants}**\n\n**Overall System Stats:**\n• Total Votes: **{$totalVotes}**\n• Total Events: **{$totalEvents}** ({$activeEvents} active)",
                'type' => 'text',
                'discussedEvent' => ['id' => $event->id, 'name' => $event->name],
            ]);
        }

        $totalVotes = Vote::whereNull('deleted_at')->count();
        $totalEvents = Event::count();
        $activeEvents = Event::where('is_active', true)->count();
        $totalEntries = Entry::count();
        $totalParticipants = Participant::count();

        return response()->json([
            'message' => "**Voting Statistics:**\n\n• Total Votes Cast: **{$totalVotes}**\n• Total Events: **{$totalEvents}** ({$activeEvents} active)\n• Total Entries: **{$totalEntries}**\n• Total Participants: **{$totalParticipants}**",
            'type' => 'text',
        ]);
    }

    private function getHelp($currentEvent = null)
    {
        $contextActions = $currentEvent
            ? "**Create (for {$currentEvent->name}):**\n" .
              "• \"Add a participant\" - Register someone new\n" .
              "• \"Add an entry\" - Create a new entry\n" .
              "• \"Add a division\" - Set up a division\n\n" .
              "**Update:**\n" .
              "• \"Update event\" - Change event details\n" .
              "• \"Update participant\" - Edit participant info\n" .
              "• \"Update entry\" - Modify entry details\n\n" .
              "**Delete:**\n" .
              "• \"Delete participant\" - Remove a participant\n" .
              "• \"Delete entry\" - Remove an entry\n\n"
            : "**Actions:**\n" .
              "• \"Create an event\" - Set up a new voting event\n" .
              "• \"Update event\" - Modify an existing event\n\n";

        return response()->json([
            'message' => "**I can help you with:**\n\n" .
                        $contextActions .
                        "**Information:**\n" .
                        "• \"What events are active?\"\n" .
                        "• \"Show me the results\"\n" .
                        "• \"How do I vote?\"\n" .
                        "• \"Show event types/templates\"\n" .
                        "• \"Show voting types\"\n" .
                        "• \"Show entries\" / \"Show participants\"\n" .
                        "• \"Voting statistics\"\n\n" .
                        "Just tell me what you'd like to do!",
            'type' => 'text',
        ]);
    }

    private function getEventTemplatesInfo()
    {
        $templates = EventTemplate::where('is_active', true)->get();

        if ($templates->isEmpty()) {
            return response()->json([
                'message' => "No event templates configured in the system.",
                'type' => 'text',
            ]);
        }

        $templateList = $templates->map(function($t) {
            $divisionTypes = $t->getDivisionTypes();
            $divisionText = '';
            if (!empty($divisionTypes)) {
                $divisionText = ' | Divisions: ' . collect($divisionTypes)->pluck('name')->join(', ');
            }
            $eventCount = Event::where('event_template_id', $t->id)->count();
            return "• **{$t->name}**\n" .
                   "  - {$t->participant_label}s create {$t->entry_label}s{$divisionText}\n" .
                   "  - {$eventCount} events using this template";
        })->join("\n\n");

        return response()->json([
            'message' => "**Available Event Types (Templates):**\n\n{$templateList}\n\n" .
                        "To create an event with any of these templates, say **\"create event\"**.",
            'type' => 'text',
        ]);
    }

    private function getVotingTypesInfo()
    {
        $votingTypes = VotingType::with('placeConfigs')->where('is_active', true)->get();

        if ($votingTypes->isEmpty()) {
            return response()->json([
                'message' => "No voting types configured in the system.",
                'type' => 'text',
            ]);
        }

        $vtList = $votingTypes->map(function($vt) {
            $places = $vt->placeConfigs;
            $pointsText = '';
            if ($places->isNotEmpty()) {
                $pointsText = $places->map(function($p) {
                    $ordinal = match($p->place) {
                        1 => '1st',
                        2 => '2nd',
                        3 => '3rd',
                        default => $p->place . 'th'
                    };
                    return "{$ordinal}={$p->points}pts";
                })->join(', ');
            }
            $eventCount = Event::where('voting_type_id', $vt->id)->count();
            return "• **{$vt->name}** ({$vt->code})\n" .
                   "  - Category: " . ucfirst($vt->category) . "\n" .
                   ($pointsText ? "  - Points: {$pointsText}\n" : '') .
                   "  - {$eventCount} events using this type";
        })->join("\n\n");

        return response()->json([
            'message' => "**Available Voting Types:**\n\n{$vtList}\n\n" .
                        "Each event can use a different voting type. The points system determines how winners are calculated.",
            'type' => 'text',
        ]);
    }

    /**
     * Handle "go back to management" - clear event context and return to main menu
     */
    private function handleClearEvent(?Event $currentEvent)
    {
        if (!$currentEvent) {
            return response()->json([
                'message' => "You're not currently managing any event.\n\n" .
                            "Would you like to select one or create a new event?",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Show all events', 'prompt' => 'show all events'],
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        return response()->json([
            'message' => "**Exited event management.**\n\n" .
                        "You were managing: {$currentEvent->name}\n\n" .
                        "You're now back to the main menu. What would you like to do?",
            'type' => 'clear_event',
            'clearEvent' => true,
            'redirectUrl' => '/admin/dashboard',
            'suggestedActions' => [
                ['label' => 'Show all events', 'prompt' => 'show all events'],
                ['label' => 'Create an event', 'prompt' => 'create event'],
                ['label' => 'View results', 'prompt' => 'show results'],
            ],
        ]);
    }

    /**
     * Handle "manage event <name>" - find event and switch context
     */
    private function handleManageEvent(string $message, ?Event $currentEvent)
    {
        // Extract event name from message
        // Patterns: "manage event X", "switch to event X", "manage X", etc.
        $patterns = [
            '/manage\s+event\s+(.+)/i',
            '/switch\s+to\s+event\s+(.+)/i',
            '/select\s+event\s+(.+)/i',
            '/work\s+on\s+event\s+(.+)/i',
            '/go\s+to\s+event\s+(.+)/i',
            '/open\s+event\s+(.+)/i',
            '/use\s+event\s+(.+)/i',
            '/switch\s+to\s+(.+)/i',      // "switch to X"
            '/manage\s+(.+)/i',            // "manage X" (direct event name)
        ];

        $eventName = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $eventName = trim($matches[1]);
                break;
            }
        }

        // If no event name extracted, show list of available events
        if (!$eventName) {
            $events = Event::with('template')
                ->orderByDesc('is_active')
                ->orderByDesc('event_date')
                ->take(10)
                ->get();

            if ($events->isEmpty()) {
                return response()->json([
                    'message' => "No events found in the system.\n\nWould you like to **create a new event**?",
                    'type' => 'text',
                    'suggestedActions' => [
                        ['label' => 'Create an event', 'prompt' => 'create event'],
                    ],
                ]);
            }

            $eventList = $events->map(function($e, $index) use ($currentEvent) {
                $status = $e->is_active ? '✅' : '📝';
                $current = ($currentEvent && $currentEvent->id === $e->id) ? ' ← current' : '';
                $type = $e->template->name ?? 'Event';
                return ($index + 1) . ". {$status} **{$e->name}**{$current}\n   *{$type}*";
            })->join("\n");

            // Create clickable actions for each event
            $eventActions = $events->map(function($e) {
                return [
                    'label' => $e->name,
                    'prompt' => "manage event {$e->name}",
                ];
            })->take(6)->values()->toArray();

            return response()->json([
                'message' => "**Select an event to manage:**\n\n{$eventList}\n\n" .
                            "Click an event below or say **\"manage [event name]\"**",
                'type' => 'event_list',
                'eventOptions' => $events->map(fn($e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'type' => $e->template->name ?? 'Event',
                    'isActive' => $e->is_active,
                ])->values()->toArray(),
                'suggestedActions' => $eventActions,
            ]);
        }

        // Search for the event by name (fuzzy match)
        $event = Event::with(['template', 'votingType', 'divisions'])
            ->where('name', 'like', "%{$eventName}%")
            ->first();

        // Try exact match if fuzzy didn't work
        if (!$event) {
            $event = Event::with(['template', 'votingType', 'divisions'])
                ->whereRaw('LOWER(name) = ?', [strtolower($eventName)])
                ->first();
        }

        if (!$event) {
            // Show similar events
            $similarEvents = Event::where('name', 'like', "%{$eventName}%")
                ->orWhere('name', 'like', '%' . substr($eventName, 0, 3) . '%')
                ->take(5)
                ->get();

            if ($similarEvents->isNotEmpty()) {
                $suggestions = $similarEvents->map(fn($e) => "• **{$e->name}**")->join("\n");
                return response()->json([
                    'message' => "I couldn't find an event named \"{$eventName}\".\n\n" .
                                "Did you mean one of these?\n{$suggestions}\n\n" .
                                "Say **\"manage event [exact name]\"** to switch.",
                    'type' => 'text',
                ]);
            }

            return response()->json([
                'message' => "I couldn't find an event named \"{$eventName}\".\n\n" .
                            "Say **\"show all events\"** to see available events.",
                'type' => 'text',
                'suggestedActions' => [
                    ['label' => 'Show all events', 'prompt' => 'show all events'],
                    ['label' => 'Create an event', 'prompt' => 'create event'],
                ],
            ]);
        }

        // Check if already managing this event
        if ($currentEvent && $currentEvent->id === $event->id) {
            return response()->json([
                'message' => "You're already managing **{$event->name}**!\n\n" .
                            "What would you like to do?",
                'type' => 'text',
                'suggestedActions' => $this->getSuggestedActions($event),
            ]);
        }

        // Build event info for switch confirmation
        $templateName = $event->template->name ?? 'General';
        $votingTypeName = $event->votingType->name ?? 'Not configured';
        $status = $event->is_active ? '✅ Active' : '📝 Draft';
        $date = $event->event_date ? $event->event_date->format('M j, Y') : 'No date set';

        $participantLabel = $event->template->participant_label ?? 'Participant';
        $entryLabel = $event->template->entry_label ?? 'Entry';
        $participantCount = Participant::where('event_id', $event->id)->count();
        $entryCount = Entry::where('event_id', $event->id)->count();
        $voteCount = Vote::where('event_id', $event->id)->count();
        $divisionCount = $event->divisions->count();

        return response()->json([
            'message' => "**Switched to: {$event->name}**\n\n" .
                        "- **Type:** {$templateName}\n" .
                        "- **Voting:** {$votingTypeName}\n" .
                        "- **Status:** {$status}\n" .
                        "- **Date:** {$date}\n\n" .
                        "**Current Stats:**\n" .
                        "- {$participantCount} {$participantLabel}s\n" .
                        "- {$entryCount} {$entryLabel}s\n" .
                        "- {$voteCount} votes\n" .
                        "- {$divisionCount} divisions\n\n" .
                        "The page will refresh to show this event.",
            'type' => 'event_switch',
            'switchToEvent' => [
                'id' => $event->id,
                'name' => $event->name,
                'url' => "/admin/events/{$event->id}",
                'refreshPage' => true,
            ],
            'suggestedActions' => $this->getSuggestedActions($event),
        ]);
    }

    /**
     * Transcribe audio using OpenAI Whisper
     */
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,mp4,m4a,wav,ogg|max:25600', // 25MB max (Whisper limit)
        ]);

        // Check if Whisper is available
        if (!$this->aiService->isWhisperAvailable()) {
            return response()->json([
                'success' => false,
                'error' => 'Voice transcription requires an OpenAI API key. Please configure OpenAI in AI Providers.',
            ], 400);
        }

        try {
            // Store the uploaded file temporarily
            $audioFile = $request->file('audio');
            $tempPath = $audioFile->store('temp', 'local');
            $fullPath = storage_path('app/' . $tempPath);

            // Transcribe using Whisper
            $result = $this->aiService->transcribeAudio($fullPath);

            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'text' => $result['text'],
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process audio: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if voice input (Whisper) is available
     */
    public function voiceStatus()
    {
        return response()->json([
            'available' => $this->aiService->isWhisperAvailable(),
        ]);
    }
}
