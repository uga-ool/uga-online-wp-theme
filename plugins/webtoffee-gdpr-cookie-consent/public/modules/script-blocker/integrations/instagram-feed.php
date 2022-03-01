<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_instagram_feed_script' );
function wt_cli_instagram_feed_script( $scripts ) {

	$scripts['instagram-feed'] = array(
		'label'     => __( 'Smash Balloon Instagram Feed', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
						'wp-content/plugins/instagram-feed/js/sb-instagram.js',
						'wp-content/plugins/instagram-feed/js/sb-instagram.min.js',
						'wp-content/plugins/instagram-feed/js/sbi-scripts.js',
						'wp-content/plugins/instagram-feed/js/sbi-scripts.min.js',
						'wp-content/plugins/instagram-feed-pro/js/sb-instagram.js',
						'wp-content/plugins/instagram-feed-pro/js/sb-instagram.min.js',
						'wp-content/plugins/instagram-feed-pro/js/sbi-scripts.js',
						'wp-content/plugins/instagram-feed-pro/js/sbi-scripts.min.js',
					),
		'js_needle' => array('sbi_init'),
		'cc'        => 'analytical',
	);
	return $scripts;
}