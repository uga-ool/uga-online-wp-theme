<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cookie_Law_Info_Export_Cookies 
{
	/**
	 * Initialization
	 *
	 * @since 2.1.3
	 **/
	public static function init() 
	{
        add_action( 'admin_post_print.csv', array( __CLASS__, 'process_csv_export' ) );
        add_action('admin_head-edit.php',array( __CLASS__,'wt_addExportButton'));
	}

	/**
	 * Add `scan & download` buttons
	 *
	 **/
	public static function wt_addExportButton()
	{	     
	    global $current_screen;
	    if (CLI_POST_TYPE != $current_screen->post_type) {
	        return;
	    }
	    $scan_export_menu = __('Download as CSV', 'webtoffee-gdpr-cookie-consent');	    
	    ?>
	        <script type="text/javascript">
	            jQuery(document).ready( function($)
	            {
	                jQuery("<a  href='<?php print wp_nonce_url(admin_url('admin-post.php?action=print.csv'), 'print.csv', 'cookie_law_info_nonce');?>' id='scan_export_cookie' class='add-new-h2'><?php echo $scan_export_menu; ?></a>").insertAfter(".wrap h1");
	            });
	        </script>
	    <?php
	}

	/**
	 * Process content of CSV file
	 *
	 * 
	 **/
	public static function process_csv_export()
	{	
		if(check_admin_referer('print.csv', 'cookie_law_info_nonce') && current_user_can('manage_options') )
		{
			self::do_export();
			wp_redirect( add_query_arg('export','file',wp_get_referer()));
			exit;
		}
		
	}


	 /**
     * Content of the settings page
     *
     * @since 2.1.3
     * */
    public static function export_cookies_page() 
    {
        if (!current_user_can('manage_options'))
        wp_die(__('You do not have sufficient permissions to access this page.', 'webtoffee-gdpr-cookie-consent'));
    	include( plugin_dir_path( __FILE__ ) . 'views/export_cookies.php' );
    }

	
	/**
	 * Export cookie list from DB
	 */
	public static function do_export( $post_type = CLI_POST_TYPE ) 
	{
		global $wpdb;
		
		$wpdb->hide_errors();
		@set_time_limit(0);
		if ( function_exists( 'apache_setenv' ) )
			@apache_setenv( 'no-gzip', 1 );
		@ini_set('zlib.output_compression', 0);
		@ob_clean();
		

			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=cli-cookielaw-export.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			
			$fp = fopen('php://output', 'w');
		

   		
		$row = array();

                
                $cookielaw_fields       = array(
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
		);

                $cookielaw_base_fields       = array(
			'post_title',
                        'post_content',
                        'post_status',
                    );
                $cookielaw_meta_fields = array(			
                        '_cli_cookie_headscript_meta',
                        '_cli_cookie_bodyscript_meta',
                        '_cli_cookie_slugid',
			'_cli_cookie_type',
                        '_cli_cookie_sensitivity',
                        '_cli_cookie_duration',
                    );
		
                
		// Export header rows
		foreach ( $cookielaw_fields as $column) {
                    $row[] = self::format_data( $column );
		}

		$row = array_map( 'self::wrap_column', $row );
		fwrite( $fp, implode( ',', $row ) . "\n" );
		unset( $row );

		

			$cli_args = apply_filters( 'cli_csv_cookielawinfo_export_args', array(
				'post_status'   => array( 'publish', ),
				'post_type'		=> array(CLI_POST_TYPE),
				'orderby' 		=> 'ID',
                'numberposts'   => -1,
				'order'			=> 'ASC',
			) );

			
			$cookies = get_posts( $cli_args );
			if ( ! $cookies || is_wp_error( $cookies ) )
                            goto fpclosingarea;

			// Loop cookies
			foreach ( $cookies as $cookie ) {
                                
				$row = array();
                                
				// Pre-process data
				$meta_data = get_post_custom( $cookie->ID );

				// Export header rows
                foreach ( $cookielaw_fields as $column) {
                    switch ($column) {
                        case "post_title":
                            $row[] = self::format_data( $cookie->{$column} );
                            break;
                        
                        case "post_content":
                            $row[] = self::format_data( $cookie->{$column} );
                            break;
                        
                        case "post_status":
                            $row[] = self::format_data( $cookie->{$column} );
                            break;
                        
						case "_cli_cookie_headscript_meta":
							$headscript_meta = isset($meta_data["_cli_cookie_headscript_meta"][0]) ? $meta_data["_cli_cookie_headscript_meta"][0] : '';
                            $row[] = self::format_data($headscript_meta);
                            break;
                        
						case "_cli_cookie_bodyscript_meta":
							$bodyscript_meta = isset( $meta_data["_cli_cookie_bodyscript_meta"][0]) ? $meta_data["_cli_cookie_bodyscript_meta"][0] : '';
                            $row[] = self::format_data( $bodyscript_meta );
                            break;
                        
                        case "_cli_cookie_slugid":
                            
                            $slugid = !empty($meta_data["_cli_cookie_slugid"][0]) ? $meta_data["_cli_cookie_slugid"][0] : '';
                            $row[] = self::format_data( $slugid );
                            break;
                        
                        case "_cli_cookie_type":
                            $cookie_type = !empty($meta_data["_cli_cookie_type"][0]) ? $meta_data["_cli_cookie_type"][0] : 'persistent';
                            $row[] = self::format_data( $cookie_type );
                            break;
                        
                        case "_cli_cookie_sensitivity":
                            $cookie_sensitivity = !empty($meta_data["_cli_cookie_sensitivity"][0]) ? $meta_data["_cli_cookie_sensitivity"][0] : 'non-necessary';
                            $row[] = self::format_data( $cookie_sensitivity );
                            break;
                        
                        case "_cli_cookie_duration":
                            $cookie_duration = !empty($meta_data["_cli_cookie_duration"][0]) ? $meta_data["_cli_cookie_duration"][0] : '1 year';
                            $row[] = self::format_data( $cookie_duration );
                            break;
                        
                        case "tax:cookielawinfo-category":
                            $category_detail = wp_get_object_terms($cookie->ID, 'cookielawinfo-category', array());//$post->ID
                            $cat_name = $category_detail ? $category_detail[0]->slug : '';
                            $cat_name = ($cat_name) ? $cat_name : 'non-necessary';
                            $row[] = self::format_data( $cat_name );
                            //$row[] = self::format_data('s');
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
			unset( $cookies );
		
		fpclosingarea:
		fclose( $fp );
		exit;
	}

	/*
	*Format data for CSV
	*/
	public static function format_data( $data ) 
	{
		$enc  = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		$data = ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		return $data;
	}

	/**
	 * Wrap a column in quotes for the CSV
	 * @param  string data to wrap
	 * @return string wrapped data
	 */
	public static function wrap_column( $data ) 
	{
		return '"' . str_replace( '"', '""', $data ) . '"';
	}

}
Cookie_Law_Info_Export_Cookies::init();