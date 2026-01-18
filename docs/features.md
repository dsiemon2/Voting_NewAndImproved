# Feature Specifications

## Implemented Features

### 1. Event Management
- **Status**: Complete
- **Description**: Full CRUD for events with template-based configuration
- **Routes**: `/admin/events/*`
- **Features**:
  - Create events from templates
  - Edit event details (name, date, location, description)
  - Manage voting configuration per event
  - Enable/disable modules per event
  - View event dashboard with quick links

### 2. Event Templates
- **Status**: Complete
- **Description**: Reusable event configurations
- **Routes**: `/admin/templates/*`
- **Available Templates**:
  | Template | Divisions | Categories | Judging | Import |
  |----------|-----------|------------|---------|--------|
  | Food Competition | Yes | No | No | Yes |
  | Photo Contest | No | Yes | Yes | No |
  | General Vote | No | No | No | No |
  | Employee Recognition | Yes | No | No | No |
  | Art Competition | Yes | Yes | Yes | Yes |
  | Talent Show | No | Yes | Yes | No |

### 3. Voting Types
- **Status**: Complete
- **Description**: Configurable point systems
- **Routes**: `/admin/voting-types/*`
- **Default Type**: Standard Ranked (3-2-1)
  - 1st Place: 3 points
  - 2nd Place: 2 points
  - 3rd Place: 1 point
- **Custom Types**: Can create any point structure

### 4. Division Management
- **Status**: Complete
- **Description**: Organize entries by type (Professional/Amateur, etc.)
- **Routes**: `/admin/events/{event}/divisions`
- **Features**:
  - Create divisions with type and code
  - Link entries to divisions
  - View results by division

### 5. Participant Management
- **Status**: Complete
- **Description**: Manage contestants/participants
- **Routes**: `/admin/events/{event}/participants`
- **Features**:
  - Add participants with contact info
  - Link to divisions
  - Associate with entries

### 6. Entry Management
- **Status**: Complete
- **Description**: Manage voting entries
- **Routes**: `/admin/events/{event}/entries`
- **Features**:
  - Create entries with number, name, description
  - Link to participant and division
  - Assign categories (if applicable)

### 7. Category Management
- **Status**: Complete
- **Description**: Award categories for applicable events
- **Routes**: `/admin/events/{event}/categories`
- **Features**:
  - Create categories with display order
  - Enable/disable categories
  - Link entries to categories

### 8. Voting Interface
- **Status**: Complete
- **Description**: Public voting page
- **Routes**: `/vote/{event}`
- **Features**:
  - Ranked voting with place selection
  - Entry number input
  - Real-time validation
  - Thank you page after voting

### 9. Results Display
- **Status**: Complete
- **Description**: View voting results
- **Routes**: `/results/{event}`
- **Features**:
  - Results grouped by division type
  - Point totals and place counts
  - Leaderboard ranking

### 10. Judging Panel
- **Status**: Complete
- **Description**: Assign judges to events
- **Routes**: `/admin/events/{event}/judges`
- **Features**:
  - Add/remove judges
  - Assign weight classes
  - Track judge assignments

### 11. User Management
- **Status**: Complete
- **Description**: Manage system users
- **Routes**: `/admin/users/*`
- **Roles**:
  - Administrator: Full access
  - Member: Event management
  - User: Voting only
  - Judge: Weighted voting

### 12. REST API
- **Status**: Complete
- **Description**: API for external integrations
- **Base URL**: `/api/*`
- **Endpoints**:
  - Events listing and details
  - Voting submission
  - Results retrieval
  - User vote status

### 13. AI Chat Assistant
- **Status**: Complete
- **Description**: Natural language interface for managing events and querying data
- **Routes**: `/api/ai-chat`, `/api/ai-chat/transcribe`, `/api/ai-chat/voice-status`
- **Features**:
  - **Multi-Provider Support**: 7 AI providers (OpenAI, Anthropic, Gemini, DeepSeek, Groq, Mistral, Grok)
  - **Voice Input**: OpenAI Whisper transcription
  - **Wizard System**: Guided CRUD operations (add event, participant, entry, division)
  - **Natural Language Queries**: "Show results for Soup Cookoff", "How many votes?"
  - **Context-Aware**: Detects specific events in messages, maintains conversation context
  - **Event Switching**: "Manage the Soup Cookoff" switches event with page refresh
  - **Visual Aids**: Stats cards, step cards, ranking cards in responses

### 14. AI Providers Management
- **Status**: Complete
- **Description**: Configure and manage AI providers
- **Routes**: `/admin/ai-providers`
- **Features**:
  - Enable/disable providers (toggle for availability)
  - Select active provider (radio for current use)
  - Configure API keys (encrypted storage)
  - Set default model and parameters
  - Test provider connections
  - View available models per provider

---

## Partially Implemented Features

### 1. Import (CSV/Excel)
- **Status**: Complete
- **Description**: Bulk import from CSV/Excel files
- **Routes**: `/admin/events/{event}/import`
- **Implementation**: `app/Imports/EventDataImport.php`
- **Features**:
  - Drag-and-drop file upload
  - Supports CSV, XLSX, XLS formats
  - Import types: Combined, Participants, Entries, Divisions
  - Auto-creates divisions based on code prefix
  - Links participants to entries
- **Sample Template**: `storage/app/import-templates/food-competition-sample.csv`

### 2. Live Results
- **Status**: Partial
- **Description**: Real-time result updates
- **Routes**: `/results/{event}/live`
- **Current State**: API exists, frontend polling not implemented
- **TODO**: Add JavaScript polling or WebSocket support

---

## Not Yet Implemented

### 1. Export
- **Status**: Not Started
- **Description**: Export data to CSV/Excel/PDF
- **Planned Routes**: `/admin/events/{event}/export`
- **TODO**:
  - Export entries list
  - Export results
  - Export ballots

### 2. PDF Generation
- **Status**: Not Started
- **Description**: Generate printable ballots and results
- **TODO**:
  - Ballot printing
  - Results certificates
  - Summary reports

### 3. Reports Dashboard
- **Status**: Not Started
- **Description**: Analytics and reporting
- **Planned Routes**: `/admin/reports/*`
- **TODO**:
  - Voting statistics
  - Participation metrics
  - Trend analysis

### 4. Email Notifications
- **Status**: Not Started
- **Description**: Automated email alerts
- **TODO**:
  - Welcome emails
  - Vote confirmation
  - Results announcement

### 5. Public Event Registration
- **Status**: Not Started
- **Description**: Allow public participant registration
- **TODO**:
  - Registration form
  - Entry submission
  - Fee collection (optional)

---

## Module System

### Available Modules
| Code | Name | Description |
|------|------|-------------|
| voting | Voting | Enable vote casting |
| results | Results | Display results |
| divisions | Divisions | Division management |
| participants | Participants | Participant management |
| entries | Entries | Entry management |
| categories | Categories | Category awards |
| judging | Judging Panel | Judge assignments |
| import | Import | CSV import |
| pdf | PDF Export | Print functionality |
| reports | Reports | Analytics |

### Module Inheritance
1. Templates define default modules
2. Events can override template defaults
3. `hasModule()` checks both event and template
