<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_youtube_feed_script' );
function wt_cli_youtube_feed_script( $scripts ) {

	$scripts['youtube-feed'] = array(
		'label'     => __( 'Feeds for YouTube', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
						'wp-content/plugins/feeds-for-youtube/js/sb-youtube.js',
						'wp-content/plugins/feeds-for-youtube/js/sb-youtube.min.js',
						'wp-content/plugins/youtube-feed-pro/js/sb-youtube.js',
						'wp-content/plugins/youtube-feed-pro/js/sb-youtube.min.js',
					),
		'js_needle' => array('sby_init'),
		'cc'        => 'analytical',
	);
	return $scripts;
}