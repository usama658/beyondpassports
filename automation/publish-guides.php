<?php
// T9: publish 8 support guides (answer-first, links up to money page). Idempotent by slug.
$g = [];
// slug => [title, answer-para, body-html, money-slug, money-anchor]
$g['things-to-do-in-side-turkey'] = [ 'Things to Do in Side, Turkey',
 'Side is a resort town on Turkey\'s Turquoise Coast, famous for its Roman ruins beside the sea, sandy beaches and lively old town. Top things to do include the Temple of Apollo at sunset, the ancient amphitheatre, relaxing on East or West Beach, browsing the harbour bazaar and a day trip to Manavgat Waterfall.',
 '<h2>Ancient ruins</h2><p>The Temple of Apollo on the water\'s edge is the signature sight, stunning at golden hour. Nearby: the Roman amphitheatre, the agora and the archaeological museum.</p><h2>Beaches</h2><p>West Beach is livelier with watersports; East Beach is quieter and family-friendly. Boat trips run daily from the harbour.</p><h2>Day trips</h2><p>Manavgat Waterfall, Köprülü Canyon rafting, and the Roman theatre at Aspendos.</p><h3>FAQ</h3><p><strong>How many days in Side?</strong> Three to four for ruins, beaches and a day trip. <strong>Family-friendly?</strong> Yes &mdash; shallow beaches and a walkable old town.</p>',
 'turkey','Turkey travel hub' ];

$g['best-time-to-visit-egypt'] = [ 'Best Time to Visit Egypt',
 'The best time to visit Egypt is from October to April, when temperatures are pleasant for sightseeing in Cairo, Luxor and Aswan. This cooler season is ideal for the pyramids and Nile cruises. Summer (June to August) brings intense heat, though Red Sea resorts stay popular year-round.',
 '<h2>October&ndash;April: peak season</h2><p>Daytime 18&ndash;28&deg;C &mdash; perfect for Giza, the Valley of the Kings and Nile cruises. Book ahead for Christmas, New Year and Easter.</p><h2>May &amp; September: shoulder</h2><p>Hot but bearable, fewer crowds, lower prices.</p><h2>June&ndash;August: summer heat</h2><p>Luxor/Aswan often exceed 40&deg;C; start early, rest at midday. Red Sea resorts stay comfortable.</p><h3>FAQ</h3><p><strong>Hottest month?</strong> July&ndash;August. <strong>Cheapest?</strong> June&ndash;August inland.</p>',
 'egypt','Egypt travel guide' ];

$g['do-i-need-vaccinations-for-india'] = [ 'Do I Need Vaccinations for India?',
 'No vaccinations are legally required to enter India from the UK, unless you are arriving from a country with yellow fever risk. However, health professionals usually recommend hepatitis A, typhoid and tetanus, plus possibly hepatitis B and rabies depending on your trip. Consult your GP or a travel clinic 6&ndash;8 weeks before you go.',
 '<h2>Commonly recommended</h2><p>Hepatitis A, typhoid and tetanus cover the most common risks for standard trips.</p><h2>Consider for some trips</h2><p>Hepatitis B, rabies, Japanese encephalitis and cholera depending on region, duration and activities.</p><h2>Yellow fever &amp; malaria</h2><p>Proof of yellow fever vaccination is required only if arriving from a yellow-fever country. Antimalarials may be advised for some regions; prevent mosquito bites everywhere.</p><h3>FAQ</h3><p><strong>Anything compulsory?</strong> Only yellow fever, and only from at-risk countries. This is general info, not medical advice &mdash; check NHS Fit for Travel.</p>',
 'india','India travel hub' ];

$g['is-morocco-safe-for-tourists'] = [ 'Is Morocco Safe for Tourists?',
 'Yes, Morocco is generally safe for tourists, and millions of British travellers visit each year without trouble. The main risks are petty crime such as pickpocketing and scams in busy markets, rather than serious violence. With normal precautions, modest dress and awareness in crowded medinas, most visitors have a smooth experience.',
 '<h2>Common scams</h2><p>Unofficial "guides", "the square is closed" lines, henna grabbing, and overpriced taxis. A polite, firm "la, shukran" and walking on handles most of it.</p><h2>Women travellers</h2><p>Many travel solo successfully; expect some catcalling. Dress modestly and walk with purpose.</p><h2>Practical tips</h2><p>Drink bottled water, keep a passport copy, use registered taxis, and check FCDO advice before you go.</p><h3>FAQ</h3><p><strong>Marrakech at night?</strong> Busy tourist areas stay lively; stick to well-lit streets.</p>',
 'morocco','Morocco travel guide' ];

$g['dubai-dress-code-for-tourists'] = [ 'Dubai Dress Code for Tourists',
 'Dubai is relatively relaxed for tourists, but modest dress is expected in public. Cover your shoulders and knees in malls, souks and government buildings. Swimwear is fine at beaches, pools and water parks only. Avoid beachwear and revealing tops in public areas, and women must cover up when visiting mosques.',
 '<h2>In public</h2><p>Cover shoulders and knees. Lightweight tops, trousers and maxi dresses work well in the heat.</p><h2>Malls &amp; souks</h2><p>Smart-casual; avoid bare shoulders and very short shorts. Souks are slightly more conservative.</p><h2>Beaches &amp; mosques</h2><p>Swimwear is fine at pools/beaches but cover up when leaving. Mosques require covered hair, arms and legs.</p><h3>FAQ</h3><p><strong>Bikinis OK?</strong> Yes, at beaches/pools. <strong>Cover hair?</strong> Only in mosques.</p>',
 'uae','Dubai travel hub' ];

$g['flight-time-uk-to-australia'] = [ 'How Long Is the Flight to Australia from the UK?',
 'A direct flight from the UK to Australia takes around 17&ndash;19 hours, but most journeys involve one stop and total roughly 20&ndash;24 hours. London to Perth is the only non-stop option (about 17 hours), while flights to Sydney, Melbourne or Brisbane usually connect via the Middle East or Asia.',
 '<h2>Direct</h2><p>London Heathrow to Perth (Qantas) is the only non-stop, ~17 hours.</p><h2>One stop</h2><p>Most journeys connect via Dubai, Doha, Singapore or Hong Kong, totalling 20&ndash;24 hours including the layover.</p><h2>Surviving it</h2><p>Consider a stopover, stay hydrated, move regularly, and adjust to local time on boarding.</p><h3>FAQ</h3><p><strong>Shortest flight?</strong> London&ndash;Perth, ~17h. <strong>Non-stop to Sydney?</strong> Not currently scheduled.</p>',
 'australia','Australia travel guide' ];

$g['esta-vs-us-visa'] = [ 'ESTA vs US Visa: What\'s the Difference?',
 'An ESTA is an online travel authorisation for short visits (up to 90 days) under the Visa Waiver Programme, available to eligible British citizens for tourism or business. A US visa is a formal document obtained via an embassy interview, needed for longer stays, work, study, or for those who do not qualify for an ESTA. Most UK holidaymakers use an ESTA.',
 '<h2>ESTA</h2><p>Online approval linked to your passport for short trips up to 90 days; no interview; usually quick. Most UK passport holders qualify.</p><h2>US visa</h2><p>A formal authorisation in your passport, issued by an embassy with an in-person interview; required when your trip falls outside the Visa Waiver Programme.</p><h2>Which do you need?</h2><p>Holiday or short business trip of 90 days or less &rarr; usually an ESTA. Longer stays, work or study &rarr; a visa.</p><h3>FAQ</h3><p><strong>Is an ESTA a visa?</strong> No. <strong>Work on an ESTA?</strong> No.</p>',
 'usa','USA travel hub' ];

$g['schengen-90-180-day-rule'] = [ 'Schengen 90/180 Day Rule Explained',
 'The Schengen 90/180 day rule lets UK citizens stay in the Schengen Area for up to 90 days within any rolling 180-day period. The 180 days is a moving window, not a fixed block, so you count backwards from any given day. Once you have used 90 days you must leave until earlier days expire.',
 '<h2>What it means</h2><p>On any day in the Schengen Area, look back over the previous 180 days and total the days present &mdash; it must not exceed 90.</p><h2>How to count</h2><p>Pick a date, count back 180 days, add up every day in Schengen within that window (entry and exit days count). Use the EU\'s official Schengen calculator.</p><h2>Why it matters</h2><p>Overstaying can mean fines, deportation and entry bans; the EES will track this automatically.</p><h3>FAQ</h3><p><strong>Reset on 1 Jan?</strong> No &mdash; it is a rolling window. <strong>UK/Ireland count?</strong> No, neither is in Schengen.</p>',
 'schengen','Schengen travel guide' ];

foreach ( $g as $slug => $d ) {
	$content = '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">' . $d[1] . '</p>'
		. $d[2]
		. '<p>Planning the practical side? See our <a href="/ukvisa/' . $d[3] . '/">' . $d[4] . '</a> for entry requirements before you fly.</p>';
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$arr = [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $d[0], 'post_content' => $content, 'post_status' => 'publish' ];
	if ( $existing ) { $arr['ID'] = $existing->ID; wp_update_post( $arr ); echo "updated $slug\n"; }
	else { wp_insert_post( $arr ); echo "created $slug\n"; }
}
