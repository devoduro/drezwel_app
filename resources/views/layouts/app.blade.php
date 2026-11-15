<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f2a43">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>@yield('title', 'Drezwel Go')</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>tailwind.config = {theme: {extend: {colors: {brand: {DEFAULT: '#1c3041', 600: '#14212d', accent: '#1c3041'}}}}}</script>
    <style>
        body{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
        main section.bg-white, main .bg-white.rounded-xl { border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgb(0 0 0 / .04); }
        main table tbody tr, main table tr { transition: background-color .12s; }
        main table thead { background: #f8fafc; text-transform: uppercase; letter-spacing: .03em; }
        @media print { aside, header, #menuBackdrop, .no-print, form.no-print { display: none !important; } main { padding: 0 !important; } body { background: #fff !important; } main > * { animation: none !important; } .print-break { page-break-before: always; } }
        .btn-hover:hover { transform: translateY(-1px); }
        main > * { animation: fadeUp .25s ease both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(6px) } to { opacity: 1; transform: none } }
    </style>
</head>
@php
    $user = auth()->user();
    $isRider = $user && $user->isRider();
    $newOrders = $user && $user->isManager() ? \App\Models\Delivery::whereIn('source', ['online', 'phone'])->where('status', 'pending')->whereNull('rider_id')->paymentSettled()->count() : 0;
    $newMessages = $user && $user->isManager() ? \App\Models\ContactMessage::where('status', 'new')->count() : 0;
    $lowStock = $user && $user->isManager() ? \App\Models\Product::needingStock()->count() : 0;
    $managerNav = [
        ['manager.dashboard', 'Dashboard', 'manager.dashboard', 'fa-gauge-high'],
        ['manager.orders', 'Orders', 'manager.orders', 'fa-clipboard-list'],
        ['manager.sales', 'Sales', 'manager.sales', 'fa-receipt'],
        ['manager.pos', 'Point of sale', 'manager.pos', 'fa-cash-register'],
        ['manager.deliveries.index', 'Deliveries', 'manager.deliveries.*', 'fa-truck-fast'],
        ['manager.riders.index', 'Riders', 'manager.riders.*', 'fa-motorcycle'],
        ['manager.products.index', 'Products & stock', 'manager.products.*', 'fa-boxes-stacked'],
        ['manager.customers.index', 'Customers', 'manager.customers.*', 'fa-users'],
        ['manager.debtors', 'Debtors', 'manager.debtors', 'fa-hand-holding-dollar'],
        ['manager.remittances', 'Rider cash & items', 'manager.remittances*', 'fa-wallet'],
        ['manager.messages', 'Messages', 'manager.messages*', 'fa-inbox'],
        ['manager.cashbook', 'Cash book', 'manager.cashbook*', 'fa-book'],
        ['manager.reports', 'Reports', 'manager.reports*', 'fa-chart-pie'],
        ['manager.settings', 'Settings', 'manager.settings', 'fa-gear'],
        ['manager.bulk', 'Bulk SMS', 'manager.bulk*', 'fa-bullhorn'],
        ['manager.sms', 'SMS log', 'manager.sms', 'fa-comment-sms'],
    ];
    $riderNav = [
        ['rider.mine', 'My Deliveries', 'rider.mine', 'fa-list-check'],
        ['rider.available', 'Pick Up', 'rider.available', 'fa-hand-pointer'],
        ['rider.completed', 'Completed', 'rider.completed', 'fa-truck'],
        ['rider.stats', 'Statistics', 'rider.stats', 'fa-chart-column'],
    ];
@endphp
<body class="bg-slate-100 text-slate-800 min-h-screen {{ $isRider ? 'pb-20' : '' }}">
@if($user && $user->isManager())
    <div class="lg:flex min-h-screen">
        {{-- Top bar with a menu button on small screens --}}
        <header class="lg:hidden sticky top-0 z-20 bg-brand text-white flex items-center gap-3 px-4 py-3">
            <button type="button" id="menuBtn" aria-label="Open menu" class="text-2xl leading-none">☰</button>
            <img src="{{ asset('images/logo-white.png') }}" alt="Drezwel Go" class="h-7 w-auto">
        </header>
        <div id="menuBackdrop" class="hidden fixed inset-0 bg-black/40 z-30 lg:hidden"></div>
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transition-transform bg-brand text-white overflow-y-auto
                                    lg:static lg:translate-x-0 lg:w-60 lg:min-h-screen lg:sticky lg:top-0 lg:self-start lg:shrink-0">
            <div class="px-4 py-5 border-b border-white/10">
                <a href="{{ route('manager.dashboard') }}"><img src="{{ asset('images/logo-white.png') }}" alt="Drezwel Go" class="h-9 w-auto"></a>
                <div class="mt-2 text-xs text-slate-400 truncate">Drezwel Go · {{ $user->name }} · Manager</div>
            </div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                <div class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu</div>
                @foreach($managerNav as [$route, $label, $pattern, $icon])
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ request()->routeIs($pattern) ? 'bg-white/15 text-white font-semibold shadow-inner ring-1 ring-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <i class="fas {{ $icon }} w-5 text-center {{ request()->routeIs($pattern) ? '' : 'text-slate-400' }}"></i>{{ $label }}
                        @if($route === 'manager.orders' && $newOrders > 0)<span class="ml-auto rounded-full bg-[#8fb8d8] px-2 py-0.5 text-[11px] font-bold text-[#1c3041]" title="New website / phone orders waiting for a rider">{{ $newOrders }}</span>@endif
                        @if($route === 'manager.messages' && $newMessages > 0)<span class="ml-auto rounded-full bg-[#8fb8d8] px-2 py-0.5 text-[11px] font-bold text-[#1c3041]" title="New messages">{{ $newMessages }}</span>@endif
                        @if($route === 'manager.products.index' && $lowStock > 0)<span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-bold text-white" title="Products low or out of stock">{{ $lowStock }}</span>@endif</a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}" class="pt-3 mt-3 border-t border-white/10">@csrf
                    <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-slate-400 hover:bg-white/10 hover:text-white transition"><i class="fas fa-right-from-bracket w-5 text-center"></i>Log out</button>
                </form>
            </nav>
        </aside>
        <script>
            (function () {
                const side = document.getElementById('sidebar'), back = document.getElementById('menuBackdrop');
                const set = open => { side.classList.toggle('-translate-x-full', !open); back.classList.toggle('hidden', !open); };
                document.getElementById('menuBtn').addEventListener('click', () => set(side.classList.contains('-translate-x-full')));
                back.addEventListener('click', () => set(false));
            })();
        </script>
        <main class="flex-1 p-4 lg:p-6 min-w-0">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
@elseif($isRider)
    <header class="bg-[#1c3041] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow">
        <img src="{{ asset('images/logo-white.png') }}" alt="Drezwel Go" class="h-7 w-auto">
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs text-white/70">{{ $user->name }} · Log out</button></form>
    </header>
    <main class="p-4 max-w-xl mx-auto">
        @include('partials.flash')
        @yield('content')
    </main>
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t grid grid-cols-4 text-center text-xs z-10" style="padding-bottom:env(safe-area-inset-bottom)">
        @foreach($riderNav as [$route, $label, $pattern, $icon])
            <a href="{{ route($route) }}" class="py-2 {{ request()->routeIs($pattern) ? 'text-brand-accent font-semibold border-t-2 border-brand-accent' : 'text-slate-500' }}">
                <i class="fas {{ $icon }} block text-lg mb-0.5"></i>{{ $label }}</a>
        @endforeach
    </nav>
@else
    <main class="p-4 max-w-xl mx-auto">
        @include('partials.flash')
        @yield('content')
    </main>
@endif
</body>
</html>
