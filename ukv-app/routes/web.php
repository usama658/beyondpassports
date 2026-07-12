<?php

use App\Http\Controllers\AppointmentEnquiryController;
use App\Http\Controllers\AppointmentSlotsController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChecklistDeliveryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\TrackController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// --- Public site (the content silo) ---
Route::view('/', 'public.home')->name('home');
Route::view('/services', 'public.services')->name('services'); // full-catalogue hub (config('ukv.services'))
Route::view('/tour-packages', 'public.tours')->name('tours'); // visa-led tour packages (config('ukv.tours'))
Route::view('/tools', 'public.tools')->name('tools');
// Nearest-centre finder (postcode / geolocation -> nearest IDP, VAC, partner centres).
Route::get('/find-a-centre', [CentreController::class, 'page'])->name('centre.page');
Route::get('/find-a-centre/search', [CentreController::class, 'search'])
    ->middleware('throttle:contact')
    ->name('centre.search');
Route::get('/driving-abroad', fn () => redirect('/services', 301))->name('idp');
Route::view('/about', 'public.about')->name('about');
Route::view('/contact', 'public.contact')->name('contact');
Route::get('/contact/thank-you', [ContactController::class, 'thanks'])->name('contact.thanks');
Route::view('/legal', 'public.legal')->name('legal');
Route::view('/compare', 'public.compare')->name('compare');
// Standalone paid-traffic landing page (Speed/outcome). Orphaned by design:
// noindex, NOT in nav/footer, NOT in SitemapController. Reachable by URL only.
// Paid-traffic landing pages (site-theme light). Orphaned: noindex, NOT in nav/footer/sitemap.
Route::view('/schengen-visa-agent', 'public.lp-speed')->name('lp.speed');
// Original dark-premium version of the Speed LP, kept on request.
Route::view('/schengen-visa-agent-premium', 'public.lp-speed-original')->name('lp.speed.original');
Route::view('/schengen-visa-refusal-risk', 'public.lp-fear')->name('lp-fear');
Route::view('/schengen-visa-appointment', 'public.lp-appointments')->name('lp-appointments');
Route::view('/honest-schengen-visa-service', 'public.lp-trust')->name('lp-trust');
Route::view('/schengen-visa-refused', 'public.lp-refused')->name('lp-refused');
Route::view('/schengen-visa-help', 'public.lp-bold')->name('lp-bold'); // Bold LP: dual-lane hero + case-file sections
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
// Legacy guide slug -> new nested country-guide home (301), registered before the {slug} catch.
Route::redirect('/guides/do-uk-travellers-need-visa-turkey', '/visa/turkey/do-i-need-a-visa', 301);
Route::get('/guides/{slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');

// --- Document-checklist tool (value-first lead magnet; reuses the requirements engine) ---
Route::get('/document-checklist', [ChecklistController::class, 'tool'])->name('checklist.tool');
Route::post('/document-checklist', [ChecklistController::class, 'result'])
    ->middleware('throttle:contact')
    ->name('checklist.result');
Route::get('/checklist/{checklistRequest}', [ChecklistController::class, 'show'])->name('checklist.show');
Route::post('/checklist/{checklistRequest}/checkout', [ChecklistController::class, 'checkout'])
    ->name('checklist.checkout');
// Instant, no-contact downloads (value-first): printable PDF + calendar reminder.
Route::get('/checklist/{checklistRequest}/print', [ChecklistController::class, 'printable'])->name('checklist.print');
Route::get('/checklist/{checklistRequest}/calendar.ics', [ChecklistController::class, 'calendar'])->name('checklist.calendar');
Route::post('/checklist/{checklist}/send', [ChecklistDeliveryController::class, 'send'])
    ->middleware('throttle:contact')
    ->name('checklist.send');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
// Footer newsletter opt-in (consent-gated marketing capture).
Route::post('/subscribe', [SubscribeController::class, 'store'])->middleware('throttle:contact')->name('subscribe.store');
// Landing-page appointment/eligibility enquiry — background lead capture (form still opens WhatsApp).
Route::post('/appointment-enquiry', [AppointmentEnquiryController::class, 'store'])
    ->middleware('throttle:contact')->name('appointment.enquiry');

// --- Apply funnel (the coded apply page lives on Netlify and POSTs here) ---
Route::view('/apply', 'public.apply')->name('apply'); // eligibility-aware intake form (POSTs to apply.store)
Route::post('/apply', [ApplyController::class, 'store'])->name('apply.store');
Route::get('/apply/thank-you', [ApplyController::class, 'thanks'])->name('apply.thanks');

// --- Checkout (standard lane -> Stripe hosted Checkout) ---
Route::match(['get', 'post'], '/checkout/{order:order_ref}', [CheckoutController::class, 'create'])
    ->name('checkout.create');
Route::get('/checkout/{order:order_ref}', [CheckoutController::class, 'create'])->name('checkout.show');

// --- Stripe webhook (CSRF-exempt via bootstrap/app.php) ---
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// Stripe config status (no secrets exposed) — confirms whether the server picked up keys.
Route::get('/health/stripe', function () {
    $secret = (string) config('services.stripe.secret');
    return response()->json([
        'secret_configured' => $secret !== '',
        'secret_mode' => str_starts_with($secret, 'sk_live_') ? 'live'
            : (str_starts_with($secret, 'sk_test_') ? 'test'
            : (str_starts_with($secret, 'rk_') ? 'restricted' : ($secret === '' ? 'none' : 'other'))),
        'webhook_secret_configured' => (string) config('services.stripe.webhook_secret') !== '',
    ]);
})->name('health.stripe');

// Mail + queue config status (no secrets) — confirms whether prod can actually send,
// and whether queued jobs are flowing or piling up in failed_jobs.
Route::get('/health/mail', function () {
    $mailer = (string) config('mail.default');
    $smtp = (array) config('mail.mailers.smtp', []);
    $pending = $failed = null;
    try { $pending = \DB::table('jobs')->count(); } catch (\Throwable $e) {}
    try { $failed = \DB::table('failed_jobs')->count(); } catch (\Throwable $e) {}
    return response()->json([
        'mailer' => $mailer,
        'smtp_host_configured' => ! empty($smtp['host']),
        'smtp_username_configured' => ! empty($smtp['username']),
        'smtp_port' => $smtp['port'] ?? null,
        'from_address' => config('mail.from.address'),
        'queue_connection' => config('queue.default'),
        'jobs_pending' => $pending,
        'jobs_failed' => $failed,
    ]);
})->name('health.mail');

// --- Confirmation / thank-you ---
Route::get('/confirmation/{order:order_ref}', function (Order $order, \App\Services\RequirementService $requirements) {
    // Document Requirements Engine: pass the personalised checklist (for()) to the view.
    return view('confirmation', [
        'order'    => $order,
        'docItems' => $requirements->for($order),
    ]);
})->name('confirmation');

// --- Post-payment document upload (customer authenticates by order ref + email) ---
Route::get('/documents', [DocumentUploadController::class, 'page'])->name('documents');
Route::post('/documents/upload', [DocumentUploadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('documents.upload');
Route::post('/documents/details', [DocumentUploadController::class, 'detail'])->middleware('throttle:10,1')->name('documents.detail'); // post-pay document-detail capture (Document Requirements Engine)

// --- Public destination money pages (DB-driven, SEO) ---
Route::get('/schengen-visa', [DestinationController::class, 'index'])->name('destinations.index');
Route::redirect('/destinations', '/schengen-visa', 301); // legacy slug → canonical Schengen visa page
Route::get('/schengen-visa-consultancy', [DestinationController::class, 'schengenLanding'])->name('schengen.landing');
// Drafted: /visa/schengen duplicated the stronger /schengen-visa hub (which has its own region
// tabs + searchable grid). 301 to the canonical page. Controller schengen() + destinations.schengen
// view are kept in code, dormant, so this is reversible — restore the Route::get to un-draft.
Route::redirect('/visa/schengen', '/schengen-visa', 301);
// Public per-centre appointment slots (JSON) for the /schengen-visa slot picker.
Route::get('/appointments/slots', [AppointmentSlotsController::class, 'index'])
    ->middleware('throttle:60,1')->name('appointments.slots');
Route::get('/visa/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
// Nested country guide (spoke) — constrained to the 15 known topic slugs so it never shadows
// a real destination slug or the money page above.
Route::get('/visa/{destination:slug}/{topic}', [GuideController::class, 'showCountry'])
    ->where('topic', 'do-i-need-a-visa|documents|passport-validity|processing-time|how-to-apply|cost|when-to-apply|children|refused|uk-residents|transit|visa-on-arrival|entries|driving|health')
    ->name('guides.country');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// --- Local-only design-preview pick capture (dev loop; never registered in production) ---
if (! app()->isProduction()) {
    Route::get('/__pick', function (\Illuminate\Http\Request $request) {
        file_put_contents(storage_path('design_pick.json'), json_encode([
            'section' => $request->query('section'),
            'pick' => $request->query('pick'),
            'note' => $request->query('note'),
            'ts' => time(),
        ]));
        return response()->json(['ok' => true]);
    });
}

// --- Public status tracker ---
Route::get('/track', [TrackController::class, 'show'])->name('track.show');
Route::post('/track/lookup', [TrackController::class, 'lookup'])
    ->middleware('throttle:tracker')
    ->name('track.lookup');
