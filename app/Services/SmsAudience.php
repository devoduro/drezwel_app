<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Support\Collection;

/** Works out who a bulk SMS goes to: a group, hand-picked people, or typed numbers. Each phone number appears once. */
class SmsAudience
{
    public const GROUPS = [
        'customers_all' => 'All customers',
        'customers_owing' => 'Customers who owe money',
        'customers_open' => 'Customers with an order in progress',
        'customers_recent' => 'Customers who ordered in the last 30 days',
        'riders_all' => 'All active riders',
        'selected' => 'People I pick',
        'numbers' => 'Numbers I type',
    ];

    /**
     * @param array{customer_ids?:array, rider_ids?:array, numbers?:?string} $input
     * @return array{recipients: Collection<int, array{name:?string, phone:string, type:string}>, skipped: array<int, array{label:string, reason:string}>, label:string}
     */
    public function resolve(string $audience, array $input = []): array
    {
        $people = collect();   // [name, phone(raw), type]
        $skipped = [];

        match ($audience) {
            'customers_all' => $people = $this->customers(Customer::query()),
            'customers_owing' => $people = $this->customers(Customer::whereIn('id', Delivery::active()->whereRaw('total_amount + delivery_fee > amount_paid')->select('customer_id'))),
            'customers_open' => $people = $this->customers(Customer::whereIn('id', Delivery::whereIn('status', ['pending', 'assigned', 'out_for_delivery'])->select('customer_id'))),
            'customers_recent' => $people = $this->customers(Customer::whereIn('id', Delivery::active()->where('delivery_date', '>=', today()->subDays(30))->select('customer_id'))),
            'riders_all' => $people = $this->riders(User::where('role', 'rider')->where('is_active', true)),
            'selected' => $people = $this->customers(Customer::whereIn('id', array_filter((array) ($input['customer_ids'] ?? []))))
                ->merge($this->riders(User::where('role', 'rider')->whereIn('id', array_filter((array) ($input['rider_ids'] ?? []))))),
            'numbers' => [$people, $skipped] = $this->typed((string) ($input['numbers'] ?? '')),
            default => null,
        };

        $recipients = collect();
        $seen = [];
        foreach ($people as $p) {
            $phone = Phone::clean($p['phone']);
            if ($phone === SalesService::WALK_IN_PHONE) {
                continue; // the "walk-in customer" placeholder is not a real number, and not worth reporting
            }
            if (! $phone || ! preg_match('/^0\d{9}$/', $phone)) {
                $skipped[] = ['label' => $p['name'] ?: ($p['phone'] ?: 'Unknown'), 'reason' => $p['phone'] ? 'Not a valid phone number' : 'No phone number'];
                continue;
            }
            if (isset($seen[$phone])) {
                continue; // same number twice (e.g. a customer who is also a rider): send once
            }
            $seen[$phone] = true;
            $recipients->push(['name' => $p['name'], 'phone' => $phone, 'type' => $p['type']]);
        }

        return ['recipients' => $recipients->values(), 'skipped' => $skipped, 'label' => self::GROUPS[$audience] ?? $audience];
    }

    private function customers($query): Collection
    {
        return $query->orderBy('name')->get(['name', 'phone'])->toBase()->map(fn ($c) => ['name' => $c->name, 'phone' => (string) $c->phone, 'type' => 'customer']);
    }

    private function riders($query): Collection
    {
        return $query->orderBy('name')->get(['name', 'phone'])->toBase()->map(fn ($u) => ['name' => $u->name, 'phone' => (string) $u->phone, 'type' => 'rider']);
    }

    /** One number per line, comma or space; "Name 024..." is fine too. */
    private function typed(string $text): array
    {
        $people = collect();
        $skipped = [];
        foreach (preg_split('/[\r\n,;]+/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/(\+?\d[\d\s\-]{7,}\d)/', $line, $m)) {
                $people->push(['name' => trim(str_replace($m[1], '', $line), " \t-:"), 'phone' => $m[1], 'type' => 'typed']);
            } else {
                $skipped[] = ['label' => $line, 'reason' => 'No phone number found'];
            }
        }

        return [$people, $skipped];
    }
}
