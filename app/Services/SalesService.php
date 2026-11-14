<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates an order from a list of products, for the website, the POS and phone orders alike.
 * Prices are looked up here from the database, never taken from the customer's browser.
 */
class SalesService
{
    public const WALK_IN_PHONE = '0000000000';

    public function __construct(private StockService $stock, private SmsService $sms) {}

    /**
     * @param array{name:string, phone:?string, location:?string} $customer
     * @param array<int, array{variant_id:int, qty:int, price?:float}> $lines  "price" is honoured only when $opts['trust_prices'] is true (POS)
     * @param array{source:string, status?:string, delivery_fee?:float, notes?:?string, user?:?User, trust_prices?:bool, update_customer?:bool,
     *              payment?:array{method:string, amount:float}|null, mode?:string} $opts
     */
    public function placeOrder(array $customer, array $lines, array $opts): Delivery
    {
        $user = $opts['user'] ?? null;
        $source = $opts['source'];
        $trust = (bool) ($opts['trust_prices'] ?? false);
        $paying = $opts['payment'] ?? null;
        $done = ($opts['status'] ?? 'pending') === 'delivered';

        if (! $lines) {
            throw ValidationException::withMessages(['items' => 'Add at least one product.']);
        }

        $variants = ProductVariant::with('product')->whereIn('id', array_column($lines, 'variant_id'))->get()->keyBy('id');
        $priced = [];
        foreach ($lines as $l) {
            $v = $variants[(int) $l['variant_id']] ?? null;
            if (! $v || ! $v->is_active || ! $v->product->is_active) {
                throw ValidationException::withMessages(['items' => 'One of the products is no longer available.']);
            }
            if ($source === 'online' && ! $v->product->show_in_store) {
                throw ValidationException::withMessages(['items' => "\"{$v->product->name}\" is not available online."]);
            }
            $priced[] = ['variant_id' => $v->id, 'qty' => max(1, (int) $l['qty']), 'price' => $trust && isset($l['price']) ? max(0, (float) $l['price']) : $v->selling_price];
        }

        $goods = array_sum(array_map(fn ($l) => $l['qty'] * $l['price'], $priced));
        $package = collect($priced)->map(fn ($l) => $variants[$l['variant_id']]->product->name
            . ($variants[$l['variant_id']]->is_plain ? '' : ' (' . $variants[$l['variant_id']]->label . ')') . ' x' . $l['qty'])->implode(', ');

        $delivery = DB::transaction(function () use ($customer, $priced, $opts, $user, $source, $goods, $package, $paying, $done) {
            $cust = $this->customerFor($customer, (bool) ($opts['update_customer'] ?? false));

            $delivery = Delivery::create([
                'reference' => Delivery::newReference(),
                'customer_id' => $cust->id,
                'location' => $customer['location'] ?: ($source === 'pos' ? 'In store' : 'To be confirmed'),
                'package_description' => mb_substr($package, 0, 200),
                'delivery_mode' => $opts['mode'] ?? ($source === 'pos' ? 'pick_up' : 'rider'),
                'order_type' => 'standard',
                'source' => $source,
                'notes' => $opts['notes'] ?? null,
                'delivery_date' => today(),
                'total_amount' => $goods,
                'delivery_fee' => (float) ($opts['delivery_fee'] ?? 0),
                'items_sent' => array_sum(array_column($priced, 'qty')),
                'items_kept' => $done ? array_sum(array_column($priced, 'qty')) : null,
                'status' => $done ? 'delivered' : 'pending',
                'delivered_at' => $done ? now() : null,
                'created_by' => $user?->id,
            ]);
            $delivery->logEvent('created', match ($source) {
                'online' => 'Order placed on the website', 'pos' => 'Sold at the counter', default => 'Order created',
            }, $user?->id);

            // Stock comes off in the same transaction: not enough stock cancels the whole order.
            $this->stock->syncDeliveryItems($delivery, $priced, $user);

            if ($paying && $paying['amount'] > 0) {
                $amount = min((float) $paying['amount'], $delivery->amount_due);
                $delivery->payments()->create(['amount' => $amount, 'method' => $paying['method'], 'recorded_by' => $user?->id, 'paid_at' => now(), 'notes' => $source === 'pos' ? 'Paid at the counter' : null]);
                $delivery->recalcPaid();
            }

            return $delivery;
        });

        return $delivery->load(['customer', 'items.product', 'items.variant']);
    }

    /** Text the customer and the managers about a new website order. Failures never block the order. */
    public function notifyOnlineOrder(Delivery $d): void
    {
        $paid = $d->payment_status === 'paid';
        $this->sms->send($d->customer->phone,
            "Drezwel: thank you {$d->customer->name}! "
            . ($paid ? "Payment of GH₵ " . number_format($d->amount_paid, 2) . " received for order {$d->reference}. We will call to arrange delivery."
                : "Order {$d->reference} for GH₵ " . number_format($d->amount_due, 2) . ' received. We will call to confirm delivery.')
            . ' Track it: ' . route('track', ['ref' => $d->reference, 'phone' => $d->customer->phone]), $d->id, 'order_received');

        User::where('role', 'manager')->where('is_active', true)->whereNotNull('phone')->get()->each(fn ($m) => $this->sms->send(
            $m->phone, "Drezwel Go: NEW online order {$d->reference} from {$d->customer->name} ({$d->customer->phone}), GH₵ " . number_format($d->amount_due, 2) . ($paid ? ' (PAID online).' : '.'), $d->id, 'new_order'));
    }

    /**
     * Customers are matched by phone number. A website visitor must not be able to rename an existing
     * customer, so name/location are only updated when staff create the order.
     */
    private function customerFor(array $c, bool $update): Customer
    {
        $phone = Phone::clean($c['phone'] ?? null) ?: self::WALK_IN_PHONE;
        $customer = Customer::firstOrNew(['phone' => $phone]);
        if (! $customer->exists || $update) {
            $customer->name = trim($c['name'] ?? '') ?: ($phone === self::WALK_IN_PHONE ? 'Walk-in customer' : 'Customer');
            if (! empty($c['location'])) {
                $customer->location = $c['location'];
            }
            $customer->save();
        }

        return $customer;
    }
}
