<?php

declare(strict_types=1);

namespace App\Control\Infrastructure\Models;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $status
 * @property array<string, mixed>|null $metrics
 * @property array<int, string>|null $event_ids
 */
final class SimulationRunModel extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public $incrementing = false;

    protected $table = 'simulation_runs';

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'started_by_user_id',
        'status',
        'fixture_slug',
        'events_per_minute',
        'duration_minutes',
        'planned_total',
        'prepare_first',
        'published',
        'queue_matches',
        'progress_current',
        'started_at',
        'finished_at',
        'error_message',
        'metrics',
        'event_ids',
    ];

    protected $casts = [
        'prepare_first'    => 'boolean',
        'metrics'          => 'array',
        'event_ids'        => 'array',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }
}
