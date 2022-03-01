<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
add_filter( 'wt_cli_third_party_scripts', 'wt_cli_facebook_wordpress_script' );
function wt_cli_facebook_wordpress_script( $scripts ) {
	$scripts['facebook-for-wordpress'] = array(
		'label'     => __( 'Facebook for Wordpress', 'webtoffee-gdpr-cookie-consent' ),
		'js'        => 'fbq',
		'js_needle' => array('fbq'),
		'cc'        => 'analytical',
	);
	return $scripts;
}
