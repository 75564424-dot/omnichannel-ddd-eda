<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class EventFeedEntryModel extends Model
{
    protected $table      = 'event_feed_projections';
    protected $primaryKey = 'id';
    public    $timestamps = true;
    const     UPDATED_AT  = null;

    protected $fillable = [
        'event_uuid', 'event_type', 'origin', 'impact',
        'status', 'occurred_at', 'received_at', 'raw_payload', 'correlation_id',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'occurred_at' => 'datetime',
        'received_at' => 'datetime',
    ];
}
