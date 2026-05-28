<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $logical_id
 * @property string      $type
 * @property string      $name
 * @property array<int, string> $event_types
 */
final class MiddlewareRegisteredModule extends Model
{
    protected $table = 'registered_modules';

    protected $fillable = [
        'logical_id',
        'type',
        'name',
        'event_types',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_types' => 'array',
        ];
    }
}
