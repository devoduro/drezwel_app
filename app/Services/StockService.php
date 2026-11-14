<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All stock changes go through here, so the ledger (stock_movements) and the running
 * balance (products.stock_qty) can never disagree, and stock can never go negative.
 */
class StockService
{
    public function __construct(private SmsService $sms) {}

    /** Add (+) or remove (-) units, record it, and raise or clear the low-stock alert. */
    public function move(Product $product, string $type, int $qty, ?User $by = null, ?string $note = null, ?Delivery $delivery = null, ?float $unitCost = null): StockMovement
    {
        $movement = DB::transaction(function () use ($product, $type, $qty, $by, $note, $delivery, $unitCost) {
            $p = Product::lockForUpdate()->findOrFail($product->id);
            $after = $p->stock_qty + $qty;
            if ($after < 0) {
                throw ValidationException::withMessages(['qty' => "Only {$p->stock_qty} of \"{$p->name}\" in stock."]);
            }

            // Receiving stock at a new cost updates the product's cost price (latest-cost method).
            $attrs = ['stock_qty' => $after];
            if ($type === 'purchase' && $unitCost !== null && $unitCost > 0) {
                $attrs['cost_price'] = $unitCost;
            }
            $p->update($attrs);

            return $p->movements()->create([
                'type' => $type, 'qty' => $qty, 'balance_after' => $after, 'unit_cost' => $unitCost ?? $p->cost_price,
                'delivery_id' => $delivery?->id, 'user_id' => $by?->id, 'note' => $note, 'created_at' => now(),
            ]);
        });

        $this->checkAlert($product->fresh());

        return $movement;
    }

    /**
     * Replace the product lines on a delivery. Old lines go back to stock, new ones come off it,
     * all in one transaction so a "not enough stock" error leaves everything untouched.
     *
     * @param array<int, array{product_id:int, qty:int, price:float}> $lines
     */
    public function syncDeliveryItems(Delivery $d, array $lines, User $by): void
    {
        $touched = [];
        DB::transaction(function () use ($d, $lines, $by, &$touched) {
            foreach ($d->items()->get() as $old) {
                $this->move($old->product, 'return', $old->qty, $by, "Order {$d->reference} edited", $d);
                $touched[$old->product_id] = true;
            }
            $d->items()->delete();

            foreach ($this->mergeLines($lines) as $line) {
                $product = Product::findOrFail($line['product_id']);
                $this->move($product, 'sale', -$line['qty'], $by, "Order {$d->reference}", $d);
                $d->items()->create([
                    'product_id' => $product->id, 'qty' => $line['qty'],
                    'unit_price' => $line['price'], 'unit_cost' => $product->cost_price,
                ]);
                $touched[$product->id] = true;
            }
        });
    }

    /** Put a cancelled order's goods back on the shelf. */
    public function releaseDelivery(Delivery $d, User $by): void
    {
        DB::transaction(function () use ($d, $by) {
            foreach ($d->items()->with('product')->get() as $item) {
                $this->move($item->product, 'return', $item->qty, $by, "Order {$d->reference} cancelled", $d);
            }
        });
    }

    /** Take goods off the shelf again when a cancelled order is reopened. */
    public function reserveDelivery(Delivery $d, User $by): void
    {
        DB::transaction(function () use ($d, $by) {
            foreach ($d->items()->with('product')->get() as $item) {
                $this->move($item->product, 'sale', -$item->qty, $by, "Order {$d->reference} reopened", $d);
            }
        });
    }

    /**
     * Text the managers once when a product drops to its reorder level, and re-arm the alert
     * once it is restocked above that level.
     */
    public function checkAlert(Product $p): void
    {
        if (! $p->is_active) {
            return;
        }
        $needs = $p->stock_qty <= $p->reorder_level;

        if ($needs && $p->low_alerted_at === null) {
            // Claim the alert first so two quick sales cannot both send it.
            $claimed = Product::where('id', $p->id)->whereNull('low_alerted_at')->update(['low_alerted_at' => now()]);
            if ($claimed !== 1) {
                return;
            }
            $msg = $p->stock_qty <= 0
                ? "Drezwel Go: \"{$p->name}\" is OUT OF STOCK. Please restock."
                : "Drezwel Go: \"{$p->name}\" is running low - only {$p->stock_qty} left (reorder level {$p->reorder_level}).";
            User::where('role', 'manager')->where('is_active', true)->whereNotNull('phone')->get()
                ->each(fn ($m) => $this->sms->send($m->phone, $msg, null, 'stock_alert'));
        } elseif (! $needs && $p->low_alerted_at !== null) {
            Product::where('id', $p->id)->update(['low_alerted_at' => null]);
        }
    }

    /** Same product twice on one order becomes one line. */
    private function mergeLines(array $lines): array
    {
        $merged = [];
        foreach ($lines as $l) {
            $id = (int) $l['product_id'];
            $merged[$id] ??= ['product_id' => $id, 'qty' => 0, 'price' => (float) $l['price']];
            $merged[$id]['qty'] += (int) $l['qty'];
        }

        return array_values($merged);
    }
}
