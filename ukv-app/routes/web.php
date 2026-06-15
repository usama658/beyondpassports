<?php

use App\Http\Controllers\ApplyController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TrackController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// --- Public site (the content silo) ---
Route::view('/', 'public.home')->name('home');
Route::view('/tools', 'public.tools')->name('tools');
Route::view('/driving-abroad', 'public.driving-abroad')->name('idp');
Route::view('/about', 'public.about')->name('about');
Route::view('/contact', 'public.contact')->name('contact');
Route::view('/legal', 'public.legal')->name('legal');
Route::view('/compare', 'public.compare')->name('compare');
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

// --- Apply funnel (the coded apply page lives on Netlify and POSTs here) ---
Route::view('/apply', 'public.apply')->name('apply'); // eligibility-aware intake form (POSTs to apply.store)
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

// --- Public destination money pages (DB-driven, SEO) ---
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/visa/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// --- Public status tracker ---
Route::get('/track', [TrackController::class, 'show'])->name('track.show');
Route::post('/track/lookup', [TrackController::class, 'lookup'])
    ->middleware('throttle:tracker')
    ->name('track.lookup');
