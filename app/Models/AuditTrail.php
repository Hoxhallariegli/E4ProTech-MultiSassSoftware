<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Database\Factories\AuditTrailsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrail extends Model
{
    /** @use HasFactory<AuditTrailsFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'link',
        'reference_id',
        'section',
        'type',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * @return BelongsTo<User, AuditTrail>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, AuditTrail> */
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function log($model, string $type, string $section): void
    {
        $dirty = $model->getDirty();
        $original = $model->getOriginal();

        if ($type === 'update' && empty($dirty)) {
            $dirty = $model->getChanges();
        }

        $oldValues = $type === 'update' ? array_intersect_key($original, $dirty) : ($type === 'delete' ? $model->attributesToArray() : null);
        $newValues = $type === 'update' ? $dirty : ($type === 'create' ? $model->attributesToArray() : null);

        self::create([
            'user_id' => auth()->id(),
            'title' => ucfirst($type) . " " . class_basename($model),
            'reference_id' => (string)$model->id,
            'section' => $section,
            'type' => $type,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public static function logCustom($model, string $type, string $section, string $title, ?array $newValues = null): void
    {
        self::create([
            'user_id' => auth()->id() ?? ($model instanceof User ? $model->id : null),
            'title' => $title,
            'reference_id' => (string) ($model ? $model->id : '0'),
            'section' => $section,
            'type' => $type,
            'old_values' => null,
            'new_values' => $newValues ?? ($model ? $model->attributesToArray() : null),
        ]);
    }

    protected static function newFactory(): AuditTrailsFactory
    {
        return AuditTrailsFactory::new();
    }
}
