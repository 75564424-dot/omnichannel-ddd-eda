<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string      $id
 * @property string|null $tenant_id
 * @property int|null    $user_id
 * @property string      $reporter_name
 * @property string      $reporter_email
 * @property string|null $tenant_name
 * @property string|null $tenant_slug
 * @property string|null $subject
 * @property string      $description
 * @property string      $severity
 * @property string      $status
 * @property string|null $page_url
 * @property array<string, mixed> $diagnostic_log
 * @property string|null        $admin_response
 * @property string|null        $responded_by_name
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property \Illuminate\Support\Carbon|null $client_read_at
 */
final class ClientIncidentReportModel extends Model
{
    protected $table = 'client_incident_reports';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'user_id',
        'reporter_name',
        'reporter_email',
        'tenant_name',
        'tenant_slug',
        'subject',
        'description',
        'severity',
        'status',
        'page_url',
        'diagnostic_log',
        'admin_response',
        'responded_by_name',
        'responded_at',
        'client_read_at',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'diagnostic_log'    => 'array',
        'responded_at'      => 'datetime',
        'client_read_at'    => 'datetime',
        'acknowledged_at'   => 'datetime',
        'resolved_at'       => 'datetime',
    ];

    public function hasUnreadResponseForClient(): bool
    {
        return $this->admin_response !== null
            && $this->admin_response !== ''
            && $this->client_read_at === null;
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<TenantModel, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }
}
