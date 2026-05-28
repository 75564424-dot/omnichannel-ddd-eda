<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for message_queue.
 * Tracks every event that passes through the Event Bus.
 */
class QueueEntryModel extends Model
{
    protected $table = 'message_queue';

    protected $fillable = [
        'tenant_id',
        'event_uuid',
        'message_type',
        'origin',
        'target_consumers',
        'payload',
        'status',
        'published_at',
        'dispatched_at',
        'processing_time_ms',
        'attempt_count',
        'correlation_id',
        'priority',
        'max_attempts',
    ];

    protected $casts = [
        'target_consumers' => 'array',
        'payload'          => 'array',
        'published_at'     => 'datetime',
        'dispatched_at'    => 'datetime',
    ];
}
