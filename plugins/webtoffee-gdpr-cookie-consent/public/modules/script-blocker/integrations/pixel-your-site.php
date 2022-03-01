<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_pixel_your_site_script' );
function wt_cli_pixel_your_site_script( $scripts ) {

	$scripts['pixel-your-site'] = array(
		'label'     => __( 'PixelYourSite', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => array(
			'wp-content/plugins/pixelyoursite-pro/dist/scripts',
			'wp-content/plugins/pixelyoursite/dist/scripts',
		),
		'js_needle' => array( 'pysOptions' ),
		'cc'        => 'analytical',
	);
	return $scripts;
}
