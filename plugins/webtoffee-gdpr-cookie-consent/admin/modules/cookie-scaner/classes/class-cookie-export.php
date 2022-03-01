<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Cookie_Law_Info_Cookie_Export {

	/**
	 * Export cookie list from DB
	 */
	public function do_export( $scan_id, $scanner_obj ) {
		global $wpdb;
		$wpdb->hide_errors();
		@set_time_limit( 0 );
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}
		@ini_set( 'zlib.output_compression', 0 );
		@ob_clean();

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=cli-scanned-cookies.csv' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$fp               = fopen( 'php://output', 'w' );
		$row              = array();
		$cookielaw_fields = array(
			'post_title',
			'post_content',
			'post_status',
			'_cli_cookie_headscript_meta',
			'_cli_cookie_bodyscript_meta',
			'_cli_cookie_slugid',
			'_cli_cookie_type',
			'_cli_cookie_sensitivity',
			'_cli_cookie_duration',
			'tax:cookielawinfo-category',
			'cli_cookie_category_description',
		);

		// Export header rows
		foreach ( $cookielaw_fields as $column ) {
			$row[] = self::format_data( $column );
		}

		$row = array_map( 'self::wrap_column', $row );
		fwrite( $fp, implode( ',', $row ) . "\n" );
		unset( $row );

		$cookies = $scanner_obj->get_scan_cookies( $scan_id, 0, -1 ); // take all cookies

		// Loop cookies
		if ( $cookies['total'] > 0 ) {
			foreach ( $cookies['cookies'] as $cookie ) {

				$row = array();
				// Export header rows
				foreach ( $cookielaw_fields as $column ) {
					switch ( $column ) {
						case 'post_title':
							$row[] = self::format_data( sanitize_text_field( $cookie['cookie_id'] ) );
							break;

						case 'post_content':
							$row[] = self::format_data( sanitize_textarea_field( $cookie['description'] ) );
							break;

						case 'post_status':
							$row[] = 'publish';
							break;

						case '_cli_cookie_headscript_meta':
							$row[] = '';
							break;

						case '_cli_cookie_bodyscript_meta':
							$row[] = '';
							break;

						case '_cli_cookie_slugid':
							$row[] = self::format_data( sanitize_text_field( $cookie['cookie_id'] ) );
							break;

						case '_cli_cookie_type':
							$row[] = self::format_data( sanitize_text_field( $cookie['type'] ) );
							break;

						case '_cli_cookie_sensitivity':
							$row[] = self::format_data( 'non-necessary' );
							break;

						case '_cli_cookie_duration':
							$row[] = self::format_data( sanitize_text_field( $cookie['expiry'] ) );
							break;

						case 'tax:cookielawinfo-category':
							$row[] = self::format_data( sanitize_text_field ( $cookie['category'] ) );
							break;
						case 'cli_cookie_category_description':
							$row[] = self::format_data( sanitize_textarea_field( $cookie['cli_cookie_category_description'] ) );
							break;
						default:
							break;
					}
				}
				// Add to csv
				$row = array_map( 'self::wrap_column', $row );
				fwrite( $fp, implode( ',', $row ) . "\n" );
				unset( $row );
			}
		}
		fclose( $fp );
		exit;

	}


	/*
	*Format data for CSV
	*/
	public static function format_data( $data ) {
		$enc  = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		$data = ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		return $data;
	}

	/**
	 * Wrap a column in quotes for the CSV
	 *
	 * @param  string data to wrap
	 * @return string wrapped data
	 */
	public static function wrap_column( $data ) {
		return '"' . str_replace( '"', '""', $data ) . '"';
	}
}
