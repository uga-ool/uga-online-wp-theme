<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_google_analytics_wordpress_script' );
function wt_cli_google_analytics_wordpress_script( $tags ) {
	$tags['google-analytics-for-wordpress'] = array(
		'label'     => __( 'MonsterInsights', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
						'wp-content/plugins/google-analytics-for-wordpress/assets/js/frontend-gtag.min.js',
						'wp-content/plugins/google-analytics-for-wordpress/assets/js/frontend-gtag.js',
						'www.google-analytics.com/analytics.js',
					),
		'js_needle' => array('mi_track_user'),
		'cc'        => 'analytical',
	);
	return $tags;
}
