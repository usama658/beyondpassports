<?php
/**
 * Configure RankMath SEO for the UKVisaCo site.
 *
 * Run with:  wp eval-file configure-rankmath.php
 *
 * RankMath stores its settings in three serialized option arrays:
 *   - rank-math-options-general  (sitemap toggle lives in -sitemap, but
 *                                 breadcrumbs + KG support flags live here)
 *   - rank-math-options-titles   (knowledge graph, separator, title templates,
 *                                 breadcrumbs, robots defaults)
 *   - rank-math-options-sitemap  (XML sitemap enable + per-post-type config)
 *
 * We always get_option() first and array_merge() onto the existing array so we
 * never clobber settings the user already has. Falls back to an empty array
 * when the option does not yet exist (fresh install / RankMath setup not run).
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "This script must be run through WP-CLI (wp eval-file).\n" );
	exit( 1 );
}

/**
 * Helper: defensively load a RankMath option group as an array.
 */
function ukv_get_rm_option( $key ) {
	$val = get_option( $key, array() );
	if ( ! is_array( $val ) ) {
		$val = array();
	}
	return $val;
}

/* -------------------------------------------------------------------------
 * 1. TITLES / META group  (rank-math-options-titles)
 *    - Knowledge Graph: Organization "UKVisaCo" + logo placeholder
 *    - Separator "|"
 *    - Site-wide title format
 *    - Breadcrumbs on
 *    - Sensible robots defaults (index, follow)
 * ---------------------------------------------------------------------- */

$logo_url = home_url( '/wp-content/uploads/ukvisaco-logo.png' ); // placeholder

$titles          = ukv_get_rm_option( 'rank-math-options-titles' );
$titles_defaults = array(
	// ---- Knowledge graph ----
	'knowledgegraph_type'    => 'company',      // company = Organization (vs 'person')
	'knowledgegraph_name'    => 'UKVisaCo',
	'knowledgegraph_logo'    => $logo_url,
	'knowledgegraph_logo_id' => 0,

	// ---- Separator character ----
	'title_separator'        => '|',

	// ---- Site-wide / homepage title ----
	'homepage_title'         => '%sitename% %page% %sep% %sitedesc%',

	// ---- Breadcrumbs ----
	'breadcrumbs'            => 'on',
	'breadcrumbs_separator'  => '-',
	'breadcrumbs_home'       => 'on',
	'breadcrumbs_home_label' => 'Home',

	// ---- Local SEO / business name mirrors ----
	'local_seo'              => 'off',

	// ---- Post type: post ----
	'pt_post_title'          => '%title% %sep% %sitename%',
	'pt_post_robots'         => array( 'index' ), // index,follow (follow is default when noindex/nofollow absent)
	'pt_post_custom_robots'  => 'off',
	'pt_post_add_meta_box'   => 'on',

	// ---- Post type: page ----
	'pt_page_title'          => '%title% %sep% %sitename%',
	'pt_page_robots'         => array( 'index' ),
	'pt_page_custom_robots'  => 'off',
	'pt_page_add_meta_box'   => 'on',
);

// Merge: our defaults win for the keys we manage, existing values kept otherwise.
$titles = array_merge( $titles, $titles_defaults );
update_option( 'rank-math-options-titles', $titles );

/* -------------------------------------------------------------------------
 * 2. GENERAL group  (rank-math-options-general)
 *    - Breadcrumbs feature flag + support toggles live here in addition to
 *      the per-template settings above.
 * ---------------------------------------------------------------------- */

$general          = ukv_get_rm_option( 'rank-math-options-general' );
$general_defaults = array(
	'breadcrumbs'              => 'on',
	'support_rank_math'        => 'off',
	'usage_tracking'           => 'off',
	// Keep noindex behaviour predictable for archives/search.
	'noindex_empty_taxonomies' => 'on',
);

$general = array_merge( $general, $general_defaults );
update_option( 'rank-math-options-general', $general );

/* -------------------------------------------------------------------------
 * 3. SITEMAP group  (rank-math-options-sitemap)
 *    - Enable XML sitemap
 *    - Include post + page
 *    - Don't exclude anything problematic
 * ---------------------------------------------------------------------- */

$sitemap          = ukv_get_rm_option( 'rank-math-options-sitemap' );
$sitemap_defaults = array(
	'items_per_page'        => 200,
	'include_images'        => 'on',
	'include_featured_image'=> 'off',

	// ---- Post type: post ----
	'pt_post_sitemap'       => 'on',
	'pt_post_image_customfields' => '',

	// ---- Post type: page ----
	'pt_page_sitemap'       => 'on',

	// ---- Don't exclude these post types ----
	'pt_attachment_sitemap' => 'off', // attachments excluded is sensible, not "problematic"

	// ---- Taxonomies: keep categories, drop tags noise is optional; leave categories on ----
	'tax_category_sitemap'  => 'on',
	'tax_post_tag_sitemap'  => 'off',

	// ---- No URL exclusions ----
	'exclude_posts'         => '',
	'exclude_terms'         => '',
);

$sitemap = array_merge( $sitemap, $sitemap_defaults );
update_option( 'rank-math-options-sitemap', $sitemap );

// RankMath also reads a top-level flag to know the sitemap module is active.
// Ensure the 'sitemap' module is enabled in the active-modules list.
$active_modules = get_option( 'rank_math_modules', array() );
if ( ! is_array( $active_modules ) ) {
	$active_modules = array();
}
foreach ( array( 'sitemap', 'breadcrumbs', 'rich-snippet' ) as $mod ) {
	if ( ! in_array( $mod, $active_modules, true ) ) {
		$active_modules[] = $mod;
	}
}
update_option( 'rank_math_modules', $active_modules );

/* -------------------------------------------------------------------------
 * 4. Per-post override: set the "apply" page to noindex
 *    RankMath stores per-post robots in post meta `rank_math_robots`
 *    as a serialized array, e.g. array( 'noindex' ).
 * ---------------------------------------------------------------------- */

$apply_status = 'not found';
$apply_page   = get_page_by_path( 'apply', OBJECT, 'page' );

if ( $apply_page instanceof WP_Post ) {
	update_post_meta( $apply_page->ID, 'rank_math_robots', array( 'noindex' ) );
	$apply_status = sprintf( 'page ID %d set to noindex', $apply_page->ID );
} else {
	$apply_status = 'page with slug "apply" not found — skipped (create it then re-run)';
}

/* -------------------------------------------------------------------------
 * 5. Flush so the sitemap rules / rewrites pick up immediately.
 * ---------------------------------------------------------------------- */

if ( function_exists( 'flush_rewrite_rules' ) ) {
	flush_rewrite_rules( false );
}

/* -------------------------------------------------------------------------
 * 6. Confirmation output.
 * ---------------------------------------------------------------------- */

echo "RankMath configuration applied for UKVisaCo:\n";
echo "  - Knowledge Graph: Organization (company) name=UKVisaCo, logo={$logo_url}\n";
echo "  - Homepage: WebSite + Organization knowledge graph enabled\n";
echo "  - Title separator: |\n";
echo "  - Title template (post & page): %title% %sep% %sitename%\n";
echo "  - Breadcrumbs: enabled (general + titles groups)\n";
echo "  - XML sitemap: enabled; post=on, page=on, category=on; no problematic exclusions\n";
echo "  - Robots default: index,follow\n";
echo "  - 'apply' page: {$apply_status}\n";
echo "  - Active modules: " . implode( ', ', $active_modules ) . "\n";
echo "Done.\n";
