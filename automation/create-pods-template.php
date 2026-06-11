<?php
// T7: create the Pods "destination-single" template (money page) rendered from Destination fields.
$code = <<<'HTML'
<article class="ukv-money">
  <h1>{@post_title} Visa for UK Citizens</h1>
  [if required_for_uk]
    <p class="ukv-status ukv-status--req"><strong>{@visa_type} required</strong> for UK citizens — up to {@max_stay_days} days.</p>
  [else]
    <p class="ukv-status ukv-status--free"><strong>No visa needed</strong> for UK citizens — up to {@max_stay_days} days.</p>
  [/if]

  <h2>Entry requirements</h2>
  <div class="ukv-list">{@requirements}</div>

  [if required_for_uk]
    <h2>Pricing</h2>
    <table class="ukv-fees">
      <thead><tr><th>Service</th><th>Price</th></tr></thead>
      <tbody>
        <tr><td>Standard</td><td>&pound;{@tier_standard_gbp} service + &pound;{@govt_fee_gbp} government fee</td></tr>
        <tr><td>Express</td><td>&pound;{@tier_express_gbp} service + &pound;{@govt_fee_gbp} government fee</td></tr>
        <tr><td>Premium</td><td>&pound;{@tier_premium_gbp} service + &pound;{@govt_fee_gbp} government fee</td></tr>
      </tbody>
    </table>
    <p class="ukv-fees-note">Government fee shown at cost; our service fee is additional.</p>
    <p><a class="ukv-cta" href="/ukvisa/apply/?dest={@post_name}">Start your application</a></p>

    <h2>How to apply</h2>
    <div class="ukv-list">{@how_to_steps}</div>

    <h2>Processing times</h2>
    <p>Standard: ~{@processing_standard_days} days &middot; Express: ~{@processing_express_hours} hours (faster handling, not a faster government decision).</p>
  [/if]

  [if idp_recommended]
    <aside class="ukv-idp"><strong>Driving in {@post_title}?</strong> You may need a {@idp_permit_type} International Driving Permit. <a href="/ukvisa/driving-in-{@post_name}/">See the driving guide</a>.</aside>
  [/if]

  <p class="ukv-disclaim">Independent service &mdash; <strong>not a government website</strong>. Our service fee is in addition to any official government fee.</p>
</article>
HTML;

$api = pods_api();
$existing = $api->load_template( [ 'name' => 'destination-single' ] );
if ( ! empty( $existing ) ) {
	$api->save_template( [ 'id' => $existing['id'], 'name' => 'destination-single', 'code' => $code ] );
	echo "template updated (#{$existing['id']})\n";
} else {
	$id = $api->save_template( [ 'name' => 'destination-single', 'code' => $code ] );
	echo "template created (#$id)\n";
}
