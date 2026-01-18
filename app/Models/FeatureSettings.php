<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSettings extends Model
{
    protected $table = 'feature_settings';

    protected $fillable = [
        // Core Features
        'public_voting',
        'live_results',
        'judging_panel',
        'import_export',
        'pdf_reports',
        'categories',

        // AI Chat Slider
        'ai_chat_enabled',
        'ai_show_on_voting',
        'ai_show_on_results',
        'ai_show_on_admin',
        'ai_show_on_dashboard',
        'ai_show_on_landing',
        'ai_show_on_mobile',

        // AI Capabilities
        'ai_voice_input',
        'ai_voice_output',
        'ai_charts',
        'ai_data_modification',

        // AI Appearance
        'ai_chat_position',
        'ai_button_color',
        'ai_panel_width',

        // Notifications
        'notifications_enabled',
        'notify_email',
        'notify_sms',
        'notify_push',
        'notify_event_reminders',
        'notify_voting_updates',
        'notify_result_alerts',
        'notify_admin_alerts',
        'notify_security_alerts',
        'notify_promotional',
    ];

    protected $casts = [
        'public_voting' => 'boolean',
        'live_results' => 'boolean',
        'judging_panel' => 'boolean',
        'import_export' => 'boolean',
        'pdf_reports' => 'boolean',
        'categories' => 'boolean',
        'ai_chat_enabled' => 'boolean',
        'ai_show_on_voting' => 'boolean',
        'ai_show_on_results' => 'boolean',
        'ai_show_on_admin' => 'boolean',
        'ai_show_on_dashboard' => 'boolean',
        'ai_show_on_landing' => 'boolean',
        'ai_show_on_mobile' => 'boolean',
        'ai_voice_input' => 'boolean',
        'ai_voice_output' => 'boolean',
        'ai_charts' => 'boolean',
        'ai_data_modification' => 'boolean',
        'ai_panel_width' => 'integer',
        'notifications_enabled' => 'boolean',
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'notify_push' => 'boolean',
        'notify_event_reminders' => 'boolean',
        'notify_voting_updates' => 'boolean',
        'notify_result_alerts' => 'boolean',
        'notify_admin_alerts' => 'boolean',
        'notify_security_alerts' => 'boolean',
        'notify_promotional' => 'boolean',
    ];
}
