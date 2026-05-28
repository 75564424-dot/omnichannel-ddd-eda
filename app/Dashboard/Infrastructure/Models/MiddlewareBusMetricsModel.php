<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class MiddlewareBusMetricsModel extends Model
{
    protected $table      = 'observability_metrics';
    protected $primaryKey = 'id';
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
