<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'location', 'notes'];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
