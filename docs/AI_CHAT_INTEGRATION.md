# AI Chat Integration Guide

## Overview

The Voting Application includes an AI-powered chat assistant that helps users manage events, query data, and perform common tasks through natural language. The system uses a **hybrid approach**: rule-based handlers for CRUD operations and AI for complex queries.

## Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        AI Chat System                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────────────┐  │
│  │  Chat Slider │───▶│AiChatController───▶│  Intent Detection  │  │
│  │  (Frontend)  │    │   (Router)    │    │  (Pattern Matching) │  │
│  └──────────────┘    └──────────────┘    └──────────┬───────────┘  │
│         │                                           │               │
│         │ Voice                  ┌──────────────────┴───────────┐  │
│         ▼                        ▼                              ▼  │
│  ┌──────────────┐      ┌─────────────────┐          ┌───────────┐ │
│  │   Whisper    │      │  Rule Handlers  │          │ AiService │ │
│  │ Transcription│      │  (Wizards/CRUD) │          │ (AI Query)│ │
│  └──────────────┘      └─────────────────┘          └───────────┘ │
│                                                           │        │
│                    ┌──────────────────────────────────────┘        │
│                    ▼                                                │
│         ┌────────────────────────────────────────────────┐         │
│         │              Multi-Provider Support            │         │
│         ├────────────────────────────────────────────────┤         │
│         │ OpenAI │ Anthropic │ Gemini │ DeepSeek │ Groq  │         │
│         │ Mistral │ Grok                                 │         │
│         └────────────────────────────────────────────────┘         │
└─────────────────────────────────────────────────────────────────────┘
```

## Key Components

### 1. Frontend (Chat Slider)
**File**: `resources/views/components/ai-chat-slider.blade.php`
- Floating chat widget with toggle button
- Voice input using OpenAI Whisper API
- Visual aids rendering (stats cards, step cards, rankings)
- Event switching with page refresh
- Conversation history tracking

### 2. Controller
**File**: `app/Http/Controllers/Api/AiChatController.php`
- Routes messages to appropriate handlers
- Intent detection using pattern matching
- Wizard state machine for multi-step operations
- Context-aware event detection in messages

### 3. AI Service
**File**: `app/Services/AI/AiService.php`
- Multi-provider support (7 providers)
- System prompt generation with context
- Whisper transcription for voice input
- Provider connection testing

### 4. Context Builder
**File**: `app/Services/AI/AiContextBuilder.php`
- Builds comprehensive system context
- Event templates and voting types
- All events summary with statistics
- Current event detailed context
- Voting results for all events

## Supported AI Providers

| Provider | API Type | Models |
|----------|----------|--------|
| OpenAI | OpenAI | gpt-4o, gpt-4o-mini, gpt-4-turbo |
| Anthropic | Anthropic | claude-3-5-sonnet, claude-3-opus |
| Google Gemini | Gemini | gemini-1.5-pro, gemini-1.5-flash |
| DeepSeek | OpenAI-compatible | deepseek-chat, deepseek-coder |
| Groq | OpenAI-compatible | llama-3.1-70b, mixtral-8x7b |
| Mistral | OpenAI-compatible | mistral-large, mistral-medium |
| Grok (xAI) | OpenAI-compatible | grok-beta |

## Configuration

### Admin UI
Navigate to `/admin/ai-providers` to:
- Enable/disable providers (Toggle = Available for use)
- Select active provider (Radio = Currently active for queries)
- Configure API keys (encrypted storage)
- Set default model and temperature
- Test provider connections

### Database Tables
```sql
ai_providers
├── id, name, code
├── api_key (encrypted)
├── api_base_url
├── default_model
├── temperature, max_tokens
├── is_enabled, is_selected
└── created_at, updated_at

ai_configs
├── id
├── default_provider_id
├── temperature, max_tokens
└── settings (JSON)
```

## Voice Input (OpenAI Whisper)

### How It Works
1. User clicks microphone button
2. Browser requests microphone permission
3. Audio recorded using MediaRecorder API (WebM/M4A)
4. Audio blob sent to `/api/ai-chat/transcribe`
5. Laravel forwards to OpenAI Whisper API
6. Transcribed text returned and sent as chat message

### Endpoints
```
POST /api/ai-chat/transcribe  - Transcribe audio file
GET  /api/ai-chat/voice-status - Check if Whisper available
```

### Requirements
- OpenAI API key configured (required even if using different chat provider)
- Browser with MediaRecorder support (Chrome, Firefox, Safari, Edge)
- Microphone permission granted

### Cost
~$0.006 per minute of audio (very affordable)

### UI States
- **Idle**: Microphone icon, ready to record
- **Requesting**: "Requesting microphone access..."
- **Recording**: Shows timer (0:00, 0:01...), visualizer animation
- **Processing**: "Transcribing with AI..."
- **Denied**: Grayed mic icon, error message

## Intent Detection

The system detects user intent using pattern matching:

```php
const INTENT_PATTERNS = [
    // Create operations
    'add_event' => ['create event', 'new event', 'add event'],
    'add_participant' => ['add participant', 'add chef', 'register'],
    'add_entry' => ['add entry', 'new entry', 'submit entry'],

    // Query operations
    'show_results' => ['results', 'who won', 'winner', 'standings'],
    'show_stats' => ['statistics', 'stats', 'how many'],

    // Navigation
    'manage_event' => ['manage event', 'switch to', 'select event'],
];
```

## Wizard System

Multi-step operations use a state machine for guided flows:

### Available Wizards
| Wizard | Steps | Description |
|--------|-------|-------------|
| Add Event | 4 | Template → Name → Date → Voting Type |
| Add Participant | 3 | Name → Contact → Division |
| Add Entry | 4 | Participant → Division → Name → Number |
| Add Division | 3 | Type → Code → Name |
| Update Event | Variable | Field selection → New value |
| Delete Entry | 2 | Confirm → Execute |

### Wizard State Structure
```json
{
    "type": "add_entry",
    "step": "select_participant",
    "data": {
        "participant_id": 5
    },
    "currentStep": 2,
    "totalSteps": 4,
    "options": [
        {"label": "Chef Mario", "value": 5},
        {"label": "Chef Luigi", "value": 6}
    ],
    "canSkip": false
}
```

## Event Context Handling

### Automatic Event Detection
The AI detects specific events mentioned in messages:
```
User: "Show results for Summer Photography Contest"
→ System finds event by name match
→ Returns results for that specific event
```

### Conversation Context Tracking
Follow-up questions maintain context:
```
User: "Show results for Summer Photography"
AI: [Shows Summer Photography results]
User: "Who came in second?"
AI: [Answers about Summer Photography, NOT current event]
```

### Event Switching
```
User: "Manage the Soup Cookoff"
→ Response includes switchToEvent object
→ JavaScript sets managing_event_id cookie
→ Page redirects to /admin/events/{id}
```

## API Reference

### Chat Endpoint
```
POST /api/ai-chat
Content-Type: application/json

{
    "message": "Show me the results for Soup Cookoff",
    "event_id": 1,
    "wizard_state": null,
    "conversation_history": [
        {"role": "user", "content": "Hello"},
        {"role": "assistant", "content": "Hi! How can I help?"}
    ]
}
```

### Response Format
```json
{
    "message": "Here are the results for Soup Cookoff...",
    "type": "ai",
    "visualAids": [
        {
            "type": "statsCard",
            "content": {
                "stats": [
                    {"label": "Votes", "value": 42},
                    {"label": "Entries", "value": 26}
                ]
            }
        }
    ],
    "suggestedActions": [
        {"label": "View Details", "prompt": "show detailed results"}
    ],
    "wizardState": null,
    "eventOptions": null,
    "switchToEvent": null,
    "discussedEvent": "Soup Cookoff"
}
```

### Response Types
| Type | Description |
|------|-------------|
| `ai` | AI-generated response |
| `rules` | Rule-based response |
| `wizard` | Wizard step response |
| `event_list` | List of events to choose from |
| `event_switch` | Event switching with page refresh |

## Visual Aids

### Stats Card
Displays key metrics in colored boxes:
```json
{
    "type": "statsCard",
    "content": {
        "stats": [
            {"label": "Participants", "value": 26},
            {"label": "Entries", "value": 52},
            {"label": "Votes", "value": 156}
        ]
    }
}
```

### Step Card
Shows numbered steps or wizard progress:
```json
{
    "type": "stepCard",
    "content": {
        "steps": [
            {"number": 1, "title": "Select Template"},
            {"number": 2, "title": "Enter Details"}
        ],
        "showProgress": true
    }
}
```

### Ranking Card
Displays competition rankings:
```json
{
    "type": "rankingCard",
    "content": {
        "entries": [
            {"rank": 1, "name": "Tuscan Soup", "points": 15, "medal": "gold"},
            {"rank": 2, "name": "Chicken Noodle", "points": 12, "medal": "silver"}
        ]
    }
}
```

## Hybrid Approach

### When Rules Are Used (Fast, Deterministic)
- CRUD operations: create, update, delete
- Wizard flows and step responses
- System commands: cancel, confirm, skip
- Short inputs: yes, no, numbers

### When AI Is Used (Flexible, Natural)
- Data queries: results, statistics
- Complex questions requiring context
- Natural language understanding
- Follow-up questions about previous topics

### Detection Logic
```php
public function shouldUseRules(string $message): bool
{
    // Short numeric inputs = wizard responses
    if (is_numeric(trim($message)) && strlen(trim($message)) <= 3) {
        return true;
    }

    // Pattern matching for CRUD operations
    $patterns = ['create', 'add', 'delete', 'update', 'cancel'];
    foreach ($patterns as $pattern) {
        if (str_contains(strtolower($message), $pattern)) {
            return true;
        }
    }

    return false; // Use AI
}
```

## Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/AiChatController.php` | Main chat controller |
| `app/Services/AI/AiService.php` | Multi-provider AI routing |
| `app/Services/AI/AiContextBuilder.php` | System context generation |
| `app/Services/AI/WizardStateMachine.php` | Wizard state management |
| `app/Models/AiProvider.php` | Provider configuration model |
| `app/Models/AiConfig.php` | Global AI config model |
| `resources/views/components/ai-chat-slider.blade.php` | Chat UI component |
| `resources/views/admin/ai-providers/index.blade.php` | Provider admin UI |
| `routes/web.php` | API route definitions |

## Testing

### Manual Testing Checklist
- [ ] Open chat slider, send a message
- [ ] Ask "show results for [event name]"
- [ ] Try "create a new event" (wizard flow)
- [ ] Test voice input (click mic, speak, stop)
- [ ] Test "manage [event name]" (page refresh)
- [ ] Switch AI providers and test each

### API Testing
```bash
# Chat query
curl -X POST http://localhost:8100/api/ai-chat \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [token]" \
  -d '{"message": "show results for soup cookoff"}'

# Voice status check
curl http://localhost:8100/api/ai-chat/voice-status

# Transcribe audio
curl -X POST http://localhost:8100/api/ai-chat/transcribe \
  -F "audio=@recording.webm"
```
