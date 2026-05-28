<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLogModel extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'tenant_id',
        'actor_type',
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'changes',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'changes'     => 'array',
        'occurred_at' => 'datetime',
    ];
}
