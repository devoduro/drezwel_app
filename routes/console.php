<?php

use Illuminate\Support\Facades\Schedule;

// Console commands live in app/Console/Commands (auto-discovered).

// Speso's callback is the fast path, not a guarantee. Sweep for anything it missed so a dropped
// webhook costs a minute, and cancel online orders that were never paid so their stock is freed.
Schedule::command('speso:reconcile')->everyMinute()->withoutOverlapping()->runInBackground()->name('speso-reconcile');
