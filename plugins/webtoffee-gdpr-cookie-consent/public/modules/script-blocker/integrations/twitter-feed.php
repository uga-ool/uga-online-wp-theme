<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_twitter_feed_script' );
function wt_cli_twitter_feed_script( $scripts ) {

	$scripts['instagram-feed'] = array(
		'label'     => __( 'Custom Twitter Feeds', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
						'wp-content/plugins/custom-twitter-feeds/js/ctf-scripts.js',
						'wp-content/plugins/custom-twitter-feeds/js/ctf-scripts.min.js',
						'wp-content/plugins/custom-twitter-feeds-pro/js/ctf-scripts.js',
						'wp-content/plugins/custom-twitter-feeds-pro/js/ctf-scripts.min.js',
					),
		'js_needle' => array('ctf_init'),
		'cc'        => 'analytical',
	);
	return $scripts;
}