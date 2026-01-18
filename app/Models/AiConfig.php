<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConfig extends Model
{
    protected $table = 'ai_configs';

    protected $fillable = [
        'selected_voice',
        'assistant_mode',
        'intensity',
        'default_model',
        'temperature',
        'max_tokens',
        'speech_speed',
        'enable_tts',
        'content_filter',
        'pii_detection',
        'transcript_logging',
        'record_calls',
        'greeting_message',
        'greeting_voice',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'speech_speed' => 'float',
        'enable_tts' => 'boolean',
        'content_filter' => 'boolean',
        'pii_detection' => 'boolean',
        'transcript_logging' => 'boolean',
        'record_calls' => 'boolean',
    ];

    protected $attributes = [
        'selected_voice' => 'alloy',
        'assistant_mode' => 'hybrid',
        'intensity' => 'moderate',
        'default_model' => 'gpt-4o',
        'temperature' => 0.7,
        'max_tokens' => 4096,
        'speech_speed' => 1.0,
        'enable_tts' => true,
        'content_filter' => true,
        'pii_detection' => true,
        'transcript_logging' => true,
        'record_calls' => false,
        'greeting_message' => "Hello! I'm your AI voting assistant. How can I help you today?",
    ];
}
