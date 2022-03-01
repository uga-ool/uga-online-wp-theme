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
if ( ! class_exists( 'Cookie_Law_Info_Remote_Request' ) ) {

	class Cookie_Law_Info_Remote_Request {

		public $module_id;
		public function __construct() {
		}

		public function parse_raw_response( $raw_response, $object ) {

			$response_code = wp_remote_retrieve_response_code( $raw_response );
			$response      = false;
			if ( 200 !== $response_code ) {
				return false;
			}
			$response_data = wp_remote_retrieve_body( $raw_response );

			if ( is_serialized( $response_data ) ) {
				$response = unserialize( $response_data );
			} else {
				if ( true === $object ) {
					$response = json_decode( $response_data );

				} else {
					$response = json_decode( $response_data, true );
				}
			}

			return $response;
		}

		public function get_default_response() {
			$api_response = array(
				'status' => false,
				'code'   => 100,
			);
			return $api_response;
		}
		public function wt_remote_request( $request_type = 'GET', $endpoint = '', $body = false, $auth_token = false, $object = false ) {

			$request_args                            = array(
				'timeout' => 60,
				'headers' => array(),
			);
			$request_args['headers']['Content-Type'] = 'application/json';
			$request_args['headers']['Accept']       = 'application/json';

			if ( $body !== false ) {
				$request_args['body'] = json_encode( $body );
			}
			if ( $auth_token !== false ) {
				$request_args['headers']['Authorization'] = 'Bearer ' . $auth_token;
			}
			// Request types.
			switch ( $request_type ) {
				case 'GET':
					$raw_response = wp_remote_get(
						$endpoint,
						$request_args
					);
					break;

				case 'PUT':
				case 'POST':
					$raw_response = wp_remote_post(
						$endpoint,
						$request_args
					);
					break;
				default:
					break;
			}
			if ( $raw_response ) {
				$response = $this->parse_raw_response( $raw_response, $object );
				return $response;
			}
			return false;
		}

	}

	$settings_popup = new Cookie_Law_Info_Remote_Request();
}
