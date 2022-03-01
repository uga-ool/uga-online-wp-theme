<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Breadcrumbs
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/genesis/
 */

/**
 * Helper function for the Genesis Breadcrumb Class.
 *
 * @since 1.0.0
 *
 * @global Genesis_Breadcrumb $_genesis_breadcrumb
 *
 * @param array $args Breadcrumb arguments.
 */
function genesis_breadcrumb( $args = [] ) {

	global $_genesis_breadcrumb;

	if ( ! $_genesis_breadcrumb ) {
		$_genesis_breadcrumb = new Genesis_Breadcrumb();
	}

	$_genesis_breadcrumb->output( $args );

}

/**
 * Are breadcrumbs hidden for the current page?
 *
 * Indicates that the “Hide breadcrumbs” checkbox is enabled and checked.
 *
 * @since 3.1.0
 *
 * @return bool True if breadcrumbs are hidden, false otherwise.
 */
function genesis_breadcrumbs_hidden_on_current_page() {

	// No “hide breadcrumbs” option is currently offered on non-singular page types, such as category archives.
	if ( ! is_singular() && ! is_home() ) {
		return false;
	}

	/**
	 * Prevents the “hide breadcrumbs” checkbox from appearing or functioning by returning false.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $breadcrumbs_toggle_enabled True if breadcrumbs toggle is enabled, false otherwise.
	 */
	$breadcrumbs_toggle_enabled = apply_filters( 'genesis_breadcrumbs_toggle_enabled', true );

	if ( ! $breadcrumbs_toggle_enabled ) {
		return false;
	}

	return get_post_meta( get_queried_object_id(), '_genesis_hide_breadcrumbs', true );

}

/**
 * Are breadcrumbs disabled for the current page type?
 *
 * @since 3.1.1
 *
 * @return bool True if breadcrumbs are disabled, false otherwise.
 */
function genesis_breadcrumbs_disabled_on_current_page() {
	return ( is_single() && ! genesis_get_option( 'breadcrumb_single' ) )
		|| ( is_page() && ! genesis_get_option( 'breadcrumb_page' ) && ! is_front_page() )
		|| ( is_404() && ! genesis_get_option( 'breadcrumb_404' ) )
		|| ( is_attachment() && ! genesis_get_option( 'breadcrumb_attachment' ) )
		|| ( ( 'posts' === get_option( 'show_on_front' ) && is_home() ) && ! genesis_get_option( 'breadcrumb_home' ) )
		|| ( ( 'page' === get_option( 'show_on_front' ) && is_front_page() ) && ! genesis_get_option( 'breadcrumb_front_page' ) )
		|| ( ( 'page' === get_option( 'show_on_front' ) && is_home() ) && ! genesis_get_option( 'breadcrumb_posts_page' ) )
		|| ( ( is_archive() || is_search() ) && ! genesis_get_option( 'breadcrumb_archive' ) );
}

add_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
/**
 * Display Breadcrumbs above the Loop. Concedes priority to popular breadcrumb
 * plugins.
 *
 * @since 1.0.0
 *
 * @return void Return early if Genesis settings dictate that no breadcrumbs should show in current context.
 */
function genesis_do_breadcrumbs() {

	/**
	 * Do not output breadcrumbs if filter returns true.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $breadcrumbs_hidden True to hide breadcrumbs, false to show them.
	 */
	$genesis_breadcrumbs_hidden = apply_filters( 'genesis_do_breadcrumbs', genesis_breadcrumbs_hidden_on_current_page() );

	if ( $genesis_breadcrumbs_hidden ) {
		return;
	}

	if ( genesis_breadcrumbs_disabled_on_current_page() ) {
		return;
	}

	$config = genesis_get_config( 'breadcrumbs' );

	if ( function_exists( 'bcn_display' ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $config['prefix'];
		bcn_display();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $config['suffix'];
	} elseif ( class_exists( 'WPSEO_Breadcrumbs' ) && genesis_get_option( 'breadcrumbs-enable', 'wpseo_titles' ) ) {
		yoast_breadcrumb( $config['prefix'], $config['suffix'] );
	} elseif ( function_exists( 'yoast_breadcrumb' ) && ! class_exists( 'WPSEO_Breadcrumbs' ) ) {
		yoast_breadcrumb( $config['prefix'], $config['suffix'] );
	} else {
		genesis_breadcrumb( $config );
	}

}

/**
 * Gets breadcrumb options that are enabled in Genesis settings.
 *
 * @since 3.1.0
 *
 * @return array The breadcrumb options that are enabled.
 */
function genesis_breadcrumb_options_enabled() {
	$genesis_options = get_option( GENESIS_SETTINGS_FIELD );

	$active_breadcrumb_types = array_filter(
		$genesis_options,
		function ( $value, $option_name ) {
			return strpos( $option_name, 'breadcrumb_' ) === 0 && $value;
		},
		ARRAY_FILTER_USE_BOTH
	);

	return array_keys( $active_breadcrumb_types );
}
