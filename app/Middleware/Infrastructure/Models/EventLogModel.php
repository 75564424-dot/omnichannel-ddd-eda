<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EventLogModel extends Model
{
    public $timestamps = false;

    protected $table = 'event_logs';

    protected $fillable = [
        'event_uuid',
        'tenant_id',
        'event_type',
        'origin',
        'channel_id',
        'integration_id',
        'correlation_id',
        'status',
        'summary',
        'payload_hash',
        'occurred_at',
        'logged_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'logged_at'   => 'datetime',
    ];
}
