<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One of Ghana's 16 regions. Its towns (delivery zones) carry the delivery fees. */
class Region extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['default_fee' => 'decimal:2'];
    }

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class)->orderBy('name');
    }
}
