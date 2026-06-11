<?php
// T3: rebuild the Home front page for conversion (brand-styled sections + Pods destination grid). Run via wp eval-file.
$home = get_page_by_path( 'home', OBJECT, 'page' );
if ( ! $home ) { echo "home page not found\n"; return; }
$id = $home->ID;

$content = <<<HTML
<section style="background:linear-gradient(135deg,#0A2540,#143a6b);color:#fff;padding:64px 24px;text-align:center;font-family:Inter,sans-serif">
  <h1 style="font-size:40px;margin:0 0 14px;color:#fff">UK Visas, eVisas &amp; ETAs &mdash; Sorted</h1>
  <p style="font-size:19px;max-width:640px;margin:0 auto 26px;color:#cdd8e8">Check what you need in seconds, then let our team review and submit your application. Independent service for British travellers.</p>
  <a href="/ukvisa/do-i-need-a-visa/" style="display:inline-block;background:#fff;color:#0A2540;padding:14px 28px;border-radius:6px;font-weight:700;text-decoration:none;margin:6px">Do I need a visa?</a>
  <a href="/ukvisa/apply/" style="display:inline-block;background:#1456B8;color:#fff;padding:14px 28px;border-radius:6px;font-weight:700;text-decoration:none;margin:6px">Start an application</a>
</section>

<section style="max-width:1040px;margin:48px auto;padding:0 24px;font-family:Inter,sans-serif">
  <h2 style="color:#0A2540;text-align:center">Where are you going?</h2>
  <p style="text-align:center;color:#5a6577;margin-bottom:24px">Pick your destination for requirements, fees and how to apply.</p>
  [ukv_dest_grid]
</section>

<section style="background:#EEF3FA;padding:48px 24px;font-family:Inter,sans-serif">
  <div style="max-width:1040px;margin:0 auto">
    <h2 style="color:#0A2540;text-align:center;margin-bottom:30px">How it works</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px">
      <div style="background:#fff;border-radius:10px;padding:22px"><div style="color:#1456B8;font-weight:800;font-size:22px">1</div><h3 style="color:#0A2540;margin:6px 0">Check &amp; apply</h3><p style="color:#5a6577">Use our free checker, then start your guided application.</p></div>
      <div style="background:#fff;border-radius:10px;padding:22px"><div style="color:#1456B8;font-weight:800;font-size:22px">2</div><h3 style="color:#0A2540;margin:6px 0">We review</h3><p style="color:#5a6577">Our team checks your documents before submission to catch errors.</p></div>
      <div style="background:#fff;border-radius:10px;padding:22px"><div style="color:#1456B8;font-weight:800;font-size:22px">3</div><h3 style="color:#0A2540;margin:6px 0">You receive it</h3><p style="color:#5a6577">Your eVisa arrives by email; an ETA is linked to your passport.</p></div>
    </div>
  </div>
</section>

<section style="max-width:1040px;margin:48px auto;padding:0 24px;font-family:Inter,sans-serif;text-align:center">
  <h2 style="color:#0A2540">Why travellers choose us</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-top:20px">
    <div><strong style="color:#1456B8">Free checker</strong><p style="color:#5a6577">Know what you need before you pay.</p></div>
    <div><strong style="color:#1456B8">Human review</strong><p style="color:#5a6577">We catch the errors that cause rejections.</p></div>
    <div><strong style="color:#1456B8">Transparent pricing</strong><p style="color:#5a6577">Service fee shown separately from official fees.</p></div>
    <div><strong style="color:#1456B8">Secure &amp; UK-based</strong><p style="color:#5a6577">Encrypted documents, deleted after delivery.</p></div>
  </div>
</section>

<section style="background:#fff7e6;border-top:1px solid #f0e2c0;border-bottom:1px solid #f0e2c0;padding:30px 24px;font-family:Inter,sans-serif;text-align:center">
  <strong style="color:#0A2540;font-size:18px">Driving abroad?</strong>
  <p style="color:#5a6577;margin:6px 0 12px">Check if you need an International Driving Permit and how to get one.</p>
  <a href="/ukvisa/international-driving-permit/" style="display:inline-block;background:#C8A24A;color:#fff;padding:11px 22px;border-radius:6px;font-weight:600;text-decoration:none">IDP guide</a>
</section>
HTML;

// Remove Elementor binding so the theme renders our post_content
delete_post_meta( $id, '_elementor_edit_mode' );
delete_post_meta( $id, '_elementor_data' );
delete_post_meta( $id, '_elementor_template_type' );
delete_post_meta( $id, '_elementor_version' );
delete_post_meta( $id, '_elementor_page_settings' );

wp_update_post( [ 'ID' => $id, 'post_content' => $content ] );
echo "home #$id rebuilt for conversion\n";
