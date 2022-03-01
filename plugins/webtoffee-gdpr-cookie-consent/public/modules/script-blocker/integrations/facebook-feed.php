<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_facebook_feed_script' );
function wt_cli_facebook_feed_script( $scripts ) {

	$scripts['facebook-feed'] = array(
		'label'     => __( 'Smash Balloon Custom Facebook Feed', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
						'wp-content/plugins/custom-facebook-feed/js/cff-scripts.js',
						'wp-content/plugins/custom-facebook-feed/js/cff-scripts.min.js',
						'wp-content/plugins/custom-facebook-feed-pro/js/cff-scripts.js',
						'wp-content/plugins/custom-facebook-feed-pro/js/cff-scripts.min.js',
					),
		'js_needle' => array('cff_init'),
		'cc'        => 'analytical',
	);
	return $scripts;
}