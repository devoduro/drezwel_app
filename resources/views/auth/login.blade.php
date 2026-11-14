@extends('layouts.app')
@section('title', 'Sign in · Drezwel Go')
@section('content')
<div class="min-h-[80vh] flex items-center">
    <div class="w-full bg-white rounded-2xl shadow p-6">
        <div class="mb-5 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Drezwel Go" class="mx-auto h-14 w-auto">
            <h1 class="mt-3 text-xl font-bold">Drezwel Go</h1>
            <p class="text-sm text-slate-500">Manager &amp; rider sign in</p>
        </div>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Phone or email</label>
                <input name="login" value="{{ old('login') }}" required autofocus class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="w-full rounded-lg bg-gradient-to-br from-slate-900 to-slate-700 text-white py-2.5 font-semibold shadow hover:shadow-md transition">Sign in</button>
        </form>
        <p class="mt-5 text-center text-sm text-slate-500">Waiting for an order? <a class="text-brand-accent font-medium" href="{{ route('track') }}">Track your delivery</a></p>
    </div>
</div>
@endsection
