<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Manager;
use App\Http\Controllers\Rider\RiderAppController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public website: the home page for visitors; staff who open the site go straight to their own screen.
Route::get('/', function () {
    $u = auth()->user();
    if ($u) {
        return redirect()->route($u->isManager() ? 'manager.dashboard' : 'rider.mine');
    }

    return app(SiteController::class)->home();
})->name('home');
Route::get('/about', [SiteController::class, 'about'])->name('site.about');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::get('/wholesale', [SiteController::class, 'wholesale'])->name('site.wholesale');
Route::post('/contact', [SiteController::class, 'send'])->middleware('throttle:5,1,contact-send')->name('site.send');
Route::get('/{slug}', [SiteController::class, 'page'])->where('slug', 'delivery-info|return-policy|terms|privacy-policy|faqs')->name('site.page');

// Addresses from the old website keep working, so existing links and search results are not lost.
Route::permanentRedirect('/categories', '/shop');
Route::permanentRedirect('/flash-deals', '/shop');
Route::permanentRedirect('/todays-deal', '/shop');
Route::permanentRedirect('/wholesale-order', '/wholesale');
Route::permanentRedirect('/track-your-order', '/track');
Route::permanentRedirect('/support-policy', '/delivery-info');
Route::permanentRedirect('/users/login', '/login');
Route::get('/category/{slug}', fn (string $slug) => redirect('/shop?q=' . urlencode(str_replace('-', ' ', $slug)), 301));
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');

// Online store (public)
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::redirect('/store', '/shop');
Route::get('/shop/{slug}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::post('/cart/add', [ShopController::class, 'add'])->middleware('throttle:60,1,cart-add')->name('shop.cart.add');
Route::post('/cart/update', [ShopController::class, 'update'])->middleware('throttle:60,1,cart-update')->name('shop.cart.update');
Route::get('/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/checkout', [ShopController::class, 'placeOrder'])->middleware('throttle:6,1,checkout')->name('shop.checkout.place');
Route::get('/order/{reference}', [ShopController::class, 'order'])->middleware('signed')->name('shop.order');
Route::get('/order/{reference}/payment', [ShopController::class, 'paymentStatus'])->middleware(['signed', 'throttle:60,1,pay-status'])->name('shop.order.status');
Route::post('/order/{reference}/pay', [ShopController::class, 'pay'])->middleware(['signed', 'throttle:6,1,pay-retry'])->name('shop.order.pay');
Route::post('/order/{reference}/pay-on-delivery', [ShopController::class, 'payOnDelivery'])->middleware(['signed', 'throttle:6,1,pay-cod'])->name('shop.order.cod');
Route::post('/webhooks/speso', App\Http\Controllers\Webhooks\SpesoWebhookController::class)->middleware('throttle:120,1,speso-webhook')->name('webhooks.speso');
Route::get('/invoice/{reference}', [InvoiceController::class, 'publicShow'])->middleware('signed')->name('invoice.public');

// Customers: follow a delivery (no account needed)
Route::get('/track', [TrackingController::class, 'show'])->middleware('throttle:60,1,track-view')->name('track');
Route::post('/track/code', [TrackingController::class, 'sendCode'])->middleware('throttle:10,1,track-code')->name('track.code');
Route::post('/track/verify', [TrackingController::class, 'verify'])->middleware('throttle:15,1,track-verify')->name('track.verify');
Route::post('/track/logout', [TrackingController::class, 'logout'])->name('track.logout');
Route::post('/track/number', [TrackingController::class, 'changeNumber'])->name('track.number');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password reset by SMS code
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.forgot');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->middleware('throttle:10,1,pw-send')->name('password.send');
    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:15,1,pw-update')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Manager
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', [Manager\DashboardController::class, 'index'])->name('dashboard');

    Route::get('orders', [Manager\OrderController::class, 'index'])->name('orders');
    Route::get('sales', [Manager\SalesController::class, 'index'])->name('sales');
    Route::get('pos', [Manager\PosController::class, 'index'])->name('pos');
    Route::post('pos', [Manager\PosController::class, 'store'])->name('pos.store');
    Route::get('messages', [Manager\MessageController::class, 'index'])->name('messages');
    Route::post('messages/{message}/toggle', [Manager\MessageController::class, 'toggle'])->name('messages.toggle');
    Route::get('settings', [Manager\SettingController::class, 'edit'])->name('settings');
    Route::put('settings', [Manager\SettingController::class, 'update'])->name('settings.update');
    Route::get('deliveries/{delivery}/invoice', [InvoiceController::class, 'manager'])->name('deliveries.invoice');

    Route::resource('deliveries', Manager\DeliveryController::class);
    Route::controller(Manager\DeliveryController::class)->prefix('deliveries/{delivery}')->name('deliveries.')->group(function () {
        Route::post('assign', 'assign')->name('assign');
        Route::post('resend-code', 'resendCode')->name('resend');
        Route::post('confirm', 'confirm')->name('confirm');
        Route::post('undeliver', 'undeliver')->name('undeliver');
        Route::post('reopen', 'reopen')->name('reopen');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('payment', 'payment')->name('payment');
        Route::post('refund', 'refund')->name('refund');
        Route::post('verify-payment', 'verifyPayment')->middleware('throttle:20,1,verify-payment')->name('verify-payment');
    });

    Route::get('riders', [Manager\RiderController::class, 'index'])->name('riders.index');
    Route::post('riders', [Manager\RiderController::class, 'store'])->name('riders.store');
    Route::get('riders/{rider}', [Manager\RiderController::class, 'show'])->name('riders.show');
    Route::put('riders/{rider}', [Manager\RiderController::class, 'update'])->name('riders.update');
    Route::post('riders/{rider}/toggle', [Manager\RiderController::class, 'toggle'])->name('riders.toggle');

    Route::get('customers', [Manager\CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [Manager\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [Manager\CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [Manager\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [Manager\CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('debtors', [Manager\CustomerController::class, 'debtors'])->name('debtors');

    Route::get('products', [Manager\ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [Manager\ProductController::class, 'create'])->name('products.create');
    Route::post('products', [Manager\ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [Manager\ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [Manager\ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [Manager\ProductController::class, 'update'])->name('products.update');
    Route::post('products/{product}/stock', [Manager\ProductController::class, 'adjust'])->name('products.adjust');
    Route::delete('product-images/{image}', [Manager\ProductController::class, 'deleteImage'])->name('products.image.delete');
    Route::post('product-images/{image}/main', [Manager\ProductController::class, 'mainImage'])->name('products.image.main');
    Route::post('products/{product}/deal', [Manager\ProductController::class, 'deal'])->name('products.deal');
    Route::post('products/{product}/toggle', [Manager\ProductController::class, 'toggle'])->name('products.toggle');
    Route::put('alert-phone', [Manager\ProductController::class, 'alertPhone'])->name('alert-phone');

    Route::get('pages', [Manager\PageController::class, 'index'])->name('pages');
    Route::get('pages/{slug}', [Manager\PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{slug}', [Manager\PageController::class, 'update'])->name('pages.update');

    Route::get('delivery-fees', [Manager\DeliveryFeeController::class, 'index'])->name('fees');
    Route::put('delivery-fees/{region}', [Manager\DeliveryFeeController::class, 'update'])->name('fees.update');
    Route::post('delivery-fees/{region}/towns', [Manager\DeliveryFeeController::class, 'addTowns'])->name('fees.towns');
    Route::delete('delivery-fees/towns/{zone}', [Manager\DeliveryFeeController::class, 'destroy'])->name('fees.destroy');

    Route::get('cashbook', [Manager\CashbookController::class, 'index'])->name('cashbook');
    Route::get('cashbook/export', [Manager\CashbookController::class, 'export'])->name('cashbook.export');
    Route::post('cashbook', [Manager\CashbookController::class, 'store'])->name('cashbook.store');
    Route::put('cashbook/{entry}', [Manager\CashbookController::class, 'update'])->name('cashbook.update');
    Route::delete('cashbook/{entry}', [Manager\CashbookController::class, 'destroy'])->name('cashbook.destroy');

    Route::get('reports', [Manager\ReportController::class, 'index'])->name('reports');
    Route::get('reports/export/{type}', [Manager\ReportController::class, 'export'])->name('reports.export');

    Route::get('remittances', [Manager\RemittanceController::class, 'index'])->name('remittances');
    Route::post('remittances', [Manager\RemittanceController::class, 'store'])->name('remittances.store');
    Route::post('remittances/{rider}/pay-fees', [Manager\RemittanceController::class, 'payFees'])->name('remittances.pay-fees');
    Route::post('remittances/{rider}/clear', [Manager\RemittanceController::class, 'clear'])->name('remittances.clear');
    Route::get('sms', [Manager\RemittanceController::class, 'smsLogs'])->name('sms');

    Route::get('bulk-sms', [Manager\BulkSmsController::class, 'index'])->name('bulk');
    Route::post('bulk-sms/preview', [Manager\BulkSmsController::class, 'preview'])->middleware('throttle:240,1,bulk-preview')->name('bulk.preview');
    Route::post('bulk-sms', [Manager\BulkSmsController::class, 'send'])->middleware('throttle:6,1,bulk-send')->name('bulk.send');
    Route::get('bulk-sms/{campaign}', [Manager\BulkSmsController::class, 'show'])->name('bulk.show');
    Route::post('sms/test', [Manager\RemittanceController::class, 'testSms'])->middleware('throttle:5,1,sms-test')->name('sms.test');
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
