<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Services\SmsService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Where customers follow their deliveries. Two ways in, no account or password needed:
 *   1. the reference + the phone number on the order (quick look-up), or
 *   2. "sign in" with the phone number: we text a 6-digit code, and they then see all their orders.
 */
class TrackingController extends Controller
{
    private const OTP_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;

    public function show(Request $request)
    {
        $customer = $this->signedInCustomer($request);
        $delivery = null;
        $searched = $request->filled('ref') || $request->filled('phone');

        if ($request->filled('ref')) {
            $query = Delivery::with(['customer', 'rider', 'events', 'items.product.images', 'items.variant'])->where('reference', strtoupper(trim($request->input('ref'))));
            if ($customer) {
                // A signed-in customer can open only their own orders.
                $delivery = $query->where('customer_id', $customer->id)->first();
            } elseif ($request->filled('phone')) {
                $delivery = $query->whereHas('customer', fn ($q) => $q->where('phone', Phone::clean($request->input('phone'))))->first();
            }
        }

        return view('track', [
            'delivery' => $delivery,
            'searched' => $searched,
            'customer' => $customer,
            'orders' => $customer ? $customer->deliveries()->with(['rider', 'items.product.images', 'items.variant'])->latest('delivery_date')->latest('id')->limit(50)->get() : collect(),
            'otpPhone' => $request->session()->get('track_otp_phone'),
        ]);
    }

    /** Step 1: text a code to the phone number. The reply is the same whether or not we know the number. */
    public function sendCode(Request $request, SmsService $sms)
    {
        $phone = Phone::clean($request->input('phone'));
        $request->merge(['phone' => $phone]);
        $request->validate(['phone' => ['required', 'regex:/^0\d{9}$/']], ['phone.regex' => 'Enter a 10-digit phone number, e.g. 0241234567.']);

        // At most 5 codes per number and 15 per device per hour, and one every 30 seconds.
        $keys = ["track-otp-phone:{$phone}" => 5, 'track-otp-ip:' . $request->ip() => 15];
        foreach ($keys as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages(['phone' => 'Too many codes requested. Please try again later.']);
            }
        }
        if (RateLimiter::tooManyAttempts("track-otp-gap:{$phone}", 1)) {
            throw ValidationException::withMessages(['phone' => 'A code was just sent. Wait 30 seconds before asking for another.']);
        }
        foreach (array_keys($keys) as $key) {
            RateLimiter::hit($key, 3600);
        }
        RateLimiter::hit("track-otp-gap:{$phone}", 30);

        if (Customer::where('phone', $phone)->exists()) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($this->otpKey($phone), ['hash' => $this->hash($phone, $code), 'attempts' => 0], now()->addMinutes(self::OTP_MINUTES));
            $sms->send($phone, "Drezwel Go: your sign-in code is {$code}. It expires in " . self::OTP_MINUTES . ' minutes. Do not share it.', null, 'customer_login');
        }

        $request->session()->put('track_otp_phone', $phone);

        return redirect()->route('track')->with('success', "If {$phone} has orders with us, a 6-digit code has been sent to it.");
    }

    /** Step 2: check the code and sign the customer in. */
    public function verify(Request $request)
    {
        $phone = $request->session()->get('track_otp_phone');
        $data = $request->validate(['code' => 'required|digits:6']);
        abort_unless($phone, 422, 'Ask for a code first.');

        $record = Cache::get($this->otpKey($phone));
        $fail = fn ($msg) => throw ValidationException::withMessages(['code' => $msg]);

        if (! $record) {
            $fail('That code has expired. Ask for a new one.');
        }
        if ($record['attempts'] >= self::OTP_MAX_ATTEMPTS) {
            Cache::forget($this->otpKey($phone));
            $fail('Too many wrong codes. Ask for a new one.');
        }
        if (! hash_equals($record['hash'], $this->hash($phone, $data['code']))) {
            $record['attempts']++;
            Cache::put($this->otpKey($phone), $record, now()->addMinutes(self::OTP_MINUTES));
            $fail('Wrong code. ' . (self::OTP_MAX_ATTEMPTS - $record['attempts']) . ' attempt(s) left.');
        }

        Cache::forget($this->otpKey($phone)); // single use
        $customer = Customer::where('phone', $phone)->firstOrFail();
        $request->session()->regenerate();
        $request->session()->forget('track_otp_phone');
        $request->session()->put('track_customer_id', $customer->id);

        return redirect()->route('track')->with('success', 'Welcome, ' . $customer->name . '.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['track_customer_id', 'track_otp_phone']);

        return redirect()->route('track')->with('success', 'You are signed out.');
    }

    /** Start over with a different number. */
    public function changeNumber(Request $request)
    {
        $request->session()->forget('track_otp_phone');

        return redirect()->route('track');
    }

    private function signedInCustomer(Request $request): ?Customer
    {
        $id = $request->session()->get('track_customer_id');

        return $id ? Customer::find($id) : null;
    }

    private function otpKey(string $phone): string
    {
        return 'track-otp:' . $phone;
    }

    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', $phone . '|' . $code, (string) config('app.key'));
    }
}
