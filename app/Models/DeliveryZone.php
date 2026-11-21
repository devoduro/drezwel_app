<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A city / town / area in a region, with the delivery fee charged to reach it. */
class DeliveryZone extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Giving a town its first price switches it on, so pricing a town is all it takes to offer it.
        static::saving(function (self $z) {
            if ($z->isDirty('fee') && $z->getOriginal('fee') === null && $z->fee !== null && ! $z->isDirty('is_active')) {
                $z->is_active = true;
            }
        });
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** Towns customers can pick: the ones switched on. A town with no fee of its own uses its region's fee. */
    public function scopeOrderable(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
