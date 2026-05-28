<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for observability_metrics (bus scope).
 */
class BusMetricsModel extends Model
{
    protected $table      = 'observability_metrics';
    public    $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'metric_scope',
        'metric_key',
        'metric_value',
        'dimensions',
        'recorded_at',
    ];

    protected $casts = [
        'metric_value' => 'float',
        'dimensions'   => 'array',
        'recorded_at'  => 'datetime',
    ];
}
