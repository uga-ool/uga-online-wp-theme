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

	/**
	 * Cookieyes License handler
	 */
	class Cookie_Law_Info_License_Handler extends Cookie_Law_Info_Remote_Request {

		/**
		 * Current license version
		 *
		 * @var int
		 */
		protected $license_version;
		protected $product_name;
		protected $product_slug;
		protected $product_id;
		protected $plugin_settings_url;
		protected $product_version;
		protected $domain;
		protected $license;
		protected $product_abs_name;
		public $last_error_message;

		private static $license_initial_version = '1.0';
		private static $instance;

		const API_BASE_URI = 'https://www.webtoffee.com/';
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->set_plugin_fields();
			add_action( 'wt_cli_license_section', array( $this, 'license_handler' ) );
			add_action( 'wp_ajax_wf_activate_license_keys_' . $this->get_product_name(), array( $this, 'license_activation_handler' ) );
			add_action( 'wp_ajax_wf_deactivate_license_keys_' . $this->get_product_name(), array( $this, 'license_deactivation_handler' ) );
			add_action( 'after_plugin_row_' . $this->get_product_slug(), array( $this, 'add_license_notification' ), 10, 2 );
			add_action( 'admin_footer', array( $this, 'add_inline_script' ) );
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
		 * Set plugin fields
		 *
		 * @return void
		 */
		public function set_plugin_fields() {
		}
		/**
		 * Return product slug
		 *
		 * @return string
		 */
		public function get_product_slug() {
			if ( ! $this->product_slug ) {
				$this->product_slug = CLI_PLUGIN_BASENAME;
			}
			return $this->product_slug;
		}
		/**
		 * Return product name
		 *
		 * @return string
		 */
		public function get_product_name() {
			if ( ! $this->product_name ) {
				$this->product_name = 'wtgdprcookieconsent';
			}
			return $this->product_name;
		}
		/**
		 * Return product id
		 *
		 * @return string
		 */
		public function get_product_id() {
			if ( ! $this->product_id ) {
				$this->product_id = '196737';
			}
			return $this->product_id;
		}
		/**
		 * Returns product directory name
		 *
		 * @return string
		 */
		public function get_product_dir_name() {
			$plugin_dir_name = $this->get_product_slug();
			if ( strpos( $plugin_dir_name, '.php' ) !== 0 ) {
				$plugin_dir_name = dirname( $plugin_dir_name );
			}
			return $plugin_dir_name;
		}
		/**
		 * Return product slug
		 *
		 * @return string
		 */
		public function get_product_version() {
			if ( ! $this->product_version ) {
				$this->product_version = CLI_VERSION;
			}
			return $this->product_version;
		}
		/**
		 * Returns the real plugin name
		 *
		 * @return string
		 */
		public function get_product_abs_name() {
			if ( ! $this->product_abs_name ) {
				$this->product_abs_name = __( 'GDPR Cookie Consent Pro' );
			}
			return $this->product_abs_name;
		}
		public function get_plugin_settings_url() {
			if ( ! $this->plugin_settings_url ) {
				$this->plugin_settings_url = admin_url( 'edit.php?post_type=' . CLI_POST_TYPE. '&page=cookie-law-info#cookie-law-info-licence' );
			}
			return $this->plugin_settings_url;
		}
		/**
		 * Return product slug
		 *
		 * @return string
		 */
		public function get_domain() {
			if ( ! $this->domain ) {
				$this->domain = home_url();
			}
			return $this->domain;
		}
		public function get_last_error_message() {
			if ( ! $this->last_error_message ) {
				$error = get_transient( $this->get_product_name() . 'license_last_error' );
				if ( false !== $error ) {
					$error = wp_kses_post( $error );
				}
				$this->last_error_message = $error;
			}
			return $this->last_error_message;
		}
		public function set_error_message( $message ) {
			if ( false === $message ) {
				delete_transient( $this->get_product_name() . 'license_last_error' );
				return;
			}
			$message = wp_kses_post( $message );
			set_transient( $this->get_product_name() . 'license_last_error', $message, 12 * HOUR_IN_SECONDS );
			$this->last_error_message = $message;
		}
		public function flush_errors() {
			$this->set_error_message( false );
		}
		/**
		 * Returns the license version
		 *
		 * @return void
		 */
		public function check_license_version() {

		}
		public function license_handler() {
			$license     = $this->get_license_data();
			$plugin_name = $this->get_product_name();
			include 'html/html-wf-activation-window.php';
		}
		public function get_base_path() {
			return self::API_BASE_URI;
		}
		public function get_license_data() {

			$license   = array(
				'status'        => false,
				'licence_key'   => '',
				'instance_id'   => '',
				'licence_email' => '',
			);
			$plugin_id = $this->get_product_name();
			if ( ! empty( $plugin_id ) ) {

				$license['status']        = sanitize_text_field( get_option( $plugin_id . '_' . 'activation_status', '' ) );
				$license['licence_key']   = sanitize_text_field( get_option( $plugin_id . '_' . 'licence_key', '' ) );
				$license['instance_id']   = sanitize_text_field( get_option( $plugin_id . '_' . 'instance_id', '' ) );
				$license['licence_email'] = sanitize_text_field( get_option( $plugin_id . '_' . 'email', '' ) );
			}
			return $license;
		}
		public function set_license_data( $license ) {
			if ( ! empty( $license ) ) {
				$plugin_id = $this->get_product_name();
				if ( ! empty( $plugin_id ) ) {
					$license_status = '';
					if ( isset( $license['status'] ) && ! empty( $license['status'] ) ) {
						$license_status = 'inactive';
						if ( true === $license['status'] ) {
							$license_status = 'active';
						}
					}
					$license_data = array(
						$plugin_id . '_' . 'activation_status' => $license_status,
						$plugin_id . '_' . 'licence_key' => isset( $license['licence_key'] ) ? sanitize_text_field( $license['licence_key'] ) : '',
						$plugin_id . '_' . 'instance_id' => isset( $license['instance_id'] ) ? sanitize_text_field( $license['instance_id'] ) : '',
						$plugin_id . '_' . 'email'       => isset( $license['licence_email'] ) ? sanitize_text_field( $license['licence_email'] ) : '',
					);
					foreach ( $license_data as $key => $data ) {
						update_option( $key, $data );
					}
				}
			}
		}
		public function reset_license_data() {
			$license_data = array(
				'status'        => '',
				'licence_key'   => '',
				'instance_id'   => '',
				'licence_email' => '',
			);
			$this->set_license_data( $license_data );
		}
		public function set_license_inactive() {
			$license = $this->get_license_data();
			if ( isset( $license['status'] ) && 'active' === $license['status'] ) {
				$license['status'] = false;
				$this->set_license_data( $license );
			}
		}
		/**
		 * Returns the license version
		 *
		 * @return int
		 */
		public function get_license_version( $license = false ) {
			$plugin_id       = $this->get_product_name();
			$license_version = self::$license_initial_version;
			if ( $license ) {
				$this->license_version = $license_version;
				if ( false === strpos( $license, 'wc_order' ) ) {
					$this->license_version = '2.0';
				}
			} else {
				if ( ! $this->license_version ) {
					$license_version       = get_option( $plugin_id . '_' . 'license_version', '1.0' );
					$this->license_version = sanitize_text_field( $license_version );
				}
			}
			return $this->license_version;
		}
		/**
		 * Set license version
		 *
		 * @param string $version current version of the license
		 * @return void
		 */
		public function set_license_version( $version ) {
			$plugin_id = $this->get_product_name();
			if ( false === $version ) {
				delete_option( $plugin_id . '_' . 'license_version' );
			} else {
				$version = sanitize_text_field( $version );
				update_option( $plugin_id . '_' . 'license_version', $version );
			}

		}
		public function license_activation_handler() {

			$api_key = isset( $_GET['licence_key'] ) ? sanitize_text_field( $_GET['licence_key'] ) : '';
			$email   = isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '';

			$license_version         = $this->get_license_version( $api_key );
			$api_response            = array();
			$api_response['message'] = __( 'Request failed, Please try again', 'webtoffee-gdpr-cookie-consent' );
			$args                    = array(
				'email'       => $email,
				'licence_key' => $api_key,
			);

			if ( version_compare( $license_version, '2.0', '>=' ) ) {
				$response = $this->edd_activate_license( $args );
			} else {
				$response = $this->api_manager_activate_license( $args );
			}
			if ( false === $response ) {
				wp_send_json_error( $api_response );
			} elseif ( isset( $response['status'] ) ) {
				if ( isset( $response['message'] ) ) {
					$api_response['message'] = $response['message'];
				}
				if ( true === $response['status'] ) {
					$license_data = array(
						'status'        => true,
						'licence_key'   => $api_key,
						'instance_id'   => isset( $response['instance'] ) ? $response['instance'] : '',
						'licence_email' => $email,
					);
					$this->set_license_data( $license_data );
					$api_response['message'] = __( 'License activation successfull', 'webtoffee-gdpr-cookie-consent' );
					wp_send_json_success( $api_response );
				}
			}
			wp_send_json_error( $api_response );

		}
		public function license_deactivation_handler() {

			$license_data            = $this->get_license_data();
			$license_version         = $this->get_license_version();
			$api_response            = array();
			$api_response['message'] = __( 'Request failed, Please try again', 'webtoffee-gdpr-cookie-consent' );
			$args                    = array(
				'email'       => $license_data['licence_email'],
				'licence_key' => $license_data['licence_key'],
				'instance_id' => $license_data['instance_id'],
			);
			if ( version_compare( $license_version, '2.0', '>=' ) ) {
				$response = $this->edd_deactivate_license( $args );
			} else {
				$response = $this->api_manager_deactivate_license( $args );
			}
			if ( false === $response ) {
				wp_send_json_error( $api_response );
			} elseif ( isset( $response['status'] ) ) {
				if ( isset( $response['message'] ) ) {
					$api_response['message'] = $response['message'];
				}
				if ( true === $response['status'] ) {
					$this->reset_license_data();
					$api_response['message'] = __( 'License deactivation successfull', 'webtoffee-gdpr-cookie-consent' );
					wp_send_json_success( $api_response );
				}
			}
			wp_send_json_error( $api_response );

		}
		/**
		 * License activation API for CookieYes
		 *
		 * @param array $request the request data.
		 * @return array
		 */
		public function edd_activate_license( $request ) {

			$api_response = $this->get_default_response();
			$request_body = array(
				'edd_action' => 'activate_license',
				'item_id'    => $this->get_product_id(),
				'license'    => isset( $request['licence_key'] ) ? $request['licence_key'] : '',
				'url'        => $this->get_domain(),
			);
			$endpoint     = $this->build_url( $request_body, true );
			$response     = $this->wt_remote_request( 'GET', $endpoint );

			if ( isset( $response ) && is_array( $response ) ) {
				if ( isset( $response['success'] ) && true === $response['success'] ) {
					$this->set_license_version( '2.0' );
					$api_response['status'] = true;
				} else {
					if ( isset( $response['error'] ) ) {
						$api_response['message'] = $this->get_edd_error_messages( $response['error'] );
					}
				}
			}
			return $api_response;
		}
		public function get_edd_errors() {
			$errors = array(
				'expired',
				'revoked',
				'missing',
				'site_inactive',
				'invalid',
				'item_name_mismatch',
				'inactive',
			);
			return $errors;
		}
		public function get_edd_error_messages( $error ) {

			switch ( $error ) {

				case 'expired':
					$message = __( 'The product license has either expired or not been activated.' );
					break;

				case 'revoked':
					$message = __( 'Your license key has been disabled.' );
					break;

				case 'missing':
					$message = __( 'Invalid license.' );
					break;

				case 'invalid':
				case 'site_inactive':
				case 'inactive':
					$message = __( 'Your license is not active for this URL.' );
					break;

				case 'item_name_mismatch':
					$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $this->get_product_name() );
					break;

				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.' );
					break;

				case 'key_mismatch':
					$message = __( 'License is not valid for this product' );
					break;

				default:
					$message = __( 'An error occurred, please try again.' );
					break;
			}
			return $message;
		}
		public function api_manager_activate_license( $request ) {

			$api_response = $this->get_default_response();
			require_once 'class-cookie-law-info-password-generator.php';
			$password_management = new Cookie_Law_Info_Password_Generator();
			$instance            = $password_management->generate_password( 12, false );
			$request_body        = array(
				'email'            => isset( $request['email'] ) ? $request['email'] : '',
				'licence_key'      => isset( $request['licence_key'] ) ? $request['licence_key'] : '',
				'request'          => 'activation',
				'product_id'       => $this->get_product_name(),
				'instance'         => $instance,
				'platform'         => $this->get_domain(),
				'software_version' => $this->get_product_version(),
			);

			$endpoint = $this->build_url( $request_body );
			$response = $this->wt_remote_request( 'GET', $endpoint );
			if ( isset( $response['activated'] ) && true === $response['activated'] ) {
				$api_response['status'] = true;
				$this->set_license_version( '1.0' );
				$api_response['instance'] = isset( $response['instance'] ) ? $response['instance'] : '';
			} else {
				if ( isset( $response['license'] ) && 'failed' === $response['license'] ) {
					$this->reset_license_data();
				}
				if ( isset( $response['error'] ) ) {
					$api_response['message'] = $response['error'];
				}
			}
			return $api_response;
		}
		/**
		 * Deacitvate license from CookieYes
		 *
		 * @param array $request request array.
		 * @return array
		 */
		public function edd_deactivate_license( $request ) {
			$api_response = $this->get_default_response();
			$request_body = array(
				'edd_action' => 'deactivate_license',
				'item_id'    => $this->get_product_id(),
				'license'    => $request['licence_key'],
				'url'        => $this->get_domain(),
			);
			$endpoint     = $this->build_url( $request_body, true );
			$response     = $this->wt_remote_request( 'GET', $endpoint );

			if ( isset( $response ) && is_array( $response ) ) {
				if ( isset( $response['success'] ) && true === $response['success'] ) {
					$this->set_license_version( false );
					$api_response['status'] = true;
				} else {
					if ( isset( $response['error'] ) ) {
						$api_response['message'] = $this->get_edd_error_messages( $response['error'] );
					}
				}
			}
			return $api_response;
		}
		public function api_manager_deactivate_license( $request ) {
			$api_response = $this->get_default_response();
			$request_body = array(
				'request'     => 'deactivation',
				'email'       => $request['email'],
				'licence_key' => $request['licence_key'],
				'product_id'  => $this->get_product_name(),
				'instance'    => $request['instance_id'],
				'platform'    => $this->get_domain(),
			);
			$endpoint     = $this->build_url( $request_body );
			$response     = $this->wt_remote_request( 'GET', $endpoint );
			if ( isset( $response['deactivated'] ) && true === $response['deactivated'] ) {
				$api_response['status'] = true;
				$this->set_license_version( false );
				$api_response['message'] = __( 'License deactivation successfull', 'webtoffee-gdpr-cookie-consent' );
			} else {
				if ( isset( $response['error'] ) ) {
					if ( isset( $response['activated'] ) && 'inactive' === $response['activated'] ) {
						$this->reset_license_data();
					}
					$api_response['message'] = $response['error'];
				}
			}
			return $api_response;
		}
		public function build_url( $args, $status = false ) {

			$api_url = add_query_arg( $args, $this->get_base_path() );
			if ( false === $status ) {
				$api_url = add_query_arg( 'wc-api', 'am-software-api', $this->get_base_path() );
				$api_url = $api_url . '&' . http_build_query( $args );
			}
			return $api_url;
		}
		public function get_api_manager_errors() {
			$errors = array(
				'no_activation'          => 'no_activation',
				'exp_license'            => 'exp_license',
				'cancelled_subscription' => 'cancelled_subscription',
				'download_revoked'       => 'download_revoked',
			);
			return $errors;
		}
		public function check_for_possible_deactivation( $errors ) {
			$license_version = $this->get_license_version();
			if ( version_compare( $license_version, '2.0', '>=' ) ) {
				if ( ! is_array( $errors ) ) {
					if ( in_array( $errors, $this->get_edd_errors() ) ) {
						$this->set_license_inactive();
					}
				}
			} else {
				if ( ! empty( $errors ) ) {
					foreach ( $errors as $error_code => $error_value ) {
						if ( in_array( $error_code, array_keys( $this->get_api_manager_errors() ) ) ) {
							$this->set_license_inactive();
							break;
						}
					}
				}
			}
		}
		public function get_api_manager_error_messages( $error_response ) {

			$product_name        = $this->get_product_abs_name();
			$plugin_settings_url = $this->get_plugin_settings_url();
			$upgrade_url         = $this->get_base_path() . 'my-account';
			$api_errors          = $this->get_api_manager_errors();
			$error               = '';
			foreach ( $api_errors as $key => $error_id ) {
				if ( isset( $error_response[ $key ] ) && $error_id === $error_response[ $key ] ) {
					$error = $error_response[ $key ];
					break;
				}
			}
			switch ( $error ) {

				case 'no_activation':
				case 'cancelled_subscription':
				case 'download_revoked':
					$message = __( 'The product license has either expired or not been activated.' );
					break;
				case 'exp_license':
					$message = sprintf( __( 'The product license has expired. Please <a href="%s" target="_blank">renew</a> to continue receiving updates.' ), $upgrade_url );
					break;
				default:
					$message = __( 'Failed to retrieve license information from server please try again' );
					break;
			}
			return $message;
		}
		public function add_license_notification( $file, $plugin ) {
			if ( is_network_admin() ) {
				return;
			}
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			if ( ! $this->get_last_error_message() ) {
				return;
			}
			echo '<tr class="plugin-update-tr installer-plugin-update-tr wt-cli-plugin-inline-notice-tr">
                    <td colspan="4" class="plugin-update colspanchange">
                        <div class="update-message notice inline wt-plugin-notice-section">
                            <p>' . $this->get_last_error_message() . '</p>
                            </div>
                    </td>
                </tr>';
		}
		public function add_inline_script() {
			global $pagenow;
			if ( $pagenow == 'plugins.php' ) {
				?>
				<style>
				.wt-plugin-notice-section p:before {
					content: "\f534";
				}
				</style>
				<script>
				if(typeof WTPluginAddParentStyle != 'function'){

					function WTPluginAddParentStyle() {
						jQuery('.wt-cli-plugin-inline-notice-tr').each(function () {
							if (jQuery(this).prev().addClass('update').hasClass('active')) {
								jQuery(this).addClass('active');
							}
						})
					}
				}
				jQuery(document).ready(WTPluginAddParentStyle);
				</script>
				<?php
			}
		}
		public function check_if_license_activated() {
			$license_data = $this->get_license_data();
			$status       = true;
			if ( '' === $license_data['status'] ) {
				$message = sprintf( __( 'The plugin license is not activated. You will not receive compatibility and security updates if the plugin license is not activated. <a href="%s" target="_blank">Activate now</a>' ), $this->get_plugin_settings_url() );
				$status  = false;
			} elseif ( 'inactive' === $license_data['status'] ) {
				$message = __( 'The product license has either expired or not been activated.' );
				$status  = false;
			}
			if ( false === $status ) {
				$this->set_error_message( $message );
			}
			return $status;
		}
	}
	Cookie_Law_Info_License_Handler::get_instance();
}
