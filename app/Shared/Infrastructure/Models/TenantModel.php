<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $status
 * @property array<string, mixed>|null $settings
 */
final class TenantModel extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
