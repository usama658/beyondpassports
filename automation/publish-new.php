<?php
// Publish 6 new-destination guides + set RankMath meta for the 6 new money pages + guides. Run via wp eval-file.
$disc = '<p>Planning the practical side? See our {LINK} for entry requirements before you fly.</p>';

// guides: slug => [title, answer, body, money-slug, money-anchor, focus]
$guides = [
'best-time-to-visit-thailand' => ['Best Time to Visit Thailand: Month-by-Month Guide',
 'The best time to visit Thailand is November to early April, during the cool, dry season — clear skies, lower humidity and the best beach weather. December and January are most comfortable; March and April turn hot; the rainy season (May–October) brings cheaper prices and greener landscapes.',
 '<h2>The three seasons</h2><p>Cool (Nov–Feb): ~28–32°C, low humidity, ideal for temples, trekking and islands. Hot (Mar–May): 35°C+, beaches still good. Rainy (Jun–Oct): short downpours, fewer crowds, lower prices.</p><h2>By region</h2><p>Andaman coast (Phuket/Krabi): dry Nov–Apr. Gulf islands (Samui): driest into June. North (Chiang Mai): Nov–Feb, avoid Mar–Apr burning season.</p><h3>FAQ</h3><p><strong>Cheapest time?</strong> Jun–Oct. <strong>Best beach weather?</strong> Dec–Mar (Andaman).</p>',
 'thailand','Thailand visa guide','best time to visit thailand'],
'is-vietnam-safe-for-tourists' => ['Is Vietnam Safe for Tourists? An Honest UK Guide',
 'Yes, Vietnam is generally safe for tourists, including solo travellers and families. Violent crime against visitors is rare. The main risks are petty theft, traffic accidents and minor scams. With sensible precautions, most UK travellers enjoy Vietnam trouble-free.',
 '<h2>Main risks</h2><p>Bag-snatching by motorbike thieves in Hanoi/HCMC — keep bags away from the road. Traffic is the biggest hazard; cross slowly and predictably. Use Grab to avoid taxi scams.</p><h2>Practical tips</h2><p>Keep passport copies, use hotel safes, drink bottled water, get comprehensive insurance, save your address in Vietnamese.</p><h3>FAQ</h3><p><strong>Safe for solo women?</strong> Yes, with normal precautions. <strong>Tap water safe?</strong> No — bottled/filtered only.</p>',
 'vietnam','Vietnam visa guide','is vietnam safe for tourists'],
'kenya-safari-packing-list' => ['Kenya Safari Packing List: What UK Travellers Need',
 'For a Kenya safari, pack neutral-coloured lightweight clothing, layers for chilly mornings, sturdy closed shoes, a wide-brimmed hat, sun cream, insect repellent and binoculars. Add a zoom camera, power bank and personal medication. Soft-sided bags are best — light aircraft have strict limits.',
 '<h2>Clothing</h2><p>Neutral tones (khaki/beige/olive); avoid blue/black (tsetse flies) and white (dust). Layer up — cold mornings, warm midday.</p><h2>Gear</h2><p>Binoculars (one pair each), zoom camera + power bank, head torch, DEET repellent, high-SPF sun cream, reusable water bottle. Use a soft holdall (~15kg limit).</p><h3>FAQ</h3><p><strong>What not to wear?</strong> Bright colours, white, blue/black. <strong>Need binoculars?</strong> Yes — bring your own.</p>',
 'kenya','Kenya visa guide','kenya safari packing list'],
'best-time-for-tanzania-safari' => ['Best Time for a Tanzania Safari: Seasons Explained',
 'The best time for a Tanzania safari is the dry season, late June to October, when animals gather at water and sparse vegetation makes them easy to spot — also prime time for the Great Migration river crossings. For calving and lower prices, the green months January–March are excellent.',
 '<h2>Dry season (Jun–Oct)</h2><p>Peak wildlife viewing; Serengeti, Ngorongoro, Tarangire. Mara River crossings usually Jul–Sep. Busier + pricier — book ahead.</p><h2>Green season (Nov–Mar)</h2><p>Lush, great birdlife, fewer crowds, lower prices. Calving Jan–Feb in the southern Serengeti draws predators.</p><h3>FAQ</h3><p><strong>Migration crossings?</strong> Jul–Sep (north). <strong>Worth going in the rains?</strong> Yes — lush, quiet, newborns.</p>',
 'tanzania','Tanzania visa guide','best time for a tanzania safari'],
'things-to-do-in-sri-lanka' => ['Things to Do in Sri Lanka: Top Experiences',
 'Sri Lanka packs huge variety into a compact island. Top things to do: climb Sigiriya rock fortress, ride the scenic train through tea country to Ella, safari in Yala for leopards, relax on southern beaches like Mirissa, and explore colonial Galle Fort.',
 '<h2>Culture & history</h2><p>Sigiriya, the Cultural Triangle (Anuradhapura, Polonnaruwa), Kandy\'s Temple of the Tooth, and Galle Fort.</p><h2>Nature & wildlife</h2><p>The Ella train, hill-country tea estates, leopards in Yala, elephants in Udawalawe, whale-watching off Mirissa (Nov–Apr).</p><h3>FAQ</h3><p><strong>How many days?</strong> 10–14 for culture, tea country, safari + beaches. <strong>Good first Asia trip?</strong> Yes — compact and friendly.</p>',
 'sri-lanka','Sri Lanka visa guide','things to do in sri lanka'],
'is-brazil-safe-for-tourists' => ['Is Brazil Safe for Tourists? A Practical UK Guide',
 'Brazil is a rewarding destination millions visit safely each year, but it requires street smarts. Petty theft and opportunistic crime are the main concerns, especially in big cities. By staying alert, avoiding flashy valuables and sticking to well-populated areas, most UK travellers enjoy Brazil without serious problems.',
 '<h2>Main risks</h2><p>Pickpocketing and phone theft in crowds, transport and beaches. Favelas carry higher risk — only with a reputable tour. Don\'t resist a robbery.</p><h2>Staying safe</h2><p>Carry minimal valuables, use hotel safes, keep your phone away on the street, use Uber after dark, withdraw cash from ATMs inside banks by day. Check yellow-fever advice.</p><h3>FAQ</h3><p><strong>Safe for solo women?</strong> Yes, with precautions. <strong>Safest cities?</strong> Florianópolis, Foz do Iguaçu, parts of the northeast.</p>',
 'brazil','Brazil visa guide','is brazil safe for tourists'],
];

foreach ( $guides as $slug => $g ) {
	$content = '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">' . $g[1] . '</p>' . $g[2]
		. str_replace( '{LINK}', '<a href="/ukvisa/' . $g[3] . '/">' . $g[4] . '</a>', $disc );
	$e = get_page_by_path( $slug, OBJECT, 'page' );
	$arr = [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $g[0], 'post_content' => $content, 'post_status' => 'publish' ];
	$id = $e ? ( $arr['ID'] = $e->ID ) && wp_update_post( $arr ) ? $e->ID : wp_update_post( $arr ) : wp_insert_post( $arr );
	if ( $e ) { $id = $e->ID; }
	update_post_meta( $id, 'rank_math_title', $g[0] );
	update_post_meta( $id, 'rank_math_focus_keyword', $g[5] );
	echo "guide $slug -> #$id\n";
}

// money-page SEO meta (destination CPT)
$money = [
	'thailand' => ['Thailand Visa for UK Travellers | UKVisaCo','Thailand is visa-free for UK travellers for 60 days. Our guide covers entry rules, the arrival card and what to sort before you fly.','thailand visa uk'],
	'vietnam' => ['Vietnam Visa & Entry for UK Travellers','UK travellers get 45 days visa-free in Vietnam; an eVisa covers longer stays. Clear guidance on entry rules and costs before you book.','vietnam visa uk'],
	'kenya' => ['Kenya eTA for UK Travellers | Apply Online','UK travellers need a Kenya eTA. Apply online before you fly — our guide covers the cost, requirements and how it works.','kenya eta uk'],
	'tanzania' => ['Tanzania eVisa for UK Travellers | Apply','UK travellers need a Tanzania eVisa. Apply online before your safari — clear guidance on cost, requirements and processing time.','tanzania evisa uk'],
	'sri-lanka' => ['Sri Lanka ETA for UK Travellers | Apply','UK travellers need a Sri Lanka ETA (free from 2026). Apply online before you fly — our guide covers requirements and how it works.','sri lanka eta uk'],
	'brazil' => ['Brazil: Visa-Free for UK Travellers | UKVisaCo','Brazil is visa-free for UK travellers for 90 days. Our guide covers entry rules, passport validity and what to check before you go.','brazil visa uk'],
];
foreach ( $money as $slug => $m ) {
	$p = get_page_by_path( $slug, OBJECT, 'destination' );
	if ( ! $p ) { continue; }
	update_post_meta( $p->ID, 'rank_math_title', $m[0] );
	update_post_meta( $p->ID, 'rank_math_description', $m[1] );
	update_post_meta( $p->ID, 'rank_math_focus_keyword', $m[2] );
	echo "money meta $slug ok\n";
}
