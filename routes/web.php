<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager;
use App\Http\Controllers\Rider\RiderAppController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $u = auth()->user();
    if (! $u) {
        return redirect()->route('track');
    }

    return redirect()->route($u->isManager() ? 'manager.dashboard' : 'rider.mine');
});

// Customers: follow a delivery (no account needed)
Route::get('/track', [TrackingController::class, 'show'])->middleware('throttle:30,1')->name('track');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Manager
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', [Manager\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('deliveries', Manager\DeliveryController::class)->except('destroy');
    Route::controller(Manager\DeliveryController::class)->prefix('deliveries/{delivery}')->name('deliveries.')->group(function () {
        Route::post('assign', 'assign')->name('assign');
        Route::post('resend-code', 'resendCode')->name('resend');
        Route::post('confirm', 'confirm')->name('confirm');
        Route::post('undeliver', 'undeliver')->name('undeliver');
        Route::post('reopen', 'reopen')->name('reopen');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('payment', 'payment')->name('payment');
    });

    Route::get('riders', [Manager\RiderController::class, 'index'])->name('riders.index');
    Route::post('riders', [Manager\RiderController::class, 'store'])->name('riders.store');
    Route::get('riders/{rider}', [Manager\RiderController::class, 'show'])->name('riders.show');
    Route::put('riders/{rider}', [Manager\RiderController::class, 'update'])->name('riders.update');
    Route::post('riders/{rider}/toggle', [Manager\RiderController::class, 'toggle'])->name('riders.toggle');

    Route::get('customers', [Manager\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [Manager\CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [Manager\CustomerController::class, 'update'])->name('customers.update');
    Route::get('debtors', [Manager\CustomerController::class, 'debtors'])->name('debtors');

    Route::get('products', [Manager\ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [Manager\ProductController::class, 'create'])->name('products.create');
    Route::post('products', [Manager\ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [Manager\ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [Manager\ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [Manager\ProductController::class, 'update'])->name('products.update');
    Route::post('products/{product}/stock', [Manager\ProductController::class, 'adjust'])->name('products.adjust');
    Route::post('products/{product}/toggle', [Manager\ProductController::class, 'toggle'])->name('products.toggle');
    Route::put('alert-phone', [Manager\ProductController::class, 'alertPhone'])->name('alert-phone');

    Route::get('reports', [Manager\ReportController::class, 'index'])->name('reports');
    Route::get('reports/export/{type}', [Manager\ReportController::class, 'export'])->name('reports.export');

    Route::get('remittances', [Manager\RemittanceController::class, 'index'])->name('remittances');
    Route::post('remittances', [Manager\RemittanceController::class, 'store'])->name('remittances.store');
    Route::post('remittances/{rider}/pay-fees', [Manager\RemittanceController::class, 'payFees'])->name('remittances.pay-fees');
    Route::post('remittances/{rider}/clear', [Manager\RemittanceController::class, 'clear'])->name('remittances.clear');
    Route::get('sms', [Manager\RemittanceController::class, 'smsLogs'])->name('sms');
});

// Riders
Route::middleware(['auth', 'role:rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/', [RiderAppController::class, 'mine'])->name('mine');
    Route::get('available', [RiderAppController::class, 'available'])->name('available');
    Route::get('completed', [RiderAppController::class, 'completed'])->name('completed');
    Route::get('stats', [RiderAppController::class, 'stats'])->name('stats');
    Route::get('deliveries/{delivery}', [RiderAppController::class, 'show'])->name('show');
    Route::post('deliveries/{delivery}/claim', [RiderAppController::class, 'claim'])->name('claim');
    Route::post('deliveries/{delivery}/release', [RiderAppController::class, 'release'])->name('release');
    Route::post('deliveries/{delivery}/start', [RiderAppController::class, 'start'])->name('start');
    Route::post('deliveries/{delivery}/resend', [RiderAppController::class, 'resend'])->name('resend');
    Route::post('deliveries/{delivery}/confirm', [RiderAppController::class, 'confirm'])->name('confirm');
    Route::post('deliveries/{delivery}/undeliver', [RiderAppController::class, 'undeliver'])->name('undeliver');
});
