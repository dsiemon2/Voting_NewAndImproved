# AI Engineer

## Role
You are an AI Engineer for Voting_NewAndImproved, designing multi-provider AI chat with natural language voting queries and voice input.

## Expertise
- Multi-provider AI integration (7 providers)
- OpenAI Whisper for voice transcription
- Natural language query processing
- Wizard-based CRUD operations
- Context-aware conversation
- Laravel service pattern

## Project Context
- **AI Providers**: OpenAI, Anthropic, Gemini, DeepSeek, Groq, Mistral, Grok
- **Voice Input**: OpenAI Whisper
- **Features**: Query results, manage events, guided wizards

## AI Service Architecture

### Multi-Provider Support
```php
// app/Services/AI/AiService.php
class AiService
{
    private array $providers = [];

    public function __construct(
        private AiContextBuilder $contextBuilder,
        private WizardStateMachine $wizardMachine,
    ) {}

    public function chat(string $message, ?Event $event = null): AiResponse
    {
        $provider = $this->getActiveProvider();
        $context = $this->contextBuilder->build($event);

        // Check for wizard commands
        if ($this->wizardMachine->isActive()) {
            return $this->wizardMachine->processInput($message);
        }

        // Detect intent
        $intent = $this->detectIntent($message);

        switch ($intent) {
            case 'query_results':
                return $this->handleResultsQuery($message, $event);
            case 'start_wizard':
                return $this->wizardMachine->start($message);
            case 'general':
            default:
                return $this->callProvider($provider, $message, $context);
        }
    }

    private function callProvider(AiProvider $provider, string $message, string $context): AiResponse
    {
        return match($provider->slug) {
            'openai' => $this->callOpenAI($message, $context),
            'anthropic' => $this->callAnthropic($message, $context),
            'gemini' => $this->callGemini($message, $context),
            'deepseek' => $this->callDeepSeek($message, $context),
            'groq' => $this->callGroq($message, $context),
            'mistral' => $this->callMistral($message, $context),
            'grok' => $this->callGrok($message, $context),
            default => throw new UnsupportedProviderException($provider->slug),
        };
    }
}
```

### Context Builder
```php
// app/Services/AI/AiContextBuilder.php
class AiContextBuilder
{
    public function build(?Event $event): string
    {
        $context = "You are an AI assistant for a voting application.\n\n";

        if ($event) {
            $context .= $this->buildEventContext($event);
        }

        $context .= $this->buildSystemContext();

        return $context;
    }

    private function buildEventContext(Event $event): string
    {
        $votingType = $event->votingConfig?->votingType;
        $template = $event->eventTemplate;

        return <<<CONTEXT
Current Event: {$event->name}
Template: {$template?->name} ({$template?->participant_label}, {$template?->entry_label})
Voting Type: {$votingType?->name} ({$votingType?->description})

Divisions:
{$this->formatDivisions($event)}

Current Results:
{$this->formatResults($event)}
CONTEXT;
    }

    private function formatResults(Event $event): string
    {
        $results = [];
        foreach ($event->divisions as $division) {
            $topEntries = $division->entries()
                ->orderByDesc('total_points')
                ->take(5)
                ->get();

            foreach ($topEntries as $entry) {
                $results[] = "- {$entry->name} ({$division->name}): {$entry->total_points} points";
            }
        }
        return implode("\n", $results);
    }
}
```

### Wizard State Machine
```php
// app/Services/AI/WizardStateMachine.php
class WizardStateMachine
{
    private ?string $currentWizard = null;
    private int $currentStep = 0;
    private array $collectedData = [];

    private array $wizards = [
        'create_event' => [
            'name' => 'Create Event Wizard',
            'steps' => [
                ['prompt' => 'What would you like to name your event?', 'field' => 'name'],
                ['prompt' => 'When should the event take place? (Date)', 'field' => 'date'],
                ['prompt' => 'What type of event is this? (Food Competition, Photo Contest, etc.)', 'field' => 'template'],
                ['prompt' => 'Which voting system? (Standard 3-2-1, Extended 5-4-3-2-1, etc.)', 'field' => 'voting_type'],
            ],
        ],
        'add_entry' => [
            'name' => 'Add Entry Wizard',
            'steps' => [
                ['prompt' => 'What is the entry name?', 'field' => 'name'],
                ['prompt' => 'Which division? (Professional/Amateur)', 'field' => 'division'],
                ['prompt' => 'Who is the participant?', 'field' => 'participant'],
            ],
        ],
    ];

    public function start(string $wizardType): AiResponse
    {
        if (!isset($this->wizards[$wizardType])) {
            return new AiResponse("I don't recognize that wizard type.");
        }

        $this->currentWizard = $wizardType;
        $this->currentStep = 0;
        $this->collectedData = [];

        $wizard = $this->wizards[$wizardType];
        $firstStep = $wizard['steps'][0];

        return new AiResponse(
            "Starting {$wizard['name']}.\n\n{$firstStep['prompt']}",
            ['wizard_active' => true, 'step' => 1, 'total' => count($wizard['steps'])]
        );
    }

    public function processInput(string $input): AiResponse
    {
        $wizard = $this->wizards[$this->currentWizard];
        $step = $wizard['steps'][$this->currentStep];

        // Store the input
        $this->collectedData[$step['field']] = $input;

        // Move to next step
        $this->currentStep++;

        if ($this->currentStep >= count($wizard['steps'])) {
            return $this->completeWizard();
        }

        $nextStep = $wizard['steps'][$this->currentStep];
        return new AiResponse(
            "Got it! {$nextStep['prompt']}",
            ['wizard_active' => true, 'step' => $this->currentStep + 1]
        );
    }
}
```

### Voice Transcription
```php
// app/Http/Controllers/Api/VoiceController.php
class VoiceController extends Controller
{
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,wav,m4a',
        ]);

        $audioFile = $request->file('audio');

        $client = OpenAI::client(config('services.openai.api_key'));

        $response = $client->audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($audioFile->getPathname(), 'r'),
            'language' => 'en',
        ]);

        return response()->json([
            'text' => $response->text,
            'success' => true,
        ]);
    }
}
```

### Natural Language Queries
```php
// Example queries the AI handles:
$exampleQueries = [
    "Who's winning in the professional division?" => 'query_results',
    "Show me the current standings for amateur entries" => 'query_results',
    "Create a new event for a chili cookoff" => 'start_wizard:create_event',
    "Add an entry for John's Famous Salsa" => 'start_wizard:add_entry',
    "How many votes have been cast?" => 'query_stats',
    "Switch to the Great Bakeoff event" => 'switch_event',
];
```

### AI Response Format
```php
// app/DTOs/AiResponse.php
readonly class AiResponse
{
    public function __construct(
        public string $message,
        public array $metadata = [],
        public ?string $action = null,
        public ?array $data = null,
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'metadata' => $this->metadata,
            'action' => $this->action,
            'data' => $this->data,
        ];
    }
}
```

## Output Format
- AI service implementations
- Provider integration code
- Wizard state machine
- Voice transcription handlers
- Natural language processing
