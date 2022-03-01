<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! defined( 'IS_IU_CSV_DELIMITER' ) )
	define ( 'IS_IU_CSV_DELIMITER', ',' );

/**
 * Main plugin class
 *
 * @since 2.1.3
 **/
class Cookie_Law_Info_Import_Cookies {
	private static $log_dir_path = '';
	private static $log_dir_url  = '';

	/**
	 * Initialization
	 *
	 * @since 2.1.3
	 **/
	public static function init() 
	{
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'process_csv' ) );
		add_action('admin_head-edit.php',array( __CLASS__,'wt_addImportButton'));

		$upload_dir = wp_upload_dir();
		self::$log_dir_path = trailingslashit( $upload_dir['basedir'] );
		self::$log_dir_url  = trailingslashit( $upload_dir['baseurl'] );
	}

	/**
	 * Add `scan & import button`
	 *
	 **/
	public static function wt_addImportButton()
	{	     
	    global $current_screen;
	    if (CLI_POST_TYPE != $current_screen->post_type) {
	        return;
	    }
	    $scan_import_menu = __('Import from CSV', 'webtoffee-gdpr-cookie-consent');
	    $import_page=admin_url('edit.php?post_type='.CLI_POST_TYPE.'&page=cookie-law-info-import');
	    ?>
	        <script type="text/javascript">
	            jQuery(document).ready( function($)
	            {                
	                jQuery('<a class="add-new-h2" href="<?php echo $import_page;?>"><?php echo $scan_import_menu; ?></a>').insertAfter(".wrap h1");
	            });
	        </script>
	    <?php
	}

	/**
	 * Add administration menus
	 *
	 * @since 2.1.3
	 **/
	public static function add_admin_pages() 
	{
        add_submenu_page(
        	'',//'edit.php?post_type='.CLI_POST_TYPE,
			__('Import Cookie','webtoffee-gdpr-cookie-consent'),
			__('Import Cookie','webtoffee-gdpr-cookie-consent'),
			'manage_options',
			'cookie-law-info-import',
			array(__CLASS__,'import_cookies_page')
		);
	}

	/**
	 * Process content of CSV file
	 *
	 * @since 2.1.3
	 **/
	public static function process_csv() 
	{
		if ( isset( $_POST['_wpnonce-cookie-page_import'] ) ) {
			check_admin_referer( 'cookie-page_import', '_wpnonce-cookie-page_import' );

			if ( !empty( $_FILES['cookie_csv']['tmp_name'] ) ) {
				// Setup settings variables
				$filename              = $_FILES['cookie_csv']['tmp_name'];

				$results = self::import_csv( $filename, array() );

				// No posts imported?
				if ( ! $results['post_ids'] )
					wp_redirect( add_query_arg( 'import', 'fail', wp_get_referer() ) );

				// Some posts imported?
				elseif ( $results['errors'] )
					wp_redirect( add_query_arg( 'import', 'errors', wp_get_referer() ) );

				// All posts imported? :D
				else
					wp_redirect( add_query_arg( 'import', 'success', wp_get_referer() ) );

				exit;
			}
			wp_redirect( add_query_arg( 'import', 'file', wp_get_referer() ) );
			exit;
		}
	}

    /**
	 * Content of the settings page
	 *
	 * @since 2.1.3
	 **/
	public static function import_cookies_page() 
	{
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'webtoffee-gdpr-cookie-consent') );
		$example_file=plugin_dir_url(__FILE__).'examples/import.csv';
		include( plugin_dir_path( __FILE__ ) . 'views/import_cookies.php' );
	}

	/**
	 * Import a csv file
	 *
	 * @since 2.1.3
	 */
	public static function import_csv( $filename, $args ) 
	{
		$errors = $post_ids = array();

		// User data fields list used to differentiate with post meta
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
			'cli_cookie_category_description',
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
		include( plugin_dir_path( __FILE__ ) . 'classes/class-readcsv.php' );

		// Loop through the file lines
		$file_handle = @fopen( $filename, 'r' );
		if($file_handle) {
			$csv_reader = new ReadCSV( $file_handle, IS_IU_CSV_DELIMITER, "\xEF\xBB\xBF" ); // Skip any UTF-8 byte order mark.

			$first = true;
			$rkey = 0;
			while ( ( $line = $csv_reader->get_row() ) !== NULL ) {

                            
				// If the first line is empty, abort
				// If another line is empty, just skip it
				if ( empty( $line ) ) {
					if ( $first )
						break;
					else
						continue;
				}

				// If we are on the first line, the columns are the headers
				if ( $first ) {
					$headers = $line;
					$first = false;
					continue;
				}

				// Separate post data from meta
				$clidata  = array();
				foreach ( $line as $ckey => $column ) {
					$column_name = $headers[$ckey];
					$column = trim( $column );

					if ( in_array( $column_name, $cookielaw_fields ) ) {
						$clidata[$column_name] = $column;
					}
				}

				// A plugin may need to filter the data and meta
				$clidata = apply_filters( 'modify_cookie_import_clidata', $clidata );
			   
				// If no data, bailout!
				if ( empty( $clidata ) )
					continue;

				// Something to be done before importing one cookie
				do_action( 'cookie_import_pre_import', $clidata );

                                
				
				
			$postdata = array(
				//'import_id'      => $processing_id,
				'post_author'    => get_current_user_id(),
				'post_date'      => date( 'Y-m-d H:i:s', strtotime('now')),
				'post_date_gmt'  => date( 'Y-m-d H:i:s', strtotime('now')),
				'post_content'   => $clidata['post_content'],
				'post_title'     => $clidata['post_title'],
				'post_name'      => ( sanitize_title( $clidata['post_title'] )),
				'post_status'    => ( $clidata['post_status'] ) ? $clidata['post_status'] : 'publish',
				'post_parent'    => 0,
				'post_type'      => CLI_POST_TYPE,
			);
                        
			$post_id = wp_insert_post( $postdata, true );
				

				// Is there an error o_O?
				if ( is_wp_error( $post_id ) ) {
					$errors[$rkey] = $post_id;
				} else 
				{
					// If no error, let's update the post meta too!
					if ( $post_id ) 
					{
						foreach ( $cookielaw_meta_fields as $metakey  ) 
						{
							$metavalue = maybe_unserialize( $clidata[$metakey] );
							update_post_meta( $post_id, $metakey, $metavalue );
						}
					}
					wp_set_object_terms($post_id, array($clidata['tax:cookielawinfo-category']), 'cookielawinfo-category', true);
					
					// Check if 'cli_cookie_category_description' is in CSV header
					if(in_array('cli_cookie_category_description',$headers))
					{	
						
						$category = get_term_by('name', $clidata['tax:cookielawinfo-category'], 'cookielawinfo-category');
						// Check if category exist
						if($category && is_object($category))
						{	
							
							$category_id=$category->term_id;
							$category_description=$category->description;
							// Check if catgory has description
							if(is_null($category_description))
							{	
								wp_update_term($category_id, 'cookielawinfo-category', array(
									'description' => $clidata['cli_cookie_category_description'],
								));
							}
						
						}
					}
					// Some plugins may need to do things after one post has been imported. Who know?
					do_action( 'cookie_import_post_import', $post_id );

					$post_ids[] = $post_id;
				}

				$rkey++;
			}
			fclose( $file_handle );
		} else {
			$errors[] = new WP_Error('file_read', 'Unable to open CSV file.');
		}

		// One more thing to do after all imports?
		do_action( 'cookie_import_finished_import', $post_ids, $errors );

		// Let's log the errors
		//self::log_errors( $errors );

		return array(
			'post_ids' => $post_ids,
			'errors'   => $errors
		);
	}

	/**
	 * Log errors to a file
	 *
	 * @since 2.1.3
	 **/
	private static function log_errors( $errors ) 
	{
		if ( empty( $errors ) )
			return;

		$log = @fopen( self::$log_dir_path . 'cookielawinfo_errors.log', 'a' );
		@fwrite( $log, sprintf( __( 'BEGIN %s' , 'webtoffee-gdpr-cookie-consent'), date( 'Y-m-d H:i:s', time() ) ) . "\n" );

		foreach ( $errors as $key => $error ) {
			$line = $key + 1;
			$message = $error->get_error_message();
			@fwrite( $log, sprintf( __( '[Line %1$s] %2$s' , 'webtoffee-gdpr-cookie-consent'), $line, $message ) . "\n" );
		}

		@fclose( $log );
	}
}
Cookie_Law_Info_Import_Cookies::init();
