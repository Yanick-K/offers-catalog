<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * @param array<string, mixed> $changes
     */
    public static function record(Model $model, string $action, array $changes = []): void
    {
        self::create([
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'user_id' => Auth::id(),
            'changes' => $changes,
        ]);
    }
}
