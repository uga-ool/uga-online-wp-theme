<?php
/**
 * Compatibility with PixelYourSite
 *
 * @link http://www.pixelyoursite.com/
 * @since 2.1.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Cookie_Law_Info_PixelYourSite {

	public function __construct() {
		
		if ( $this->is_plugin_active() ) {
			if ( ! $this->add_main_filter() ) {
				// checks script blocker is available.
				if ( Cookie_Law_Info_Public::module_exists( 'script-blocker' ) ) {
					$script_list = apply_filters('wt_cli_script_blocker_scripts',array());

					$default_scripts = ( isset( $script_list['default'] ) && is_array( $script_list['default'] ) ) ? $script_list['default'] : array();
					$plugin_scripts  = ( isset( $script_list['plugins'] ) && is_array( $script_list['plugins'] ) ) ? $script_list['plugins'] : array();

					$script_list = $default_scripts + $plugin_scripts;
				
					if ( ! empty( $script_list ) ) {
						foreach ( $script_list as $key => $script ) {
							$scriptkey       = $key;
							$category_cookie = 'cookielawinfo-checkbox-' . $script['category'];
							// user is disabled the checkbox.
							if ( ! isset( $_COOKIE[ $category_cookie ] ) || ( isset( $_COOKIE[ $category_cookie ] ) && $_COOKIE[ $category_cookie ] == 'no' ) ) {
								if ( $scriptkey == 'facebook_pixel' ) {
									// block fb pixel.
									add_filter( 'pys_disable_facebook_by_gdpr', '__return_true', 10, 2 );
								} elseif ( $scriptkey == 'googleanalytics' ) {
									// block google analytics.
									add_filter( 'pys_disable_analytics_by_gdpr', '__return_true', 10, 2 );
								} elseif ( $scriptkey == 'google_publisher_tag' ) {
									// block google ads.
									add_filter( 'pys_disable_google_ads_by_gdpr', '__return_true', 10, 2 );
								} elseif ( $scriptkey == 'pinterest' ) {
									// block pinterest.
									add_filter( 'pys_disable_pinterest_by_gdpr', '__return_true', 10, 2 );
								}
							}
						}
					}
				}
			}
		}
	}

	/*
	*
	* Add main filter based on GDPR main cookie (accept/reject)
	* @since 2.1.8
	*/
	private function add_main_filter() {
		$viewed_cookie = 'viewed_cookie_policy';
		$out_fn        = '__return_true'; // block it
		$out           = true;
		if ( isset( $_COOKIE[ $viewed_cookie ] ) ) {
			if ( $_COOKIE[ $viewed_cookie ] == 'yes' ) {
				$out_fn = '__return_false'; // remove blocking
				$out    = false;
			}
		}
		add_filter( 'pys_disable_by_gdpr', $out_fn, 10, 2 );
		return $out;
	}


	/*
	*
	* Checks PixelYourSite plugin is active
	* @since 2.1.8
	*/
	private function is_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' ) || is_plugin_active( 'pixelyoursite/facebook-pixel-master.php' );
	}
}
new Cookie_Law_Info_PixelYourSite();
