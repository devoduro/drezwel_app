<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash' => 'Cash', 'momo' => 'MTN MoMo', 'bank' => 'Bank Transfer'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
