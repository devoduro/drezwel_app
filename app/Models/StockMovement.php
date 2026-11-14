<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPES = [
        'opening' => 'Opening stock', 'purchase' => 'Stock received', 'sale' => 'Sold / dispatched',
        'return' => 'Returned to stock', 'adjustment' => 'Adjustment', 'damage' => 'Damaged / lost',
    ];

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
