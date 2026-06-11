<?php
/**
 * Plugin Name: UKV Analytics (GDPR Consent-Gated)
 * Description: Injects Google Analytics 4 (gtag.js) and Microsoft Clarity, gated behind a dependency-free cookie-consent banner using Google Consent Mode v2.
 * Version:     1.0.0
 * Author:      UKV
 *
 * Save as: wp-content/mu-plugins/ukv-analytics.php
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * SITE OWNER: edit these two values.
 *   - UKV_GA4_ID:     your GA4 Measurement ID, e.g. 'G-XXXXXXX'
 *   - UKV_CLARITY_ID: your Microsoft Clarity project ID, e.g. 'abcdefghij'
 * Leave a value empty ('') to disable that tag.
 * ---------------------------------------------------------------------- */
if ( ! defined( 'UKV_GA4_ID' ) ) {
	define( 'UKV_GA4_ID', 'G-LPFVV2XPSR' );
}
if ( ! defined( 'UKV_CLARITY_ID' ) ) {
	define( 'UKV_CLARITY_ID', '' );
}

if ( ! function_exists( 'ukv_analytics_consent_value' ) ) {
	/**
	 * Read the visitor's stored consent choice.
	 *
	 * @return string 'accept', 'reject', or '' when no choice has been made.
	 */
	function ukv_analytics_consent_value() {
		if ( empty( $_COOKIE['ukv_consent'] ) ) {
			return '';
		}

		$value = sanitize_key( wp_unslash( $_COOKIE['ukv_consent'] ) );

		return in_array( $value, array( 'accept', 'reject' ), true ) ? $value : '';
	}
}

if ( ! function_exists( 'ukv_analytics_head' ) ) {
	/**
	 * Output Consent Mode v2 defaults, and (only when consent === accept)
	 * the GA4 + Clarity tracking snippets, in the document <head>.
	 */
	function ukv_analytics_head() {
		// Never load tracking in admin, feeds, or for logged-out previews of nothing.
		if ( is_admin() ) {
			return;
		}

		$ga4_id     = trim( (string) UKV_GA4_ID );
		$clarity_id = trim( (string) UKV_CLARITY_ID );

		// If neither tag is configured, there is nothing to do at all.
		if ( '' === $ga4_id && '' === $clarity_id ) {
			return;
		}

		$consent      = ukv_analytics_consent_value();
		$has_accepted = ( 'accept' === $consent );

		// Pre-escape IDs for safe inline JS string usage.
		$ga4_js     = esc_js( $ga4_id );
		$clarity_js = esc_js( $clarity_id );
		$ga4_url    = esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga4_id ) );

		// --- Consent Mode v2 bootstrap (always emitted when GA4 is configured) ---
		if ( '' !== $ga4_id ) {
			?>
<!-- UKV Analytics: Google Consent Mode v2 defaults -->
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){ dataLayer.push(arguments); }
	gtag('consent', 'default', {
		'ad_storage': 'denied',
		'ad_user_data': 'denied',
		'ad_personalization': 'denied',
		'analytics_storage': 'denied',
		'functionality_storage': 'denied',
		'personalization_storage': 'denied',
		'security_storage': 'granted',
		'wait_for_update': 500
	});
<?php if ( $has_accepted ) : ?>
	gtag('consent', 'update', {
		'ad_storage': 'granted',
		'ad_user_data': 'granted',
		'ad_personalization': 'granted',
		'analytics_storage': 'granted',
		'functionality_storage': 'granted',
		'personalization_storage': 'granted'
	});
<?php endif; ?>
</script>
			<?php
		}

		// Nothing further to inject until the visitor has accepted.
		if ( ! $has_accepted ) {
			return;
		}

		// --- Google Analytics 4 (gtag.js) ---
		if ( '' !== $ga4_id ) {
			?>
<!-- UKV Analytics: Google Analytics 4 -->
<script async src="<?php echo esc_url( $ga4_url ); ?>"></script>
<script>
	gtag('js', new Date());
	gtag('config', '<?php echo $ga4_js; ?>');
</script>
			<?php
		}

		// --- Microsoft Clarity ---
		if ( '' !== $clarity_id ) {
			?>
<!-- UKV Analytics: Microsoft Clarity -->
<script>
	(function(c,l,a,r,i,t,y){
		c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
		t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
		y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
	})(window, document, "clarity", "script", "<?php echo $clarity_js; ?>");
</script>
			<?php
		}
	}
	add_action( 'wp_head', 'ukv_analytics_head', 20 );
}

if ( ! function_exists( 'ukv_analytics_consent_banner' ) ) {
	/**
	 * Output the dependency-free cookie-consent banner in the footer.
	 * Shown until the visitor makes a choice (accept or reject).
	 */
	function ukv_analytics_consent_banner() {
		if ( is_admin() ) {
			return;
		}

		$ga4_id     = trim( (string) UKV_GA4_ID );
		$clarity_id = trim( (string) UKV_CLARITY_ID );

		// No tags configured -> no need to ask for consent.
		if ( '' === $ga4_id && '' === $clarity_id ) {
			return;
		}

		// A choice has already been made: do not show the banner.
		if ( '' !== ukv_analytics_consent_value() ) {
			return;
		}

		$privacy_url = esc_url( home_url( '/privacy/' ) );
		?>
<!-- UKV Analytics: cookie-consent banner -->
<div id="ukv-consent-banner" role="dialog" aria-live="polite" aria-label="<?php echo esc_attr__( 'Cookie consent', 'ukv' ); ?>" style="position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#0A2540;color:#ffffff;padding:16px 20px;box-shadow:0 -2px 12px rgba(0,0,0,.25);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;">
	<div style="max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;gap:12px 20px;justify-content:space-between;">
		<p style="margin:0;flex:1 1 320px;color:#ffffff;">
			<?php echo esc_html__( 'We use cookies to analyse traffic and improve your experience. You can accept or reject analytics cookies.', 'ukv' ); ?>
			<a href="<?php echo $privacy_url; ?>" style="color:#9DBDF2;text-decoration:underline;"><?php echo esc_html__( 'Privacy Policy', 'ukv' ); ?></a>
		</p>
		<div style="display:flex;gap:10px;flex:0 0 auto;">
			<button type="button" id="ukv-consent-reject" style="cursor:pointer;border:1px solid #ffffff;background:transparent;color:#ffffff;padding:9px 18px;border-radius:4px;font-size:14px;font-weight:600;">
				<?php echo esc_html__( 'Reject', 'ukv' ); ?>
			</button>
			<button type="button" id="ukv-consent-accept" style="cursor:pointer;border:1px solid #1456B8;background:#1456B8;color:#ffffff;padding:9px 18px;border-radius:4px;font-size:14px;font-weight:600;">
				<?php echo esc_html__( 'Accept', 'ukv' ); ?>
			</button>
		</div>
	</div>
</div>
<script>
	(function () {
		var banner = document.getElementById('ukv-consent-banner');
		if (!banner) { return; }

		function setConsent(choice) {
			var days = 180;
			var d = new Date();
			d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
			var secure = (location.protocol === 'https:') ? ';Secure' : '';
			document.cookie = 'ukv_consent=' + encodeURIComponent(choice) +
				';expires=' + d.toUTCString() +
				';path=/;SameSite=Lax' + secure;
			banner.parentNode && banner.parentNode.removeChild(banner);
			// Reload so PHP can emit (or withhold) the tracking scripts on next render.
			location.reload();
		}

		var acceptBtn = document.getElementById('ukv-consent-accept');
		var rejectBtn = document.getElementById('ukv-consent-reject');
		if (acceptBtn) { acceptBtn.addEventListener('click', function () { setConsent('accept'); }); }
		if (rejectBtn) { rejectBtn.addEventListener('click', function () { setConsent('reject'); }); }
	})();
</script>
		<?php
	}
	add_action( 'wp_footer', 'ukv_analytics_consent_banner', 20 );
}
