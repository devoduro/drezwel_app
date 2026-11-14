<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }

    public function remittances(): HasMany
    {
        return $this->hasMany(Remittance::class, 'rider_id');
    }

    /**
     * Cash and items a rider still has to hand in:
     * cash collected from customers minus cash remitted, and items due back minus items checked in.
     */
    public function custody(): array
    {
        $cashCollected = (float) Payment::where('rider_id', $this->id)->where('method', 'cash')->sum('amount');
        $cashRemitted = (float) $this->remittances()->sum('cash');

        // Items come back when a customer keeps fewer than were sent, or when a delivery fails.
        $due = (int) Delivery::where('rider_id', $this->id)->where('status', 'delivered')
            ->selectRaw('COALESCE(SUM(GREATEST(items_sent - COALESCE(items_kept, items_sent), 0)), 0) as n')->value('n')
            + (int) Delivery::where('rider_id', $this->id)->where('status', 'undelivered')->sum('items_sent');
        $itemsRemitted = (int) $this->remittances()->sum('items');

        // The delivery fee is collected from the customer along with the goods, handed in with the rest,
        // and then paid back to the rider by the business.
        $feeQuery = fn () => Delivery::where('rider_id', $this->id)->where('status', 'delivered')->where('delivery_fee', '>', 0);
        $feesOwed = (float) $feeQuery()->where('rider_fee_status', 'pending')->sum('delivery_fee');
        $feesPaid = (float) $feeQuery()->where('rider_fee_status', 'paid')->sum('delivery_fee');

        $cashHeld = round($cashCollected - $cashRemitted, 2);
        $itemsPending = max($due - $itemsRemitted, 0);

        return [
            'cash_collected' => $cashCollected,
            'cash_remitted' => $cashRemitted,
            'cash_held' => $cashHeld,
            'fees_owed' => $feesOwed,
            'fees_paid' => $feesPaid,
            'items_due' => $due,
            'items_remitted' => $itemsRemitted,
            'items_pending' => $itemsPending,
            'open_runs' => $this->deliveries()->whereIn('status', ['assigned', 'out_for_delivery'])->count(),
            'cleared' => $cashHeld <= 0 && $itemsPending === 0,
        ];
    }
}
