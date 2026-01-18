<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'organization_name',
        'organization_email',
        'organization_phone',
        'organization_address',
        'timezone',
        'date_format',
        'time_format',
        'primary_color',
        'accent_color',
        'logo_path',
        'favicon_path',
        'default_voting_type',
        'max_votes_per_user',
        'allow_vote_changes',
        'require_email_verification',
        'maintenance_mode',
        'maintenance_message',
    ];

    protected $casts = [
        'default_voting_type' => 'integer',
        'max_votes_per_user' => 'integer',
        'allow_vote_changes' => 'boolean',
        'require_email_verification' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];
}
