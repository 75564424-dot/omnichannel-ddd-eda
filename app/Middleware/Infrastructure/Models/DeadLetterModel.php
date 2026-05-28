<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for dead_letter_queue.
 * Events that exhausted all retries are moved here for operational review.
 */
class DeadLetterModel extends Model
{
    protected $table      = 'dead_letter_queue';
    public    $timestamps = false;

    protected $fillable = [
        'message_queue_id',
        'event_uuid',
        'event_type',
        'origin',
        'payload',
        'failure_reason',
        'failure_code',
        'retry_count',
        'failed_at',
        'resolved_at',
        'resolution_action',
    ];

    protected $casts = [
        'payload'     => 'array',
        'failed_at'   => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
