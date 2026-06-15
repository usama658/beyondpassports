<?php

use App\Http\Controllers\ApplyController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TrackController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- Apply funnel (the coded apply page lives on Netlify and POSTs here) ---
Route::view('/apply', 'welcome')->name('apply'); // placeholder GET target (cancel_url); static UI is on the front host
Route::post('/apply', [ApplyController::class, 'store'])->name('apply.store');

// --- Checkout (standard lane -> Stripe hosted Checkout) ---
Route::match(['get', 'post'], '/checkout/{order:order_ref}', [CheckoutController::class, 'create'])
    ->name('checkout.create');
Route::get('/checkout/{order:order_ref}', [CheckoutController::class, 'create'])->name('checkout.show');

// --- Stripe webhook (CSRF-exempt via bootstrap/app.php) ---
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// --- Confirmation / thank-you ---
Route::get('/confirmation/{order:order_ref}', function (Order $order) {
    return view('confirmation', ['order' => $order]);
})->name('confirmation');

// --- Post-payment document upload (customer authenticates by order ref + email) ---
Route::post('/documents/upload', [DocumentUploadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('documents.upload');

// --- Public status tracker ---
Route::get('/track', [TrackController::class, 'show'])->name('track.show');
Route::post('/track/lookup', [TrackController::class, 'lookup'])
    ->middleware('throttle:tracker')
    ->name('track.lookup');
