<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Delivery fees by region and town: add towns, price them one by one or many at once. */
class DeliveryFeeController extends Controller
{
    public function index(Request $request)
    {
        $regions = Region::withCount([
            'zones as towns', 'zones as priced' => fn ($q) => $q->whereNotNull('fee'),
        ])->orderBy('sort_order')->get();
        $region = $regions->firstWhere('id', (int) $request->input('region')) ?? $regions->first();

        return view('manager.fees', [
            'regions' => $regions,
            'region' => $region,
            'zones' => $region->zones()->get(),
            'summary' => ['regions' => $regions->count(), 'towns' => $regions->sum('towns'), 'priced' => $regions->sum('priced')],
        ]);
    }

    /** Save the edited fees, or apply one action (set a fee, switch on/off, remove) to the ticked towns. */
    public function update(Request $request, Region $region)
    {
        $data = $request->validate([
            'do' => ['required', Rule::in(['save', 'set_fee', 'clear_fee', 'activate', 'deactivate', 'delete'])],
            'default_fee' => 'nullable|numeric|min:0|max:100000',
            'fees' => 'nullable|array', 'fees.*' => 'nullable|numeric|min:0|max:100000',
            'active' => 'nullable|array',
            'ids' => 'nullable|array', 'ids.*' => 'integer',
            'bulk_fee' => 'nullable|numeric|min:0|max:100000',
            'all' => 'nullable|boolean',
        ]);
        $zones = $region->zones()->get()->keyBy('id');

        if ($data['do'] === 'save') {
            $region->update(['default_fee' => $data['default_fee'] ?? null]);
            foreach ($zones as $id => $z) {
                $fee = $data['fees'][$id] ?? null;
                $z->update(['fee' => ($fee === null || $fee === '') ? null : (float) $fee, 'is_active' => isset($data['active'][$id])]);
            }

            return back()->with('success', "Fees saved for {$region->name}.");
        }

        $targets = $request->boolean('all') ? $zones : $zones->only($data['ids'] ?? []);
        if ($targets->isEmpty()) {
            return back()->with('error', 'Tick the towns first, or choose "All towns in this region".');
        }

        $n = $targets->count();
        $towns = $n === 1 ? '1 town' : "{$n} towns";
        switch ($data['do']) {
            case 'set_fee':
                if (! isset($data['bulk_fee'])) {
                    return back()->with('error', 'Enter the fee to apply.');
                }
                DeliveryZone::whereIn("id", $targets->pluck("id"))->update(['fee' => (float) $data['bulk_fee'], 'is_active' => true]);
                $msg = "GH₵ " . number_format((float) $data['bulk_fee'], 2) . " set for {$towns}.";
                break;
            case 'clear_fee':
                DeliveryZone::whereIn("id", $targets->pluck("id"))->update(['fee' => null]);
                $msg = "Fee cleared for {$towns}. They now use the region's fee.";
                break;
            case 'activate':
                DeliveryZone::whereIn("id", $targets->pluck("id"))->update(['is_active' => true]);
                $msg = "{$towns} switched on.";
                break;
            case 'deactivate':
                DeliveryZone::whereIn("id", $targets->pluck("id"))->update(['is_active' => false]);
                $msg = "{$towns} switched off.";
                break;
            default:
                DeliveryZone::whereIn("id", $targets->pluck("id"))->delete();
                $msg = "{$towns} deleted.";
        }

        return back()->with('success', $msg);
    }

    /** Add several towns at once, one per line: "Name" or "Name, 25". Lines without a fee get the fee typed beside the box. */
    public function addTowns(Request $request, Region $region)
    {
        $data = $request->validate(['names' => 'required|string|max:5000', 'fee' => 'nullable|numeric|min:0|max:100000']);
        $existing = $region->zones()->get()->keyBy(fn ($z) => mb_strtolower($z->name));
        $added = $updated = 0;

        foreach (preg_split('/\r\n|\r|\n/', $data['names']) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // "Name, 25", "Name: 25", "Name - 25" or "Name<tab>25"
            $name = $line;
            $fee = $data['fee'] ?? null;
            if (preg_match('/^(.*?)\s*(?:,|:|\t|\s-\s)\s*(\d+(?:\.\d{1,2})?)$/u', $line, $m)) {
                [$name, $fee] = [trim($m[1]), (float) $m[2]];
            }
            $name = mb_substr($name, 0, 80);
            if ($name === '') {
                continue;
            }

            if ($zone = $existing->get(mb_strtolower($name))) {
                if ($fee !== null && $fee !== '') {
                    $zone->update(['fee' => (float) $fee, 'is_active' => true]);
                    $updated++;
                }
            } else {
                $z = $region->zones()->create(['name' => $name, 'fee' => ($fee === null || $fee === '') ? null : (float) $fee]);
                $existing->put(mb_strtolower($name), $z);
                $added++;
            }
        }

        return back()->with('success', "{$added} " . ($added === 1 ? 'town' : 'towns') . ' added' . ($updated ? ", {$updated} updated" : '') . '.');
    }

    public function destroy(DeliveryZone $zone)
    {
        $zone->delete();

        return back()->with('success', "{$zone->name} deleted.");
    }
}
