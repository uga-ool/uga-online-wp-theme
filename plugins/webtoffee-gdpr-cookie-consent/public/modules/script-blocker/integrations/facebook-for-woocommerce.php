<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_facebook_woo_script' );
function wt_cli_facebook_woo_script( $scripts ) {
	$scripts['facebook-for-woocommerce'] = array(
		'label'     => __( 'Facebook for Woocommerce', 'webtoffee-gdpr-cookie-consent' ),
		'js_needle' => array('fbq','connect.facebook.net'),
		'cc'        => 'analytical',
	);
	return $scripts;
}
