@extends('layouts.app')
@section('title', 'Forgot password · Drezwel Go')
@section('content')
<div class="min-h-[80vh] flex items-center">
    <div class="w-full bg-white rounded-2xl shadow p-6">
        <div class="mb-5 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Drezwel Go" class="mx-auto h-14 w-auto">
            <h1 class="mt-3 text-xl font-bold">Forgot your password?</h1>
            <p class="text-sm text-slate-500">Enter your phone number or email. We will text a 6-digit code to the phone number saved on your account.</p>
        </div>
        <form method="POST" action="{{ route('password.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Phone or email</label>
                <input name="login" value="{{ old('login') }}" required autofocus class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <button class="w-full rounded-lg bg-[#1c3041] text-white py-2.5 font-semibold shadow hover:shadow-md transition">Send me a code</button>
        </form>
        <p class="mt-5 text-center text-sm text-slate-500">No phone number on your account? Ask your manager to reset it from the Riders page.</p>
        <p class="mt-2 text-center text-sm"><a class="text-brand-accent font-medium" href="{{ route('login') }}">← Back to sign in</a></p>
    </div>
</div>
@endsection
