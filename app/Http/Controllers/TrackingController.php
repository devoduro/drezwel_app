<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Support\Phone;
use Illuminate\Http\Request;

/** Public page where a customer follows a delivery with the reference + the phone number on the order. */
class TrackingController extends Controller
{
    public function show(Request $request)
    {
        $delivery = null;
        $searched = $request->filled('ref') || $request->filled('phone');

        if ($request->filled('ref') && $request->filled('phone')) {
            $delivery = Delivery::with(['customer', 'rider', 'events'])
                ->where('reference', strtoupper(trim($request->input('ref'))))
                ->whereHas('customer', fn ($q) => $q->where('phone', Phone::clean($request->input('phone'))))
                ->first();
        }

        return view('track', ['delivery' => $delivery, 'searched' => $searched]);
    }
}
