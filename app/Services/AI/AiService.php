<?php

namespace App\Services\AI;

use App\Models\Event;
use App\Models\Entry;
use App\Models\Vote;
use App\Models\AiConfig;
use App\Models\AiProvider;
use App\Models\AiKnowledgeDocument;
use App\Models\AiPromptTemplate;
use App\Models\AiTool;
use App\Services\AI\AiContextBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

/**
 * Multi-provider AI Service
 * Supports: OpenAI, Anthropic, Gemini, DeepSeek, Groq, Mistral, Grok
 */
class AiService
{
    protected ?AiConfig $config;
    protected ?AiProvider $provider;

    public function __construct()
    {
        $this->config = AiConfig::first();
        $this->provider = AiProvider::getSelected();
    }

    /**
     * Check if AI is configured and available
     */
    public function isAvailable(): bool
    {
        return $this->provider !== null && $this->provider->hasApiKey();
    }

    /**
     * Get the current provider
     */
    public function getProvider(): ?AiProvider
    {
        return $this->provider;
    }

    /**
     * Get the configured model
     */
    protected function getModel(): string
    {
        return $this->provider?->default_model ?? 'gpt-4o-mini';
    }

    /**
     * Get temperature setting
     */
    protected function getTemperature(): float
    {
        return $this->provider?->temperature ?? $this->config?->temperature ?? 0.7;
    }

    /**
     * Get max tokens setting
     */
    protected function getMaxTokens(): int
    {
        return $this->provider?->max_tokens ?? $this->config?->max_tokens ?? 1024;
    }

    /**
     * Generate an AI response for the given message
     * Routes to the appropriate provider
     */
    public function chat(string $message, ?Event $currentEvent = null, array $conversationHistory = []): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'AI service is not configured. Please configure an AI provider in the AI Config page.',
                'visualAids' => [],
            ];
        }

        // Build system prompt with context and knowledge
        $systemPrompt = $this->buildSystemPrompt($currentEvent, $message);

        // Route to appropriate provider
        $providerCode = $this->provider->code;

        try {
            return match($providerCode) {
                'openai', 'deepseek', 'groq', 'mistral', 'grok' => $this->callOpenAICompatible($systemPrompt, $message, $conversationHistory),
                'anthropic' => $this->callAnthropic($systemPrompt, $message, $conversationHistory),
                'gemini' => $this->callGemini($systemPrompt, $message, $conversationHistory),
                'huggingface' => $this->callHuggingFace($systemPrompt, $message, $conversationHistory),
                default => $this->callOpenAICompatible($systemPrompt, $message, $conversationHistory),
            };
        } catch (\Exception $e) {
            Log::error("AI Service Error ({$providerCode}): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your request. Please try again.',
                'visualAids' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call OpenAI-compatible APIs (OpenAI, DeepSeek, Groq, Mistral, Grok)
     */
    protected function callOpenAICompatible(string $systemPrompt, string $message, array $conversationHistory): array
    {
        $apiKey = $this->provider->getDecryptedApiKey();
        $baseUrl = $this->provider->api_base_url;
        $model = $this->getModel();

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

        // Make API call
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("{$baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $this->getTemperature(),
            'max_tokens' => $this->getMaxTokens(),
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("API Error: {$error}");
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? null;

        // Extract visual aids from response
        $visualAids = $this->extractVisualAids($content);

        return [
            'success' => true,
            'message' => $content,
            'visualAids' => $visualAids,
            'tokens_used' => $tokensUsed,
            'provider' => $this->provider->name,
            'model' => $model,
        ];
    }

    /**
     * Call Anthropic Claude API
     */
    protected function callAnthropic(string $systemPrompt, string $message, array $conversationHistory): array
    {
        $apiKey = $this->provider->getDecryptedApiKey();
        $model = $this->getModel();

        // Build messages array (Anthropic format - no system in messages)
        $messages = [];

        // Add conversation history (last 10 messages)
        foreach (array_slice($conversationHistory, -10) as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        // Make API call with Anthropic-specific headers
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $this->getMaxTokens(),
            'system' => $systemPrompt,
            'messages' => $messages,
            'temperature' => $this->getTemperature(),
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Anthropic API Error: {$error}");
        }

        $data = $response->json();
        $content = $data['content'][0]['text'] ?? '';
        $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

        // Extract visual aids from response
        $visualAids = $this->extractVisualAids($content);

        return [
            'success' => true,
            'message' => $content,
            'visualAids' => $visualAids,
            'tokens_used' => $tokensUsed,
            'provider' => $this->provider->name,
            'model' => $model,
        ];
    }

    /**
     * Call Google Gemini API
     */
    protected function callGemini(string $systemPrompt, string $message, array $conversationHistory): array
    {
        $apiKey = $this->provider->getDecryptedApiKey();
        $model = $this->getModel();

        // Build contents array for Gemini
        $contents = [];

        // Gemini handles system instruction separately
        // Add conversation history
        foreach (array_slice($conversationHistory, -10) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Add current message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        // Make API call
        $baseUrl = $this->provider->api_base_url;
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig' => [
                'temperature' => $this->getTemperature(),
                'maxOutputTokens' => $this->getMaxTokens(),
            ],
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Gemini API Error: {$error}");
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? null;

        // Extract visual aids from response
        $visualAids = $this->extractVisualAids($content);

        return [
            'success' => true,
            'message' => $content,
            'visualAids' => $visualAids,
            'tokens_used' => $tokensUsed,
            'provider' => $this->provider->name,
            'model' => $model,
        ];
    }

    /**
     * Call Hugging Face Inference API
     */
    protected function callHuggingFace(string $systemPrompt, string $message, array $conversationHistory): array
    {
        $apiKey = $this->provider->getDecryptedApiKey();
        $model = $this->provider->default_model;

        // Build messages array (chat format)
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add conversation history
        foreach (array_slice($conversationHistory, -10) as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        // Use Hugging Face's OpenAI-compatible chat endpoint
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("https://api-inference.huggingface.co/models/{$model}/v1/chat/completions", [
            'messages' => $messages,
            'max_tokens' => $this->getMaxTokens(),
            'temperature' => $this->getTemperature(),
            'stream' => false,
        ]);

        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            if (is_array($error)) {
                $error = $error['message'] ?? json_encode($error);
            }
            throw new \Exception("Hugging Face API Error: {$error}");
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? null;

        // Extract visual aids from response
        $visualAids = $this->extractVisualAids($content);

        return [
            'success' => true,
            'message' => $content,
            'visualAids' => $visualAids,
            'tokens_used' => $tokensUsed,
            'provider' => $this->provider->name,
            'model' => $model,
        ];
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

## CRITICAL: Conversation Context Rules
**PAY CLOSE ATTENTION TO CONVERSATION HISTORY!**
- If the user just asked about a specific event (e.g., "results of Summer Photography Contest"), follow-up questions like "show statistics" or "who won" should refer to THAT event, not the "Current Event" from context.
- The "Current Event" section shows what event is selected in the UI, but conversation context takes priority.
- When a user asks about a specific event by name, remember it for follow-up questions.
- Use your intelligence to understand what the user means based on the flow of conversation.

## CRITICAL: "What event am I managing?" Response Rule
When the user asks "what event am I managing?", "which event is this?", "current event?", or similar questions about what they are currently working on:
- Look at the "Current Event Details" section in the context
- Answer CONCISELY with just the current event name and basic info
- Do NOT list all events in the system
- Example response: "You're currently managing **The Great Bakeoff 2025** - a Food Competition with 20 entries and 20 votes."
- If no current event is set, say "You don't have an event selected. Would you like to select one or create a new event?"

## Response Format Guidelines:
- Use markdown formatting for better readability
- Use **bold** for important information
- Use bullet points for lists
- Keep responses concise but informative
- Be friendly and helpful

## What You CAN Do:
- **Events:** Answer questions about ANY event in the system (see Voting Results Summary)
- **Results:** Provide voting results, statistics, rankings for any event
- **Participants:** Answer questions about participants in any event
- **Entries:** Answer questions about entries/submissions in any event
- **Information:** Event types, voting types, templates, statistics

## What You CANNOT Do:
- **User Management:** You CANNOT add, modify, or delete system user accounts
- **Create/Update/Delete:** For these actions, tell the user to use commands like "create event", "add participant", etc.

## Important Rules:
- For data queries, provide accurate information from the Voting Results Summary and Events data
- Never make up data - only reference what's in the context
- Use the correct labels based on event type (Chef vs Photographer, Dish vs Photo, etc.)
- When answering about statistics, use the data provided in context for that specific event

PROMPT;
    }

    /**
     * Extract visual aids from AI response
     */
    protected function extractVisualAids(string $content): array
    {
        $visualAids = [];
        $order = 0;

        // Extract step-by-step content
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
                    'content' => ['steps' => $steps, 'showProgress' => true],
                    'order' => $order++,
                ];
            }
        }

        // Extract numbered lists if 3+ items
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
                    'content' => ['steps' => $steps, 'showProgress' => false],
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
                    'content' => ['stats' => $stats],
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
            'create event', 'new event', 'add event',
            'add participant', 'new participant', 'register',
            'add entry', 'new entry', 'submit entry',
            'add division', 'new division',
            'update event', 'edit event', 'modify event',
            'update participant', 'edit participant',
            'update entry', 'edit entry',
            'activate event', 'deactivate event',
            'delete', 'remove',
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

    /**
     * Test connection to a specific provider
     */
    public static function testProviderConnection(AiProvider $provider): array
    {
        $apiKey = $provider->getDecryptedApiKey();
        if (!$apiKey) {
            return ['success' => false, 'error' => 'No API key configured'];
        }

        try {
            $response = match($provider->code) {
                'openai' => self::testOpenAI($apiKey),
                'anthropic' => self::testAnthropic($apiKey),
                'gemini' => self::testGemini($apiKey, $provider->api_base_url),
                'deepseek' => self::testOpenAICompatible($apiKey, $provider->api_base_url),
                'groq' => self::testOpenAICompatible($apiKey, $provider->api_base_url),
                'mistral' => self::testOpenAICompatible($apiKey, $provider->api_base_url),
                'grok' => self::testOpenAICompatible($apiKey, $provider->api_base_url),
                default => self::testOpenAICompatible($apiKey, $provider->api_base_url),
            };

            return $response;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected static function testOpenAI(string $apiKey): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->timeout(10)->get('https://api.openai.com/v1/models');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Connected successfully to OpenAI'];
        }

        $error = $response->json('error.message') ?? 'Unknown error';
        return ['success' => false, 'error' => $error];
    }

    protected static function testAnthropic(string $apiKey): array
    {
        // Anthropic doesn't have a models endpoint, so we make a minimal completion request
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(10)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 10,
            'messages' => [['role' => 'user', 'content' => 'Hi']],
        ]);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Connected successfully to Anthropic'];
        }

        $error = $response->json('error.message') ?? 'Unknown error';
        return ['success' => false, 'error' => $error];
    }

    protected static function testGemini(string $apiKey, string $baseUrl): array
    {
        $response = Http::timeout(10)->get("{$baseUrl}/models?key={$apiKey}");

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Connected successfully to Google Gemini'];
        }

        $error = $response->json('error.message') ?? 'Unknown error';
        return ['success' => false, 'error' => $error];
    }

    protected static function testOpenAICompatible(string $apiKey, string $baseUrl): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->timeout(10)->get("{$baseUrl}/models");

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Connected successfully'];
        }

        $error = $response->json('error.message') ?? $response->json('error') ?? 'Unknown error';
        return ['success' => false, 'error' => is_array($error) ? json_encode($error) : $error];
    }

    /**
     * Transcribe audio using OpenAI Whisper API
     *
     * @param string $audioPath Path to the audio file
     * @param string $language Optional language code (e.g., 'en')
     * @return array ['success' => bool, 'text' => string, 'error' => string|null]
     */
    public function transcribeAudio(string $audioPath, string $language = 'en'): array
    {
        // Whisper is OpenAI-specific, so we need the OpenAI provider
        $openaiProvider = AiProvider::where('code', 'openai')->first();

        if (!$openaiProvider || !$openaiProvider->hasApiKey()) {
            return [
                'success' => false,
                'text' => '',
                'error' => 'OpenAI API key is required for voice transcription. Please configure OpenAI in AI Providers.',
            ];
        }

        $apiKey = $openaiProvider->getDecryptedApiKey();

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])
            ->timeout(30)
            ->attach(
                'file',
                file_get_contents($audioPath),
                basename($audioPath)
            )
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => $language,
                'response_format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'text' => $data['text'] ?? '',
                    'error' => null,
                ];
            }

            $error = $response->json('error.message') ?? 'Transcription failed';
            Log::error('Whisper API error', ['error' => $error, 'status' => $response->status()]);

            return [
                'success' => false,
                'text' => '',
                'error' => $error,
            ];

        } catch (\Exception $e) {
            Log::error('Whisper transcription error', ['exception' => $e->getMessage()]);

            return [
                'success' => false,
                'text' => '',
                'error' => 'Failed to transcribe audio: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if Whisper transcription is available
     */
    public function isWhisperAvailable(): bool
    {
        $openaiProvider = AiProvider::where('code', 'openai')->first();
        return $openaiProvider && $openaiProvider->hasApiKey();
    }
}
