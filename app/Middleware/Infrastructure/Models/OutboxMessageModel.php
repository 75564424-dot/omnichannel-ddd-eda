<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxMessageModel extends Model
{
    public $timestamps = false;

    protected $table = 'outbox_messages';

    protected $fillable = [
        'event_uuid',
        'event_type',
        'origin',
        'payload',
        'status',
        'attempt_count',
        'published_at',
        'created_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
