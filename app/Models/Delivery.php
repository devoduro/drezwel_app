<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'undelivered' => 'Undelivered',
        'cancelled' => 'Cancelled',
    ];

    public const MODES = ['rider' => 'Rider', 'station_bus' => 'Station Bus', 'parcel_office' => 'Parcel Office', 'pick_up' => 'Pick Up'];

    /** How a website customer chose to receive the order. */
    public const SERVICES = ['door' => 'Regular delivery', 'express' => 'Express delivery', 'pickup' => 'Pick up'];

    public const TYPES = ['standard' => 'Standard Delivery', 'selection_trial' => 'Selection Trial', 'exchange' => 'Exchange'];

    public const WINDOWS = ['7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm'];

    public const MAX_CODE_ATTEMPTS = 5;

    /** SQL for money received for the goods: what was paid less the delivery fee, which passes through to the rider. */
    public const GOODS_RECEIVED_SQL = "CASE WHEN amount_paid > 0 THEN GREATEST(amount_paid - delivery_fee, 0) ELSE amount_paid END";

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'delivered_at' => 'datetime',
            'rider_fee_paid_at' => 'datetime',
            'code_sent_at' => 'datetime',
            'payment_requested_at' => 'datetime',
            'confirmed_by_manager' => 'boolean',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** The sale this exchange / return was made against, if it was started from one. */
    public function exchangeOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'exchange_of_id');
    }

    public function exchanges(): HasMany
    {
        return $this->hasMany(self::class, 'exchange_of_id');
    }

    /** Items the customer is bringing back (stored as negative quantities). */
    public function getReturnedItemsAttribute()
    {
        return $this->items->filter(fn ($i) => $i->qty < 0)->values();
    }

    /** Items being handed over (positive quantities). */
    public function getSentItemsAttribute()
    {
        return $this->items->filter(fn ($i) => $i->qty > 0)->values();
    }

    /** Money owed back to the customer, when what they returned is worth more than what they took. */
    public function getRefundDueAttribute(): float
    {
        return max(round((float) $this->amount_paid - $this->amount_due, 2), 0);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeliveryEvent::class)->orderBy('id');
    }

    /** What the customer pays in total: the goods plus the delivery fee. */
    public function getAmountDueAttribute(): float
    {
        return round((float) $this->total_amount + (float) $this->delivery_fee, 2);
    }

    /** Money received for the goods: what was paid, less the delivery fee that passes through to the rider. */
    public function getGoodsReceivedAttribute(): float
    {
        $paid = (float) $this->amount_paid;

        return $paid > 0 ? round(max($paid - (float) $this->delivery_fee, 0), 2) : $paid;
    }

    /** What is still owed for the goods themselves. */
    public function getGoodsBalanceAttribute(): float
    {
        return max(round((float) $this->total_amount - $this->goods_received, 2), 0);
    }

    /**
     * Orders that must be paid online before they go out: express, pick up, and door delivery outside Accra
     * (those leave by station bus or parcel office, so there is nobody to pay on arrival).
     */
    public function getPayNowOnlyAttribute(): bool
    {
        return in_array($this->service, ['express', 'pickup'], true) || ($this->service === 'door' && $this->delivery_mode !== 'rider');
    }

    public function getBalanceAttribute(): float
    {
        return max(round($this->amount_due - (float) $this->amount_paid, 2), 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsOpenAttribute(): bool
    {
        return in_array($this->status, ['pending', 'assigned', 'out_for_delivery', 'undelivered'], true);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', '!=', 'cancelled');
    }

    /** Anything that is actually delivered or handed over to a rider: counter sales (point of sale) never are. */
    public function scopeForDelivery(Builder $q): Builder
    {
        return $q->where('source', '!=', 'pos');
    }

    /** Orders that are not still waiting on (or failed at) an online payment, so a rider can be sent. */
    public function scopePaymentSettled(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->whereNull('payment_status')->orWhere('payment_status', 'paid'));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function ($w) use ($term) {
            $w->where('reference', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('package_description', 'like', "%{$term}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
        });
    }

    public function recalcPaid(): void
    {
        $this->amount_paid = $this->payments()->sum('amount');
        $this->save();
    }

    public function logEvent(string $type, ?string $note = null, ?int $userId = null): void
    {
        $this->events()->create(['type' => $type, 'note' => $note, 'user_id' => $userId]);
    }

    public static function newReference(): string
    {
        do {
            $ref = 'DEL-' . now()->format('ymd') . '-' . strtolower(substr(bin2hex(random_bytes(2)), 0, 4));
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }
}
