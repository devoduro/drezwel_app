<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DeliveryWorkflow;
use Illuminate\Http\Request;

class RiderAppController extends Controller
{
    public function __construct(private DeliveryWorkflow $flow) {}

    /** My Deliveries: what the rider has picked or been given and still has to do. */
    public function mine(Request $request)
    {
        $deliveries = Delivery::with('customer')->where('rider_id', $request->user()->id)
            ->whereIn('status', ['assigned', 'out_for_delivery'])->orderByRaw("status='out_for_delivery' desc")->orderBy('delivery_date')->get();

        return view('rider.mine', ['deliveries' => $deliveries, 'custody' => $request->user()->custody()]);
    }

    /** Pending orders any rider may pick. */
    public function available()
    {
        $deliveries = Delivery::with('customer')->where('status', 'pending')->whereNull('rider_id')->where('delivery_mode', 'rider')
            ->orderBy('delivery_date')->orderBy('id')->get();

        return view('rider.available', ['deliveries' => $deliveries]);
    }

    public function completed(Request $request)
    {
        $deliveries = Delivery::with('customer')->where('rider_id', $request->user()->id)->whereIn('status', ['delivered', 'undelivered'])
            ->latest('updated_at')->limit(60)->get();

        return view('rider.completed', ['deliveries' => $deliveries]);
    }

    public function stats(Request $request)
    {
        $id = $request->user()->id;
        $q = fn () => Delivery::where('rider_id', $id);

        return view('rider.stats', [
            'delivered' => $q()->where('status', 'delivered')->count(),
            'today' => $q()->where('status', 'delivered')->whereDate('delivered_at', today())->count(),
            'undelivered' => $q()->where('status', 'undelivered')->count(),
            'open' => $q()->whereIn('status', ['assigned', 'out_for_delivery'])->count(),
            'value' => (float) $q()->where('status', 'delivered')->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
            'custody' => $request->user()->custody(),
        ]);
    }

    public function show(Request $request, Delivery $delivery)
    {
        $this->authorizeView($request, $delivery);

        return view('rider.show', ['delivery' => $delivery->load('customer')]);
    }

    public function claim(Request $request, Delivery $delivery)
    {
        if ($this->flow->claim($delivery, $request->user())) {
            return redirect()->route('rider.show', $delivery)->with('success', 'It is yours. Start the delivery when you set off.');
        }

        return redirect()->route('rider.available')->with('error', 'Sorry, someone else already picked that delivery.');
    }

    public function release(Request $request, Delivery $delivery)
    {
        abort_unless($delivery->rider_id === $request->user()->id, 403);
        $this->flow->release($delivery, $request->user());

        return redirect()->route('rider.mine')->with('success', 'Delivery released back to the queue.');
    }

    public function start(Request $request, Delivery $delivery)
    {
        abort_unless($delivery->rider_id === $request->user()->id, 403);
        $this->flow->start($delivery, $request->user());

        return back()->with('success', 'On the way. Tap "Delivered" when the customer has received the order.');
    }

    /** "Delivered": no code needed. The customer is texted that it was delivered. */
    public function deliver(Request $request, Delivery $delivery)
    {
        abort_unless($delivery->rider_id === $request->user()->id, 403);
        $data = $request->validate(['items_kept' => 'nullable|integer|min:0', 'cash' => 'nullable|numeric|min:0']);
        $this->flow->deliver($delivery, $request->user(), isset($data['items_kept']) ? (int) $data['items_kept'] : null, isset($data['cash']) ? (float) $data['cash'] : null);

        return redirect()->route('rider.mine')->with('success', "Delivery {$delivery->reference} marked as delivered. The customer has been texted. Well done!");
    }

    public function undeliver(Request $request, Delivery $delivery)
    {
        abort_unless($delivery->rider_id === $request->user()->id, 403);
        $data = $request->validate(['reason' => 'required|string|max:200']);
        $this->flow->markUndelivered($delivery, $request->user(), $data['reason']);

        return redirect()->route('rider.mine')->with('success', 'Marked as undelivered. Return the items to the manager.');
    }

    private function authorizeView(Request $request, Delivery $delivery): void
    {
        // A rider can open their own deliveries, or look at an unassigned one before picking it.
        abort_unless($delivery->rider_id === $request->user()->id || ($delivery->rider_id === null && $delivery->status === 'pending'), 403);
    }
}
