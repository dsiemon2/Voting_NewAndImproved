<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiVoice extends Model
{
    protected $table = 'ai_voices';

    protected $fillable = [
        'voice_id',
        'name',
        'gender',
        'description',
        'detail',
        'color',
        'accent',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get all available voices (static list matching OpenAI)
     */
    public static function getAvailableVoices(): array
    {
        return [
            'male' => [
                [
                    'id' => 'ash',
                    'name' => 'Ash',
                    'description' => 'Confident & authoritative',
                    'detail' => 'Clear articulation, professional',
                    'color' => '#2c3e50',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'echo',
                    'name' => 'Echo',
                    'description' => 'Calm & reassuring',
                    'detail' => 'Soft-spoken, gentle delivery',
                    'color' => '#1a5276',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'verse',
                    'name' => 'Verse',
                    'description' => 'Dynamic & engaging',
                    'detail' => 'Energetic, great for interaction',
                    'color' => '#6f42c1',
                    'accent' => 'American English',
                ],
            ],
            'female' => [
                [
                    'id' => 'alloy',
                    'name' => 'Alloy',
                    'description' => 'Neutral & balanced',
                    'detail' => 'Versatile, works for any context',
                    'color' => '#6c757d',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'ballad',
                    'name' => 'Ballad',
                    'description' => 'Warm & expressive',
                    'detail' => 'Emotional range, storytelling',
                    'color' => '#d63384',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'coral',
                    'name' => 'Coral',
                    'description' => 'Friendly & upbeat',
                    'detail' => 'Cheerful, great for engagement',
                    'color' => '#fd7e14',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'sage',
                    'name' => 'Sage',
                    'description' => 'Wise & professional',
                    'detail' => 'Mature, trustworthy tone',
                    'color' => '#198754',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'shimmer',
                    'name' => 'Shimmer',
                    'description' => 'Bright & energetic',
                    'detail' => 'Youthful, enthusiastic',
                    'color' => '#0dcaf0',
                    'accent' => 'American English',
                ],
                [
                    'id' => 'nova',
                    'name' => 'Nova',
                    'description' => 'Warm & patient',
                    'detail' => 'Best for guidance',
                    'color' => '#d63384',
                    'accent' => 'American English',
                ],
            ],
        ];
    }
}
