{{-- Shared templated body for guides without a dedicated article partial.
     Reads from the $guide registry entry passed by show.blade.php. Deliberately
     general and compliance-safe. Concrete, destination-specific facts live in
     the dedicated per-slug partials. --}}

  <p>This is one of the questions our UK team hears most, so here's a plain-English overview. Treat it as general orientation rather than a personal answer: the exact rules depend on your nationality, your country of residence and the destination, and they can change over time.</p>

  <h2>Start with the principle, not the rumour</h2>
  <p>Travel admin attracts a lot of confident-sounding advice, from forums, from friends, from a half-remembered trip a few years ago. The trouble is that the same trip can look completely different for two people, and rules quietly change between years. The reliable approach is to understand the underlying principle, then confirm the current detail for your own situation before you commit to dates.</p>

  <h2>What usually matters most</h2>
  <ul>
    <li><strong>Your nationality and residence.</strong> Both can change what you need, even for an identical itinerary.</li>
    <li><strong>Your purpose and length of stay.</strong> Tourism, business and longer visits often follow different rules.</li>
    <li><strong>Your documents.</strong> Most delays and refusals come down to paperwork (passport validity, blank pages, a clear photo and accurate details) rather than eligibility.</li>
    <li><strong>Timing.</strong> Applying in good time leaves room to fix a detail or renew a passport before it affects your trip.</li>
  </ul>

  <p class="inline-note">Requirements depend on your nationality and where you live, and they change. This guide explains the general idea. It doesn't tell you your personal requirement. Always confirm the current rule before you travel.</p>

  <h2>A simple way to get it right</h2>
  <p>You don't have to piece it together alone. The quickest route is to check your specific trip, then have a real person review the details before anything is submitted:</p>
  <ul>
    <li><a href="{{ url('/tools') }}">Run our checker</a> to see what your trip is likely to need.</li>
    <li>Browse our <a href="{{ url('/schengen-visa') }}">destinations</a> for the current category and typical requirements.</li>
    <li>If driving is part of your trip, see our <a href="{{ url('/driving-abroad') }}">driving-abroad (IDP) guidance</a>.</li>
    <li>Read up first, then <a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I have a question about this guide.') }}" target="_blank" rel="noopener">Check eligibility →</a> when you're ready and we'll check it for errors.</li>
  </ul>

  <p>For more background, the <a href="{{ url('/guides/eta-vs-visa-difference') }}">ETA vs visa explainer</a> and our checklist of <a href="{{ url('/guides/documents-before-you-apply') }}">documents to prepare before you apply</a> are good companions to this guide.</p>

  <p class="inline-note">Beyond Passports is an independent service and not a government website. We help you understand requirements, prepare carefully and submit correctly. We can't change a rule or guarantee any government decision.</p>
