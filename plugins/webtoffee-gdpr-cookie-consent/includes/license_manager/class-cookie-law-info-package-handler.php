<?php

/**
 *
 * Cookieyes Integration
 *
 * @version 2.3.2
 * @package CookieLawInfo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cookie_Law_Info_License_Handler' ) ) {
	require_once 'class-cookie-law-info-license-handler.php';
}
if ( ! class_exists( 'Cookie_Law_Info_Package_Handler' ) ) {

	/**
	 * Cookieyes License handler
	 */
	class Cookie_Law_Info_Package_Handler extends Cookie_Law_Info_License_Handler {

		/**
		 * Constructor
		 */
		private static $instance;

		public function __construct() {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
			add_filter( 'plugins_api', array( $this, 'get_product_info' ), 10, 3 );
		}
		/**
		 * Init hook callback
		 *
		 * @return void
		 */
		public function init() {

		}
		/**
		 * Returns the current instance
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		/**
		 * Fetch product info from the Server
		 *
		 * @return array
		 */
		public function get_product_info( $false, $action, $args ) {

			if ( isset( $args->slug ) ) {
				$current_plugin_slug = $args->slug;
				if ( strpos( $current_plugin_slug, '.php' ) !== false ) {
					$current_plugin_slug = dirname( $current_plugin_slug );
				}
				if ( $current_plugin_slug === $this->get_product_dir_name() ) {
					$license_version = $this->get_license_version();
					if ( version_compare( $license_version, '2.0', '>=' ) ) {
						$response = $this->edd_get_product_info();
					} else {
						$response = $this->api_manager_get_product_info();
					}
					if ( isset( $response ) && is_object( $response ) && $response !== false ) {
						return $response;
					}
				}
			}
			return $false;
		}
		public function check_for_update( $transient ) {

			$this->flush_errors(); // Remove exising errors from transient.
			if ( false === $this->check_if_license_activated() ) {
				return $transient;
			}
			$last_check = get_option( $this->get_product_name() . '-last-update-check' );
			if ( $last_check == false ) { // first time
				$last_check = time() - 14402;
				update_option( $this->get_product_name() . '-last-update-check', $last_check );
			}
			if ( time() - $last_check > 14400 || ( isset( $_GET['force-check'] ) && $_GET['force-check'] == 1 ) ) {

				$license_version = $this->get_license_version();
				$plugin_slug     = $this->get_product_slug();

				if ( version_compare( $license_version, '2.0', '>=' ) ) {
					$response = $this->edd_check_for_update();
				} else {
					$response = $this->api_manager_check_for_update();
				}
				if ( isset( $response ) && is_object( $response ) && $response !== false ) {
					$new_version     = sanitize_text_field( $response->new_version );
					$current_version = $this->get_product_version();
					if ( isset( $new_version ) && isset( $current_version ) ) {

						if ( $response !== false && version_compare( $new_version, $current_version, '>' ) ) {
							$transient->response[ $plugin_slug ] = $response;
						}
					}
				}
				update_option( $this->product_id . '-last-update-check', time() );
			}
			return $transient;
		}
		public function edd_check_for_update() {
			$license_status = $this->edd_check_license();
			if ( true === $license_status['status'] ) {
				$license      = $this->get_license_data();
				$request_body = array(
					'edd_action' => 'get_version',
					'item_id'    => $this->get_product_id(),
					'license'    => $license['licence_key'],
					'url'        => $this->get_domain(),
				);
				$endpoint     = $this->build_url( $request_body, true );
				$response     = $this->wt_remote_request( 'GET', $endpoint, false, false, true );
				if ( isset( $response->download_link ) ) {
					$response->version       = isset( $response->new_version ) ? $response->new_version : 0;
					$response->slug          = $this->get_product_slug();
					$response->download_link = isset( $response->download_link ) ? $response->download_link : '';
					$response->sections      = isset( $response->sections ) ? maybe_unserialize( $response->sections ) : array();
					$response->banners       = isset( $response->banners ) ? maybe_unserialize( $response->banners ) : array();
					$response->icons         = isset( $response->icons ) ? maybe_unserialize( $response->icons ) : array();
				}
				return $response;
			}
			return false;

		}
		public function api_manager_check_for_update() {

			$license      = $this->get_license_data();
			$request_args = array(
				'wc-api'           => 'upgrade-api',
				'request'          => 'pluginupdatecheck',
				'slug'             => $this->get_product_dir_name(),
				'plugin_name'      => $this->get_product_slug(),
				'version'          => $this->get_product_version(),
				'product_id'       => $this->get_product_name(),
				'api_key'          => $license['licence_key'],
				'activation_email' => $license['licence_email'],
				'instance'         => $license['instance_id'],
				'domain'           => $this->get_domain(),
				'software_version' => $this->get_product_version(),
			);
			$endpoint     = $this->build_url( $request_args, true );
			$response     = $this->wt_remote_request( 'GET', $endpoint, false, false, true );
			if ( isset( $response->errors ) ) {
				$error_message = $this->get_api_manager_error_messages( $response->errors );
				$this->set_error_message( $error_message );
				$this->check_for_possible_deactivation( $response->errors );
				return false;
			}
			return $response;
		}
		public function edd_get_product_info() {
			return $this->edd_check_for_update();
		}
		public function api_manager_get_product_info() {
			$license      = $this->get_license_data();
			$request_args = array(
				'wc-api'           => 'upgrade-api',
				'request'          => 'plugininformation',
				'plugin_name'      => $this->get_product_slug(),
				'version'          => $this->get_product_version(),
				'product_id'       => $this->get_product_name(),
				'api_key'          => $license['licence_key'],
				'activation_email' => $license['licence_email'],
				'instance'         => $license['instance_id'],
				'domain'           => $this->get_domain(),
				'software_version' => $this->get_product_version(),
			);
			$endpoint     = $this->build_url( $request_args, true );
			$response     = $this->wt_remote_request( 'GET', $endpoint, false, false, true );
			return $response;
		}
		public function edd_check_license() {
			$api_response = $this->get_default_response();
			$license      = $this->get_license_data();
			$request_body = array(
				'edd_action' => 'check_license',
				'item_id'    => $this->get_product_id(),
				'license'    => $license['licence_key'],
				'url'        => $this->get_domain(),
			);
			$endpoint     = $this->build_url( $request_body, true );
			$response     = $this->wt_remote_request( 'GET', $endpoint );
			if ( ( isset( $response['success'] ) && true === $response['success'] ) && ( isset( $response['license'] ) && 'valid' === $response['license'] )) {
				$api_response['status'] = true;
			} else {
				if ( isset( $response['license'] ) && 'valid' !== $response['license'] ) { // Exact license status.
					$error_message = $this->get_edd_error_messages( $response['license'] );
					$this->set_error_message( $error_message );
					$this->check_for_possible_deactivation( $response['license'] );
				}
			}
			return $api_response;
		}
		public function show_admin_notices() {
			add_action(
				'admin_notices',
				function() {
					if ( $this->get_last_error_message() && ! empty( $this->get_last_error_message() ) ) {
						$notice = '<div id="message" class="error"><p>' . $this->get_last_error_message() . '</p></div>';
						echo $notice;
					}
				}
			);
		}
	}
	Cookie_Law_Info_Package_Handler::get_instance();
}
