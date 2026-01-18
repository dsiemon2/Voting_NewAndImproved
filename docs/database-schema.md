# Database Schema Documentation

## Entity Relationship Diagram (Conceptual)

```
                    ┌──────────────────┐
                    │   event_templates │
                    │   ────────────── │
                    │   id             │
                    │   name           │
                    │   division_types │
                    └────────┬─────────┘
                             │ 1
                             │
                             │ *
                    ┌────────┴─────────┐
                    │      events      │
                    │   ────────────── │
                    │   id             │
┌───────────────┐   │   name           │   ┌───────────────┐
│  voting_types │───│   event_template │───│    users      │
│  ───────────  │ 1 │   voting_type_id │ * │  ──────────── │
│  id           │   │   created_by     │   │  id           │
│  name         │   └────────┬─────────┘   │  email        │
│  category     │            │             └───────────────┘
└───────┬───────┘            │
        │                    │ 1
        │ 1                  │
        │                    ├──────────────────┬──────────────────┐
        │ *                  │ *                │ *                │ *
┌───────┴───────┐   ┌────────┴─────────┐  ┌────┴────────┐  ┌──────┴───────┐
│voting_place_  │   │    divisions     │  │participants │  │   entries    │
│   configs     │   │   ────────────── │  │ ────────── │  │ ──────────── │
│  ───────────  │   │   id             │  │ id         │  │ id           │
│  place        │   │   name, type     │  │ name       │  │ name         │
│  points       │   │   code           │  │ email      │  │ entry_number │
│  label        │   └──────────────────┘  └────────────┘  │ division_id  │
└───────────────┘            │                    │       │ participant  │
                             │                    │       └──────┬───────┘
                             │                    │              │
                             └────────────────────┴──────────────┤
                                                                 │ 1
                                                                 │
                                                                 │ *
                                                          ┌──────┴───────┐
                                                          │    votes     │
                                                          │ ──────────── │
                                                          │ id           │
                                                          │ event_id     │
                                                          │ user_id      │
                                                          │ entry_id     │
                                                          │ division_id  │
                                                          │ place        │
                                                          │ base_points  │
                                                          │ final_points │
                                                          └──────────────┘
```

## Table Definitions

### events
Primary table for all voting events.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| name | VARCHAR(255) | NO | Event name |
| description | TEXT | YES | Event description |
| location | VARCHAR(255) | YES | Event location |
| event_date | DATE | YES | Event date |
| event_end_date | DATE | YES | End date for multi-day events |
| event_template_id | BIGINT UNSIGNED | NO | FK to event_templates |
| voting_type_id | BIGINT UNSIGNED | YES | FK to voting_types |
| state_id | BIGINT UNSIGNED | YES | FK to states |
| created_by | BIGINT UNSIGNED | YES | FK to users |
| is_active | BOOLEAN | NO | Active status (default: true) |
| is_public | BOOLEAN | NO | Public voting allowed |
| settings | JSON | YES | Additional settings |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |
| deleted_at | TIMESTAMP | YES | Soft delete |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (event_template_id)
- INDEX (is_active, event_date)

### event_templates
Reusable event configurations.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| name | VARCHAR(100) | NO | Template name |
| description | TEXT | YES | Description |
| icon | VARCHAR(50) | YES | FontAwesome icon class |
| division_types | JSON | YES | [{code, name, description}] |
| entry_label | VARCHAR(50) | NO | Label for entries (Entry, Dish, Photo) |
| participant_label | VARCHAR(50) | NO | Label for participants (Participant, Chef) |
| is_active | BOOLEAN | NO | Active status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

### voting_types
Voting system configurations.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| name | VARCHAR(100) | NO | Display name |
| code | VARCHAR(50) | NO | Unique code |
| description | TEXT | YES | Description |
| category | ENUM | NO | ranked, approval, rating |
| settings | JSON | YES | Additional settings |
| is_active | BOOLEAN | NO | Active status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (code)

### voting_place_configs
Points per place for voting types.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| voting_type_id | BIGINT UNSIGNED | NO | FK to voting_types |
| place | INT | NO | Place number (1, 2, 3...) |
| points | DECIMAL(5,2) | NO | Points awarded |
| label | VARCHAR(50) | YES | Display label |
| color | VARCHAR(20) | YES | Display color |
| icon | VARCHAR(50) | YES | FontAwesome icon |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (voting_type_id, place)

### divisions
Event divisions/categories.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_id | BIGINT UNSIGNED | NO | FK to events |
| name | VARCHAR(100) | NO | Division name |
| type | VARCHAR(50) | YES | Type (Professional, Amateur) |
| code | VARCHAR(20) | NO | Unique code within event |
| description | TEXT | YES | Description |
| is_active | BOOLEAN | NO | Active status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (event_id, code)
- INDEX (event_id, is_active)

### entries
Items being voted on.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_id | BIGINT UNSIGNED | NO | FK to events |
| division_id | BIGINT UNSIGNED | YES | FK to divisions |
| participant_id | BIGINT UNSIGNED | YES | FK to participants |
| category_id | BIGINT UNSIGNED | YES | FK to categories |
| entry_number | INT | NO | Display number |
| name | VARCHAR(255) | NO | Entry name |
| description | TEXT | YES | Description |
| is_active | BOOLEAN | NO | Active status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (event_id, entry_number)
- INDEX (event_id, division_id)
- INDEX (event_id, is_active)

### votes
Individual vote records.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_id | BIGINT UNSIGNED | NO | FK to events |
| user_id | BIGINT UNSIGNED | NO | FK to users |
| entry_id | BIGINT UNSIGNED | NO | FK to entries |
| division_id | BIGINT UNSIGNED | YES | FK to divisions |
| category_id | BIGINT UNSIGNED | YES | FK to categories |
| place | INT | YES | Place voted (1, 2, 3) |
| rating | DECIMAL(3,1) | YES | Rating (for rating voting) |
| base_points | DECIMAL(8,2) | NO | Base points |
| weight_multiplier | DECIMAL(5,2) | NO | Weight (default: 1.00) |
| final_points | DECIMAL(8,2) | GENERATED | base_points * weight_multiplier |
| voter_ip | VARCHAR(45) | YES | Voter IP address |
| voter_fingerprint | VARCHAR(255) | YES | Browser fingerprint |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- INDEX (event_id, created_at)
- INDEX (event_id, entry_id)
- INDEX (event_id, division_id)
- INDEX (user_id, event_id)

### vote_summaries
Aggregated vote results.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_id | BIGINT UNSIGNED | NO | FK to events |
| entry_id | BIGINT UNSIGNED | NO | FK to entries |
| division_id | BIGINT UNSIGNED | YES | FK to divisions |
| category_id | BIGINT UNSIGNED | YES | FK to categories |
| total_points | DECIMAL(10,2) | NO | Sum of final_points |
| vote_count | INT | NO | Number of votes |
| first_place_count | INT | NO | Count of 1st place votes |
| second_place_count | INT | NO | Count of 2nd place votes |
| third_place_count | INT | NO | Count of 3rd place votes |
| ranking | INT | YES | Calculated rank |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (event_id, entry_id, division_id, category_id)
- INDEX (event_id, total_points)

### modules
Feature modules.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| name | VARCHAR(100) | NO | Display name |
| code | VARCHAR(50) | NO | Unique code |
| description | TEXT | YES | Description |
| is_active | BOOLEAN | NO | Active status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (code)

### event_template_modules
Links templates to modules.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_template_id | BIGINT UNSIGNED | NO | FK |
| module_id | BIGINT UNSIGNED | NO | FK |
| is_enabled | BOOLEAN | NO | Enabled status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (event_template_id, module_id)

### event_modules
Event-specific module overrides.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT UNSIGNED | NO | Primary key |
| event_id | BIGINT UNSIGNED | NO | FK |
| module_id | BIGINT UNSIGNED | NO | FK |
| is_enabled | BOOLEAN | NO | Enabled status |
| created_at | TIMESTAMP | YES | |
| updated_at | TIMESTAMP | YES | |

**Indexes:**
- UNIQUE (event_id, module_id)

## Sample Queries

### Get Results by Division Type
```sql
SELECT
    entries.name as entry_name,
    divisions.type as division_type,
    SUM(votes.final_points) as total_points,
    COUNT(votes.id) as vote_count
FROM votes
JOIN entries ON votes.entry_id = entries.id
JOIN divisions ON votes.division_id = divisions.id
WHERE votes.event_id = 1
GROUP BY entries.id, divisions.type
ORDER BY divisions.type, total_points DESC;
```

### Get Leaderboard
```sql
SELECT
    entries.name,
    participants.name as participant,
    SUM(votes.final_points) as total_points
FROM votes
JOIN entries ON votes.entry_id = entries.id
LEFT JOIN participants ON entries.participant_id = participants.id
WHERE votes.event_id = 1
GROUP BY entries.id
ORDER BY total_points DESC
LIMIT 10;
```
