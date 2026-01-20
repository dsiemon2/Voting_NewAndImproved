# Voting System Compatibility Matrix

## Event Templates

| ID | Template Name | Participant Label | Entry Label | Division Types |
|----|--------------|-------------------|-------------|----------------|
| 1 | Food Competition | Chef | Entry | P (Professional), A (Amateur) |
| 2 | Photo Contest | Photographer | Photo | N (Nature), P (Portrait), S (Street) |
| 3 | General Vote | Nominee | Option | None (flexible) |
| 4 | Employee Recognition | Employee | Nomination | None (flexible) |
| 5 | Art Competition | Artist | Artwork | None (flexible) |
| 6 | Talent Show | Performer | Performance | None (flexible) |

## Voting Types

| ID | Name | Category | Description | Places | Point Distribution |
|----|------|----------|-------------|--------|-------------------|
| 1 | Standard Ranked (3-2-1) | ranked | Classic 3-place ranking | 3 | 3, 2, 1 |
| 2 | Extended Ranked (5-4-3-2-1) | ranked | Five-place ranking | 5 | 5, 4, 3, 2, 1 |
| 3 | Top-Heavy (5-3-1) | ranked | Emphasis on winning | 3 | 5, 3, 1 |
| 4 | Equal Weight | approval | All votes count equally | N/A | 1 per vote |
| 5 | Limited Approval (Top 3) | approval | Select up to 3 favorites | N/A | 1 per vote (max 3) |
| 6 | Weighted with Judges | weighted | Judges count more | 3 | Judge 3x, Public 1x |
| 7 | 5-Star Rating | rating | Rate entries 1-5 stars | N/A | 1-5 scale |

## Template + Voting Type Compatibility

All templates are compatible with all voting types. The choice depends on the use case:

| Template | Best Voting Types | Reasoning |
|----------|-------------------|-----------|
| Food Competition | Ranked (1, 2, 3) | Traditional competition format with clear winners |
| Photo Contest | Ranked or Rating | Subjective judging benefits from ratings or rankings |
| General Vote | Approval (4, 5) | Simple majority voting |
| Employee Recognition | Weighted (6) | Management votes may carry more weight |
| Art Competition | Rating (7) | Artistic merit suits star ratings |
| Talent Show | Ranked (1, 2, 3) | Clear 1st, 2nd, 3rd place format |

## Division Code Conventions

### Legacy Format (Recommended)
Division codes should follow the pattern: `{TypeCode}{Number}`

Examples:
- P1, P2, P3 (Professional 1, 2, 3)
- A1, A2, A3 (Amateur 1, 2, 3)
- N1, N2 (Nature 1, 2)

### Alternative Format (Supported)
Single-letter or descriptive codes are supported:
- T (Traditional), V (Verde), H (Homestyle)
- B (Brisket), R (Ribs), P (Pulled Pork)

When using alternative formats, the system looks up entries by `entry_number` within divisions of the matching type.

## Entry Number Conventions

| Division Type | Entry Number Range |
|--------------|-------------------|
| First type (usually Professional) | 1-99 |
| Second type (usually Amateur) | 101-199 |
| Third type | 201-299 |
| Fourth type | 301-399 |

This convention ensures unique entry numbers per event and easy identification of division type.

## Database Schema Support

### Core Tables
- `event_templates` - Template definitions with division_types JSON
- `voting_types` - Voting type definitions with category and settings
- `voting_type_place_configs` - Point configurations for ranked voting
- `events` - Events linking to templates
- `event_voting_configs` - Event-specific voting settings
- `divisions` - Divisions with code, name, and type
- `entries` - Entries with entry_number and division link
- `votes` - Individual votes with place, points, and weights

### Flexibility Features
1. **JSON Settings**: Both templates and voting types support JSON settings for custom configurations
2. **Dynamic Place Configs**: Ranked voting types can have any number of places
3. **Weight Multipliers**: Votes can have different weights (judges vs public)
4. **Division Types**: Templates can define any division type codes

## Validation Rules

### Required Configurations
1. Every event MUST have a voting_type_id set in event_voting_configs
2. Ranked voting types MUST have at least one place config
3. Entries MUST have unique entry_numbers per event
4. Divisions MUST have a type matching template's division_types (if defined)

### Recommended Validations
1. Warn if division codes don't follow the legacy format
2. Warn if entry numbers don't follow the convention
3. Prevent duplicate votes by same user for ranked/approval types
4. Validate rating values are within configured min/max

## Adding New Templates

1. Create template in `event_templates`:
   ```sql
   INSERT INTO event_templates (name, participant_label, entry_label, division_types)
   VALUES ('New Template', 'Participant', 'Entry', '[{"code":"X","name":"Category","description":"..."}]');
   ```

2. Division types JSON format:
   ```json
   [
     {"code": "X", "name": "Category Name", "description": "Description"}
   ]
   ```

3. Null division_types allows flexible division creation without type constraints.

## Adding New Voting Types

1. Create voting type in `voting_types`:
   ```sql
   INSERT INTO voting_types (name, category, description, settings)
   VALUES ('New Type', 'ranked', 'Description', '{"custom": "settings"}');
   ```

2. For ranked types, add place configs:
   ```sql
   INSERT INTO voting_type_place_configs (voting_type_id, place, points, label, color)
   VALUES (8, 1, 10, '1st', 'gold'),
          (8, 2, 5, '2nd', 'silver');
   ```

3. Valid categories: `ranked`, `approval`, `rating`, `weighted`

## Testing Checklist

- [ ] Voting works with legacy division codes (P1, A1)
- [ ] Voting works with alternative division codes (T, V, H)
- [ ] Voting works with entry_number lookup
- [ ] Results display correctly sorted by points
- [ ] Export generates correct PDF/CSV
- [ ] Each voting category (ranked/approval/rating/weighted) functions
- [ ] Duplicate vote prevention works
- [ ] Invalid entry numbers are rejected
