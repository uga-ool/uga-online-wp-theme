<?php

/**
 * Fired during plugin activation
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.3
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.1.3
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Cookie_Law_Info_Activator {
	/**
	 * Database update callbacks
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'2.3.3' => array(
			'wt_cli_update_233_db_version',
		),
	);
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.1.3
	 */
	public static function activate() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		Cookie_Law_Info::check_for_upgrade();

		if ( is_multisite() ) {
			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::install_tables();
				Cookie_Law_Info::cli_patches();
				restore_current_blog();
			}
		} else {
			self::install_tables();
			Cookie_Law_Info::cli_patches();
			Cookie_Law_Info::wt_cli_init_consent_version();
		}
	}
	public static function install_tables() {
		global $wpdb;
		// install necessary tables
	}
	public static function needs_db_update() {

		$current_db_version = get_option( 'wt_cli_pro_db_version', Cookie_Law_Info::$db_initial_version ); // @since 1.9.6 introduced DB migrations
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	public static function update_db_version( $version = null ) {
		update_option( 'wt_cli_pro_db_version', is_null( $version ) ? CLI_VERSION : $version );
	}

	private static function maybe_update_db_version() {

		if ( self::needs_db_update() ) {
			self::update();
		} else {
			self::update_db_version(); // First time install
		}
	}

	private static function update() {
		$current_db_version = get_option( 'wt_cli_pro_db_version', Cookie_Law_Info::$db_initial_version );

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {

			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					self::$update_callback();
				}
			}
		}
	}

	private static function wt_cli_update_233_db_version() {

		$cookie_db_version = self::get_cookie_db_version();
		if ( Cookie_Law_Info::maybe_first_time_install() === true || false !== $cookie_db_version  ) {
			self::update_cookie_db_version( '2.0' );
		} else {
			self::update_cookie_db_version( '1.0' );
		}
		self::update_db_version();
	}
	/**
	 * Check the plugin version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'wt_cli_pro_version', '2.3.3' ), CLI_VERSION, '<' ) ) {
			self::install();
		}
	}
	private static function update_plugin_version() {
		update_option( 'wt_cli_pro_version', CLI_VERSION );
	}
	public static function install() {
		self::maybe_update_db_version();
		self::update_plugin_version();
	}
	public static function update_cookie_db_version( $version = null ) {
		update_option( 'wt_cli_cookie_db_version', is_null( $version ) ? '1.0' : $version );
	}
	public static function get_cookie_db_version() {
		return get_option( 'wt_cli_cookie_db_version', false );
	}
}
Cookie_Law_Info_Activator::init();
