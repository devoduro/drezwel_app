<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Staff (manager / rider) password reset by SMS code:
 *   1. enter the phone number or email on the account,
 *   2. a 6-digit code is texted to the phone number saved on that account,
 *   3. enter the code and choose a new password.
 * The reply in step 1 is identical whether or not an account exists, so the form cannot be used to find out who has one.
 */
class PasswordResetController extends Controller
{
    private const MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendCode(Request $request, SmsService $sms)
    {
        $data = $request->validate(['login' => 'required|string|max:100']);
        $phone = Phone::clean($data['login']);

        // 5 codes per account identifier and 15 per device per hour, and one every 30 seconds.
        $id = strtolower($data['login']);
        foreach (["pw-reset-id:{$id}" => 5, 'pw-reset-ip:' . $request->ip() => 15] as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages(['login' => 'Too many requests. Please try again later.']);
            }
        }
        if (RateLimiter::tooManyAttempts("pw-reset-gap:{$id}", 1)) {
            throw ValidationException::withMessages(['login' => 'A code was just sent. Wait 30 seconds before asking for another.']);
        }
        RateLimiter::hit("pw-reset-id:{$id}", 3600);
        RateLimiter::hit('pw-reset-ip:' . $request->ip(), 3600);
        RateLimiter::hit("pw-reset-gap:{$id}", 30);

        $user = User::where('is_active', true)->where(fn ($q) => $q->where('email', $data['login'])->when($phone, fn ($q) => $q->orWhere('phone', $phone)))->first();

        if ($user && $user->phone) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($this->key($user), ['hash' => $this->hash($user, $code), 'attempts' => 0], now()->addMinutes(self::MINUTES));
            $sms->send($user->phone, "Drezwel Go: your password reset code is {$code}. It expires in " . self::MINUTES . ' minutes. If you did not ask for it, ignore this message.', null, 'password_reset');
        }

        // Remember who the code is for (not who exists): the id is only used if a code is actually in the cache.
        $request->session()->put('pw_reset_user', $user?->id ?? 0);
        $request->session()->put('pw_reset_hint', $user?->phone ? $this->mask($user->phone) : null);

        return redirect()->route('password.reset')->with('success', 'If that account has a phone number on file, we have texted it a 6-digit code.');
    }

    public function showReset(Request $request)
    {
        abort_unless($request->session()->has('pw_reset_user'), 404);

        return view('auth.reset', ['hint' => $request->session()->get('pw_reset_hint')]);
    }

    public function reset(Request $request)
    {
        abort_unless($request->session()->has('pw_reset_user'), 404);
        $data = $request->validate(['code' => 'required|digits:6', 'password' => 'required|string|min:8|confirmed']);

        $user = User::find($request->session()->get('pw_reset_user'));
        $record = $user ? Cache::get($this->key($user)) : null;
        $fail = fn ($msg) => throw ValidationException::withMessages(['code' => $msg]);

        if (! $user || ! $record) {
            $fail('That code is not valid or has expired. Ask for a new one.');
        }
        if ($record['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($this->key($user));
            $fail('Too many wrong codes. Ask for a new one.');
        }
        if (! hash_equals($record['hash'], $this->hash($user, $data['code']))) {
            $record['attempts']++;
            Cache::put($this->key($user), $record, now()->addMinutes(self::MINUTES));
            $fail('Wrong code. ' . (self::MAX_ATTEMPTS - $record['attempts']) . ' attempt(s) left.');
        }

        Cache::forget($this->key($user)); // single use
        // A new password also signs out any "keep me signed in" devices.
        $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
        $request->session()->forget(['pw_reset_user', 'pw_reset_hint']);

        return redirect()->route('login')->with('success', 'Your password has been changed. Sign in with the new password.');
    }

    private function key(User $u): string
    {
        return 'pw-reset:' . $u->id;
    }

    private function hash(User $u, string $code): string
    {
        return hash_hmac('sha256', $u->id . '|' . $code, (string) config('app.key'));
    }

    private function mask(string $phone): string
    {
        return substr($phone, 0, 3) . str_repeat('•', max(strlen($phone) - 6, 0)) . substr($phone, -3);
    }
}
