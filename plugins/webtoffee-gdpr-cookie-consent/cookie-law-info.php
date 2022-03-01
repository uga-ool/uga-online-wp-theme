<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * 
 *
 * @wordpress-plugin
 * Plugin Name:       GDPR Cookie Consent
 * Plugin URI:        https://www.webtoffee.com/product/gdpr-cookie-consent/
 * Description:       A simple way to show your website complies with the EU Cookie Law / GDPR.
 * Version:           2.3.7
 * Author:            WebToffee
 * Author URI:        http://cookielawinfo.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       webtoffee-gdpr-cookie-consent
 * Domain Path:       /languages
 */

/*	
    Copyright 2018  WebToffee

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
$wt_cli_wp_lanugage = get_option('WPLANG', 'en_US');
if ( empty($wt_cli_wp_lanugage) || strlen($wt_cli_wp_lanugage) <= 1) {
    $wt_cli_wp_lanugage = 'en';
}
define ( 'CLI_PLUGIN_DEVELOPMENT_MODE', false );
define ( 'CLI_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define ( 'CLI_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define ( 'CLI_PLUGIN_URL', plugin_dir_url(__FILE__));
define ( 'CLI_DB_KEY_PREFIX', 'CookieLawInfo-' );
define ( 'CLI_LATEST_VERSION_NUMBER', '0.9' );
define ( 'CLI_SETTINGS_FIELD', CLI_DB_KEY_PREFIX . CLI_LATEST_VERSION_NUMBER );
define ( 'CLI_MIGRATED_VERSION', CLI_DB_KEY_PREFIX . 'MigratedVersion' );
// Previous version settings (depreciated from 0.9 onwards):
define ( 'CLI_ADMIN_OPTIONS_NAME', 'CookieLawInfo-0.8.3' );
define ( 'CLI_PLUGIN_FILENAME',__FILE__);
define ( 'CLI_POST_TYPE','cookielawinfo');
define ( 'CLI_ACTIVATION_ID','wtgdprcookieconsent');
define  ('CLI_DEFAULT_LANGUAGE', substr($wt_cli_wp_lanugage, 0, 2));


/**
 * Currently plugin version.
 * 
 */

define( 'CLI_VERSION', '2.3.7' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'cookie-law-info/cookie-law-info.php' ) ){
    
    deactivate_plugins(basename(__FILE__));
    wp_die(__("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete GDPR Cookie Consent (BASIC) and then try again", "webtoffee-gdpr-cookie-consent"), "", array('back_link' => 1));

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cookie-law-info-activator.php
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-cookie-law-info-activator.php';
function activate_wt_cookie_law_info() {
    Cookie_Law_Info_Activator::activate();
    set_transient( 'cli-admin-notice-activation', true, 5 );
}
add_action( 'admin_notices', 'cli_script_blocker_activation_notice' );
/*
* @since 2.2.0
* Display custom admin notices on plugin activation
*/
function cli_script_blocker_activation_notice(){
    if( get_transient( 'cli-admin-notice-activation' ) ){
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e('The pre-defined script blocker services are enabled by default. Hence activating the plugin blocks these services which may affect the web layout. For e.g, if you have embedded any youtube or Vimeo services the plugin will block these by default since they collect user information. Hence the videos will not render wherever it is placed. Therefore you may want to review the website w.r.t the blocked scripts. <a href="edit.php?post_type='.CLI_POST_TYPE.'&page=cli-script-settings">View Script blocker.</a>', 'webtoffee-gdpr-cookie-consent' ); ?></p>
    </div>
    <?php
    delete_transient( 'cli-admin-notice-activation' );
    }
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cookie-law-info-deactivator.php
 */
function deactivate_wt_cookie_law_info() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cookie-law-info-deactivator.php';
	Cookie_Law_Info_Deactivator::deactivate();
}
register_activation_hook( __FILE__,'activate_wt_cookie_law_info');
register_deactivation_hook( __FILE__,'deactivate_wt_cookie_law_info');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cookie-law-info.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.1.3
 */
function run_wt_cookie_law_info() 
{
	$plugin = new Cookie_Law_Info();
	$plugin->run();
}
run_wt_cookie_law_info();