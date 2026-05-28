<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class NodeStatusSnapshotModel extends Model
{
    protected $table      = 'channel_status_snapshots';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['channel_id', 'node_code', 'status', 'events_enabled', 'metadata', 'recorded_at', 'updated_at'];

    protected $casts = [
        'events_enabled' => 'boolean',
        'metadata'       => 'array',
        'recorded_at'    => 'datetime',
        'updated_at'     => 'datetime',
    ];
}
