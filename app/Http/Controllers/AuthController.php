<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['login' => 'required|string|max:100', 'password' => 'required|string']);

        $key = 'login:' . strtolower($data['login']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            throw ValidationException::withMessages(['login' => 'Too many attempts. Try again in a minute.']);
        }

        // Staff sign in with an email address or a phone number.
        $phone = Phone::clean($data['login']);
        $user = User::where(fn ($q) => $q->where('email', $data['login'])->when($phone, fn ($q) => $q->orWhere('phone', $phone)))->first();

        if (! $user || ! $user->is_active || ! Auth::attempt(['id' => $user->id, 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'Those details do not match an active account.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended($user->isManager() ? route('manager.dashboard') : route('rider.mine'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
