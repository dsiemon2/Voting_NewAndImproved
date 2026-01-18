<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Voting Type
    |--------------------------------------------------------------------------
    |
    | The default voting type to use when creating new events.
    |
    */
    'default_voting_type' => 'ranked_321',

    /*
    |--------------------------------------------------------------------------
    | Voting Type Categories
    |--------------------------------------------------------------------------
    |
    | Available voting type categories in the system.
    |
    */
    'categories' => [
        'ranked' => 'Ranked Voting',
        'approval' => 'Approval Voting',
        'weighted' => 'Weighted Voting',
        'rating' => 'Rating Voting',
        'cumulative' => 'Cumulative Voting',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Definitions
    |--------------------------------------------------------------------------
    |
    | System modules that can be enabled/disabled per event template.
    |
    */
    'modules' => [
        'divisions' => [
            'name' => 'Divisions',
            'description' => 'Organize entries into divisions or tiers',
            'icon' => 'fa-layer-group',
            'route_prefix' => 'divisions',
            'is_core' => false,
        ],
        'participants' => [
            'name' => 'Participants',
            'description' => 'Manage participants/contestants',
            'icon' => 'fa-users',
            'route_prefix' => 'participants',
            'is_core' => false,
        ],
        'categories' => [
            'name' => 'Categories',
            'description' => 'Event categories for voting',
            'icon' => 'fa-tags',
            'route_prefix' => 'categories',
            'is_core' => false,
        ],
        'entries' => [
            'name' => 'Entries',
            'description' => 'Manage items being voted on',
            'icon' => 'fa-clipboard-list',
            'route_prefix' => 'entries',
            'is_core' => false,
        ],
        'import' => [
            'name' => 'Import',
            'description' => 'Bulk import from spreadsheets',
            'icon' => 'fa-file-import',
            'route_prefix' => 'import',
            'is_core' => false,
        ],
        'voting' => [
            'name' => 'Voting',
            'description' => 'Cast votes',
            'icon' => 'fa-vote-yea',
            'route_prefix' => 'voting',
            'is_core' => true,
        ],
        'results' => [
            'name' => 'Results',
            'description' => 'View voting results',
            'icon' => 'fa-chart-bar',
            'route_prefix' => 'results',
            'is_core' => true,
        ],
        'reports' => [
            'name' => 'Reports',
            'description' => 'Generate detailed reports',
            'icon' => 'fa-file-alt',
            'route_prefix' => 'reports',
            'is_core' => false,
        ],
        'pdf' => [
            'name' => 'PDF Export',
            'description' => 'Print ballots and results',
            'icon' => 'fa-file-pdf',
            'route_prefix' => 'pdf',
            'is_core' => false,
        ],
        'judging' => [
            'name' => 'Judging Panel',
            'description' => 'Professional judges with weighted votes',
            'icon' => 'fa-gavel',
            'route_prefix' => 'judging',
            'is_core' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Place Labels
    |--------------------------------------------------------------------------
    |
    | Default labels for voting places.
    |
    */
    'place_labels' => [
        1 => '1st Place',
        2 => '2nd Place',
        3 => '3rd Place',
        4 => '4th Place',
        5 => '5th Place',
        6 => '6th Place',
        7 => '7th Place',
        8 => '8th Place',
        9 => '9th Place',
        10 => '10th Place',
    ],

    /*
    |--------------------------------------------------------------------------
    | Place Colors
    |--------------------------------------------------------------------------
    |
    | Default colors for voting places (for display).
    |
    */
    'place_colors' => [
        1 => '#FFD700', // Gold
        2 => '#C0C0C0', // Silver
        3 => '#CD7F32', // Bronze
        4 => '#4A90A4', // Blue
        5 => '#6B7280', // Gray
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Places
    |--------------------------------------------------------------------------
    |
    | Maximum number of places allowed in a voting type.
    |
    */
    'max_places' => 10,

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Default validation rules for voting.
    |
    */
    'validation' => [
        'prevent_duplicate_selections' => true,
        'require_all_places' => false,
        'require_at_least_one' => true,
    ],

];
