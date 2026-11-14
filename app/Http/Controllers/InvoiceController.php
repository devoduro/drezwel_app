<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

/** Printable invoice / receipt for an order. Staff open it by id; customers open a signed link. */
class InvoiceController extends Controller
{
    public function manager(Request $request, Delivery $delivery)
    {
        return $this->render($request, $delivery);
    }

    /** Public link: the `signed` middleware guarantees the URL was issued by us, so nobody can guess other invoices. */
    public function publicShow(Request $request, string $reference)
    {
        $delivery = Delivery::where('reference', $reference)->firstOrFail();

        return $this->render($request, $delivery);
    }

    private function render(Request $request, Delivery $delivery)
    {
        $delivery->load(['customer', 'rider', 'items.product', 'items.variant', 'payments']);

        return view('invoice', [
            'delivery' => $delivery,
            'receipt' => $request->input('format') === 'receipt', // narrow layout for a thermal printer
            'autoprint' => $request->boolean('print'),
        ]);
    }
}
