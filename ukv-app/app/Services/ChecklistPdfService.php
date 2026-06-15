<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChecklistRequest;
use Illuminate\Http\Response;

/**
 * Renders a checklist snapshot to a downloadable/printable document.
 *
 * PDF STRATEGY (owner decision — see spec "Open decisions"): this DEFAULTS to a PRINT-CSS FALLBACK,
 * NOT a PDF library, so we add ZERO new Composer dependencies (the project ships without composer/
 * artisan access). The method returns an HTML Response whose Blade view carries a print-optimised
 * stylesheet (@media print) and an auto-print hint; the user taps "Save as PDF" in the browser's
 * print dialog to keep a branded copy.
 *
 * If a true server-side binary PDF is later required (e.g. as an email attachment), install dompdf
 * (`composer require barryvdh/laravel-dompdf`) — a DEPENDENCY — and swap renderPrintable() for a
 * `Pdf::loadView(...)->download()` call against the SAME 'checklist' view. The view is intentionally
 * inline-styled/self-contained so it renders identically under dompdf. We did NOT add dompdf here.
 *
 * The document is rendered from the request's stored SNAPSHOT (items + inputs), so it stays stable
 * even if the underlying requirement rules change after the request was created.
 */
final class ChecklistPdfService
{
    /**
     * Build a print-optimised HTML response for the checklist (browser "Save as PDF" fallback).
     *
     * Reuses the same email-checklist styling/shape so the printed copy matches what was emailed.
     */
    public function renderPrintable(ChecklistRequest $request): Response
    {
        $destination = $request->destination?->name ?? 'your destination';
        $items = is_array($request->items) ? $request->items : [];

        $html = view('checklist-pdf', [
            'request' => $request,
            'destination' => $destination,
            'items' => $items,
            'savedLink' => $this->savedLink($request),
        ])->render();

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /** Absolute saved-link URL for the request, so the printed copy can be re-found online. */
    private function savedLink(ChecklistRequest $request): string
    {
        return rtrim((string) config('ukv.base_url', ''), '/').'/checklist/'.$request->token;
    }
}
