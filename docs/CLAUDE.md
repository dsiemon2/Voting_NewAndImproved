# Claude Code Context - Voting Application

## Project Overview
A Laravel 11 voting application rebuilt from a legacy PHP codebase. Supports multiple event types, voting systems, and templates.

## Tech Stack
- **Framework**: Laravel 11.x
- **PHP Version**: 8.3.10
- **Database**: MySQL (XAMPP)
- **Server**: Apache (XAMPP) on port 8100
- **Frontend**: Blade templates with vanilla CSS/JS

## Key Directories
```
app/
├── Http/Controllers/
│   ├── Admin/          # Event, Template, VotingType, User management
│   ├── Api/            # REST API endpoints
│   ├── Auth/           # Authentication
│   └── Voting/         # Vote and Results controllers
├── Models/             # Eloquent models
├── Repositories/       # Repository pattern implementation
│   ├── Contracts/      # Interfaces
│   └── Eloquent/       # Implementations
└── Services/           # Business logic (VotingService, EventConfigurationService)

resources/views/
├── admin/              # Admin panel views
│   └── events/         # Event management (divisions, entries, participants, etc.)
├── voting/             # Voting interface
├── results/            # Results display
└── layouts/            # Main layout with sidebar

database/
├── migrations/         # Database schema
└── seeders/           # Sample data
```

## Database Schema (Key Tables)
- `events` - Main events table
- `event_templates` - Reusable event configurations (Food Competition, Photo Contest, etc.)
- `voting_types` - Voting systems (Ranked, Approval, Rating)
- `voting_place_configs` - Points per place (1st=3pts, 2nd=2pts, etc.)
- `divisions` - Event divisions (Professional, Amateur, etc.)
- `participants` - Registered participants
- `entries` - Competition entries
- `categories` - Award categories (for events that use them)
- `votes` - Individual votes (final_points is a MySQL generated column)
- `vote_summaries` - Aggregated results
- `modules` - Feature flags (voting, judging, categories, etc.)
- `event_template_modules` - Links templates to modules
- `event_modules` - Links events to modules (overrides template)

## Key Relationships
```
Event -> EventTemplate (belongsTo)
Event -> EventVotingConfig -> VotingType (hasOne -> belongsTo)
Event -> Divisions (hasMany)
Event -> Entries (hasMany)
Event -> Participants (hasMany)
Event -> Categories (hasMany)
Event -> Modules (via event_modules or event_template_modules)
Entry -> Division (belongsTo)
Entry -> Participant (belongsTo)
Vote -> Event, User, Entry, Division (belongsTo)
```

## Current Event Templates
1. **Food Competition** - Professional/Amateur divisions, ranked voting
2. **Photo Contest** - Categories (Landscape, Portrait, Street), judging panel
3. **General Vote** - Simple polls without divisions
4. **Employee Recognition** - Departments as divisions
5. **Art Competition** - Divisions + categories + judging
6. **Talent Show** - Performance categories, judging panel

## Voting Types
- **Standard Ranked (3-2-1)** - 1st=3pts, 2nd=2pts, 3rd=1pts
- Custom voting types can be created with different point structures

## Module System
Events inherit modules from their template, but can override:
- `voting` - Enable voting
- `judging` - Judging panel
- `categories` - Award categories
- `divisions` - Enable divisions
- `participants` - Participant management
- `entries` - Entry management
- `import` - CSV import
- `results` - Results display
- `pdf` - PDF export
- `reports` - Reporting

## Session/Cookie Handling
- Event context menu uses JavaScript cookies (`managing_event_id`)
- Must read cookies with `$_COOKIE` directly (not Laravel's encrypted cookies)

## Important Implementation Details

### Vote Storage
- `final_points` is a MySQL generated column: `base_points * weight_multiplier`
- Never include `final_points` in INSERT/UPDATE - it auto-calculates

### Entry Lookup
- Entries are looked up by division type code + entry number
- Type code: P=Professional, A=Amateur, etc.
- Entry numbers can be 1,2,3 for Pro or 101,102,103 for Amateur

### hasModule() Method
```php
// Event model checks both event_modules and template modules
$event->hasModule('voting') // Returns true/false
```

## URLs
- Login: http://localhost:8100/login
- Admin Dashboard: http://localhost:8100/dashboard
- Events: http://localhost:8100/admin/events
- Voting: http://localhost:8100/vote/{event_id}
- Results: http://localhost:8100/results/{event_id}

## Test Accounts
- admin@example.com (Administrator)
- dsiemon2@gmail.com (User)

## Sample Data (as of 2026-01-04)
- 11 events across 6 templates
- 90 votes distributed across all events (100% entry coverage)
- 23 categories for events that support them
- 61 divisions total
- 89+ entries total
- 79 participants

## Import Feature
- **Location**: `app/Imports/EventDataImport.php`
- **Supported formats**: CSV, XLSX, XLS
- **Import types**: Combined, Participants only, Entries only, Divisions only
- **CSV Format**:
  ```csv
  division,participant,entry1,entry2,entry3
  P1,John Smith,Beef Wellington,Mushroom Risotto,
  A1,Sarah Johnson,Homestyle Lasagna,Caesar Salad,
  ```
- **Sample template**: `storage/app/import-templates/food-competition-sample.csv`

## AI Chat System (v1.1.0)

### Architecture
```
Chat Slider → AiChatController → Intent Detection
                    ↓
        ┌──────────┴──────────┐
        ↓                     ↓
   Rule Handlers         AiService
   (Wizards/CRUD)     (AI Providers)
```

### Key Files
| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/AiChatController.php` | Main chat controller |
| `app/Services/AI/AiService.php` | Multi-provider AI routing |
| `app/Services/AI/AiContextBuilder.php` | System context builder |
| `app/Services/AI/WizardStateMachine.php` | Wizard state management |
| `app/Models/AiProvider.php` | Provider model (encrypted keys) |
| `resources/views/components/ai-chat-slider.blade.php` | Chat UI |
| `resources/views/admin/ai-providers/index.blade.php` | Provider config |

### PDF Generation Files
| File | Purpose |
|------|---------|
| `app/Http/Controllers/Admin/PdfController.php` | PDF generation controller |
| `resources/views/pdf/ballot.blade.php` | Ballot PDF template |
| `resources/views/pdf/results.blade.php` | Results PDF template |
| `resources/views/pdf/certificate.blade.php` | Certificate PDF template |
| `resources/views/pdf/entries-list.blade.php` | Entries list PDF template |
| `resources/views/pdf/summary.blade.php` | Event summary PDF template |

### Analytics Files
| File | Purpose |
|------|---------|
| `app/Http/Controllers/Admin/AnalyticsController.php` | Analytics dashboard controller |
| `resources/views/admin/analytics/index.blade.php` | Analytics dashboard view |

### AI Providers (7 Supported)
- **OpenAI**: GPT-4o, GPT-4o-mini (+ Whisper for voice)
- **Anthropic**: Claude-3.5-sonnet, Claude-3-opus
- **Google Gemini**: gemini-1.5-pro, gemini-1.5-flash
- **DeepSeek**: deepseek-chat, deepseek-coder
- **Groq**: llama-3.1-70b, mixtral-8x7b
- **Mistral**: mistral-large, mistral-medium
- **Grok (xAI)**: grok-beta

### Voice Input (Whisper)
- Records via MediaRecorder API
- Sends to `/api/ai-chat/transcribe`
- Uses OpenAI Whisper API
- Cost: ~$0.006/minute

### Hybrid Approach
| Use Rules | Use AI |
|-----------|--------|
| CRUD operations | Data queries |
| Wizard flows | Results/statistics |
| Short inputs (yes/no) | Complex questions |
| System commands | Follow-up questions |

### Intent Patterns
```php
'add_event' => ['create event', 'new event', 'add event']
'show_results' => ['results', 'who won', 'winner', 'standings']
'show_stats' => ['statistics', 'stats', 'how many']
'manage_event' => ['manage event', 'switch to', 'select event']
```

### API Endpoints
```
POST /api/ai-chat           - Chat message
POST /api/ai-chat/transcribe - Voice transcription
GET  /api/ai-chat/voice-status - Check Whisper availability
```

## Payment Processing System (v1.2.0)

### Supported Gateways
- **Stripe** (Default, pre-configured) - 2.9% + 30c
- **Braintree** - 2.59% + 49c
- **Square** - 2.6% + 10c
- **Authorize.net** - 2.9% + 30c

### Key Files
| File | Purpose |
|------|---------|
| `app/Models/PaymentGateway.php` | Gateway model |
| `app/Http/Controllers/Admin/PaymentGatewayController.php` | Admin controller |
| `resources/views/admin/payment-processing/index.blade.php` | Admin UI |
| `database/seeders/PaymentGatewaySeeder.php` | Default Stripe config |

### Admin URL
```
/admin/payment-processing
```

### Stripe Configuration
Configure in `.env` file (never commit live keys!):
```
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_TEST_MODE=true
```

## Subscription System (v1.2.0)

### Pricing Tiers
| Plan | Price | Events | Entries |
|------|-------|--------|---------|
| Free Trial | $0/mo | 1 | 20 |
| Non-Profit | $9.99/mo | 3 | 100 |
| Professional | $29.99/mo | 10 | Unlimited |
| Premium | $59.00/mo | Unlimited | Unlimited |

### Key Files
| File | Purpose |
|------|---------|
| `app/Models/SubscriptionPlan.php` | Plan model |
| `app/Models/UserSubscription.php` | User subscription model |
| `app/Http/Controllers/SubscriptionController.php` | Subscription controller |
| `app/Http/Middleware/CheckPlanLimits.php` | Feature gate middleware |
| `app/Http/Middleware/CheckEventLimit.php` | Event limit middleware |
| `resources/views/subscription/pricing.blade.php` | Pricing page |
| `resources/views/subscription/manage.blade.php` | Subscription management |

### URLs
```
/pricing                    - Public pricing page
/subscription/manage        - User subscription management
/subscription/subscribe/{id} - Subscribe to plan
/webhook/stripe             - Stripe webhooks
```

### User Model Methods
```php
$user->currentPlan();              // Get current plan
$user->activeSubscription();       // Get active subscription
$user->hasFeature('judging_panels'); // Check feature access
$user->canCreateEvent();           // Check event limit
$user->canAddEntries($event, 5);   // Check entry limit
```

### Middleware Usage
```php
// Feature gate
Route::middleware(['plan.feature:judging_panels'])->...

// Event limit
Route::middleware(['plan.events'])->...
```

## Implemented Features (v1.3.0)
1. **PDF Generation** - Ballots, results, certificates, entries list, summary reports
2. **Advanced Analytics** - Dashboard with charts (voting trends, division breakdown, top performers)
3. **Live Results Polling** - JavaScript polling with auto-refresh every 10 seconds
4. **Feature Gate Enforcement** - Middleware applied to routes (judging, import, API, event limits)

## Pending Features (TODO)
1. **CSV Export** - Export results to CSV format
2. **Email Notifications** - Vote confirmation, results announcement
3. **Voice Output (TTS)** - Planned for AI responses
4. **WebSocket Updates** - Optional upgrade from polling

## Common Issues & Solutions

### Event Context Menu Not Showing
- Uses cookies set by JavaScript
- Read with `$_COOKIE['managing_event_id']` not `request()->cookie()`

### Votes Not Showing in Results
- Check `final_points` column is being calculated
- Verify division_id is set on votes (can be NULL for events without divisions)
- Check results query joins correctly
- For events without divisions, results are displayed in a single "Results" table

### Events Without Divisions (e.g., General Vote)
- `division_id` is NULL for these votes
- Results are grouped differently in `vote.blade.php`
- The `@else` block handles single results table display

### Module Not Appearing
- Check event_template_modules for template defaults
- Check event_modules for event-specific overrides
- Use `$event->hasModule('code')` for checking

## Recent Fixes (2026-01-04)
1. **Voting results for events without divisions** - Fixed single results table display
2. **Import functionality** - Implemented CSV/Excel import with Maatwebsite/Excel
3. **Sample data** - Added complete votes for all entries in all events
4. **VoteController null user check** - Fixed `hasUserVoted()` call
5. **Responsive admin tables** - Added mobile card layouts for all admin table pages
   - Divisions, Participants, Entries, Categories, Events, Voting Types
   - Tables convert to centered cards below 768px breakpoint
   - Cards display with data-label attributes for field names
6. **Categories pagination** - Added pagination (15 per page) to categories management
7. **Row selection removed** - Removed non-functional row selection from all tables
8. **Results page header centering** - Fixed event header card centering with flexbox wrapper
