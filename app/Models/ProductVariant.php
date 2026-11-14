<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** "Size M · Black", or "Standard" for a product with no sizes or colours. */
    public function getLabelAttribute(): string
    {
        $parts = array_filter([$this->size ? "Size {$this->size}" : null, $this->color]);

        return $parts ? implode(' · ', $parts) : 'Standard';
    }

    public function getIsPlainAttribute(): bool
    {
        return ! $this->size && ! $this->color;
    }

    /** Price a customer pays for this variant (the variant's own, else the product's). */
    public function getSellingPriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product->selling_price);
    }
}
