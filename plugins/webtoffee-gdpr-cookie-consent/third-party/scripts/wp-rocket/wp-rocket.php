<?php
/**
 * Compatibility with WP Rocket
 * @link https://wp-rocket.me/
 * @since 2.1.6
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Cookie_Law_Info_Wp_Rocket
{
	public function __construct()
    {   	
		/* adding filters for dynamic cookies */
		//activation
    	register_activation_hook(CLI_PLUGIN_FILENAME,array(__CLASS__,'activator'));
    	add_action('activate_wp-rocket/wp-rocket.php',array(__CLASS__,'activator'),11);

    	//deactivation
    	register_deactivation_hook(CLI_PLUGIN_FILENAME,array(__CLASS__,'deactivator'));
    	add_action('deactivate_wp-rocket/wp-rocket.php',array(__CLASS__,'deactivator'),9);
    }

    /**
	 * Add WP Rocket dynamic cookies on activation
	 * @since 2.1.6
	 */
    public static function activator()
    {	
		if( Cookie_Law_Info::maybe_first_time_install() === true ) {
			update_option('wt_cli_wp_rocket_cache_status', 'false');
		}
		if( self::get_wp_rocket_cache_status() === true ) {
			// Create cache version based on value set in cookie_notice_accepted cookie
			add_filter('rocket_cache_dynamic_cookies',array('Cookie_Law_Info','get_user_preference_cookie'));
			add_filter('rocket_cache_mandatory_cookies',array('Cookie_Law_Info','get_user_preference_cookie'));
			
			//update rocket data
			self::flush_rocket_data();
		}
    	
    }

    /**
	 * Remove WP Rocket dynamic cookies on deactivation
	 * @since 2.1.6
	 */
    public static function deactivator()
    {	
		if( self::get_wp_rocket_cache_status() === true ) {
			// Create cache version based on value set in cookie_notice_accepted cookie
			remove_filter('rocket_cache_dynamic_cookies',array('Cookie_Law_Info','get_user_preference_cookie'));
			remove_filter('rocket_cache_mandatory_cookies',array('Cookie_Law_Info','get_user_preference_cookie'));
			

			//update rocket data
			self::flush_rocket_data();
		}
    	
    }

    /**
	 * Regenerate WP Rocket config datas
	 * @since 2.1.6
	 */
    public static function flush_rocket_data()
    {
    	// Update the WP Rocket rules on the .htaccess file.
		if(function_exists('flush_rocket_htaccess')){
			flush_rocket_htaccess();
		}

		// Regenerate the config file.
		if(function_exists('rocket_generate_config_file')){
			rocket_generate_config_file();
		}
		
		// Clear WP Rocket cache
		if(function_exists('rocket_clean_domain')){
			rocket_clean_domain();
		}
	}
	public static function get_wp_rocket_cache_status() {
		$status = get_option('wt_cli_wp_rocket_cache_status','true');
		$return = false;
		if( isset( $status ) && $status === 'true' ) {
			$return =  true;
		}
		return apply_filters('wt_cli_wp_rocket_cache_status',$return);
	}
}
new Cookie_Law_Info_Wp_Rocket();