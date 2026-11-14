<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'is_active' => 'boolean',
            'low_alerted_at' => 'datetime',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function getIsOutAttribute(): bool
    {
        return $this->stock_qty <= 0;
    }

    public function getIsLowAttribute(): bool
    {
        return $this->stock_qty > 0 && $this->stock_qty <= $this->reorder_level;
    }

    /** ok | low | out */
    public function getLevelAttribute(): string
    {
        return $this->is_out ? 'out' : ($this->is_low ? 'low' : 'ok');
    }

    public function getMarginAttribute(): float
    {
        return (float) $this->selling_price - (float) $this->cost_price;
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Active products at or below their reorder level (includes out of stock). */
    public function scopeNeedingStock(Builder $q): Builder
    {
        return $q->where('is_active', true)->whereColumn('stock_qty', '<=', 'reorder_level');
    }
}
