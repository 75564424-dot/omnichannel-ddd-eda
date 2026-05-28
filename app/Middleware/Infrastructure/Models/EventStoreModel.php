<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EventStoreModel extends Model
{
    public $timestamps = false;

    protected $table = 'event_store';

    protected $fillable = [
        'event_uuid',
        'tenant_id',
        'correlation_id',
        'causation_id',
        'aggregate_type',
        'aggregate_id',
        'event_type',
        'event_version',
        'origin',
        'channel_id',
        'integration_id',
        'payload',
        'metadata',
        'occurred_at',
        'recorded_at',
        'schema_version',
    ];

    protected $casts = [
        'payload'     => 'array',
        'metadata'    => 'array',
        'occurred_at' => 'datetime',
        'recorded_at' => 'datetime',
    ];
}
