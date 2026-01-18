# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-01-13

### Added - AI Chat System
- **Multi-Provider AI Support**
  - 7 AI providers: OpenAI, Anthropic, Gemini, DeepSeek, Groq, Mistral, Grok
  - Admin UI at `/admin/ai-providers` for configuration
  - Toggle (enable/disable) and Select (active) provider
  - Encrypted API key storage
  - Provider connection testing

- **Voice Input with OpenAI Whisper**
  - Record audio using MediaRecorder API
  - Transcribe via OpenAI Whisper API
  - Works in all modern browsers (Chrome, Firefox, Safari, Edge)
  - Visual recording indicator with timer
  - Cost: ~$0.006/minute

- **AI Chat Features**
  - Natural language queries for results and statistics
  - Context-aware event detection in messages
  - Conversation history tracking
  - Visual aids: stats cards, step cards, ranking cards
  - Suggested actions after responses

- **Wizard System for CRUD Operations**
  - Add Event wizard (template → name → date → voting type)
  - Add Participant wizard
  - Add Entry wizard
  - Add Division wizard
  - Update/Delete wizards with confirmation

- **Event Management via Chat**
  - "Manage an event" shows clickable event list
  - "Manage [event name]" switches event with page refresh
  - Event context preserved across conversation

- **New Documentation**
  - `docs/AI_CHAT_INTEGRATION.md` - Complete AI integration guide
  - `docs/PLATFORM.md` - Platform overview
  - `docs/ROADMAP.md` - Product roadmap

### Changed
- Renamed `/admin/ai-config` to `/admin/ai-providers`
- Results queries now group by division TYPE (Professional/Amateur)
- AI responses include discussedEvent for context tracking

### Fixed
- Results showing wrong event when specific event mentioned in query
- Statistics showing current event instead of discussed event
- All entries showing gold medal (now proper ranking per division type)

---

## [Unreleased]

### Added
- Comprehensive documentation in `/docs` directory
  - `claude.md` - Development context for AI assistants
  - `features.md` - Feature specifications
  - `implementation.md` - Technical implementation details
  - `database-schema.md` - Database documentation
  - `CHANGELOG.md` - This file
- Responsive card layouts for admin tables on mobile devices
  - Divisions, Participants, Entries, Categories, Events, Voting Types
  - Tables convert to centered card layout below 768px
  - Cards have max-width of 400px and display data with labels
- Pagination for Categories management page (15 per page)
- Results section container wrapper on voting page (`div.results-container`)
- Firefox-specific mobile styles using `@-moz-document url-prefix()`

### Fixed
- VoteController null user handling in `hasUserVoted()` check
- Event context menu persistence using JavaScript cookies
- Results page event header centering using flexbox wrapper
- Orphan `@endpush` directive errors in Blade templates

### Changed
- Increased max-width of voting and results containers to 1700px
- Removed row selection from all admin table pages (was non-functional)

### Removed
- Row selection highlighting from admin table pages (Divisions, Participants, Entries, Events, Voting Types)

---

## [1.0.0] - 2026-01-04

### Added
- **Core System**
  - Laravel 11 framework setup
  - Repository pattern for data access
  - Service layer for business logic
  - Role-based access control

- **Event Management**
  - CRUD operations for events
  - Event templates (6 types)
  - Voting type configuration
  - Module system for features

- **Voting System**
  - Ranked voting (3-2-1, customizable)
  - Approval voting
  - Rating/score voting
  - Vote weighting for judges
  - Real-time results display

- **Admin Features**
  - User management
  - Template management
  - Voting type management
  - Division management
  - Participant management
  - Entry management
  - Category management
  - Judging panel

- **Sample Data**
  - 9 sample events
  - 6 event templates
  - Multiple voting configurations
  - 51 sample votes
  - 23 categories

- **API**
  - Events API
  - Voting API
  - Results API
  - User authentication

### Database
- 27 tables with proper relationships
- Foreign key constraints
- Optimized indexes
- MySQL generated columns for vote calculations

### Templates
1. Food Competition (Professional/Amateur divisions)
2. Photo Contest (Categories, Judging)
3. General Vote (Simple polling)
4. Employee Recognition (Departments)
5. Art Competition (Divisions + Categories + Judging)
6. Talent Show (Categories, Judging)

### Modules
- voting, results, divisions, participants, entries
- categories, judging, import, pdf, reports

---

## Migration from Legacy System

The application was rebuilt from a legacy PHP codebase with the following improvements:

1. **Architecture**
   - Moved from procedural PHP to Laravel MVC
   - Implemented Repository pattern
   - Added Service layer
   - Used Eloquent ORM

2. **Database**
   - Normalized schema
   - Added proper foreign keys
   - Implemented soft deletes
   - Added audit logging

3. **Features**
   - Template-based event creation
   - Flexible voting types
   - Module system for features
   - REST API

4. **Security**
   - CSRF protection
   - Password hashing
   - Role-based access
   - Input validation

5. **UI/UX**
   - Responsive design
   - Consistent styling
   - Admin sidebar navigation
   - Real-time feedback
