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

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** Towns customers can pick: switched on and given a price. */
    public function scopeOrderable(Builder $q): Builder
    {
        return $q->where('is_active', true)->whereNotNull('fee');
    }
}
