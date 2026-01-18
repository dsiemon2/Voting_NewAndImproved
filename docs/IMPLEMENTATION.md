# Implementation Details

## Architecture Overview

### Design Patterns Used

1. **Repository Pattern**
   - Abstracts data access from business logic
   - Interfaces in `app/Repositories/Contracts/`
   - Implementations in `app/Repositories/Eloquent/`
   - Bound via `RepositoryServiceProvider`

2. **Service Layer**
   - Business logic in `app/Services/`
   - `VotingService` - Vote casting, validation, results
   - `EventConfigurationService` - Dynamic labels and config

3. **MVC (Model-View-Controller)**
   - Standard Laravel architecture
   - Controllers handle HTTP, Services handle logic

### Database Design

#### Core Tables
```
events
├── id
├── name, description, location
├── event_date, event_end_date
├── event_template_id (FK)
├── voting_type_id (FK)
├── state_id (FK)
├── created_by (FK)
├── is_active, is_public
└── settings (JSON)

event_templates
├── id
├── name, description
├── icon
├── division_types (JSON) - [{code, name, description}]
├── entry_label, participant_label
└── is_active

voting_types
├── id
├── name, code
├── description
├── category (ranked|approval|rating)
├── settings (JSON)
└── is_active
```

#### Voting Tables
```
votes
├── id
├── event_id, user_id, entry_id, division_id
├── category_id (nullable)
├── place (1, 2, 3...)
├── rating (for rating-type voting)
├── base_points
├── weight_multiplier
├── final_points (GENERATED: base_points * weight_multiplier)
├── voter_ip, voter_fingerprint
└── timestamps

vote_summaries
├── id
├── event_id, entry_id, division_id, category_id
├── total_points, vote_count
├── first_place_count, second_place_count, third_place_count
├── ranking
└── timestamps
```

#### Module System Tables
```
modules
├── id
├── name, code, description
└── is_active

event_template_modules (pivot)
├── event_template_id
├── module_id
└── is_enabled

event_modules (pivot - overrides template)
├── event_id
├── module_id
└── is_enabled
```

### Key Implementation Details

#### Vote Point Calculation
```sql
-- final_points is a MySQL generated column
ALTER TABLE votes ADD COLUMN final_points DECIMAL(8,2)
  GENERATED ALWAYS AS (base_points * weight_multiplier) STORED;
```

**Important**: Never include `final_points` in INSERT/UPDATE queries.

#### Module Checking
```php
// Event model
public function hasModule(string $code): bool
{
    // First check event-specific modules
    $eventModule = $this->modules()->where('code', $code)->first();
    if ($eventModule) {
        return $eventModule->pivot->is_enabled;
    }

    // Fall back to template modules
    return $this->template->modules()
        ->where('code', $code)
        ->wherePivot('is_enabled', true)
        ->exists();
}
```

#### Entry Lookup by Type Code
```php
// VotingService
private function findEntryByTypeAndNumber(Event $event, string $typeCode, mixed $input): ?Entry
{
    // typeCode = 'P' for Professional, 'A' for Amateur
    // input = entry number (1, 2, 3 or 101, 102, 103)

    return Entry::where('event_id', $event->id)
        ->where('entry_number', (int) $input)
        ->whereHas('division', function ($query) use ($typeCode) {
            $query->where('code', 'like', $typeCode . '%');
        })
        ->first();
}
```

#### Session/Cookie Handling for Event Context
```php
// In layout blade
@php
    // Read JavaScript-set cookie directly (not Laravel encrypted)
    $managingEventId = $_COOKIE['managing_event_id'] ?? null;
    if ($managingEventId && is_numeric($managingEventId)) {
        $currentEvent = \App\Models\Event::with('template')->find($managingEventId);
    }
@endphp
```

### API Response Formats

#### Results API
```json
{
  "data": [
    {
      "entry_id": 45,
      "entry_name": "Tuscan Bean Soup",
      "entry_number": 1,
      "division_id": 32,
      "division_name": "Professional 1",
      "division_code": "P1",
      "participant_name": "Chef Mario",
      "total_points": "4.00",
      "vote_count": 2,
      "first_place_count": 1,
      "second_place_count": 0,
      "third_place_count": 1
    }
  ]
}
```

### Error Handling

#### Validation Errors
```php
throw ValidationException::withMessages([
    'votes' => 'You cannot select the same entry for multiple places.',
]);
```

#### Vote Casting Flow
1. Validate voting is open (`$event->isVotingOpen()`)
2. Validate no duplicate entries per division
3. Find entries by type code and number
4. Get points from voting config
5. Create vote records in transaction
6. Update vote summaries

### File Organization

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── EventController.php
│   │   │   ├── TemplateController.php
│   │   │   ├── VotingTypeController.php
│   │   │   ├── UserController.php
│   │   │   ├── DivisionController.php
│   │   │   ├── ParticipantController.php
│   │   │   ├── EntryController.php
│   │   │   ├── JudgeController.php
│   │   │   └── PaymentGatewayController.php
│   │   ├── Api/
│   │   │   ├── EventApiController.php
│   │   │   ├── VotingApiController.php
│   │   │   ├── ResultsApiController.php
│   │   │   └── AiChatController.php
│   │   ├── Voting/
│   │   │   ├── VoteController.php
│   │   │   └── ResultsController.php
│   │   └── SubscriptionController.php
│   └── Middleware/
│       ├── CheckRole.php
│       ├── EnsureEventActive.php
│       ├── EnsureModuleEnabled.php
│       ├── HandleInertiaRequests.php
│       ├── TrackManagedEvent.php
│       ├── CheckPlanLimits.php
│       └── CheckEventLimit.php
├── Models/
│   ├── Event.php
│   ├── EventTemplate.php
│   ├── VotingType.php
│   ├── VotingPlaceConfig.php
│   ├── Division.php
│   ├── Participant.php
│   ├── Entry.php
│   ├── Category.php
│   ├── Vote.php
│   ├── VoteSummary.php
│   ├── Module.php
│   ├── User.php
│   ├── Role.php
│   ├── PaymentGateway.php
│   ├── SubscriptionPlan.php
│   ├── UserSubscription.php
│   └── AiProvider.php
├── Repositories/
│   ├── Contracts/
│   │   ├── EventRepositoryInterface.php
│   │   ├── VoteRepositoryInterface.php
│   │   ├── EntryRepositoryInterface.php
│   │   └── DivisionRepositoryInterface.php
│   └── Eloquent/
│       ├── BaseRepository.php
│       ├── EventRepository.php
│       ├── VoteRepository.php
│       ├── EntryRepository.php
│       └── DivisionRepository.php
└── Services/
    ├── VotingService.php
    ├── EventConfigurationService.php
    └── AI/
        ├── AiService.php           # Multi-provider AI routing
        ├── AiContextBuilder.php    # System context generation
        └── WizardStateMachine.php  # Wizard state management
```

### AI System Architecture

#### Multi-Provider Support
```php
// AiService routes to appropriate provider
return match($providerCode) {
    'openai', 'deepseek', 'groq', 'mistral', 'grok'
        => $this->callOpenAICompatible($prompt, $message),
    'anthropic' => $this->callAnthropic($prompt, $message),
    'gemini' => $this->callGemini($prompt, $message),
};
```

#### Voice Transcription (Whisper)
```php
// AiService::transcribeAudio()
$response = Http::withHeaders([
    'Authorization' => "Bearer {$apiKey}",
])
->attach('file', file_get_contents($audioPath), basename($audioPath))
->post('https://api.openai.com/v1/audio/transcriptions', [
    'model' => 'whisper-1',
    'language' => 'en',
]);
```

#### Context Builder
```php
// Builds comprehensive context for AI
AiContextBuilder::buildFullContext($currentEvent)
// Returns:
// - Event templates
// - Voting types
// - All events summary
// - Voting results for all events
// - Current event details (if set)
```

#### Intent Detection
```php
// Pattern matching for routing
const INTENT_PATTERNS = [
    'add_event' => ['create event', 'new event'],
    'show_results' => ['results', 'who won', 'winner'],
    'manage_event' => ['manage event', 'switch to'],
];
```

### Payment Processing Architecture

#### Gateway Model
```php
// PaymentGateway model
class PaymentGateway extends Model
{
    protected $fillable = [
        'provider',          // stripe, braintree, square, authorize
        'is_enabled',        // Only one can be enabled
        'publishable_key',
        'secret_key',        // Stored encrypted
        'test_mode',
        'ach_enabled',       // Stripe only
        'webhook_secret',
        'merchant_id',       // Braintree/Square
    ];

    public static function getActiveProvider(): ?self
    {
        return self::where('is_enabled', true)->first();
    }
}
```

#### Payment Controller Flow
```php
// PaymentGatewayController
public function update(Request $request, string $provider)
{
    $gateway = PaymentGateway::firstOrNew(['provider' => $provider]);

    // Only update secret key if new value provided
    if ($request->filled('secret_key')) {
        $gateway->secret_key = $request->input('secret_key');
    }

    $gateway->save();
}

public function enable(string $provider)
{
    // Disable all others, enable this one
    PaymentGateway::where('provider', '!=', $provider)
        ->update(['is_enabled' => false]);

    $gateway->update(['is_enabled' => true]);
}
```

### Subscription System Architecture

#### Subscription Plan Model
```php
// SubscriptionPlan model
class SubscriptionPlan extends Model
{
    // Limits
    public function isUnlimitedEvents(): bool
    {
        return $this->max_events === -1;
    }

    // Feature checking
    public function hasFeature(string $feature): bool
    {
        $field = 'has_' . $feature;
        return $this->$field ?? false;
    }
}
```

#### User Subscription Methods
```php
// User model additions
public function currentPlan(): ?SubscriptionPlan
{
    $subscription = $this->activeSubscription();
    return $subscription?->plan ?? SubscriptionPlan::getFreePlan();
}

public function canCreateEvent(): bool
{
    $plan = $this->currentPlan();
    if ($plan->isUnlimitedEvents()) return true;

    $activeEvents = $this->createdEvents()
        ->where('is_active', true)->count();
    return $activeEvents < $plan->max_events;
}
```

#### Stripe Integration Flow
```
1. User clicks Subscribe
2. SubscriptionController::subscribe()
3. Create/get Stripe Customer
4. Create Checkout Session
5. Redirect to Stripe Checkout
6. User completes payment
7. Redirect to success URL
8. Create UserSubscription record
9. Webhooks handle status updates
```

#### Middleware Integration
```php
// bootstrap/app.php
$middleware->alias([
    'plan.feature' => CheckPlanLimits::class,
    'plan.events' => CheckEventLimit::class,
]);

// Usage in routes
Route::middleware(['plan.feature:judging_panels'])
    ->get('/judges', [JudgeController::class, 'index']);

Route::middleware(['plan.events'])
    ->post('/events', [EventController::class, 'store']);
```

### Testing Approach

#### Manual Testing Checklist
1. Create event from each template type
2. Add divisions (if applicable)
3. Add participants and entries
4. Configure voting type
5. Cast votes through voting interface
6. Verify results display correctly
7. Test API endpoints

#### Payment System Testing
1. Configure Stripe test keys
2. Enable Stripe gateway
3. Subscribe to paid plan (test card: 4242 4242 4242 4242)
4. Verify subscription created
5. Test feature gates
6. Test event limits
7. Cancel subscription
8. Verify webhook handling

#### Database Verification
```bash
# Check votes
mysql -u root -e "SELECT * FROM votes WHERE event_id = 1;" voting_new

# Check results aggregation
mysql -u root -e "
SELECT e.name, SUM(v.final_points) as total
FROM entries e
JOIN votes v ON v.entry_id = e.id
WHERE v.event_id = 1
GROUP BY e.id
ORDER BY total DESC;
" voting_new

# Check subscriptions
mysql -u root -e "
SELECT u.email, sp.name as plan, us.status
FROM user_subscriptions us
JOIN users u ON u.id = us.user_id
JOIN subscription_plans sp ON sp.id = us.subscription_plan_id;
" voting_new
```
