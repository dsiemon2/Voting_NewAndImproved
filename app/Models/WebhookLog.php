<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'attempts',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the webhook this log belongs to.
     */
    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Check if the webhook was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }
}
