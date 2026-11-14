<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Remittance extends Model
{
    public const METHODS = ['cash' => 'Cash', 'momo' => 'MTN MoMo', 'bank' => 'Bank Transfer', 'warehouse' => 'Warehouse Return / Inventory Check-in'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['remitted_at' => 'datetime', 'cash' => 'decimal:2'];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
