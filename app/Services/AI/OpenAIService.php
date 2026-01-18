<?php

namespace App\Services\AI;

use App\Models\Event;
use App\Models\Entry;
use App\Models\Vote;
use App\Models\AiConfig;
use App\Models\AiKnowledgeDocument;
use App\Models\AiPromptTemplate;
use App\Models\AiTool;
use App\Services\AI\AiContextBuilder;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OpenAIService
{
    protected $client;
    protected ?AiConfig $config;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');

        if ($apiKey) {
            $this->client = OpenAI::client($apiKey);
        }

        // Load config from database (or use defaults)
        $this->config = AiConfig::first();
    }

    /**
     * Check if OpenAI is configured and available
     */
    public function isAvailable(): bool
    {
        return $this->client !== null;
    }

    /**
     * Get the configured model
     */
    protected function getModel(): string
    {
        return $this->config?->default_model ?? config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Get temperature setting
     */
    protected function getTemperature(): float
    {
        return $this->config?->temperature ?? 0.7;
    }

    /**
     * Get max tokens setting
     */
    protected function getMaxTokens(): int
    {
        return $this->config?->max_tokens ?? 1024;
    }

    /**
     * Generate an AI response for the given message
     */
    public function chat(string $message, ?Event $currentEvent = null, array $conversationHistory = []): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'AI service is not configured.',
                'visualAids' => [],
            ];
        }

        try {
            // Build system prompt with context and knowledge
            $systemPrompt = $this->buildSystemPrompt($currentEvent, $message);

            // Build messages array
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            // Add conversation history (last 10 messages)
            foreach (array_slice($conversationHistory, -10) as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }

            // Add current message
            $messages[] = ['role' => 'user', 'content' => $message];

            // Make API call with database config
            $response = $this->client->chat()->create([
                'model' => $this->getModel(),
                'messages' => $messages,
                'temperature' => $this->getTemperature(),
                'max_tokens' => $this->getMaxTokens(),
            ]);

            $content = $response->choices[0]->message->content;
            $tokensUsed = $response->usage->totalTokens ?? null;

            // Extract visual aids from response
            $visualAids = $this->extractVisualAids($content);

            return [
                'success' => true,
                'message' => $content,
                'visualAids' => $visualAids,
                'tokens_used' => $tokensUsed,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your request.',
                'visualAids' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build a context-aware system prompt
     */
    protected function buildSystemPrompt(?Event $currentEvent, string $userMessage = ''): string
    {
        // Start with base prompt from template or default
        $basePrompt = $this->getBasePrompt();

        // Add available tools/commands
        $basePrompt .= AiTool::buildToolsDescription($currentEvent !== null);

        // Add relevant knowledge based on user message
        $knowledgeContext = AiKnowledgeDocument::buildContext($userMessage);
        if ($knowledgeContext) {
            $basePrompt .= $knowledgeContext;
        }

        // Add comprehensive context using AiContextBuilder
        // This includes ALL events, event types, voting types, and current event details
        $basePrompt .= AiContextBuilder::buildFullContext($currentEvent);

        return $basePrompt;
    }

    /**
     * Get base prompt from template or default
     */
    protected function getBasePrompt(): string
    {
        $template = AiPromptTemplate::getDefault('general');

        if ($template) {
            $prompt = $template->system_prompt;
            if ($template->instructions) {
                $prompt .= "\n\n## Instructions:\n" . $template->instructions;
            }
            return $prompt . "\n";
        }

        // Fallback default prompt
        return <<<PROMPT
You are a helpful AI assistant for a voting and competition management system. You help users manage voting events, participants (competitors like Chefs, Photographers, etc.), entries (submissions like Dishes, Photos, etc.), and view results.

## Response Format Guidelines:
- Use markdown formatting for better readability
- Use **bold** for important information
- Use bullet points for lists
- Keep responses concise but informative
- Be friendly and helpful

## What You CAN Do:
- **Events:** Create, update, activate/deactivate events
- **Participants:** Add, update, delete competitors (Chefs, Photographers, Performers, etc.)
- **Entries:** Add, update, delete submissions (Dishes, Photos, Performances, etc.)
- **Divisions:** Add divisions/categories to events
- **Information:** Answer questions about events, results, voting types, templates, statistics

## What You CANNOT Do:
- **User Management:** You CANNOT add, modify, or delete system user accounts. User management is done through the admin panel only.
- When asked about user management, explain that this is handled in the admin panel for security reasons.

## Important Rules:
- If asked to create, add, update, or delete data, tell the user to use the appropriate command (e.g., "Say 'create event' to start", "Say 'delete participant' to remove a competitor")
- For data queries, provide accurate information from the system context provided
- If you don't have specific data, say so clearly
- Never make up data or statistics - only reference what's in the context
- Always use the correct labels (Chef vs Participant, Entry vs Dish) based on the event template

PROMPT;
    }

    /**
     * Build event context for prompt
     */
    protected function buildEventContext(Event $currentEvent): string
    {
        $currentEvent->load(['template', 'divisions', 'participants']);

        $participantLabel = $currentEvent->template->participant_label ?? 'Participant';
        $entryLabel = $currentEvent->template->entry_label ?? 'Entry';

        // Get statistics
        $participantCount = $currentEvent->participants()->count();
        $entryCount = Entry::where('event_id', $currentEvent->id)->count();
        $voteCount = Vote::where('event_id', $currentEvent->id)->count();
        $divisionCount = $currentEvent->divisions->count();

        // Get divisions list
        $divisionsList = $currentEvent->divisions->map(fn($d) => "- {$d->name} ({$d->code})")->join("\n");
        if (empty($divisionsList)) {
            $divisionsList = "No divisions configured yet.";
        }

        // Get top entries if votes exist
        $topEntriesText = '';
        if ($voteCount > 0) {
            $topEntries = DB::table('votes')
                ->select('entries.name', DB::raw('SUM(votes.final_points) as total'))
                ->join('entries', 'votes.entry_id', '=', 'entries.id')
                ->where('votes.event_id', $currentEvent->id)
                ->groupBy('entries.id', 'entries.name')
                ->orderByDesc('total')
                ->limit(3)
                ->get();

            if ($topEntries->count() > 0) {
                $topEntriesText = "\n### Current Top Entries:\n";
                foreach ($topEntries as $i => $entry) {
                    $rank = $i + 1;
                    $topEntriesText .= "{$rank}. {$entry->name} - {$entry->total} points\n";
                }
            }
        }

        $templateName = $currentEvent->template->name ?? 'General';
        $eventStatus = $currentEvent->is_active ? 'Active' : 'Draft';

        return <<<CONTEXT

## Current Event Context:
**Event:** {$currentEvent->name}
**Template:** {$templateName}
**Status:** {$eventStatus}

### Labels for this event:
- Participants are called: **{$participantLabel}s**
- Submissions are called: **{$entryLabel}s**

### Current Statistics:
- {$participantLabel}s: {$participantCount}
- {$entryLabel}s: {$entryCount}
- Votes Cast: {$voteCount}
- Divisions: {$divisionCount}

### Divisions:
{$divisionsList}
{$topEntriesText}

Use this context to provide relevant, accurate responses. Reference actual data when answering questions.
CONTEXT;
    }

    /**
     * Get context when no event is selected
     */
    protected function getNoEventContext(): string
    {
        return <<<NOCONTEXT

## Current Context:
No event is currently selected. The user should select an event to manage or create a new one.

Suggest:
- "Create an event" to start a new voting event
- "Show active events" to see available events
NOCONTEXT;
    }

    /**
     * Extract visual aids from AI response
     */
    protected function extractVisualAids(string $content): array
    {
        $visualAids = [];
        $order = 0;

        // Extract step-by-step content (Step 1:, Step 2:, etc.)
        if (preg_match_all('/(?:Step|STEP)\s*(\d+)[:\s]*([^\n]+)(?:\n([^S]*))?/i', $content, $matches, PREG_SET_ORDER)) {
            if (count($matches) >= 2) {
                $steps = [];
                foreach ($matches as $match) {
                    $steps[] = [
                        'number' => (int) $match[1],
                        'title' => trim($match[2]),
                        'content' => isset($match[3]) ? trim($match[3]) : '',
                    ];
                }
                $visualAids[] = [
                    'id' => 'steps-' . $order,
                    'type' => 'stepCard',
                    'content' => [
                        'steps' => $steps,
                        'showProgress' => true,
                    ],
                    'order' => $order++,
                ];
            }
        }

        // Extract numbered lists (1. 2. 3.) if 3+ items
        if (empty($visualAids) && preg_match_all('/^\s*(\d+)\.\s+(.+)$/m', $content, $matches, PREG_SET_ORDER)) {
            if (count($matches) >= 3) {
                $steps = [];
                foreach ($matches as $match) {
                    $steps[] = [
                        'number' => (int) $match[1],
                        'title' => trim($match[2]),
                        'content' => '',
                    ];
                }
                $visualAids[] = [
                    'id' => 'list-' . $order,
                    'type' => 'stepCard',
                    'content' => [
                        'steps' => $steps,
                        'showProgress' => false,
                    ],
                    'order' => $order++,
                ];
            }
        }

        // Extract statistics blocks
        if (preg_match_all('/\*\*([^*]+)\*\*:\s*(\d+)/i', $content, $matches, PREG_SET_ORDER)) {
            if (count($matches) >= 2) {
                $stats = [];
                foreach ($matches as $match) {
                    $stats[] = [
                        'label' => trim($match[1]),
                        'value' => (int) $match[2],
                    ];
                }
                $visualAids[] = [
                    'id' => 'stats-' . $order,
                    'type' => 'statsCard',
                    'content' => [
                        'stats' => $stats,
                    ],
                    'order' => $order++,
                ];
            }
        }

        return $visualAids;
    }

    /**
     * Detect if message should be handled by rules vs AI
     */
    public function shouldUseRules(string $message): bool
    {
        $rulesPatterns = [
            // Action patterns that should trigger wizards
            'create event', 'create an event', 'new event', 'add event', 'start event',
            'add participant', 'add a participant', 'new participant', 'register',
            'add entry', 'add an entry', 'new entry', 'submit entry',
            'add division', 'add a division', 'new division', 'create division',
            // Update patterns
            'update event', 'edit event', 'change event', 'modify event',
            'update participant', 'edit participant',
            'update entry', 'edit entry',
            'activate event', 'deactivate event',
            // Delete patterns
            'delete', 'remove',
            // Cancel/navigation
            'cancel', 'skip', 'yes', 'no', 'confirm',
        ];

        $messageLower = strtolower($message);

        foreach ($rulesPatterns as $pattern) {
            if (str_contains($messageLower, $pattern)) {
                return true;
            }
        }

        // Short numeric inputs are likely wizard responses
        if (is_numeric(trim($message)) && strlen(trim($message)) <= 3) {
            return true;
        }

        return false;
    }

    /**
     * Get AI configuration
     */
    public function getConfig(): ?AiConfig
    {
        return $this->config;
    }
}
