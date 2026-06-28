<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncCursor extends Model
{
    protected $fillable = [
        'entity',
        'cursor_value',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public static function getValue(string $entity): ?string
    {
        return static::query()->where('entity', $entity)->value('cursor_value');
    }

    public static function setValue(string $entity, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['entity' => $entity],
            ['cursor_value' => $value, 'last_synced_at' => now()]
        );
    }
}
