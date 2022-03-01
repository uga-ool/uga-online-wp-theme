<?php
/**
 * The cookie scanning functionality of the plugin.
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.5
 *
 * @package    Cookie_Law_Info
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include( plugin_dir_path( __FILE__ ).'classes/class-cookie-scanner-ajax.php');

class Cookie_Law_Info_Cookie_Scaner extends Cookie_Law_Info_Cookieyes
{
	
	public $scan_table='cli_cookie_scan';
	public $url_table='cli_cookie_scan_url';
	public $cookies_table='cli_cookie_scan_cookies';
	public $category_table = 'cli_cookie_scan_categories';
	public $not_keep_records=true;
	public $scan_page_mxdata; //maximum url per request for scanning //!important do not give value more than 5
	public $fetch_page_mxdata=100; //take pages
	public $last_query;
	
	public function __construct()
	{		
		/* creating necessary tables for cookie scaner  */
        register_activation_hook(CLI_PLUGIN_FILENAME,array($this,'activator'));
		add_action('wt_cli_initialize_plugin', array( $this, 'activator' ) );
        $this->status_labels=array(
			0	=>	'',
			1	=>	__('Incomplete','webtoffee-gdpr-cookie-consent'),
			2	=>	__('Completed','webtoffee-gdpr-cookie-consent'),
			3	=>	__('Stopped','webtoffee-gdpr-cookie-consent'),
			4	=>	__('Failed','webtoffee-gdpr-cookie-consent'),
		);
        add_action('admin_init',array( $this,'export_result'));
		add_action( 'admin_menu', array($this,'add_admin_pages'));
		
		$url_per_request=get_option('cli_cs_url_per_request');
        if(!$url_per_request)
        {
            $url_per_request=5;
        }
        $this->scan_page_mxdata=$url_per_request;

		add_action('wt_cli_cookie_scanner_body',array( $this,'scanner_notices'));
		add_filter( 'wt_cli_cookie_scan_status', array( $this, 'check_scan_status' ) );
		add_action('rest_api_init', function () {
			register_rest_route('cookieyes/v1', '/fetch_results', array(
				'methods' => 'POST',
				'callback' => array($this, 'fetch_scan_result'),
				'permission_callback' => '__return_true'
			));
		});
	}
	
	/*
    * returning labels of status
    */
	public function getStatusText($status)
	{
		return isset($this->status_labels[$status]) ? $this->status_labels[$status] : __('Unknown','webtoffee-gdpr-cookie-consent');
	}

	/*
    * export to csv
    */
	public function export_result()
	{
		if(isset($_GET['cli_scan_export']) && (int) $_GET['cli_scan_export']>0 && check_admin_referer('cli_cookie_scaner', 'cli_cookie_scaner') && current_user_can('manage_options')) 
		{	
			//cookie export class
            include( plugin_dir_path( __FILE__ ).'classes/class-cookie-export.php');
            $cookie_serve_export=new Cookie_Law_Info_Cookie_Export();
            $cookie_serve_export->do_export($_GET['cli_scan_export'],$this);
			exit();
		}
	}

	/*
    *called on activation
    */
    public function activator()
    {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach($blog_ids as $blog_id) 
            {
                switch_to_blog( $blog_id );
                $this->install_tables();
		        if(!get_option('cli_cs_url_per_request'))
		        {
		            update_option('cli_cs_url_per_request',5);
		        }
                restore_current_blog();
            }
        }
        else 
        {
            $this->install_tables();
            if(!get_option('cli_cs_url_per_request'))
	        {
	            update_option('cli_cs_url_per_request',5);
	        }
        }
    }

    /*
    * Install necessary tables
    */
    public function install_tables()
    {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
        //creating main table ========================
        $table_name=$wpdb->prefix.$this->scan_table;
		if ( false === $this->table_exists( $table_name ) ) {      
            $create_table_sql= "CREATE TABLE `$table_name`(
			    `id_cli_cookie_scan` INT NOT NULL AUTO_INCREMENT,
			    `status` INT NOT NULL DEFAULT '0',
			    `created_at` INT NOT NULL DEFAULT '0',
			    `total_url` INT NOT NULL DEFAULT '0',
			    `total_cookies` INT NOT NULL DEFAULT '0',
			    `current_action` VARCHAR(50) NOT NULL,
			    `current_offset` INT NOT NULL DEFAULT '0',
			    PRIMARY KEY(`id_cli_cookie_scan`)
			) $charset_collate;";
            dbDelta($create_table_sql);
        }
        //creating main table ========================


        //creating url table ========================
        $table_name=$wpdb->prefix.$this->url_table;

        if ( false === $this->table_exists( $table_name ) ) {
            $create_table_sql= "CREATE TABLE `$table_name`(
			    `id_cli_cookie_scan_url` INT NOT NULL AUTO_INCREMENT,
			    `id_cli_cookie_scan` INT NOT NULL DEFAULT '0',
			    `url` TEXT NOT NULL,
			    `scanned` INT NOT NULL DEFAULT '0',
			    `total_cookies` INT NOT NULL DEFAULT '0',
			    PRIMARY KEY(`id_cli_cookie_scan_url`)
			) $charset_collate;";
            dbDelta($create_table_sql);
		}
		
        //creating url table ========================

        //creating cookies table ========================
        $table_name=$wpdb->prefix.$this->cookies_table;
        if ( false === $this->table_exists( $table_name ) ) {
            $create_table_sql= "CREATE TABLE `$table_name`(
			    `id_cli_cookie_scan_cookies` INT NOT NULL AUTO_INCREMENT,
			    `id_cli_cookie_scan` INT NOT NULL DEFAULT '0',
			    `id_cli_cookie_scan_url` INT NOT NULL DEFAULT '0',
			    `cookie_id` VARCHAR(100) NOT NULL,
			    `expiry` VARCHAR(255) NOT NULL,
			    `type` VARCHAR(255) NOT NULL,
			    `category` VARCHAR(255) NOT NULL,
			    PRIMARY KEY(`id_cli_cookie_scan_cookies`),
			    UNIQUE `cookie` (`id_cli_cookie_scan`, `cookie_id`)
			)";
			$this->insert_scanner_tables( $create_table_sql, $charset_collate );
		}
		//creating cookies table ========================

		 //creating categories table ========================
		 $table_name=$wpdb->prefix.$this->category_table;
		 if ( false === $this->table_exists( $table_name ) ) {         
			 $create_table_sql= "CREATE TABLE `$table_name`(
				 `id_cli_cookie_category` INT NOT NULL AUTO_INCREMENT,
				 `cli_cookie_category_name` VARCHAR(100) NOT NULL,
				 `cli_cookie_category_description` TEXT  NULL,
				 PRIMARY KEY(`id_cli_cookie_category`),
				 UNIQUE `cookie` (`cli_cookie_category_name`)
			 )";
			 $this->insert_scanner_tables( $create_table_sql, $charset_collate );
		 }
		 //creating cookies table ========================
        $this->update_tables();
	}
	/**
	* Error handling of scanner table creation
	*
	* @since  2.3.1
	* @access private
	* @throws Exception Error message.
	* @param  string
	*/
	private function insert_scanner_tables( $sql, $prop = '', $status = 0 ) {
		
		global $wpdb;
		dbDelta( $sql.' '.$prop );
		if( $wpdb->last_error ) {
			$status++;
			if( $status === 1) {
				$prop = '';
			} else if( $status === 2) {
				$prop = 'ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci';
			} else {
				return true;
			}
			$this->insert_scanner_tables( $sql, $prop, $status);
		} else {
			return true;
		}
	}
	/*
    * @since 2.1.9
    * update the table
    */
    private function update_tables()
    {
    	global $wpdb;
    	//Cookie table =======
        //`description` column
		$table_name=$wpdb->prefix.$this->cookies_table;
		$cat_table=$wpdb->prefix.$this->category_table;
        $search_query = "SHOW COLUMNS FROM `$table_name` LIKE 'description'";
        if(!$wpdb->get_results($search_query,ARRAY_N)) 
        {
        	$wpdb->query("ALTER TABLE `$table_name` ADD `description` TEXT NULL DEFAULT '' AFTER `category`");
		}
		// category_id` column
		$search_query = "SHOW COLUMNS FROM `$table_name` LIKE 'category_id'";
        if(!$wpdb->get_results($search_query,ARRAY_N)) 
        {
			$wpdb->query("ALTER TABLE `$table_name` ADD `category_id` INT NOT NULL  AFTER `category`");
			$wpdb->query("ALTER TABLE `$table_name` ADD CONSTRAINT FOREIGN KEY (`category_id`) REFERENCES `$cat_table` (`id_cli_cookie_category`)");
			
		}
		
	}
	
    /*
    * checking necessary tables are installed
    */
    protected function check_tables()
    {
    	global $wpdb;

		$scanner_tables = array(
			$this->scan_table,
			$this->url_table,
			$this->cookies_table,
			$this->category_table,

		);
		foreach ( $scanner_tables as $table ) {
			$table_name = $wpdb->prefix . $table;
			$sql        = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

			if ( ! $wpdb->get_results( $sql, ARRAY_N ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				return false;
			}
		}
		return true;
    }

	/**
	 * Add administration menus
	 *
	 * @since 2.1.5
	 **/
	public function add_admin_pages() 
	{
        add_submenu_page(
			'edit.php?post_type='.CLI_POST_TYPE,
			__('Cookie Scanner','webtoffee-gdpr-cookie-consent'),
			__('Cookie Scanner','webtoffee-gdpr-cookie-consent'),
			'manage_options',
			'cookie-law-info-cookie-scaner',
			array($this, 'cookie_scaner_page')
		);		
	}

	/*
	*
	* Scaner page (Admin page)
	*/
	public function cookie_scaner_page()
	{	
		$plugin_help_url = get_admin_url(null, 'edit.php?post_type=' . CLI_POST_TYPE . '&page=cookie-law-info#cookie-law-info-advanced');
		$cookie_list=self::get_cookie_list();
		wp_enqueue_script('cookielawinfo_cookie_scaner',plugin_dir_url( __FILE__ ).'assets/js/cookie-scaner.js',array(),CLI_VERSION);
		$scan_page_url=admin_url('edit.php?post_type='.CLI_POST_TYPE.'&page=cookie-law-info-cookie-scaner');
		$result_page_url=$scan_page_url.'&scan_result';
		$export_page_url=$scan_page_url.'&cli_scan_export=';
		$import_page_url=$scan_page_url.'&cli_cookie_import=';
		$last_scan=$this->get_last_scan();
		$params = array(
	        'nonces' => array(
	            'cli_cookie_scaner' => wp_create_nonce('cli_cookie_scaner'),
			),
			
	        'ajax_url' => admin_url('admin-ajax.php'),
	        'scan_page_url'=>$scan_page_url,
	        'result_page_url'=>$result_page_url,
	        'export_page_url'=>$export_page_url,
			'loading_gif'=>plugin_dir_url(__FILE__).'assets/images/loading.gif',
			'scan_status'	=>	( $this->check_scan_status() === 1  ? true : false ),
	        'labels'=>array(
	        	'scanned'=>__('Scanned','webtoffee-gdpr-cookie-consent'),
				'finished'=>__('Scanning completed.','webtoffee-gdpr-cookie-consent'),
				'import_finished'=>__('Added to cookie list.','webtoffee-gdpr-cookie-consent'),
				'retrying'=> sprintf( wp_kses( __( 'Unable to connect. Try setting the URL per scan request to 1 or 2 under advanced settings tab <a href="%s">click here.</a>', 'webtoffee-gdpr-cookie-consent' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $plugin_help_url ) ),
				'finding'=>__('Finding pages...','webtoffee-gdpr-cookie-consent'),
				'scanning'=>__('Scanning pages...','webtoffee-gdpr-cookie-consent'),
				'error'=>__('Error','webtoffee-gdpr-cookie-consent'),
				'stop'=>__('Stop','webtoffee-gdpr-cookie-consent'),
				'scan_again'=>__('Scan again','webtoffee-gdpr-cookie-consent'),
				'export'=>__('Download cookies as CSV','webtoffee-gdpr-cookie-consent'),
				'import'=>__('Add to cookie list','webtoffee-gdpr-cookie-consent'),
				'view_result'=>__('View scan result','webtoffee-gdpr-cookie-consent'),
				'import_options'=>__('Import options','webtoffee-gdpr-cookie-consent'),
				'replace_old'=>__('Replace old','webtoffee-gdpr-cookie-consent'),
				'merge'=>__('Merge','webtoffee-gdpr-cookie-consent'),
				'recommended'=>__('Recommended','webtoffee-gdpr-cookie-consent'),
				'append'=>__('Append','webtoffee-gdpr-cookie-consent'),
				'not_recommended'=>__('Not recommended','webtoffee-gdpr-cookie-consent'),
				'cancel'=>__('Cancel','webtoffee-gdpr-cookie-consent'),
				'start_import'=>__('Start import','webtoffee-gdpr-cookie-consent'),
				'importing'=>__('Importing....','webtoffee-gdpr-cookie-consent'),
				'refreshing'=>__('Refreshing....','webtoffee-gdpr-cookie-consent'),
				'reload_page'=>__('Error !!! Please reload the page to see cookie list.','webtoffee-gdpr-cookie-consent'),
				'stoping'=>__('Stopping...','webtoffee-gdpr-cookie-consent'),
				'scanning_stopped'=>__('Scanning stopped.','webtoffee-gdpr-cookie-consent'),
				'ru_sure'=>__('Are you sure?','webtoffee-gdpr-cookie-consent'),
				'success'=>__('Success','webtoffee-gdpr-cookie-consent'),
				'thankyou'=>__('Thank you','webtoffee-gdpr-cookie-consent'),
				'checking_api'=>__('Checking API','webtoffee-gdpr-cookie-consent'),
				'sending'=>__('Sending...','webtoffee-gdpr-cookie-consent'),
				'total_urls_scanned'=>__('Total URLs scanned','webtoffee-gdpr-cookie-consent'),
				'total_cookies_found'=>__('Total Cookies found','webtoffee-gdpr-cookie-consent'),
				'page_fetch_error'    => __( 'Could not fetch the URLs, please try again', 'webtoffee-gdpr-cookie-consent' ),
				'abort'               => __( 'Aborting the scan...', 'webtoffee-gdpr-cookie-consent' ),
				'abort_failed'        => __( 'Could not abort the scan, please try again', 'webtoffee-gdpr-cookie-consent' ),
	        )
	    );
	    wp_localize_script('cookielawinfo_cookie_scaner','cookielawinfo_cookie_scaner',$params);
	    if(isset($_GET['scan_result']))
		{
			$scan_details=$this->get_last_scan();
			$scan_urls=array(
				'total'=>0,
				'data'=>array()
			);
			$scan_cookies=array(
				'total'=>0,
				'data'=>array()
			);
			if($scan_details && isset($scan_details['id_cli_cookie_scan']))
			{
				$scan_urls=$this->get_scan_urls($scan_details['id_cli_cookie_scan']);
				$scan_cookies=$this->get_scan_cookies($scan_details['id_cli_cookie_scan'],0,-1);
			}
			$view_file="scan-result.php";
		}else
		{
			$view_file="scan-cookies.php";
		}

		$localhost_arr = array(
		    '127.0.0.1',
		    '::1'
		);
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
		{
		    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip_address = $_SERVER['REMOTE_ADDR'];
		}
		$ip_address = apply_filters('wt_cli_change_ip_address',$ip_address );
	    if(!$this->check_tables() 
	    	|| version_compare(CLI_VERSION,'2.1.4')<=0 
	    	|| in_array($ip_address,$localhost_arr))
		{
			
			$error_message=__("Unable to load cookie scanner.","webtoffee-gdpr-cookie-consent");
			if(version_compare(CLI_VERSION,'2.1.4')<=0)
			{
				$error_message.=" ".__("Need `GDPR Cookie Consent` plugin version above 2.1.4","webtoffee-gdpr-cookie-consent");
			}
			if(in_array($ip_address,$localhost_arr))
			{
				$error_message.=" ".__("Scanning will not work on local server.","webtoffee-gdpr-cookie-consent");
			}
			// $view_file="unable-to-start.php";
		}
		include( plugin_dir_path( __FILE__ ).'views/'.$view_file);
	}

	/*
	*
	*	Create a DB entry for scanning
	*/
	protected function createScanEntry($total_url=0)
	{
		global $wpdb;

		//we are not planning to keep records of old scans
		if($this->not_keep_records)
		{
			$this->flushScanRecords();
		}

		$scan_table=$wpdb->prefix.$this->scan_table;
		$data_arr=array(
			'created_at'=>time(),
			'total_url'=>$total_url,
			'total_cookies'=>0,
			'current_action'=>'get_pages',
			'status'=>1
		);
		update_option('CLI_BYPASS',1);
		if($wpdb->insert($scan_table,$data_arr))
		{
			return $wpdb->insert_id;
		}else
		{
			return '0';
		}
	}

	/*
	*
	*	Update scanning status
	*/
	protected function update_scan_entry($data_arr,$scan_id)
	{
		global $wpdb;
		$scan_table = $wpdb->prefix . $this->scan_table;
		if ( $wpdb->update( $scan_table, $data_arr, array( 'id_cli_cookie_scan' => esc_sql( $scan_id ) ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
			return true;
		} else {
			return false;
		}
	}

	/*
	*
	*	Insert URLs
	*/
	protected function insert_url($scan_id,$permalink)
	{
		global $wpdb;
		$url_table = $wpdb->prefix . $this->url_table;
		$data_arr  = array(
			'id_cli_cookie_scan' => esc_sql( $scan_id ),
			'url'                => esc_sql( $permalink ),
			'scanned'            => 0,
			'total_cookies'      => 0,
		);
		$wpdb->insert( $url_table, $data_arr );
	}
	/*
	*
	*	Update scanned to URL
	*/
	protected function updateUrl($url_id_arr)
	{
		global $wpdb;
		$url_table=$wpdb->prefix.$this->url_table;
		$sql="UPDATE `$url_table` SET `scanned`=1 WHERE id_cli_cookie_scan_url IN(".implode(",",$url_id_arr).")";
		$wpdb->query($sql);
	}
	
	/*
	*
	* Get last scan details
	*/
	protected function get_last_scan()
	{
		global $wpdb;
		$scan_table = $wpdb->prefix . $this->scan_table;
		$data       = array();
		if ( true === $this->table_exists( $scan_table ) ) {
			$sql      = "SELECT * FROM `$scan_table` ORDER BY id_cli_cookie_scan DESC LIMIT 1";
			$raw_data = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
			if ( $raw_data ) {
				$data['id_cli_cookie_scan'] = isset( $raw_data['id_cli_cookie_scan'] ) ? absint( $raw_data['id_cli_cookie_scan'] ) : 0;
				$data['status']             = isset( $raw_data['status'] ) ? absint( $raw_data['status'] ) : 1;
				$data['created_at']         = isset( $raw_data['created_at'] ) ? sanitize_text_field( $raw_data['created_at'] ) : '';
				$data['total_url']          = isset( $raw_data['total_url'] ) ? absint( $raw_data['total_url'] ) : 0;
				$data['total_cookies']      = isset( $raw_data['total_cookies'] ) ? absint( $raw_data['total_cookies'] ) : 0;
				$data['current_action']     = isset( $raw_data['current_action'] ) ? sanitize_text_field( $raw_data['current_action'] ) : '';
				$data['current_offset']     = isset( $raw_data['current_offset'] ) ? (int) $raw_data['current_offset'] : -1;
				return $data;
			}
		}
		return false;
	}

	/*
	*
	* URLs that are scanned
	*/
	public function get_scan_urls($scan_id,$offset=0,$limit=100)
	{
		global $wpdb;
		$out = array(
			'total' => 0,
			'data'  => array(),
		);

		$url_table = $wpdb->prefix . $this->url_table;

		$count_sql = $wpdb->prepare( "SELECT COUNT( id_cli_cookie_scan_url ) AS ttnum FROM $url_table WHERE id_cli_cookie_scan = %d", $scan_id );
		$count_arr = $wpdb->get_row( $count_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		if ( $count_arr ) {
			$out['total'] = $count_arr['ttnum'];
		}
		$sql = $wpdb->prepare( "SELECT * FROM $url_table WHERE id_cli_cookie_scan = %d ORDER BY id_cli_cookie_scan_url ASC LIMIT %d,%d", $scan_id, $offset, $limit );

		$data_arr = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		if ( $data_arr ) {
			$out['urls'] = $data_arr;
		}
		return $out;
	}

	/**
	 * Return the identified cookies after the scanning
	 *
	 * @param integer  $scan_id scan ID.
	 * @param integer $offset offset number.
	 * @param integer $limit page limit if pagination is used.
	 * @return array
	 */
	public function get_scan_cookies( $scan_id, $offset = 0, $limit = 100 ) {
		global $wpdb;
		$out            = array(
			'total'   => 0,
			'cookies' => array(),
		);
		$limits         = '';
		$cookies        = array();
		$cookies_table  = $wpdb->prefix . $this->cookies_table;
		$url_table      = $wpdb->prefix . $this->url_table;
		$category_table = $wpdb->prefix . $this->category_table;

		$count_sql = $wpdb->prepare( "SELECT COUNT( id_cli_cookie_scan_cookies ) AS ttnum FROM $cookies_table WHERE id_cli_cookie_scan = %d", $scan_id );
		$count_arr = $wpdb->get_row( $count_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		if ( $count_arr ) {
			$out['total'] = $count_arr['ttnum'];
		}
		$offset = (int) $offset;
		$limit  = (int) $limit;

		$limits = $limit > 0 ? 'LIMIT ' . $offset . ',' . $limit : '';

		$sql = $wpdb->prepare( "SELECT * FROM $cookies_table AS cookie INNER JOIN $category_table as category ON category.id_cli_cookie_category = cookie.category_id INNER JOIN $url_table as urls ON cookie.id_cli_cookie_scan_url = urls.id_cli_cookie_scan_url WHERE cookie.id_cli_cookie_scan = %s ORDER BY id_cli_cookie_scan_cookies ASC $limits", $scan_id );

		$db_data = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		if ( is_array( $db_data ) && ! empty( $db_data ) ) {
			foreach ( (array) $db_data as $raw_data ) {
				$data                                    = array();
				$data['id_cli_cookie_scan_cookies']      = isset( $raw_data['id_cli_cookie_scan_cookies'] ) ? absint( $raw_data['id_cli_cookie_scan_cookies'] ) : 1;
				$data['id_cli_cookie_scan']              = isset( $raw_data['id_cli_cookie_scan'] ) ? absint( $raw_data['id_cli_cookie_scan'] ) : 1;
				$data['id_cli_cookie_scan_url']          = isset( $raw_data['id_cli_cookie_scan_url'] ) ? absint( $raw_data['id_cli_cookie_scan_url'] ) : 1;
				$data['cookie_id']                       = isset( $raw_data['cookie_id'] ) ? sanitize_text_field( $raw_data['cookie_id'] ) : '';
				$data['expiry']                          = isset( $raw_data['expiry'] ) ? sanitize_text_field( $raw_data['expiry'] ) : '';
				$data['type']                            = isset( $raw_data['type'] ) ? sanitize_text_field( $raw_data['type'] ) : 1;
				$data['category']                        = isset( $raw_data['category'] ) ? sanitize_text_field( $raw_data['category'] ) : '';
				$data['category_id']                     = isset( $raw_data['category_id'] ) ? absint( $raw_data['category_id'] ) : '';
				$data['description']                     = isset( $raw_data['description'] ) ? sanitize_textarea_field( $raw_data['description'] ) : '';
				$data['id_cli_cookie_category']          = isset( $raw_data['id_cli_cookie_category'] ) ? absint( $raw_data['id_cli_cookie_category'] ) : '';
				$data['cli_cookie_category_name']        = isset( $raw_data['cli_cookie_category_name'] ) ? sanitize_text_field( $raw_data['cli_cookie_category_name'] ) : '';
				$data['cli_cookie_category_description'] = isset( $raw_data['cli_cookie_category_description'] ) ? sanitize_textarea_field( $raw_data['cli_cookie_category_description'] ) : '';
				$data['url']                             = isset( $raw_data['url'] ) ? esc_url( sanitize_text_field( $raw_data['url'] ) ) : '';
				$data['scanned']                         = isset( $raw_data['scanned'] ) ? absint( $raw_data['scanned'] ) : 0;
				$data['total_cookies']                   = isset( $raw_data['total_cookies'] ) ? absint( $raw_data['total_cookies'] ) : 0;
				$cookies[]                               = $data;
			}
		}
		if ( $cookies ) {
			$out['cookies'] = $cookies;
		}
		return $out;
	}

	/*
	*
	* Taking existing cookie list (Manually created and Inserted via scanner)
	*/
	public static function get_cookie_list()
	{
		$args=array(
			'numberposts'=>-1,
			'post_type'=>CLI_POST_TYPE,
			'orderby'=>'ID',
			'order'=>'DESC'
		);
		return get_posts($args);
	}

	/*
	*
	* Delete all previous scan records
	*/
	public function flushScanRecords()
	{
		global $wpdb;
		$table_name=$wpdb->prefix.$this->scan_table; 
		$wpdb->query("TRUNCATE TABLE $table_name");
		$table_name=$wpdb->prefix.$this->url_table;
		$wpdb->query("TRUNCATE TABLE $table_name");
		$table_name=$wpdb->prefix.$this->cookies_table;
		$wpdb->query("TRUNCATE TABLE $table_name");
	}
	public function scanner_notices(){

		$license_url 		=	admin_url('edit.php?post_type=cookielawinfo&page=cookie-law-info#cookie-law-info-licence');
		if( $this->get_license_status() === false ) {
			$hide_scan_btn = true;
			echo '<div class="wt-cli-callout wt-cli-callout-alert"><p>'.__( 'Please activate your license in order to proceed with the scan.', 'webtoffee-gdpr-cookie-consent' ).' <a href="'.$license_url.'">'.__('Activate','webtoffee-gdpr-cookie-consent').'</a></p></div>';
		
		} else {

			if( $this->get_cookieyes_status() === false ) {
				$last_scan  = $this->get_last_scan();

				if( $last_scan ) {
					echo $this->get_cookieyes_scan_notice( true );
					echo $this->get_last_scan_info();
				} else {
					echo $this->get_cookieyes_scan_notice();
				}
			}
			else {
				if( $this->check_scan_status() === 1 ) {
					echo $this->get_scan_progress_html();
				} else {
					echo $this->get_last_scan_info();
				}
				
			}
		}
	}
	public function get_license_activated_message(){
		
	}
	public function get_cookieyes_scan_notice( $existing = false ){

		$ckyes_link 			 = 	'https://www.cookieyes.com/';
		$ckyes_privacy_policy 	 = 	'https://www.cookieyes.com/privacy-policy';
		$ckyes_terms_conditions  = 	'https://www.cookieyes.com/terms-and-conditions/';
		$notice  = '<div class="wt-cli-callout wt-cli-callout-info" style="font-weight: 500;">';

		if( $existing === true  ) { // Existing user so show this notice
			$notice .= '<p>'.sprintf( wp_kses( __( 'GDPR Cookie Consent now uses <a href="%s" target="_blank">CookieYes</a> to bring you enhanced scanning for cookies on your website! To further avail quick and accurate scanning of your website, connect with CookieYes, in just a click! By continuing, you agree to CookieYes\'s <a href="%s" target="_blank">Privacy Policy</a> & <a href="%s" target="_blank">Terms of service</a>.', 'webtoffee-gdpr-cookie-consent' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $ckyes_link ), esc_url( $ckyes_privacy_policy ), esc_url( $ckyes_terms_conditions ) ).'</p></div>';
		} else {
			$notice .= '<p>'.__('Scan your website for cookies automatically with CookieYes!','webtoffee-gdpr-cookie-consent').'</p>';
			$notice .= '<p>'.sprintf( wp_kses( __( 'Connect with <a href="%s" target="_blank">CookieYes</a>, our scanning solution to get fast and accurate cookie scanning. By continuing, you agree to CookieYes\'s <a href="%s" target="_blank">Privacy Policy</a> & <a href="%s" target="_blank">Terms of service</a>.', 'webtoffee-gdpr-cookie-consent' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $ckyes_link ), esc_url( $ckyes_privacy_policy ), esc_url( $ckyes_terms_conditions ) ).'</p></div>';
			$notice .= '<a id="wt-cli-ckyes-connect-scan" class="button-primary pull-right">'.__('Connect & scan','webtoffee-gdpr-cookie-consent').'</a>';
		}
		
		return $notice;
	}
	/**
	 * Get last scan info
	 *
	 * @return string
	 */
	public function get_last_scan_info() {

		$last_scan = $this->get_last_scan();

		$scan_notice = $this->get_scan_default_html();
		$show_results = false;

		if ( $last_scan ) {
			$scan_status = intval( ( isset( $last_scan['status'] ) ? $last_scan['status'] : 0 ) );
			if ( 2 === $scan_status ) {
				$scan_notice = $this->get_scan_success_html( $last_scan );
				$show_results = true;
			} elseif ( 3 === $scan_status ) {
				$scan_notice = $this->get_scan_abort_html( $last_scan );
			} elseif ( 4 === $scan_status ) {
				$scan_notice = $this->get_scan_failed_html( $last_scan );
			}
		}
		$notice  = '<div class="wt-cli-cookie-scan-container">';
		$notice .= '<div class="wt-cli-cookie-scanner-actions">' . apply_filters( 'wt_cli_ckyes_account_widget', '' ) . '</div>';
		$notice .= $scan_notice;
		$notice .= '</div>';
		$notice .= '<div class="wt-cli-cookie-scanner-actions">' . $this->get_scan_btn() . '</div>';
		if( true === $show_results ) {
			$notice .= $this->get_scan_result_table();
		}
		return $notice;

	}
	public function get_scan_default_html(){
		$scan_notice	=	'<div class="wt-cli-callout wt-cli-callout-info"><p>'.__('You haven\'t performed a site scan yet.','webtoffee-gdpr-cookie-consent').'</p></div>';
		return $scan_notice;
	}
	public function get_scan_failed_html( $scan_data ){
		$last_scan_date =  ( isset( $scan_data['created_at'] ) ? $scan_data['created_at'] : '' );
		if( !empty(( $last_scan_date ))) {
			$last_scan_date  = date( 'F j, Y g:i a T',$last_scan_date );
		}
		$scan_notice	=	'<div class="wt-cli-callout wt-cli-callout-warning">';
		$scan_notice   .=	'<div class="wt-cli-scan-status"><p><b>'.__('Scan failed','webtoffee-gdpr-cookie-consent').'</b></p></div>';
		$scan_notice   .=	'<div class="wt-cli-scan-date"><p style="color: #80827f;">'.__("Last scan:","webtoffee-gdpr-cookie-consent").' '.$last_scan_date.'</p></div>';
		$scan_notice   .=	'</div>';
		return $scan_notice;
	}
	public function get_scan_success_html( $scan_data ){

		$scan_notice 	=  '';
		$result_page 	=  admin_url('edit.php?post_type='.CLI_POST_TYPE.'&page=cookie-law-info-cookie-scaner&scan_result');
		$last_scan_date =  ( isset( $scan_data['created_at'] ) ? $scan_data['created_at'] : '' );
		if( !empty(( $last_scan_date ))) {
			$last_scan_date  = date( 'F j, Y g:i a T',$last_scan_date );
		}
		$last_scan_text =  sprintf(  __( 'Last scan: %s', 'webtoffee-gdpr-cookie-consent' ),$last_scan_date );
		$scan_notice	=	'<div class="wt-cli-callout wt-cli-callout-success">';
		$scan_notice   .=	'<div class="wt-cli-scan-status"><p><b>'.__('Scan complete','webtoffee-gdpr-cookie-consent').'</b></p></div>';
		$scan_notice   .=	'<div class="wt-cli-scan-date"><p style="color: #80827f;">'.$last_scan_text.'</p></div>';
		$scan_notice   .=	'</div>';

		return $scan_notice;

	}
	public function get_scan_action( $scan_btn_id = '', $scan_btn_text = '')  {
		return '<a id="'.$scan_btn_id.'" class="button-primary pull-right cli_scan_now">'.$scan_btn_text.'</a>';
	}
	/**
	 * Scan abort HTML
	 *
	 * @since 2.3.4
	 * @param array $scan_data scan result.
	 * @return string
	 */
	public function get_scan_abort_html( $scan_data ) {
		$last_scan_date = ( isset( $scan_data['created_at'] ) ? $scan_data['created_at'] : '' );

		if ( ! empty( ( $last_scan_date ) ) ) {
			$last_scan_date = date( 'F j, Y g:i a T', $last_scan_date );
		}

		$message  = '<div class="wt-cli-scan-status"><p><b>' . __( 'Scan aborted', 'webtoffee-gdpr-cookie-consent' ) . '</b></p></div>';
		$message .= '<div class="wt-cli-scan-date"><p style="color: #80827f;">' . __( 'Last scan:', 'webtoffee-gdpr-cookie-consent' ) . ' ' . $last_scan_date . '</p></div>';
		return Cookie_Law_Info_Admin::wt_cli_admin_notice( 'alert', $message, true );
	}

	public function get_scan_progress_html(){
		$last_scan                = $this->get_last_scan();
		$total_urls               = ( isset( $last_scan['total_url'] ) ? $last_scan['total_url'] : 0 );
		$last_scan_timestamp      = ( isset( $last_scan['created_at'] ) ? $last_scan['created_at'] : '' );
		$scan_estimate_in_seconds = $this->get_ckyes_scan_estimate();
		$scan_estimate            = date( 'H:i:s', $scan_estimate_in_seconds );
		$last_scan_date           = '';
		if ( ! empty( ( $last_scan_timestamp ) ) ) {
			$last_scan_date = date( 'F j, Y g:i a T', $last_scan_timestamp );
		}
		
		$html	=	'<div class="wt-cli-scan-status-container" style="">
						<div class="wt-cli-row">
							<div class="wt-cli-col-5">
								<div class="wt-cli-row">
									<div class="wt-cli-col-5">
										<div class="wt-cli-scan-status-bar" style="display:flex;align-items:center; color: #2fab10;">
											<span class="wt-cli-status-icon wt-cli-status-success"></span><span style="margin-left:10px">' . __( 'Scan initiated...', 'webtoffee-gdpr-cookie-consent' ) . '</span>
										</div>
									</div>
									<div class="wt-cli-col-7"><a id="wt-cli-cookie-scan-abort" href="#">' . __( 'Abort scan', 'webtoffee-gdpr-cookie-consent' ) . '</a></div>
								</div>
							</div>
						</div>
					</div>
					<div class="wt-scan-status-info">
						<div class="wt-cli-row">
							<div class="wt-cli-col-5">
								<div class="wt-scan-status-info-item">
									<div class="wt-cli-row">
										<div class="wt-cli-col-5">
											<b>'.__('Scan started at','webtoffee-gdpr-cookie-consent').':</b> 
										</div>
										<div class="wt-cli-col-7">'.$last_scan_date.'</div>
									</div>
								</div>
								<div class="wt-scan-status-info-item">
									<div class="wt-cli-row">
										<div class="wt-cli-col-5">
											<b>'.__('Total URLs','webtoffee-gdpr-cookie-consent').':</b> 
										</div>
										<div class="wt-cli-col-7">'.$total_urls.'</div>
									</div>
								</div>
								<div class="wt-scan-status-info-item">
									<div class="wt-cli-row">
										<div class="wt-cli-col-5">
											<b>'.__('Total estimated time (Approx)','webtoffee-gdpr-cookie-consent').':</b> 
										</div>
										<div class="wt-cli-col-7">'.$scan_estimate.'</div>
									</div>	
								</div>
							</div>
						</div>
					</div>
					<div class="wt-cli-notice wt-cli-info">'.__('Your website is currently being scanned for cookies. This might take from a few minutes to a few hours, depending on your website speed and the number of pages to be scanned.','webtoffee-gdpr-cookie-consent').
					
					'</br><b>'.__('Once the scanning is complete, we will notify you by email.','webtoffee-gdpr-cookie-consent').'</b></div>
					';
		return $html;
	}
	/**
	* Check whether a scanning has initiated or not
	*
	* @since  2.3.3
	* @access public
	* @return bool
	*/
	public function check_scan_status(  ){
		$last_scan 		= 	$this->get_last_scan();
		$status			=	( isset( $last_scan['status'] ) ? $last_scan['status'] : 0 );
		if( $this->get_cookieyes_status() === 0 || $this->get_cookieyes_status() === false ) { 
			$status = 0;
		}
		return intval( $status );
	}
	
	public function fetch_scan_result( $request ){
		
		if( isset( $request) && is_object( $request )) {
			$request_body = $request->get_body();
			if( !empty( $request_body )) {
				if( is_wp_error( $this->save_cookie_data( json_decode( $request_body, true ) ) ) ) {
					wp_send_json_error( __('Token mismatch','webtoffee-gdpr-cookie-consent') );
				}
				wp_send_json_success( __('Successfully inserted','webtoffee-gdpr-cookie-consent') );
			}
		}
		wp_send_json_error( __('Failed to insert','webtoffee-gdpr-cookie-consent') );
	}

	/**
	 * Save cookie data to cookies table
	 *
	 * @param array $cookie_data Array of data.
	 * @return WP_Error|void
	 */
	public function save_cookie_data( $cookie_data ) {

		global $wpdb;
		$url_table = $wpdb->prefix . $this->url_table;
		$scan_id   = $this->get_last_scan_id();
		$scan_urls = array();

		if ( $cookie_data ) {
			if ( $scan_id !== false ) {

				$sql  = $wpdb->prepare( "SELECT id_cli_cookie_scan_url,url FROM $url_table WHERE id_cli_cookie_scan = %s ORDER BY id_cli_cookie_scan_url ASC", $scan_id );
				$urls = $wpdb->get_results( $sql, ARRAY_A );
				foreach ( $urls as $url_data ) {
					$url               = isset( $url_data['url'] ) ? sanitize_text_field( $url_data['url'] ) : '';
					$scan_urls[ $url ] = isset( $url_data['id_cli_cookie_scan_url'] ) ? absint( $url_data['id_cli_cookie_scan_url'] ) : 1;
				}
				$scan_data         = ( isset( $cookie_data['scan_result'] ) ? json_decode( $cookie_data['scan_result'], true ) : array() );
				$scan_result_token = ( isset( $cookie_data['scan_result_token'] ) ? $cookie_data['scan_result_token'] : array() );

				if ( $this->validate_scan_instance( $scan_result_token ) === false ) {
					return new WP_Error( 'invalid', __( 'Invalid scan token', 'cookie-law-info' ) );
				}
				$this->insert_categories( $scan_data );
				foreach ( $scan_data as $key => $data ) {
					$cookies  = ( isset( $data['cookies'] ) && is_array( $data['cookies'] ) ) ? $data['cookies'] : array();
					$category = ( isset( $data['category'] ) ? $data['category'] : '' );

					if ( ! empty( $cookies ) ) {
						$this->insert_cookies( $scan_id, $scan_urls, $cookies, $category );
					}
				}
			}
		}
		$this->finish_scan( $scan_id );
	}
	public function finish_scan( $scan_id ){
		$scan_data	=	array(
			'current_action'	=>	'scan_pages',
			'current_offset'	=> 	-1,
			'status'			=>	2
		);
		$this->set_ckyes_scan_status( 2 );
		$this->update_scan_entry( $scan_data, $scan_id );
		$this->reset_scan_token();
	}

	public function validate_scan_instance( $instance ){
		$last_instance = $this->get_ckyes_scan_instance();
		if ( ( 0 !== $instance ) && !empty( $instance ) && ( $instance === $last_instance ) ) {
			return true;
		}
		return false;
	}
	
	public function get_last_scan_id(){
		$last_scan 		= 	$this->get_last_scan();
		$scan_id		=	( isset( $last_scan['id_cli_cookie_scan'] ) ? $last_scan['id_cli_cookie_scan'] : false );
		return $scan_id;
	}

	/**
	 * Insert all the cookie categories.
	 *
	 * @param array $categories Array of cookie categories.
	 * @return void
	 */
	protected function insert_categories( $categories ) {
		global $wpdb;
		$cat_table = $wpdb->prefix . $this->category_table;
		$cat_arr   = array();
		$cat_sql   = "INSERT IGNORE INTO `$cat_table` (`cli_cookie_category_name`,`cli_cookie_category_description`) VALUES ";

		foreach ( $categories as $id => $category_data ) {
			$category    = ( isset( $category_data['category'] ) ? esc_sql( sanitize_text_field( $category_data['category'] ) ) : '' );
			$description = ( isset( $category_data['category_desc'] ) ? esc_sql( addslashes( sanitize_textarea_field( $category_data['category_desc'] ) ) ) : '' );

			if ( ! empty( $category ) ) {
				$cat_arr[] = "('$category','$description')";
			}
		}
		$cat_sql = $cat_sql . implode( ',', $cat_arr );
		if ( ! empty( $cat_arr ) ) {
			$wpdb->query( $cat_sql );
		}
	}
	/**
	 * Insert the scanned Cookies to the corresponding table
	 *
	 * @param int    $scan_id scan Id.
	 * @param array  $urls scanned URLs.
	 * @param array  $cookie_data scanned cookies.
	 * @param string $category category.
	 * @return void
	 */
	protected function insert_cookies( $scan_id, $urls, $cookie_data, $category ) {
		global $wpdb;
		$cookie_table   = $wpdb->prefix . $this->cookies_table;
		$category_table = $wpdb->prefix . $this->category_table;

		$sql = "INSERT IGNORE INTO `$cookie_table` (`id_cli_cookie_scan`,`id_cli_cookie_scan_url`,`cookie_id`,`expiry`,`type`,`category`,`category_id`,`description`) VALUES ";

		$sql_arr = array();

		foreach ( $cookie_data as $cookies ) {
			if ( is_array( $cookies ) && ! empty( $cookies ) ) {
				$cookie_id   = isset( $cookies['cookie_id'] ) ? esc_sql( sanitize_text_field( $cookies['cookie_id'] ) ) : '';
				$description = isset( $cookies['description'] ) ? esc_sql( sanitize_textarea_field( $cookies['description'] ) ) : '';
				$expiry      = isset( $cookies['duration'] ) ? esc_sql( sanitize_text_field( $cookies['duration'] ) ) : '';
				$type        = isset( $cookies['type'] ) ? esc_sql( sanitize_text_field( $cookies['type'] ) ) : '';
				$category    = esc_sql( sanitize_text_field( $category ) );
				$url_id      = ( isset( $cookies['frist_found_url'] ) ? esc_sql( sanitize_text_field( $cookies['frist_found_url'] ) ) : '' );
				$url_id      = ( isset( $urls[ $url_id ] ) ? esc_sql( $urls[ $url_id ] ) : 1 );
				$category_id = $wpdb->get_var( $wpdb->prepare( "SELECT `id_cli_cookie_category` FROM `$category_table` WHERE `cli_cookie_category_name` = %s;", array( $category ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				$category_id = esc_sql( absint( $category_id ) );
				$sql_arr[]   = "('$scan_id','$url_id','$cookie_id','$expiry','$type','$category','$category_id','$description')";
			}
		}

		$sql = $sql . implode( ',', $sql_arr );
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
	}
	/**
	 * Return HTML table structure for listing cookies
	 *
	 * @param [type] $cookies The cookie list.
	 * @return string
	 */
	public function create_cookies_table( $cookies ) {

		$count = 1;
		$html  = '<table class="wt-cli-table">';
		$html .= '<thead>';
		$html .= '<th style="width: 6%;">' . __( 'Sl.No:', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		$html .= '<th>' . __( 'Cookie Name', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		$html .= '<th style="width:15%;" >' . __( 'Duration', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		$html .= '<th style="width:15%;" >' . __( 'Category', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		$html .= '<th style="width:40%;" >' . __( 'Description', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		$html .= '</thead>';
		$html .= '<tbody>';

		if ( isset( $cookies ) && is_array( $cookies ) && count( $cookies ) > 0 ) :
			foreach ( $cookies as $cookie ) :
				$html .= '<tr>';
				$html .= '<td>' . $count . '</td>';
				$html .= '<td>' . $cookie['id'] . '</td>';
				$html .= '<td>' . $cookie['expiry'] . '</td>';
				$html .= '<td>' . $cookie['category'] . '</td>';
				$html .= '<td>' . $cookie['description'] . '</td>';
				$html .= '</tr>';
				$count ++;
			endforeach;
		else :
			$html .= '<tr><td class="colspanchange" colspan="5" style="text-align:center" >' . __( 'Your cookie list is empty', 'webtoffee-gdpr-cookie-consent' ) . '</td></tr>';
		endif;

		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}
	/**
	 * Return last scan history
	 *
	 * @param [type] $id Scan ID.
	 * @return array
	 */
	public function get_last_scan_result() {

		$last_scan    = $this->get_last_scan();
		$scan_results = array();

		if ( $last_scan && isset( $last_scan['id_cli_cookie_scan'] ) ) {
			$scan_results = $this->get_scan_results_by_id( $last_scan['id_cli_cookie_scan'] );
		}

		return $scan_results;
	}
	/**
	 * Retuns scan results by ID
	 *
	 * @param [type] $id scan ID.
	 * @return array
	 */
	public function get_scan_results_by_id( $id ) {

		$data            = array();
		$scan_info       = $this->get_scan_history( $id );
		$scan_urls       = $this->get_scan_urls( $id );
		$scan_cookies    = $this->get_scan_cookies( $id );
		$data['scan_id'] = $id;
		$data['date']    = isset( $scan_info['created_at'] ) ? $scan_info['created_at'] : '';
		$data['status']  = isset( $scan_info['status'] ) ? $scan_info['status'] : '';

		$data['urls']       = isset( $scan_urls['urls'] ) ? $scan_urls['urls'] : '';
		$data['total_urls'] = isset( $scan_urls['total'] ) ? $scan_urls['total'] : '';

		$data['cookies']       = isset( $scan_cookies['cookies'] ) ? $this->process_cookies( $scan_cookies['cookies'] ) : '';
		$data['total_cookies'] = isset( $scan_cookies['total'] ) ? $scan_cookies['total'] : '';

		return $data;
	}
	/**
	 * Process cookies and re order by description.
	 *
	 * @param array $raw_cookie_data raw cookie data.
	 * @return array
	 */
	public function process_cookies( $raw_cookie_data ) {
		$cookies                   = array();
		$cookie_has_description    = array();
		$cookie_has_no_description = array();
		$count                     = 0;
		foreach ( $raw_cookie_data  as $key => $data ) {
			$cookie_data = array(

				'id'          => isset( $data['cookie_id'] ) ? $data['cookie_id'] : '',
				'type'        => isset( $data['type'] ) ? $data['type'] : '',
				'expiry'      => isset( $data['expiry'] ) ? $data['expiry'] : '',
				'category'    => isset( $data['category'] ) ? $data['category'] : '',
				'description' => isset( $data['description'] ) ? $data['description'] : '',
			);
			if ( '' === $cookie_data['description'] || 'No description' === $cookie_data['description'] ) {
				$cookie_has_no_description[ $count ] = $cookie_data;
			} else {
				$cookie_has_description[ $count ] = $cookie_data;
			}
			$count ++;
		}
		$cookies = $cookie_has_description + $cookie_has_no_description;
		return $cookies;
	}
	/**
	 * Return last scan history
	 *
	 * @param [type] $id Scan ID.
	 * @return array
	 */
	public function get_scan_history( $id ) {
		global $wpdb;
		$scan_table                 = $wpdb->prefix . $this->scan_table;
		$data                       = array();
		$sql                        = $wpdb->prepare(
			"SELECT * FROM {$scan_table} WHERE `id_cli_cookie_scan` = %d",
			$id
		);
		$raw_data                   = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		
		if ( $raw_data ) {

			$data['id_cli_cookie_scan'] = isset( $raw_data['id_cli_cookie_scan'] ) ? absint( $raw_data['id_cli_cookie_scan'] ) : 0;
			$data['status']             = isset( $raw_data['status'] ) ? absint( $raw_data['status'] ) : 1;
			$data['created_at']         = isset( $raw_data['created_at'] ) ? sanitize_text_field( $raw_data['created_at'] ) : '';
			$data['total_url']          = isset( $raw_data['total_url'] ) ? absint( $raw_data['total_url'] ) : 0;
			$data['total_cookies']      = isset( $raw_data['total_cookies'] ) ? absint( $raw_data['total_cookies'] ) : 0;
			$data['current_action']     = isset( $raw_data['current_action'] ) ? sanitize_text_field( $raw_data['current_action'] ) : '';
			$data['current_offset']     = isset( $raw_data['current_offset'] ) ? (int) $raw_data['current_offset'] : -1;
			
			return $data;
		}
		return false;
	}
	public function get_scan_btn( $strict = false ) {

		$last_scan     = $this->get_last_scan();
		$scan_btn_id   = 'wt-cli-ckyes-scan';
		$scan_btn_text = __( 'Scan website for cookies', 'webtoffee-gdpr-cookie-consent' );
		$scan_status   = intval( ( isset( $last_scan['status'] ) ? $last_scan['status'] : 0 ) );
		$show_btn      = true;

		if ( 2 === $scan_status ) {
			$show_btn = false;
		}
		if ( true === $strict ) {
			$scan_btn_text = __( 'Scan again', 'webtoffee-gdpr-cookie-consent' );
			$show_btn      = true; // Override the existing settings.
		}
		if ( $this->get_cookieyes_status() === 0 || $this->get_cookieyes_status() === false ) { // Disconnected with Cookieyes after registering account.
			$scan_btn_id   = 'wt-cli-ckyes-connect-scan';
			$scan_btn_text = __( 'Connect & scan', 'webtoffee-gdpr-cookie-consent' );
			$show_btn      = true;
			if ( true === $strict ) {
				$show_btn = false;
			}
		} else if( $this->get_cookieyes_status() === 2 ) {
			$show_btn      = true;
		}

		return ( true === $show_btn ? '<a id="' . $scan_btn_id . '" class="wt-cli-cookie-scan-btn button-primary pull-right">' . $scan_btn_text . '</a>' : '' );

	}
	public function get_scan_result_table() {

		ob_start();
		$scan_results     = $this->get_last_scan_result();
		$cookie_list_page = admin_url( 'edit.php?post_type=' . CLI_POST_TYPE );
		$scan_status      = intval( ( isset( $scan_results['status'] ) ? $scan_results['status'] : 0 ) );
		$scan_page_url    = admin_url( 'edit.php?post_type=' . CLI_POST_TYPE . '&page=cookie-law-info-cookie-scaner' );
		$export_page_url  = $scan_page_url.'&cli_scan_export=';

		if ( 2 === $scan_status ) {
			include plugin_dir_path( __FILE__ ) . 'views/scan-result.php';
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	/**
	 * Check whether table exist or not
	 *
	 * @param string $table_name table name.
	 * @return bool
	 */
	public function table_exists( $table_name ) {
		global $wpdb;

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) === $table_name ) {
			return true;
		}
		return false;
	}

}
new Cookie_Law_Info_Cookie_Scaner();