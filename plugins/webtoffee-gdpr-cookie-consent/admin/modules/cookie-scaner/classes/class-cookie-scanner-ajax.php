<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Cookie_Law_Info_Cookie_Scanner_Ajax extends Cookie_Law_Info_Cookie_Scaner
{
	
	public function __construct()
	{		
		add_action('wp_ajax_cli_cookie_scaner',array($this,'ajax_cookie_scaner'));
		add_action( 'wt_cli_ckyes_abort_scan', array( $this, 'update_abort_status'));
		$url_per_request=get_option('cli_cs_url_per_request');
        if(!$url_per_request)
        {
            $url_per_request=5;
        }
        $this->scan_page_mxdata=$url_per_request;
	}

	/*
	*
	* Main Ajax hook for processing requests
	*/
	public function ajax_cookie_scaner()
	{	
		if (!current_user_can('manage_options')) 
		{
		    wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
		}
		check_ajax_referer('cli_cookie_scaner','security');
		$out=array(
			'response'=>false,
			'message'=>__('Unable to handle your request.','webtoffee-gdpr-cookie-consent'),
		);
		if(isset($_POST['cli_scaner_action']))
		{
			$cli_scan_action = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( $_POST['cli_scaner_action'] ); ;
			$allowed_actions = array(
				'get_pages',
				'scan_pages',
				'stop_scan',
				'import_now',
				'check_api',
				'report_now',
				'connect_scan',
				'next_scan_id',
				'bulk_scan',
				'check_status',
				'fetch_result',
				'get_scan_html',
				
			);
			if(in_array($cli_scan_action,$allowed_actions) && method_exists($this,$cli_scan_action))
			{
				$out=$this->{$cli_scan_action}();
			}
		}
		echo json_encode($out);
		exit();
	}

	/*
	* Send Cookie serve API un avaialable report
	*
	*
	*/
	public function report_now()
	{
		$to="support@webtoffee.com";
		$sub="Cookie server API unavailable.";
		$msg="Cookie serve API is down. <br /> Site URL: ".site_url();

		$cli_activation_status=get_option(CLI_ACTIVATION_ID.'_activation_status');
		if($cli_activation_status) //if activated then send user registration email
		{
			$reg_email=get_option(CLI_ACTIVATION_ID.'_email');
			$msg.="<br /> Registered email: ".$reg_email;
		}

		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail($to,$sub,$msg,$headers);
	}

	/*
	* Cookie serve API is avaialable or not
	*
	*/
	public function check_api()
	{
		$error_head='<h3 style="color:#333;">'.__('Sorry...','webtoffee-gdpr-cookie-consent').'</h3>';
		$report_now='&nbsp; <a class="button-primary cli_scanner_send_report">Yes</a>&nbsp;<a class="button-secondary cli_scanner_not_send_report">No</a>';
		$out=array(
			'message'=>$error_head.__("Cookie Scanner API is not available now. Please try again later. <br />Do you want to report this to developer and get notified?","webtoffee-gdpr-cookie-consent").$report_now,
			'response'=>false,
		);
		//cookie serve API
        include( plugin_dir_path( __FILE__ ).'class-cookie-serve.php');
        $cookie_serve_api=new Cookie_Law_Info_Cookie_Serve_Api();
        if($cookie_serve_api->check_server())
        {
        	$out['response']=true;
        	$out['message']=__("Success","webtoffee-gdpr-cookie-consent");
        }
		return $out;
	}

	/*
	*
	*	Import Cookies (Ajax-main)
	*/
	public function import_now()
	{	
		$scan_id = Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['scan_id']) ? $_POST['scan_id'] : 0 ),'int'); 
		$out=array(
			'response'=>false,
			'scan_id'=>$scan_id,
			'message'=>__('Unable to handle your request','webtoffee-gdpr-cookie-consent'),
		);
		if(!current_user_can('manage_options'))
		{
			$out['message']=__('You do not have sufficient permissions to access this page.', 'webtoffee-gdpr-cookie-consent');
			return $out;
		}
		$deleted=0;
		$skipped=0;
		$added=0;
		$scan_id = Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['scan_id']) ? $_POST['scan_id'] : 0 ),'int'); 
		$import_option= Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['import_option']) ? $_POST['import_option'] : 2 ),'int');
		if($scan_id>0)
		{
			$cookies=$this->get_scan_cookies($scan_id,0,-1); // taking cookies
			if($cookies['total']>0)
			{
				if($import_option==1) //replace old (Delete all old cookies)
				{
					$all_cookies= get_posts(array('post_type'=>CLI_POST_TYPE,'numberposts'=>-1) );
					foreach($all_cookies as $cookie) 
					{
						$deleted++;
						wp_delete_post($cookie->ID,true);
					}
				}
				foreach($cookies['cookies'] as $cookie)
				{
					
					$skip=false;
					if($import_option==2) //merge - skip the insertion of existing cookies
					{
						$existing_cookie=get_posts(array('name' =>$cookie['cookie_id'],'post_type' =>CLI_POST_TYPE));
						if(!empty($existing_cookie))
						{	
							$cli_post=$existing_cookie[0];
							if(empty($cli_post->post_content))
							{	
								$post_data = array(
									'ID'           => $cli_post->ID,
									'post_content' => $cookie['description'],
								);
								wp_update_post( $post_data );
							}
							$skipped++;
							$skip=true;	
						}
					}
					if($skip===false) //adding new cookies
					{
						$added++;
						$cookie_data = array(
			                'post_type' => CLI_POST_TYPE,
			                'post_title' => $cookie['cookie_id'],
			                'post_content' =>$cookie['description'],
			                'post_status' => 'publish',
			                'ping_status' => 'closed',
			                'post_excerpt' => $cookie['cookie_id'],
			                'post_author' => 1,
			            );
			            $post_id = wp_insert_post($cookie_data);
			            update_post_meta($post_id, '_cli_cookie_duration',$cookie['expiry']);
			            update_post_meta($post_id, '_cli_cookie_sensitivity','non-necessary');
			            update_post_meta($post_id, '_cli_cookie_slugid',$cookie['cookie_id']);
			            update_post_meta($post_id, '_cli_cookie_headscript_meta', "");
						update_post_meta($post_id, '_cli_cookie_bodyscript_meta', "");
						wp_set_object_terms($post_id, array($cookie['category']), 'cookielawinfo-category', true);
						
						// Import Categories 
						$category = get_term_by('name', $cookie['category'], 'cookielawinfo-category');
						// Check if category exist
						if($category && is_object($category))
						{	
							
							$category_id=$category->term_id;
							$category_description = $category->description;

						
							// Check if catgory has description
							if(empty($category_description))
							{	
								$description = $cookie['cli_cookie_category_description'];
								$category_slug          = $category->slug;
								$cookie_audit_shortcode = sprintf( '[cookie_audit category="%s" style="winter" columns="cookie,duration,description"]', $category_slug );
								$description           .= "\n";
								$description           .= $cookie_audit_shortcode;
								wp_update_term($category_id, 'cookielawinfo-category', array(
									'description' => $description,
								));
							}
						
						}
					}
				}
				
				//preparing response message based on choosed option
				$out_message=$added.' '.__('cookies added.','webtoffee-gdpr-cookie-consent');
				if($import_option==2) //merge
				{
					$out_message.=' '.$skipped.' '.__('cookies skipped.','webtoffee-gdpr-cookie-consent');
				}
				if($import_option==1) //replace old
				{
					$out_message.=' '.$deleted.' '.__('cookies deleted.','webtoffee-gdpr-cookie-consent');
				}
				$out['response']=true;
				$out['message']=$out_message;
			}else
			{
				$out['response']=false;
				$out['message']=__('No cookies found','webtoffee-gdpr-cookie-consent');
			}
		}
		return $out;
	}
	/*
	*
	*	Taking public pages of the website (Ajax-main)
	*/
	public function get_pages()
	{
		global $wpdb;
		
		$mxdata=$this->fetch_page_mxdata;
		//taking query params
		$offset = Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['offset']) ? $_POST['offset'] : 0 ),'int');
		$scan_id = Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['scan_id']) ? $_POST['scan_id'] : 0 ),'int');
		$total = Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['total']) ? $_POST['total'] : 0 ),'int');
		$wt_cli_site_host = $this->wt_cli_get_host( get_site_url() );
		
		$out=array(
			'log'=>array(),
			'total'=>$total,
			'offset'=>$offset,
			'limit'=>$mxdata,
			'scan_id'=>$scan_id,
			'response'=>true,
		);		
		
		$sql = $this->get_scan_pages_query();
		
		if($total==0) //may be this is first time
		{
			//taking total
			$total_rows=$wpdb->get_row("SELECT COUNT(ID) AS ttnum".$sql,ARRAY_A);
			$total=$total_rows ? $total_rows['ttnum']+1 : 1; //always add 1 becuase home url is there
			$out['total'] = apply_filters( 'wt_cli_cookie_scanner_urls', $total);
		}
		if($scan_id==0) //first scan, create scan entry and add home url
		{	
			$this->set_ckyes_scan_status( 0 );
			$scan_id=$this->createScanEntry($total);
			$out['scan_id']=$scan_id;
			$out['log'][]=get_home_url();
            $this->insert_url($scan_id,get_home_url());
		}


		//creating sql for fetching data
		$sql="SELECT post_name,post_title,post_type,ID".$sql." ORDER BY post_type='page' DESC LIMIT $offset,$mxdata";
		$data=$wpdb->get_results($sql,ARRAY_A);
		if(!empty($data))
		{
            foreach($data as $value) 
            {
				$permalink=get_permalink($value['ID']);
				$currrent_url_host = $this->wt_cli_get_host($permalink);

                if( ( $this->filter_url($permalink) ) && ( $currrent_url_host == $wt_cli_site_host ) )
                {	
                	$out['log'][]=$permalink;
                	$this->insert_url($scan_id,$permalink);
                }else
                {
                	$out['total']=$out['total']-1;
                } 
            }
        }
        //saving current action status
		$data_arr=array('current_action'=>'get_pages','current_offset'=>$offset,'status'=>1,'total_url'=>$out['total']);
		$this->update_scan_entry($data_arr,$scan_id);
		
	    return $out;
	}
	public function get_scan_pages_query( ) {
		global $wpdb;
		$post_table = $wpdb->prefix."posts";
		$post_types=get_post_types(array(
	    	'public'=>true,
	    ));
		unset($post_types['attachment']);
	    unset($post_types['revision']);
	    unset($post_types['custom_css']);
	    unset($post_types['customize_changeset']);
	    unset($post_types['user_request']);
		//generating sql conditions
		$sql=" FROM $post_table WHERE post_type IN('".implode("','",$post_types)."') AND post_status='publish'";
		return $sql;
	}
	public function get_total_page_count(){
		global $wpdb;
		$sql = $this->get_scan_pages_query();
		$total_rows = $wpdb->get_row("SELECT COUNT(ID) AS ttnum".$sql,ARRAY_A);
		$page_count = ( isset( $total_rows ) ? $total_rows : 0 );
		return $page_count;
	}
	/*
	*
	* Return site host name 
	* @return string
	* @since 2.2.4
	*/
	private function wt_cli_get_host($url)
	{
		$site_host = '';
		$parsed_url = parse_url($url);
		$site_host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		return $site_host;
	}
	/*
	*
	* Filtering non html URLS
	* @return boolean
	*/
	private function filter_url($permalink)
	{
		$url_arr=explode("/",$permalink);
		$end=trim(end($url_arr));
		if($end!="")
		{
			$url_end_arr=explode(".",$end);
			if(count($url_end_arr)>1)
			{
				$end_end=trim(end($url_end_arr));
				if($end_end!="")
				{
					$allowed=array('html','htm','shtml','php');
					if(!in_array($end_end,$allowed))
					{
						return false;
					}
				}
			}
		}
		return true;
	}
	/**
	* Create account with Cookieyes & Scan 
	*
	* @since  2.3.2
	* @access public
	* @throws Exception Error message.
	* @return string
	*/
	public function connect_scan(){

		$data 				= 	array();
		if( $this->get_cookieyes_status() === false ) {
			$response 		= 	$this->register();

		} else {
			$response 		=	$this->ckyes_connect();
		}
		$status 			=	( isset( $response['status'] ) ? $response['status'] : false );
		$status_code 		=	( isset( $response['code'] ) ? $response['code'] : '' );

		if( !empty( $status_code )) {
			$data['code']		= 	$status_code;
			$data['message']	=	$this->get_ckyes_message( $status_code );
		}
		if( $status === true ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error( $data );
	}

	public function next_scan_id(){
		
		$data 			=	array();
		$urls 			=	$this->get_total_page_count();
		$total_urls		=	intval( ( isset( $urls['ttnum'] ) ? $urls['ttnum'] : 0 ) );
		$total_urls     =	$total_urls + 1;
		
		$response 		=	$this->get_next_scan_id( $total_urls );
		$status 		=	( isset( $response['status'] ) ? $response['status'] : false );
		$status_code 	=	( isset( $response['code'] ) ? $response['code'] : '' );
		if( !empty( $status_code )) {
			$data['code']		= 	$status_code;
			$data['message']	=	$this->get_ckyes_message( $status_code );
		}
		if( $status === true ) {
			if( isset( $response['scan_id'] ) && isset( $response['scan_token'] ) ) {
				$this->set_ckyes_scan_id( $response['scan_id'] );
				$this->set_ckyes_scan_token( $response['scan_token'] );
			}
			wp_send_json_success( $data );
		}
		wp_send_json_error( $data );

	}
	
	public function bulk_scan(){
		
		include( plugin_dir_path( __FILE__ ).'class-cookie-serve.php');
		
		$scan_id 			= 	Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['scan_id']) ? $_POST['scan_id'] : 0 ),'int');
		$total 				= 	Wt_Cookie_Law_Info_Security_Helper::sanitize_item(( isset($_POST['total']) ? $_POST['total'] : 0 ),'int');
		
		$data_arr			=	array(
			'current_action'	=>	'bulk_scan',
			'current_offset'	=>	-1,
			'status'			=>	1
		);

		$cookie_serve_api	=	new Cookie_Law_Info_Cookie_Serve_Api();
		
		$urls				=	$this->get_urls( $scan_id );
		$data				=	array();
		$data['title']		=	'';
		$data['message']	=	__('Could not initiate scan','webtoffee-gdpr-cookie-consent');
		
		$response			=	$cookie_serve_api->fetch_all_cookies( $urls );	
		$response			=	json_decode( $response, true );
		
		if( $response !== false ) { 
			
			if( isset( $response['status']) && $response['status'] === 'initiated') {
				
				$this->update_scan_entry( $data_arr,$scan_id );
				$this->set_ckyes_scan_status( 1 );
				$estimate = ( isset( $response['estimatedTimeInSeconds'] ) ? $response['estimatedTimeInSeconds'] : 0 );
				
				$this->set_ckyes_scan_estimate( $estimate ); 
				$data['title']		=	__('Scanning initiated successfully','webtoffee-gdpr-cookie-consent');
				$data['message']	=	__('It might take a few minutes to a few hours to complete the scanning of your website. This depends on the number of pages to scan and the website speed. Once the scanning is complete, we will notify you by email.','webtoffee-gdpr-cookie-consent');
				$data['html']		=	$this->get_scan_progress_html();
				wp_send_json_success( $data );
			}
		}
		$this->update_failed_status();
		wp_send_json_error( $data );
	}

	public function get_urls( $scan_id ) {
		global $wpdb;
		$urls         = array();
		$url_table    = $wpdb->prefix . $this->url_table;
		$sql          = $wpdb->prepare( "SELECT id_cli_cookie_scan_url,url FROM $url_table WHERE id_cli_cookie_scan=%d ORDER BY id_cli_cookie_scan_url ASC", $scan_id );
		$urls_from_db = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $urls_from_db ) ) {
			foreach ( $urls_from_db as $data ) {
				if ( isset( $data['url'] ) ) {
					$urls[] = sanitize_text_field( $data['url'] );
				}
			}
		}
		return $urls;
	}
	public function check_status() {
		
		$scan_id 	 =	$this->get_ckyes_scan_id();
		$response 	 = 	$this->get_scan_status( $scan_id );
		$scan_status =  isset( $response['scan_status'] ) ? $response['scan_status'] : '';
		
		$data	     =  array(
			'status'	=> false,
			'refresh'	=>	false,
			'scan_id'	=>	$scan_id,
			'scan_status' => $scan_status,
		);

		if (isset($response['status']) && $response['status'] == 'error') {
			if (isset($response['error_code']) && $response['error_code'] == 1008 ) {
				$data['refresh'] = true;
				$this->update_failed_status();
			}
		} else {
			if( $scan_status === 'completed' ) {

				$data['refresh'] = true;
				$data['status'] = true;
				wp_send_json_success( $data );

			} else if( $scan_status === 'failed' || intval( $scan_id ) === 0 || $this->get_ckyes_scan_status() === 0 ) { // Scan id has expired or scan fails on Cookieyes
				$data['refresh'] = true;
				$this->update_failed_status();
				wp_send_json_error( $data );
			}
		}
		wp_send_json_error( $data );
	}

	public function fetch_result() {
		$scan_id 	 	=	$this->get_ckyes_scan_id();
		$scan_results 	=	$this->get_scan_results( $scan_id );
		if( is_wp_error( $this->save_cookie_data( $scan_results ) ) ) {
			$this->update_failed_status();
			wp_send_json_error( __('Token mismatch','webtoffee-gdpr-cookie-consent') );
		}
		wp_send_json_success();
	}

	public function update_failed_status(){
		$scan_id	=	$this->get_last_scan_id();
		$data_arr=array( 'status' => 4 ); //updating scan status to stopped
		$this->update_scan_entry($data_arr,$scan_id);
		update_option('CLI_BYPASS',0);
	}
	/**
	 * API to abort the scanning
	 *
	 * @return void
	 */
	public function stop_scan() {
		$scan_id       = $this->get_last_scan_id();
		$ckyes_scan_id = $this->get_ckyes_scan_id();
		$response      = $this->ckyes_abort_scan( $ckyes_scan_id );
		$status        = isset( $response['status'] ) ? $response['status'] : false;

		$data = array(
			'status'  => $status,
			'refresh' => false,
			'message' => '',
		);
		if ( true === $status ) {

			$data_arr = array( 'status' => 3 ); // Updating scan status to stopped.
			$this->update_scan_entry( $data_arr, $scan_id );
			update_option( 'CLI_BYPASS', 0 );
			$data['refresh'] = true;
			$data['message'] = __( 'Abort successfull', 'webtoffee-gdpr-cookie-consent' );
			wp_send_json_success( $data );

		} else {
			$data['message'] = __( 'Abort failed', 'webtoffee-gdpr-cookie-consent' );
		}
		wp_send_json_error( $data );
	}
	/**
	 * Get latest scan HTML
	 *
	 * @return void
	 */
	public function get_scan_html() {
		$data = array();
		if ( '' !== $this->scanner_notices( true ) ) {
			$data['scan_html'] = $this->scanner_notices( true );
			wp_send_json_success( $data );
		}
		wp_send_json_error( $data );
	}
	public function update_abort_status() {
		$scan_id  = $this->get_last_scan_id();
		$data_arr = array( 'status' => 3 ); // Updating scan status to stopped.
		$this->update_scan_entry( $data_arr, $scan_id );
		update_option( 'CLI_BYPASS', 0 );
	}
}
new Cookie_Law_Info_Cookie_Scanner_Ajax();