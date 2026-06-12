<?php
/**
 * Plugin Name: UKV Conversion Optimisation
 * Desc: Lightweight, non-spammy conversion features for the UK visa facilitation site:
 *       (1) [ukv_trust_bar] trust strip, auto-prepended to destination singles;
 *       (2) [ukv_testimonials] published reviews from the `testimonial` category;
 *       (3) once-per-session exit-intent callback modal (front-end only, vanilla JS).
 */
defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Ensure the `testimonial` category exists (guarded — runs once if missing).
 * ---------------------------------------------------------------------- */
add_action( 'init', 'ukv_conv_ensure_testimonial_term' );
function ukv_conv_ensure_testimonial_term() {
	if ( ! term_exists( 'testimonial', 'category' ) ) {
		wp_insert_term( 'Testimonials', 'category', array( 'slug' => 'testimonial' ) );
	}
}

/* -------------------------------------------------------------------------
 * 1. Trust bar — [ukv_trust_bar] + auto-prepend on destination singles.
 * ---------------------------------------------------------------------- */
add_shortcode( 'ukv_trust_bar', 'ukv_conv_trust_bar' );
function ukv_conv_trust_bar( $atts = array() ) {
	$items = array(
		'Independent service',
		'Secure Stripe checkout',
		'UK-based support',
		'Not a government website',
	);
	$html  = '<div class="ukv-trust-bar" role="note" aria-label="Trust information" style="display:flex;flex-wrap:wrap;gap:8px 18px;align-items:center;justify-content:center;font-size:13px;line-height:1.4;padding:10px 14px;margin:0 0 18px;background:#f4f7fb;border:1px solid #dbe4f0;border-radius:8px;color:#1f3d5c;">';
	$last  = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		$html .= '<span class="ukv-trust-bar__item">' . esc_html( $item ) . '</span>';
		if ( $i < $last ) {
			$html .= '<span class="ukv-trust-bar__sep" aria-hidden="true" style="opacity:.4;">·</span>';
		}
	}
	$html .= '</div>';
	return $html;
}

/** Auto-prepend the trust bar to the top of destination single content (main query only). */
add_filter( 'the_content', 'ukv_conv_prepend_trust_bar', 9 );
function ukv_conv_prepend_trust_bar( $content ) {
	if ( is_admin() ) {
		return $content;
	}
	if ( is_singular( 'destination' ) && in_the_loop() && is_main_query() ) {
		return ukv_conv_trust_bar() . $content;
	}
	return $content;
}

/* -------------------------------------------------------------------------
 * 2. Testimonials — [ukv_testimonials] published posts in `testimonial` cat.
 * ---------------------------------------------------------------------- */
add_shortcode( 'ukv_testimonials', 'ukv_conv_testimonials' );
function ukv_conv_testimonials( $atts = array() ) {
	$posts = get_posts( array(
		'category_name' => 'testimonial',
		'post_status'   => 'publish',
		'numberposts'   => 6,
	) );
	if ( empty( $posts ) ) {
		return '';
	}

	$html = '<div class="ukv-testimonials" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin:18px 0;">';
	foreach ( $posts as $post ) {
		$title   = get_the_title( $post );
		$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
		$html   .= '<div class="ukv-testimonial-card" style="padding:16px 18px;background:#fff;border:1px solid #e3e8ef;border-radius:10px;box-shadow:0 1px 3px rgba(20,40,70,.06);">';
		$html   .= '<p class="ukv-testimonial-card__rating" aria-hidden="true" style="margin:0 0 6px;color:#f5a623;letter-spacing:2px;">&#9733;&#9733;&#9733;&#9733;&#9733;</p>';
		$html   .= '<h3 class="ukv-testimonial-card__title" style="margin:0 0 8px;font-size:16px;line-height:1.3;">' . esc_html( $title ) . '</h3>';
		$html   .= '<p class="ukv-testimonial-card__text" style="margin:0;font-size:14px;line-height:1.5;color:#444;">' . esc_html( $excerpt ) . '</p>';
		$html   .= '</div>';
	}
	$html .= '</div>';
	return $html;
}

/* -------------------------------------------------------------------------
 * 3. Exit-intent callback modal — front-end only, once per session.
 * ---------------------------------------------------------------------- */
add_action( 'wp_footer', 'ukv_conv_exit_intent_modal' );
function ukv_conv_exit_intent_modal() {
	// Front-end only. Never in admin / feeds.
	if ( is_admin() || is_feed() ) {
		return;
	}

	$url = esc_url( home_url( '/ukvisa/request-a-callback/' ) );
	?>
<!-- UKV exit-intent callback modal -->
<div id="ukv-exit-modal" role="dialog" aria-modal="true" aria-labelledby="ukv-exit-title" hidden
     style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;background:rgba(15,30,50,.55);padding:18px;">
	<div style="max-width:420px;width:100%;background:#fff;border-radius:12px;padding:26px 24px;box-shadow:0 12px 40px rgba(10,25,50,.35);text-align:center;position:relative;">
		<button type="button" id="ukv-exit-close" aria-label="Close"
		        style="position:absolute;top:8px;right:10px;border:0;background:transparent;font-size:24px;line-height:1;cursor:pointer;color:#888;">&times;</button>
		<h2 id="ukv-exit-title" style="margin:0 0 10px;font-size:20px;line-height:1.3;color:#1f3d5c;">Before you go &mdash; want us to call you back?</h2>
		<p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#555;">A UK-based adviser can answer your questions at a time that suits you. No obligation.</p>
		<a id="ukv-exit-cta" href="<?php echo $url; // already escaped ?>"
		   style="display:inline-block;background:#1f5fbf;color:#fff;text-decoration:none;font-weight:600;padding:11px 22px;border-radius:8px;font-size:15px;">Request a callback</a>
		<p style="margin:14px 0 0;"><button type="button" id="ukv-exit-dismiss" style="border:0;background:transparent;color:#888;font-size:13px;cursor:pointer;text-decoration:underline;">No thanks</button></p>
	</div>
</div>
<script>
(function(){
	var KEY = 'ukvExitShown';
	try { if ( sessionStorage.getItem( KEY ) ) { return; } } catch (e) {}
	var modal = document.getElementById('ukv-exit-modal');
	if ( ! modal ) { return; }
	var shown = false;
	function show(){
		if ( shown ) { return; }
		shown = true;
		try { sessionStorage.setItem( KEY, '1' ); } catch (e) {}
		modal.hidden = false;
		modal.style.display = 'flex';
	}
	function hide(){
		modal.hidden = true;
		modal.style.display = 'none';
	}
	document.addEventListener('mouseout', function(e){
		// Fire only when the pointer leaves toward the top of the viewport (exit intent).
		if ( ! e.relatedTarget && ! e.toElement && e.clientY <= 0 ) { show(); }
	});
	var close = document.getElementById('ukv-exit-close');
	var dismiss = document.getElementById('ukv-exit-dismiss');
	if ( close ) { close.addEventListener('click', hide); }
	if ( dismiss ) { dismiss.addEventListener('click', hide); }
	modal.addEventListener('click', function(e){ if ( e.target === modal ) { hide(); } });
	document.addEventListener('keydown', function(e){ if ( e.key === 'Escape' ) { hide(); } });
})();
</script>
	<?php
}
