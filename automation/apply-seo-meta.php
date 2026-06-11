<?php
// Apply RankMath SEO meta (title/description/focus) per page. Run via wp eval-file.
// slug => [title, description, focus]. Destinations are CPT 'destination'; rest are 'page'.
$meta = [
	'home' => ['UK Visas, eVisas, ETAs & IDPs Made Easy | UKVisaCo','Get your visa, eVisa, ETA or international driving permit sorted before you travel. Clear guidance and fast applications for UK travellers worldwide.','uk travel visa'],
	'turkey' => ['Turkey: Visa-Free for UK Citizens | UKVisaCo','Good news for UK travellers: Turkey is visa-free for stays up to 90 days. Find out exactly what you need before you fly, plus passport and entry tips.','turkey visa uk citizens'],
	'egypt' => ['Egypt eVisa for UK Citizens | Apply Online','British travellers need an eVisa for Egypt. Apply online in minutes, skip the queues and arrive ready to explore. Simple, fast guidance from UKVisaCo.','egypt evisa uk citizens'],
	'india' => ['India eVisa for UK Citizens | Apply Online','UK travellers need an eVisa to visit India. Apply online with clear step-by-step guidance, avoid common mistakes and get approved before you fly.','india evisa uk citizens'],
	'morocco' => ['Morocco: Visa-Free for UK Citizens | UKVisaCo','Great news for British travellers: Morocco is visa-free for stays up to 90 days. Check your passport validity and entry requirements before you go.','morocco visa uk citizens'],
	'uae' => ['UAE: Visa-Free Entry for UK Citizens | UKVisaCo','UK travellers get visa-free entry to the UAE for short stays. Learn what Dubai and Abu Dhabi require at the border before your trip.','uae visa uk citizens'],
	'australia' => ['Australia ETA & eVisitor for UK Citizens | UKVisaCo','British travellers need an ETA or eVisitor to visit Australia. Apply online quickly, understand which one suits you and travel with confidence.','australia eta uk citizens'],
	'usa' => ['USA ESTA for UK Citizens | Apply Online','UK travellers need an ESTA to visit the USA visa-free. Apply online in minutes, understand the rules and get approved well before you fly.','usa esta uk citizens'],
	'schengen' => ['Schengen: Visa-Free for UK Citizens | UKVisaCo','British travellers enjoy visa-free Schengen access for 90 days in any 180. Understand the rules, count your days and travel Europe with ease.','schengen visa uk citizens'],
	'do-i-need-a-visa' => ['Do I Need a Visa? Free Checker | UKVisaCo','Find out in seconds whether you need a visa, eVisa or ETA for your trip. Free visa requirements checker built for UK passport holders.','do i need a visa'],
	'international-driving-permit' => ['International Driving Permit (IDP) Guide | UKVisaCo','Do you need an International Driving Permit? UK drivers can get an IDP for £5.50 in person at PayPoint. Find out which one you need and how.','international driving permit'],
	'driving-in-france' => ['Driving in France: Do UK Drivers Need an IDP?',"UK drivers don't need an IDP for France with a photocard licence. Learn what you do need, from insurance to kit, before you hit the road.",'driving in france uk licence'],
	'driving-in-usa' => ['Driving in the USA: IDP Guide for UK Drivers','Planning to drive in the USA? UK drivers may need a 1949 International Driving Permit. Learn the rules state by state before you travel.','driving in usa uk licence'],
	'driving-in-turkey' => ['Driving in Turkey: IDP Guide for UK Drivers','Driving in Turkey? UK drivers need a 1968 International Driving Permit alongside their licence. Find out how to get one and what to carry.','driving in turkey uk licence'],
	'things-to-do-in-side-turkey' => ['Best Things to Do in Side, Turkey | UKVisaCo','Discover the best things to do in Side, Turkey, from ancient ruins to golden beaches. A handy guide for UK travellers planning their trip.','things to do in side turkey'],
	'best-time-to-visit-egypt' => ['Best Time to Visit Egypt | UKVisaCo','When is the best time to visit Egypt? Find the ideal months for weather, sightseeing and Nile cruises, with practical tips for UK travellers.','best time to visit egypt'],
	'do-i-need-vaccinations-for-india' => ["Vaccinations for India: UK Travellers' Guide",'Wondering which vaccinations you need for India? Our guide for UK travellers covers recommended jabs, timings and NHS advice before you fly.','vaccinations for india'],
	'is-morocco-safe-for-tourists' => ['Is Morocco Safe for Tourists? UK Travel Guide','Is Morocco safe for tourists? An honest guide for UK travellers covering safety, scams, local customs and practical tips for a smooth trip.','is morocco safe for tourists'],
	'dubai-dress-code-for-tourists' => ['Dubai Dress Code for Tourists | UKVisaCo','What should you wear in Dubai? Our dress code guide for UK travellers covers malls, beaches, mosques and dining so you pack with confidence.','dubai dress code for tourists'],
	'flight-time-uk-to-australia' => ['Flight Time from the UK to Australia | UKVisaCo','How long is the flight from the UK to Australia? Compare routes, stopovers and total journey times to plan your trip down under with ease.','flight time uk to australia'],
	'esta-vs-us-visa' => ['ESTA vs US Visa: Which Do UK Travellers Need?','ESTA or US visa? We explain the difference for UK travellers, who qualifies for each and how to choose the right one before booking.','esta vs us visa'],
	'schengen-90-180-day-rule' => ['Schengen 90/180 Day Rule Explained | UKVisaCo','Confused by the Schengen 90/180 day rule? We explain how it works for UK travellers, how to count your days and avoid overstaying in Europe.','schengen 90 180 day rule'],
];
$dests = ['turkey','egypt','india','morocco','uae','australia','usa','schengen'];
$n = 0;
foreach ( $meta as $slug => $m ) {
	$type = in_array( $slug, $dests, true ) ? 'destination' : 'page';
	$p = get_page_by_path( $slug, OBJECT, $type );
	if ( ! $p ) { echo "MISS $slug ($type)\n"; continue; }
	update_post_meta( $p->ID, 'rank_math_title', $m[0] );
	update_post_meta( $p->ID, 'rank_math_description', $m[1] );
	update_post_meta( $p->ID, 'rank_math_focus_keyword', $m[2] );
	$n++;
}
echo "applied SEO meta to $n pages\n";
