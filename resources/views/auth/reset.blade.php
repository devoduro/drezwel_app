@extends('layouts.app')
@section('title', 'Reset password · Drezwel Go')
@section('content')
<div class="min-h-[80vh] flex items-center">
    <div class="w-full bg-white rounded-2xl shadow p-6">
        <div class="mb-5 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Drezwel Go" class="mx-auto h-14 w-auto">
            <h1 class="mt-3 text-xl font-bold">Choose a new password</h1>
            <p class="text-sm text-slate-500">Enter the 6-digit code we texted{{ $hint ? ' to ' . $hint : '' }}, then your new password.</p>
        </div>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">6-digit code</label>
                <input name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" required autofocus placeholder="• • • • • •" class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5 text-center text-2xl font-bold tracking-[0.4em]">
            </div>
            <div>
                <label class="text-sm font-medium">New password (at least 8 characters)</label>
                <input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <div>
                <label class="text-sm font-medium">Repeat new password</label>
                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <button class="w-full rounded-lg bg-[#1c3041] text-white py-2.5 font-semibold shadow hover:shadow-md transition">Change password</button>
        </form>
        <div class="mt-4 flex justify-between text-sm">
            <a class="text-brand-accent font-medium" href="{{ route('password.forgot') }}">Send a new code</a>
            <a class="text-slate-500" href="{{ route('login') }}">Back to sign in</a>
        </div>
    </div>
</div>
@endsection
