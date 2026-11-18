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
            'cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'is_active' => 'boolean', 'is_deal' => 'boolean',
            'low_alerted_at' => 'datetime',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** First photo, or null when none have been uploaded. */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $img = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $img?->url;
    }

    /** The plain variant new stock goes to when a product has no sizes or colours. */
    public function defaultVariant(): ProductVariant
    {
        return $this->variants()->first() ?? $this->variants()->create(['stock_qty' => 0]);
    }

    public function getHasOptionsAttribute(): bool
    {
        return $this->variants->contains(fn ($v) => ! $v->is_plain);
    }

    /** Lowest and highest price across active variants (for "From GH₵ X"). */
    public function priceRange(): array
    {
        $prices = $this->variants->where('is_active', true)->map(fn ($v) => (float) ($v->price ?? $this->selling_price));
        $prices = $prices->isEmpty() ? collect([(float) $this->selling_price]) : $prices;

        return [$prices->min(), $prices->max()];
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

    /** Percent off against the "was" price, or null when there is no genuine discount. */
    public function getDiscountPercentAttribute(): ?int
    {
        $was = (float) $this->compare_at_price;
        $now = (float) $this->selling_price;

        return $was > 0 && $was > $now ? (int) round((1 - $now / $was) * 100) : null;
    }

    /** Products on offer: a "was" price above the selling price. */
    public function scopeOnOffer(Builder $q): Builder
    {
        // Today's deals are picked by the manager (is_deal), whether or not the price was reduced.
        return $q->where('is_deal', true);
    }

    public function scopeInStore(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('show_in_store', true);
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
