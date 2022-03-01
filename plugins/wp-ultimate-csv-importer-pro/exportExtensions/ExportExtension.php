<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/
namespace Smackcoders\WCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class ExportExtension {

	public $response = array();
	public $headers = array();
	public $module;	
	public $exportType = 'csv';
	public $optionalType = null;	
	public $conditions = array();	
	public $eventExclusions = array();
	public $fileName;	
	public $data = array();	
	public $heading = true;	
	public $delimiter = ',';
	public $enclosure = '"';
	public $auto_preferred = ",;\t.:|";
	public $output_delimiter = ',';
	public $linefeed = "\r\n";
	public $export_mode;
	public $export_log = array();
	public $limit;
	protected static $instance = null,$mapping_instance,$export_handler,$post_export,$woocom_export,$review_export,$ecom_export;
	protected $plugin,$activateCrm,$crmFunctionInstance;
	public $plugisnScreenHookSuffix=null;

	/**
	 * ExportExtension constructor.
	 * Set values into global variables based on post value
	 */
	public function __construct() {
		$this->plugin = Plugin::getInstance();
	}

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			ExportExtension::$mapping_instance = MappingExtension::getInstance();
			ExportExtension::$export_handler = ExportHandler::getInstance();
			ExportExtension::$post_export = PostExport::getInstance();
			ExportExtension::$woocom_export = WooCommerceExport::getInstance();
			ExportExtension::$review_export = CustomerReviewExport::getInstance();
			ExportExtension::$ecom_export = EComExport::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}	

	public  function doHooks(){
		add_action('wp_ajax_parse_data',array($this,'parseData'));
		add_action('wp_ajax_total_records', array($this, 'totalRecords'));
	}

	public function totalRecords(){
		global $wpdb;
		$module = $_POST['module'];
		$optionalType = isset($_POST['optionalType'])?$_POST['optionalType']:'';
		if(empty($optionalType)){
			$check_for_template = $wpdb->get_results("SELECT filename FROM {$wpdb->prefix}ultimate_csv_importer_export_template WHERE module = '$module' ");
		}else{
			$check_for_template = $wpdb->get_results("SELECT filename FROM {$wpdb->prefix}ultimate_csv_importer_export_template WHERE module = '$module' AND optional_type = '$optionalType' ");
		}
		
		$response = [];
		$elementor = false;
		if($module == 'CustomPosts'){
			if($optionalType == 'elementor_library'){
				$elementor = true;
			}
		}
		if(empty($check_for_template)){
			$response['show_template'] = false;
		}else{
			$response['show_template'] = true;
		}

		if ($module == 'WooCommerceOrders') {
			$module = 'shop_order';
		}
		elseif ($module == 'WooCommerceCoupons') {
			$module = 'shop_coupon';
		}
		elseif ($module == 'Marketpress') {
			$module = 'product';
		}
		elseif ($module == 'WooCommerceRefunds') {
			$module = 'shop_order_refund';
		}
		elseif ($module == 'WooCommerceVariations') {
			$module = 'product_variation';
		}
		elseif($module == 'WPeCommerceCoupons'){
			$query = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}wpsc_coupon_codes");
			$response['count'] = count($query);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Comments'){
			$response['count'] = $this->commentsCount();
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Images'){
			$get_images = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts where post_type='attachment'");
			$response['count'] = count($get_images);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Users'){
			$get_available_user_ids = "select DISTINCT ID from {$wpdb->prefix}users u join {$wpdb->prefix}usermeta um on um.user_id = u.ID";
			$availableUsers = $wpdb->get_col($get_available_user_ids);
			$response['count'] = count($availableUsers);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Tags'){
			$get_all_terms = get_tags('hide_empty=0');
			$response['count'] = count($get_all_terms);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Categories'){
			$get_all_terms = get_categories('hide_empty=0');
			$response['count'] = count($get_all_terms);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'Taxonomies'){
			$query = "SELECT * FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tax 
				ON  `tax`.term_id = `t`.term_id WHERE `tax`.taxonomy =  '{$optionalType}'";         
			$get_all_taxonomies =  $wpdb->get_results($query);
			$response['count'] = count($get_all_taxonomies);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'CustomPosts' && $optionalType == 'nav_menu_item'){
			$get_menu_ids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms AS t LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'nav_menu' ", ARRAY_A);
			$response['count'] = count($get_menu_ids);
			echo wp_json_encode($response);
			wp_die();
		}
		elseif($module == 'CustomPosts' && $optionalType == 'widgets'){
			$response['count'] = 1;
			echo wp_json_encode($response);
			wp_die();
		}
		else {
			if($module == 'CustomPosts') {
				$optional_type = $optionalType;
			}
			$optional_type=isset($optional_type)?$optional_type:'';
			$module = ExportExtension::$post_export->import_post_types($module,$optional_type);
		}
		if(is_plugin_active('jet-engine/jet-engine.php')){
			$get_slug_name = $wpdb->get_results($wpdb->prepare("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'"));
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				$optional_type=$value;	
				if($optionalType ==$optional_type){
					$table_name='wp_jet_cct_'.$optional_type;
					$get_menu= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE cct_status = 'publish'"));
					$response['count'] = count($get_menu);
			
						echo wp_json_encode($response);
						wp_die();
				}
			}
		}
		$get_post_ids = "select DISTINCT ID from {$wpdb->prefix}posts";
		$get_post_ids .= " where post_type = '$module'";
		if($module == 'shop_order'){
			$get_post_ids .= " and post_status in ('wc-completed','wc-cancelled','wc-on-hold','wc-processing','wc-pending')";
		}elseif ($module == 'shop_coupon') {
			$get_post_ids .= " and post_status in ('publish','draft','pending')";
		}elseif ($module == 'shop_order_refund') {

		}
		elseif($module == 'lp_order'){
			$get_post_ids .= " and post_status in ('lp-pending', 'lp-processing', 'lp-completed', 'lp-cancelled', 'lp-failed')";
		}
		else{
			$get_post_ids .= " and post_status in ('publish','draft','future','private','pending')";
		}
		$get_total_row_count = $wpdb->get_col($get_post_ids);
		$total = count($get_total_row_count);

		$response['count'] = $total;
		$response['elementor'] = $elementor;
		echo wp_json_encode($response);
		wp_die();
	}

	public  function parseData(){
		if(!empty($_POST)) {
			$categorybased = $_POST['categoryName'];
			$this->module          = $_POST['module'];
			$this->exportType      = isset( $_POST['exp_type'] ) ? sanitize_text_field( $_POST['exp_type'] ) : 'csv';
			$conditions =  str_replace("\\" , '' , $_POST['conditions']);
			$conditions = json_decode($conditions, True);

			$conditions['specific_period']['to'] = date("Y-m-d", strtotime($conditions['specific_period']['to']) );
			$conditions['specific_period']['from'] = date("Y-m-d", strtotime($conditions['specific_period']['from']) );
			$this->conditions      = isset( $conditions ) && ! empty( $conditions ) ? $conditions : array();
			if($this->module == 'Taxonomies' || $this->module == 'CustomPosts' ){
				$this->optionalType    = $_POST['optionalType'];
			}
			else{
				$this->optionalType    = $this->getOptionalType($this->module);
			}
			$eventExclusions = str_replace("\\" , '' , $_POST['eventExclusions']);
			$eventExclusions = json_decode($eventExclusions, True);
			$this->eventExclusions = isset( $eventExclusions ) && ! empty( $eventExclusions ) ? $eventExclusions : array();
			$this->fileName        = isset( $_POST['fileName'] ) ? sanitize_text_field( $_POST['fileName'] ) : '';
			if(empty($_POST['offset'] ) || $_POST['offset']== 'undefined'){
				$this->offset = 0 ;
			}
			else{
				$this->offset          = isset( $_POST['offset'] ) ? sanitize_text_field( $_POST['offset'] ) : 0;
			}
			if(!empty($_POST['limit'] )){
				$this->limit           = isset( $_POST['limit'] ) ? sanitize_text_field( $_POST['limit'] ) : 1000;
			}
			else{
				$this->limit           = 50;
			}
			if($this->optionalType == 'elementor_library'){
				$this->limit = 1;
			}
			if(!empty($this->conditions['delimiter']['optional_delimiter'])){
				$this->delimiter = $this->conditions['delimiter']['optional_delimiter'] ? $this->conditions['delimiter']['optional_delimiter']: ',';
			}
			elseif(!empty($this->conditions['delimiter']['delimiter'])){
				$this->delimiter = $this->conditions['delimiter']['delimiter'] ? $this->conditions['delimiter']['delimiter'] : ',';
				if($this->delimiter == '{Tab}'){
					$this->delimiter = " ";
				}
				elseif($this->delimiter == '{Space}'){
					$this->delimiter = " ";	
				}
			}

			$this->export_mode = 'normal';
			$this->checkSplit = isset( $_POST['is_check_split'] ) ? sanitize_text_field( $_POST['is_check_split'] ) : 'false';
			
			$time = date('Y-m-d h:i:s');
			$export_conditions = serialize($conditions);
			$export_event_exclusions = serialize(($eventExclusions));
			global $wpdb;


			$file_post_name = $_POST['fileName'];
			$post_module = $_POST['module'];
			$_POST['optionalType']=isset($_POST['optionalType'])?$_POST['optionalType']:'';
			$post_optional = $_POST['optionalType'];

			if(empty($post_optional)){
				$check_for_existing_template = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}ultimate_csv_importer_export_template WHERE filename = '$file_post_name' AND module = '$post_module' ");
			}
			else{
				$check_for_existing_template = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}ultimate_csv_importer_export_template WHERE filename = '$file_post_name' AND module = '$post_module' AND optional_type = '$post_optional' ");
			}

			if(empty($check_for_existing_template)){
				$wpdb->insert($wpdb->prefix.'ultimate_csv_importer_export_template',
					array('filename' => $file_post_name,
						'module' => $post_module,
						'optional_type' => $_POST['optionalType'],
						'export_type' => $_POST['exp_type'],
						'split' => $_POST['is_check_split'],
						'split_limit' => $_POST['limit'],
						'category_name' => $_POST['categoryName'],
						'conditions' => $export_conditions,
						'event_exclusions' => $export_event_exclusions,
						'export_mode' => 'normal',
						'createdtime' => $time,
						'offset' => $_POST['offset'],
						'actual_start_date' => $_POST['actual_start_date'],
						'actual_end_date' => $_POST['actual_end_date']
					),
					array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
				);
			}
			else{
				$id = $check_for_existing_template[0]->id;

				$wpdb->update( 
					$wpdb->prefix.'ultimate_csv_importer_export_template', 
					array(
						'export_type' => $_POST['exp_type'],
						'split' => $_POST['is_check_split'],
						'split_limit' => $_POST['limit'],
						'category_name' => $_POST['categoryName'],
						'conditions' => $export_conditions,
						'event_exclusions' => $export_event_exclusions,
						'export_mode' => 'normal',
						'createdtime' => $time,
						'offset' => $_POST['offset'],
						'actual_start_date' => $_POST['actual_start_date'],
						'actual_end_date' => $_POST['actual_end_date']
					),
					array( 'id' => $id )
				);
			}
			$this->mode=isset($this->mode)?$this->mode:'';
			$this->exportData($this->mode,$categorybased);

		}
	}


	public function commentsCount() {
		global $wpdb;
		self::generateHeaders($this->module, $this->optionalType);
		$get_comments = "select * from {$wpdb->prefix}comments";
		// Check status
		if(isset($this->conditions['specific_status'])){
		if($this->conditions['specific_status']['is_check'] == 'true') {
			if($this->conditions['specific_status']['status'] == 'Pending')
				$get_comments .= " where comment_approved = '0'";
			elseif($this->conditions['specific_status']['status'] == 'Approved')
				$get_comments .= " where comment_approved = '1'";
			else
				$get_comments .= " where comment_approved in ('0','1')";
		}
		else
			$get_comments .= " where comment_approved in ('0','1')";
	}
		// Check for specific period
		if(isset($this->conditions['specific_period']['is_check']) && $this->conditions['specific_period']['is_check'] == 'true') {
			if($this->conditions['specific_period']['from'] == $this->conditions['specific_period']['to']){
				$get_comments .= " and comment_date >= '" . $this->conditions['specific_period']['from'] . "'";
			}else{
				$get_comments .= " and comment_date >= '" . $this->conditions['specific_period']['from'] . "' and comment_date <= '" . $this->conditions['specific_period']['to'] . "'";
			}
		}
		// Check for specific authors
		if(isset($this->conditions['specific_authors']['is_check']) && $this->conditions['specific_authors']['is_check'] == '1') {
			if(isset($this->conditions['specific_authors']['author'])) {
				$get_comments .= " and comment_author_email = '".$this->conditions['specific_authors']['author']."'"; 
			}
		}
		$get_comments .= " order by comment_ID";
		$comments = $wpdb->get_results( $get_comments );
		$totalRowCount = count($comments);
		return $totalRowCount;
	}

	public function getOptionalType($module){
		if($module == 'Tags'){
			$optionalType = 'post_tag';
		}
		elseif($module == 'Posts'){
			$optionalType = 'posts';
		}
		elseif($module == 'Pages'){
			$optionalType = 'pages';
		} 
		elseif($module == 'Categories'){
			$optionalType = 'category';
		} 
		elseif($module == 'Users'){
			$optionalType = 'users';
		}
		elseif($module == 'Comments'){
			$optionalType = 'comments';
		}
		elseif($module == 'Images'){
			$optionalType = 'images';
		}
		elseif($module == 'CustomerReviews'){
			$optionalType = 'wpcr3_review';
		}
		elseif($module == 'WooCommerce' || $module == 'WooCommerceOrders' || $module == 'WooCommerceCoupons' || $module == 'WooCommerceRefunds' || $module == 'WooCommerceVariations' || $module == 'Marketpress' ){
			$optionalType = 'product';
		}
		elseif($module == 'WooCommerce'){
			$optionalType = 'product';
		}
		elseif($module == 'WPeCommerce'){
			$optionalType = 'wpsc-product';
		}
		elseif($module == 'WPeCommerce' ||$module == 'WPeCommerceCoupons'){
			$optionalType = 'wpsc-product';
		}
		$optionalType=isset($optionalType)?$optionalType:'';
		return $optionalType;
	}

	/**
	 * set the delimiter
	 */
	public function setDelimiter($conditions)
	{		
		if (isset($conditions['optional_delimiter']) && $conditions['optional_delimiter'] != '') {
			return $conditions['optional_delimiter'];
		}
		elseif(isset($conditions['delimiter']) && $conditions['delimiter'] != 'Select'){
			if($conditions['delimiter'] == '{Tab}')
				return "\t";
			elseif ($conditions['delimiter'] == '{Space}')
				return " ";
			else
				return $conditions['delimiter'];
		}
		else{
			return ',';
		}
	}

	/**
	 * Export records based on the requested module
	 */
	public function exportData($mod = '',$cat = '', $is_filter = '') {
		switch ($this->module) {
		case 'Posts':
		case 'Pages':
		case 'CustomPosts':
		case 'WooCommerce':
		case 'Marketpress':
		case 'WooCommerceVariations':
		case 'WooCommerceOrders':
		case 'WooCommerceCoupons':
		case 'WooCommerceRefunds':
		case 'WPeCommerce':
		case 'WPeCommerceCoupons':
		case 'eShop':
			case 'Images':
			$result = self::FetchDataByPostTypes($mod,$cat, $is_filter);
			break;
		case 'Users':
			$result = self::FetchUsers($mod, $is_filter);
			break;
		case 'Comments':
			$result = self::FetchComments( $is_filter,$mod);
			break;
		
		
		case 'CustomerReviews':
			$result = ExportExtension::$review_export->FetchCustomerReviews($this->module, $this->optionalType, $this->conditions,$this->offset,$this->limit, $is_filter,$this->mode);
			break;
		case 'Categories':
			$result = ExportExtension::$post_export->FetchCategories($this->module,$this->optionalType, $is_filter,$this->mode);
			break;
		case 'Tags':
			$result = ExportExtension::$post_export->FetchTags($this->module,$this->optionalType, $is_filter,$this->mode);
			break;
		case 'Taxonomies':
			$result = ExportExtension::$woocom_export->FetchTaxonomies($this->module,$this->optionalType, $is_filter,$this->mode);
			break;

		}
		$result=isset($result)?$result:'';
		return $result;
	}

	/**
	 * Fetch users and their meta information
	 * @param $mode
	 *
	 * @return array
	 */
	public function FetchUsers($mode = null, $is_filter = '') {
		global $wpdb;
		self::generateHeaders($this->module, $this->optionalType);
		$get_available_user_ids = "select DISTINCT ID from {$wpdb->prefix}users u join {$wpdb->prefix}usermeta um on um.user_id = u.ID";
		if($this->conditions['specific_period']['is_check'] == 'true') {
			if($this->conditions['specific_period']['from'] == $this->conditions['specific_period']['to']){
				$get_available_user_ids .= " where u.user_registered >= '" . $this->conditions['specific_period']['from'] . "'";
			}else{
				$get_available_user_ids .= " where u.user_registered >= '" . $this->conditions['specific_period']['from'] . "' and u.user_registered <= '" . $this->conditions['specific_period']['to'] . "'";
			}
		}
		$availableUsers = $wpdb->get_col($get_available_user_ids);
		$this->totalRowCount = count($availableUsers);
		$get_available_user_ids .= " order by ID asc limit $this->offset, $this->limit";
		$availableUserss = $wpdb->get_col($get_available_user_ids);
		if(!empty($availableUserss)) {
			$whereCondition = '';
			foreach($availableUserss as $userId) {
				if($whereCondition != ''){
					$whereCondition = $whereCondition . ',' . $userId;
				}else{
					$whereCondition = $userId;
				}
				// Prepare the user details to be export
				$query_to_fetch_users = "SELECT * FROM {$wpdb->prefix}users where ID in ($whereCondition);";
				$users = $wpdb->get_results($query_to_fetch_users);
				if(!empty($users)) {
					foreach($users as $userInfo) {
						foreach($userInfo as $userKey => $userVal) {
							$this->data[$userId][$userKey] = $userVal;
						}
					}
				}
				// Prepare the user meta details to be export
				$query_to_fetch_users_meta = $wpdb->prepare("SELECT user_id, meta_key, meta_value FROM  {$wpdb->prefix}users wp JOIN {$wpdb->prefix}usermeta wpm  ON wpm.user_id = wp.ID where ID= %d", $userId);
				$userMeta = $wpdb->get_results($query_to_fetch_users_meta);

				if(!empty($userMeta)) {
					foreach($userMeta as $userMetaInfo) {
						if($userMetaInfo->meta_key == $wpdb->prefix.'capabilities') {
							$userRole = $this->getUserRole($userMetaInfo->meta_value);
							$this->data[ $userId ][ 'role' ] = $userRole;
						}
						elseif($userMetaInfo->meta_key == 'description') {
							$this->data[ $userId ][ 'biographical_info' ] = $userMetaInfo->meta_value;
						}
						elseif($userMetaInfo->meta_key == 'comment_shortcuts') {
							$this->data[ $userId ][ 'enable_keyboard_shortcuts' ] = $userMetaInfo->meta_value;
						}
						elseif($userMetaInfo->meta_key == 'show_admin_bar_front') {
							$this->data[ $userId ][ 'show_toolbar' ] = $userMetaInfo->meta_value;
						}
						elseif($userMetaInfo->meta_key == 'rich_editing') {
							$this->data[ $userId ][ 'disable_visual_editor' ] = $userMetaInfo->meta_value;
						}
						elseif($userMetaInfo->meta_key == 'locale') {
							$this->data[ $userId ][ 'language' ] = $userMetaInfo->meta_value;
						}
						else {
							$this->data[ $userId ][ $userMetaInfo->meta_key ] = $userMetaInfo->meta_value;
						}
					}	
				// Prepare the buddy meta details to be export
				if(is_plugin_active('buddypress/bp-loader.php')){
					$query_to_fetch_buddy_meta = $wpdb->prepare("SELECT user_id,field_id,value,name FROM {$wpdb->prefix}bp_xprofile_data bxd inner join {$wpdb->prefix}users wp  on bxd.user_id = wp.ID inner join {$wpdb->prefix}bp_xprofile_fields bxf on bxf.id = bxd.field_id where user_id=%d",$userId);
					$buddy = $wpdb->get_results($query_to_fetch_buddy_meta);
					if(!empty($buddy)) {
					foreach($buddy as $buddyInfo) {
						foreach($buddyInfo as $field_id => $value) {
							$this->data[$userId][$buddyInfo->name] = $buddyInfo->value;
							}
						}
					}
				}	
				ExportExtension::$post_export->getPostsMetaDataBasedOnRecordId($userId, $this->module, $this->optionalType);
				}
			}
		}
		$result = self::finalDataToExport($this->data, $this->module ,$this->optionalType);
		
		if($is_filter == 'filter_action'){
			return $result;
		}

		if($mode == null)
			self::proceedExport($result);
		else
			return $result;
	}

	/**
	 * Fetch all Comments
	 * @param $mode
	 *
	 * @return array
	 */
	public function FetchComments( $is_filter,$mode = null) {
		global $wpdb;
		self::generateHeaders($this->module, $this->optionalType);
		$get_comments = "select * from {$wpdb->prefix}comments";
		// Check status
		if($this->conditions['specific_status']['is_check'] == 'true') {
			if($this->conditions['specific_status']['status'] == 'Pending')
				$get_comments .= " where comment_approved = '0'";
			elseif($this->conditions['specific_status']['status'] == 'Approved')
				$get_comments .= " where comment_approved = '1'";
			else
				$get_comments .= " where comment_approved in ('0','1')";
		}
		else
			$get_comments .= " where comment_approved in ('0','1')";
		// Check for specific period
		if($this->conditions['specific_period']['is_check'] == 'true') {
			if($this->conditions['specific_period']['from'] == $this->conditions['specific_period']['to']){
				$get_comments .= " and comment_date >= '" . $this->conditions['specific_period']['from'] . "'";
			}else{
				$get_comments .= " and comment_date >= '" . $this->conditions['specific_period']['from'] . "' and comment_date <= '" . $this->conditions['specific_period']['to'] . "'";
			}
		}
		// Check for specific authors
		if($this->conditions['specific_authors']['is_check'] == '1') {
			if(isset($this->conditions['specific_authors']['author'])) {
				$get_comments .= " and comment_author_email = '".$this->conditions['specific_authors']['author']."'"; 
			}
		}
		$comments = $wpdb->get_results( $get_comments );
		$this->totalRowCount = count($comments);
		$get_comments .= " order by comment_ID asc limit $this->offset, $this->limit";
		$limited_comments = $wpdb->get_results( $get_comments );
		if(!empty($limited_comments)) {
			foreach($limited_comments as $commentInfo) {
				$user_id=$commentInfo->user_id;
				if(!empty($user_id)) {
					$users_login =  $wpdb->get_results("SELECT user_login FROM {$wpdb->prefix}users WHERE ID = '$user_id'");		
					foreach($users_login as $users_key => $users_value){
						foreach($users_value as $u_key => $u_value){
							$users_id=$u_value;
						}
					}
				}
				foreach($commentInfo as $commentKey => $commentVal) {
					$this->data[$commentInfo->comment_ID][$commentKey] = $commentVal;
					$users_id=isset($users_id)?$users_id:'';
					$this->data[$commentInfo->comment_ID]['user_id'] = $users_id;
				}
				$get_comment_rating = get_comment_meta($commentInfo->comment_ID, 'rating', true);
				if(!empty($get_comment_rating)){
					$this->data[$commentInfo->comment_ID]['comment_rating'] = $get_comment_rating;
				}
			}
		}
		$result = self::finalDataToExport($this->data, $this->module ,$this->optionalType);
		
		if($is_filter == 'filter_action'){
			return $result;
		}
		
		if($mode == null)
			self::proceedExport($result);
		else
			return $result;
	}
	
	/**
	 * Generate CSV headers
	 *
	 * @param $module       - Module to be export
	 * @param $optionalType - Exclusions
	 */
	public function generateHeaders ($module, $optionalType) {
		global $wpdb;
		if($module == 'CustomPosts' || $module == 'Tags' || $module == 'Categories' || $module == 'Taxonomies'){
			if($optionalType == 'event'){
				$optionalType = 'Events';
			}elseif($optionalType == 'location'){
				$optionalType = 'Event Locations';
			}elseif($optionalType == 'event-recurring'){
				$optionalType = 'Recurring Events';
			}
			if(empty($optionalType)){
				$default = ExportExtension::$mapping_instance->get_fields($module);
			}
		
		else
			$default = ExportExtension::$mapping_instance->get_fields($optionalType);
		}
		else{
			$default = ExportExtension::$mapping_instance->get_fields($module);
			
		}
	
		$headers = [];
		foreach ($default as $key => $fields) {	
			foreach($fields as $groupKey => $fieldArray) {
				foreach ( $fieldArray as $fKey => $fVal ) {
					if (is_array($fVal) || is_object($fVal)){
						foreach ( $fVal as $rKey => $rVal ) {
							if(!in_array($rVal['name'], $headers))
								// if($fKey == 'acf_group_fields' || $fKey == 'acf_repeater_fields'){
								// 	$headers[] = $rVal['id'];
								// }
								// else{
									//$headers[] = $rVal['name'];
								//}
								if(strpos($rVal['name'], 'field_') !== false){
									$value=$rVal['name'];
									$get_acf_excerpt = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE post_name = '$value'  ");
									$headers[] = $get_acf_excerpt;
								}
								else{
									$headers[] = $rVal['name'];
								}
						}
					}
				}

			}
		}

		if(isset($this->eventExclusions['is_check']) && $this->eventExclusions['is_check'] == 'true') {
			$headers_with_exclusion = self::applyEventExclusion($headers);
			$this->headers = $headers_with_exclusion;
	
		}else{
			$this->headers = $headers;			
		
		}
	
	}

	/**
	 * Fetch data by requested Post types
	 * @param $mode
	 * @return array
	 */
	public function FetchDataByPostTypes ($exp_mod,$exp_cat, $is_filter = '') {
		if(empty($this->headers))
			$this->generateHeaders($this->module, $this->optionalType);
		    $recordsToBeExport = ExportExtension::$post_export->getRecordsBasedOnPostTypes($this->module, $this->optionalType, $this->conditions,$this->offset,$this->limit,$exp_mod,$exp_cat);			
			if(!empty($recordsToBeExport)) {
			foreach($recordsToBeExport as $postId) {
				$this->data[$postId] = $this->getPostsDataBasedOnRecordId($postId,$this->module);
				$exp_module = $this->module; 
				if($exp_module == 'Posts' || $exp_module =='WooCommerce' || $exp_module == 'CustomPosts' || $exp_module == 'Categories' || $exp_module == 'Tags' || $exp_module == 'Taxonomies' || $exp_module == 'Pages'){
					$this->getWPMLData($postId,$this->optionalType,$exp_module);
				}
				if($exp_module == 'Posts' ||  $exp_module == 'CustomPosts' ||$exp_module == 'Pages'||$exp_module == 'WooCommerce'){
					$this->getPolylangData($postId,$this->optionalType,$exp_module);
				}				
				ExportExtension::$post_export->getPostsMetaDataBasedOnRecordId($postId, $this->module, $this->optionalType);
				$this->getTermsAndTaxonomies($postId, $this->module, $this->optionalType);
				if($this->module == 'WooCommerce')
					ExportExtension::$woocom_export->getProductData($postId, $this->module, $this->optionalType);
				if($this->module == 'WooCommerceRefunds')
					ExportExtension::$woocom_export->getWooComCustomerUser($postId, $this->module, $this->optionalType);
				if($this->module == 'WooCommerceOrders')
					ExportExtension::$woocom_export->getWooComOrderData($postId, $this->module, $this->optionalType);
				if($this->module == 'WooCommerceVariations')
					ExportExtension::$woocom_export->getProductData($postId, $this->module, $this->optionalType);
				if($this->module == 'WPeCommerce')
					ExportExtension::$ecom_export->getEcomData($postId, $this->module, $this->optionalType);
				if($this->module == 'WPeCommerceCoupons')
					ExportExtension::$ecom_export->getEcomCouponData($postId, $this->module, $this->optionalType);

				if($this->optionalType == 'lp_course')
					ExportExtension::$woocom_export->getCourseData($postId);
				if($this->optionalType == 'lp_lesson')
					ExportExtension::$woocom_export->getLessonData($postId);
				if($this->optionalType == 'lp_quiz')
					ExportExtension::$woocom_export->getQuizData($postId);
				if($this->optionalType == 'lp_question')
					ExportExtension::$woocom_export->getQuestionData($postId);
				if($this->optionalType == 'lp_order')
					ExportExtension::$woocom_export->getOrderData($postId);

				if($this->optionalType == 'nav_menu_item')
					ExportExtension::$woocom_export->getMenuData($postId);

				if($this->optionalType == 'widgets')
					self::$instance->getWidgetData($postId,$this->headers);	

			}
		}
		$exp_module = $this->module; 
		if(is_plugin_active('jet-engine/jet-engine.php')){
			global $wpdb;
			$get_slug_name = $wpdb->get_results($wpdb->prepare("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'"));
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				$optional_type=$value;
				$table_name='wp_jet_cct_'.$this->optionalType;
				$query_meta = $wpdb->prepare("SELECT * FROM $table_name where cct_status = 'publish'");
				$jet_values= $wpdb->get_results($query_meta);
				if(!empty($jet_values)) {
					foreach($jet_values as $jet_value) {
						foreach($jet_value as $field_id => $value) {
							$this->data[$jet_value->_ID][$field_id] = $value;					

						}
					}
				}
			}
			
		}
		/** Added post format for 'standard' property */
		if($exp_module == 'Posts' || $exp_module == 'CustomPosts') {
			foreach($this->data as $id => $records) {
				if(!array_key_exists('post_format',$records))
					{
						$records['post_format'] = 'standard';
						$this->data[$id] = $records;
					}
			}
		}
		/** End post format */
		$result = self::finalDataToExport($this->data, $this->module ,$this->optionalType);
	
		if($is_filter == 'filter_action'){
			return $result;
		}
		if(empty($mode))
		//if($mode == null)
			self::proceedExport( $result );
		else
			return $result;
	}	

	public function getWidgetData($postId, $headers){
		global $wpdb;
		$get_sidebar_widgets = get_option('sidebars_widgets');
		$total_footer_arr = [];
	
		foreach($get_sidebar_widgets as $footer_key => $footer_arr){
			if($footer_key != 'wp_inactive_widgets' || $footer_key != 'array_version'){
				if( strpos($footer_key, 'sidebar') !== false ){
					$get_footer = explode('-', $footer_key);
					$footer_number = $get_footer[1];

					foreach($footer_arr as $footer_values){
						$total_footer_arr[$footer_values] = $footer_number;
					}
				}
			}
		}
		
		foreach ($headers as $key => $value){
			$get_widget_value[$value] = $wpdb->get_row("SELECT option_value FROM {$wpdb->prefix}options where option_name = '{$value}'", ARRAY_A);
			
			$header_key = explode('widget_', $value);
			
			if ($value == 'widget_recent-posts'){
				$recent_posts = unserialize($get_widget_value[$value]['option_value']); 
				$recent_post = '';
				foreach($recent_posts as $dk => $dv){
					if($dk != '_multiwidget'){
						$post_key = $header_key[1].'-'.$dk;
						$recent_post .= $dv['title'].','.$dv['number'].','.$dv['show_date'].'->'.$total_footer_arr[$post_key].'|';
					}
				}
				$recent_post = rtrim($recent_post , '|');
			}
			elseif ($value == 'widget_pages'){
				$recent_pages = unserialize($get_widget_value[$value]['option_value']); 
				$recent_page = '';
				foreach($recent_pages as $dk => $dv){
					if(isset($dv['exclude'])){
						$exclude_value = str_replace(',', '/', $dv['exclude']);
					}

					if($dk != '_multiwidget'){
						$page_key = $header_key[1].'-'.$dk;
						$recent_page .= $dv['title'].','.$dv['sortby'].','.$exclude_value.'->'.$total_footer_arr[$page_key].'|';
					}
				}
				$recent_page = rtrim($recent_page , '|');
			}
			elseif ($value == 'widget_recent-comments'){
				$recent_comments = unserialize($get_widget_value[$value]['option_value']); 
				$recent_comment = '';
				foreach($recent_comments as $dk => $dv){
					if($dk != '_multiwidget'){
						$comment_key = $header_key[1].'-'.$dk;
						$recent_comment .= $dv['title'].','.$dv['number'].'->'.$total_footer_arr[$comment_key].'|';
					}
				}
				$recent_comment = rtrim($recent_comment , '|');
			}
			elseif ($value == 'widget_archives'){
				$recent_archives = unserialize($get_widget_value[$value]['option_value']); 
				$recent_archive = '';
				foreach($recent_archives as $dk => $dv){
					if($dk != '_multiwidget'){
						$archive_key = $header_key[1].'-'.$dk;
						$recent_archive .= $dv['title'].','.$dv['count'].','.$dv['dropdown'].'->'.$total_footer_arr[$archive_key].'|';
					}
				}
				$recent_archive = rtrim($recent_archive , '|');
			}
			elseif ($value == 'widget_categories'){
				$recent_categories = unserialize($get_widget_value[$value]['option_value']); 
				$recent_category = '';
				foreach($recent_categories as $dk => $dv){
					if($dk != '_multiwidget'){
						$cat_key = $header_key[1].'-'.$dk;
						$recent_category .= $dv['title'].','.$dv['count'].','.$dv['hierarchical'].','.$dv['dropdown'].'->'.$total_footer_arr[$cat_key].'|';
					}
				}
				$recent_category = rtrim($recent_category , '|');
			}
		}
			
		$this->data[$postId]['widget_recent-posts'] = $recent_post;
		$this->data[$postId]['widget_pages'] = $recent_page;
		$this->data[$postId]['widget_recent-comments'] = $recent_comment;
		$this->data[$postId]['widget_archives'] = $recent_archive;
		$this->data[$postId]['widget_categories'] = $recent_category;
	}

	/**
	 * Function used to fetch the Terms & Taxonomies for the specific posts
	 *
	 * @param $id
	 * @param $type
	 * @param $optionalType
	 */
	public function getTermsAndTaxonomies ($id, $type, $optionalType) {
		$TermsData = array();
		if($type == 'WooCommerce' || $type == 'Marketpress' || ($type == 'CustomPosts' && $type == 'WooCommerce')) {
			$type = 'product';
			$postTags = '';
			$taxonomies = get_object_taxonomies($type);
			$get_tags = get_the_terms( $id, 'product_tag' );
			if($get_tags){
				foreach($get_tags as $tags){
					$postTags .= $tags->name . ',';
				}
			}
			$postTags = substr($postTags, 0, -1);
			$this->data[$id]['product_tag'] = $postTags;
			foreach ($taxonomies as $taxonomy) {
				$postCategory = '';
				if($taxonomy == 'product_cat' || $taxonomy == 'product_category'){
					//$get_categories = get_the_terms( $id, $taxonomy );
					$get_categories = wp_get_object_terms( $id, $taxonomy, array( 'orderby' => 'term_order' ) );
					if($get_categories){
						$postCategory = $this->hierarchy_based_term_name($get_categories, $taxonomy) ;
						// foreach($get_categories as $category){
						// 	$postCategory .= $this->hierarchy_based_term_name($category, $taxonomy) . ',';
						// }
					}
					$postCategory = substr($postCategory, 0 , -1);
					$this->data[$id]['product_category'] = $postCategory;
				}else{
					$get_categories = get_the_terms( $id, $taxonomy );
					if($get_categories){
						$postCategory = $this->hierarchy_based_term_name($get_categories, $taxonomy) ;
						// foreach($get_categories as $category){
						// 	$postCategory .= $this->hierarchy_based_term_name($category, $taxonomy) . ',';
						// }
					}
					$postCategory = substr($postCategory, 0 , -1);
					$this->data[$id][$taxonomy] = $postCategory;
				}
			}
			if(($type == 'WooCommerce' && $type != 'CustomPosts') || $type == 'Marketpress' ) {
				$product = wc_get_product	($id);
				$pro_type = $product->get_type();
				switch ($pro_type) {
				case 'simple':
					$product_type = 1;
					break;
				case 'grouped':
					$product_type = 2;
					break;
				case 'external':
					$product_type = 3;
					break;
				case 'variable':
					$product_type = 4;
					break;
				case 'subscription':
					$product_type = 5;
					break;
				case 'variable-subscription':
					$product_type = 6;
					break;
				default:
					$product_type = 1;
					break;
				}
				$this->data[$id]['product_type'] = $product_type;
			}
			$shipping = get_the_terms( $id, 'product_shipping_class' );
			if(!(is_wp_error($shipping))){
				if($shipping){
					$taxo_shipping = $shipping[0]->name;	
					$this->data[$id][ 'product_shipping_class' ] = $taxo_shipping;
				}
			}
			
		} else if($type == 'WPeCommerce') {
			$type = 'wpsc-product';
			$postTags = $postCategory = '';
			$taxonomies = get_object_taxonomies($type);
			$get_tags = get_the_terms( $id, 'product_tag' );
			if($get_tags){
				foreach($get_tags as $tags){
					$postTags .= $tags->name.',';
				}
			}
			$postTags = substr($postTags,0,-1);
			$this->data[$id]['product_tag'] = $postTags;
			foreach ($taxonomies as $taxonomy) {
				$postCategory = '';
				if($taxonomy == 'wpsc_product_category'){
					$get_categories = wp_get_post_terms( $id, $taxonomy );
					if($get_categories){
						$postCategory = $this->hierarchy_based_term_name($get_categories, $taxonomy);
						// foreach($get_categories as $category){
						// 	$postCategory .= $this->hierarchy_based_term_name($category, $taxonomy).',';
						// }
					}
					$postCategory = substr($postCategory, 0 , -1);
					$this->data[$id]['product_category'] = $postCategory;
				}else{
					$get_categories = wp_get_post_terms( $id, $taxonomy );
					if($get_categories){
						$postCategory = $this->hierarchy_based_term_name($get_categories, $taxonomy);
						// foreach($get_categories as $category){
						// 	$postCategory .= $this->hierarchy_based_term_name($category, $taxonomy).',';
						// }
					}
					$postCategory = substr($postCategory, 0 , -1);
					$this->data[$id]['product_category'] = $postCategory;
				}
			}
		} else {
			global $wpdb;
			$postTags = $postCategory = '';
			$taxonomyId = $wpdb->get_col($wpdb->prepare("select term_taxonomy_id from {$wpdb->prefix}term_relationships where object_id = %d", $id));
			foreach($taxonomyId as $taxonomy) {
				$taxo[] = get_term($taxonomy);
			}
			if(!empty($taxo)){
				foreach($taxo as $key=>$taxo_val){
					if($taxo_val->taxonomy == 'category'){
						$taxo1[]=$taxo_val;
						
					}
				}
			}
			if(!empty($taxonomyId)) {
				foreach($taxonomyId as $taxonomy) {
					$taxonomyType = $wpdb->get_col($wpdb->prepare("select taxonomy from {$wpdb->prefix}term_taxonomy where term_taxonomy_id = %d", $taxonomy));
					if(!empty($taxonomyType)) {
						foreach($taxonomyType as $taxanomy_name) {
							if($taxanomy_name == 'category'){
								$termName = 'post_category';
							}else{
								$termName = $taxanomy_name;
							}
							if(in_array($termName, $this->headers)) {
								if($termName != 'post_tag' && $termName !='post_category') {

									$taxonomyData = $wpdb->get_col($wpdb->prepare("select name from {$wpdb->prefix}terms where term_id = %d",$taxonomy));
									if(!empty($taxonomyData)) {

										if(isset($TermsData[$termName])){
											$this->data[$id][$termName] = $TermsData[$termName] . ',' . $taxonomyData[0];
										}else{
											$this->data[$id][$termName]=isset($this->data[$id][$termName])?$this->data[$id][$termName]:'';
											$get_exist_data = $this->data[$id][$termName];
										}
										if( $get_exist_data == '' ){
											$this->data[$id][$termName] = $taxonomyData[0];
										}else {
											$taxonomyID = $wpdb->get_col($wpdb->prepare("select term_id from {$wpdb->prefix}terms where name = %s",$taxonomyData[0]));
											$postterm = substr($this->hierarchy_based_term_name($taxo, $taxanomy_name), 0 , -1);
											$this->data[$id][$termName] = $postterm;
											//$this->data[$id][$termName] = $get_exist_data . ',' . $this->hierarchy_based_term_name(get_term($taxonomy), $taxanomy_name);
										}

									}
								} else {
									if(!isset($TermsData['post_tag'])) {
										if($termName == 'post_tag'){
											$postTags = '';
											$get_tags = wp_get_post_tags($id, array('fields' => 'names'));
											foreach ($get_tags as $tags) {
												$postTags .= $tags . ',';
											}
											$postTags = substr($postTags, 0, -1);
											if( $this->data[$id][$termName] == '' ) {
												$this->data[$id][$termName] = $postTags;
											}
										}
										if($termName == 'post_category'){
											$postCategory = '';
											$get_categories = wp_get_post_categories($id, array('fields' => 'names'));
											// foreach ($get_categories as $category) {
											// 	$postCategory .= $category . ',';
											// }
											$postterm1= substr($this->hierarchy_based_term_name($taxo1, $taxanomy_name), 0 , -1);
											$this->data[$id][$termName] = $postterm1;
											// $postCategory = substr($postCategory, 0, -1);
											// if( $this->data[$id][$termName] == '' ) {
											// 	$this->data[$id][$termName] = $postCategory;
											// }
										}
		
									}
								}
								// if(!isset($TermsData['category'])){
								// 	$get_categories = wp_get_post_categories($id, array('fields' => 'names'));
								// 	foreach ($get_categories as $category) {
								// 		$postCategory .= $category . ',';
								// 	}
								// 	$postCategory = substr($postCategory, 0, -1);
								// 	$this->data[$id]['category'] = $postCategory;
								// }

							}
							else{
								$this->data[$id][$termName] = '';
							}
						}
					}					
				}
			}
		}
	}

	/**
	 * Get user role based on the capability
	 * @param null $capability  - User capability
	 * @return int|string       - Role of the user
	 */
	public function getUserRole ($capability = null) {
		if($capability != null) {
			$getRole = unserialize($capability);
			foreach($getRole as $roleName => $roleStatus) {
				$role = $roleName;
			}
			return $role;
		} else {
			return 'subscriber';
		}
	}

	public function array_to_xml( $data, &$xml_data ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = 'item'; 
			}
			if( is_array($value) ) {
				$subnode = $xml_data->addChild($key);
				$this->array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		}
	}

	/**
	 * Export Data
	 * @param $data
	 */
	public function proceedExport ($data) {
	
		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/exports/';
		if(!is_dir($upload_dir)) {
			wp_mkdir_p($upload_dir);
		}
		$base_dir = wp_upload_dir();
		$upload_url = network_home_url().'/wp-content/uploads/smack_uci_uploads/exports/';
		chmod($upload_dir, 0777);
		if($this->checkSplit == 'true'){
			$i = 1;
			while ( $i != 0) {
				$file = $upload_dir . $this->fileName .'_'.$i.'.' . $this->exportType;
				if(file_exists($file)){
					$allfiles[$i] = $file;
					$i++;
				}
				else
					break;
			}
			$fileURL = $upload_url . $this->fileName.'_'.$i.'.' .$this->exportType;
		}
		else{
			$file = $upload_dir . $this->fileName .'.' . $this->exportType;
			$fileURL = $upload_url . $this->fileName.'.' .$this->exportType;
		}
		if ($this->offset == 0) {
			if(file_exists($file))
				unlink($file);
		}

		$checkRun = "no";
		if($this->checkSplit == 'true' && ($this->totalRowCount - $this->offset) > 0){
			$checkRun = 'yes';
		}
		if($this->checkSplit != 'true'){
			$checkRun = 'yes';
		}
	
		if($checkRun == 'yes'){
			if($this->exportType == 'xml'){
				$xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
				$this->array_to_xml($data,$xml_data);
				$result = $xml_data->asXML($file);
			}else{
				if($this->exportType == 'json')
					$csvData = json_encode($data);
				else
					$csvData = $this->unParse($data, $this->headers);
				try {
					file_put_contents( $file, $csvData, FILE_APPEND | LOCK_EX );
			} catch (\Exception $e) {
			}
			}
			}

			$this->offset = $this->offset + $this->limit;

			$filePath = $upload_dir . $this->fileName . '.' . $this->exportType;
			$filename = $fileURL;
			if(($this->offset) > ($this->totalRowCount) && $this->checkSplit == 'true'){
				$allfiles[$i] = $file;
				$zipname = $upload_dir . $this->fileName .'.' . 'zip';
				$zip = new \ZipArchive;
				$zip->open($zipname, \ZipArchive::CREATE);
				foreach ($allfiles as $allfile) {
					$newname = str_replace($upload_dir, '', $allfile);
					$zip->addFile($allfile, $newname);
			}
			$zip->close();
			$fileURL = $upload_url . $this->fileName.'.'.'zip';
			foreach ($allfiles as $removefile) {
				unlink($removefile);
			}
			$filename = $upload_url . $this->fileName.'.'.'zip';
			}
			if($this->checkSplit == 'true' && !($this->offset) > ($this->totalRowCount)){
				$responseTojQuery = array('success' => false, 'new_offset' => $this->offset, 'limit' => $this->limit, 'total_row_count' => $this->totalRowCount, 'exported_file' => $zipname, 'exported_path' => $zipname,'export_type'=>$this->exportType);
			}
			elseif($this->checkSplit == 'true' && (($this->offset) > ($this->totalRowCount))){
				$responseTojQuery = array('success' => true, 'new_offset' => $this->offset, 'limit' => $this->limit, 'total_row_count' => $this->totalRowCount, 'exported_file' => $fileURL, 'exported_path' => $fileURL,'export_type'=>$this->exportType);
			}
			elseif(!(($this->offset) > ($this->totalRowCount))){
				$responseTojQuery = array('success' => false, 'new_offset' => $this->offset, 'limit' => $this->limit, 'total_row_count' => $this->totalRowCount, 'exported_file' => $filename, 'exported_path' => $filePath,'export_type'=>$this->exportType);
			}
			else{
				$responseTojQuery = array('success' => true, 'new_offset' => $this->offset, 'limit' => $this->limit, 'total_row_count' => $this->totalRowCount, 'exported_file' => $filename, 'exported_path' => $filePath,'export_type'=>$this->exportType);
			}

			if($this->export_mode == 'normal'){
				echo wp_json_encode($responseTojQuery);
				wp_die();
			}
			else{
				$this->export_log = $responseTojQuery;
			}
			}

			/**
			 * Get post data based on the record id
			 * @param $id       - Id of the records
			 * @return array    - Data based on the requested id.
			 */
			public function getPostsDataBasedOnRecordId ($id,$module) {
				global $wpdb;
				$PostData = array();
				$query1 = $wpdb->prepare("SELECT wp.* FROM {$wpdb->prefix}posts wp where ID=%d", $id);
				$result_query1 = $wpdb->get_results($query1);
				if (!empty($result_query1)) {
					foreach ($result_query1 as $posts) {
						if(is_numeric($posts->post_parent) && $posts->post_parent !=='0' && $posts->post_type !=='product_variation'){
							if($module != 'WooCommerceRefunds'){
								$tit=get_the_title($posts->post_parent);
								$posts->post_parent=$tit;
							}		
						}
						if($posts->post_type =='event' ||$posts->post_type =='event-recurring'){

							$loc=get_post_meta($id , '_location_id' , true);
							$event_id=get_post_meta($id , '_event_id' , true);
							$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}em_locations WHERE location_id='$loc' "); 
			
							if($res){
								foreach($res as $location){
								$posts=array_merge((array)$posts,(array)$location);
								}
							}

								$ticket = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}em_tickets WHERE event_id='$event_id' "); 
								
								
								//$ticket_meta = $wpdb->get_results("SELECT ticket_meta FROM {$wpdb->prefix}em_tickets WHERE event_id='$event_id' ");
								$ticket[0]=isset($ticket[0])?$ticket[0]:'';
								$ticket_meta= $ticket[0];
								if(isset($ticket_meta->{'ticket_meta'})){
								$ticket_meta_value=$ticket_meta->{'ticket_meta'};
								}
								$ticket_meta_value=isset($ticket_meta_value)?$ticket_meta_value:'';
								$ticket_value=unserialize($ticket_meta_value);
								if(isset($ticket_id)){
								$ticket_values = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}em_tickets WHERE ticket_id='$ticket_id' ");
								}
								$count=count($ticket);
								if($count>1){
									$ticknamevalue = '';
									$tickidvalue = '';
									$eventidvalue = '';
									$tickdescvalue = '';
									$tickpricevalue = '';
									$tickstartvalue = '';
									$tickendvalue = '';
									$tickminvalue = '';
									$tickmaxvalue = '';
									$tickspacevalue = '';
									$tickmemvalue = '';
									$tickmemrolevalue = '';
									//$tickmemroles = '';
									$tickguestvalue = '';
									$tickreqvalue = '';
									$tickparvalue = '';
									$tickordervalue = '';
									$tickmetavalue = '';
									
									foreach($ticket as $tic => $ticval){
										$ticknamevalue .= $ticval->ticket_name . ', ';
										$tickidvalue .=$ticval->ticket_id . ', ';
										$eventidvalue .=$ticval->event_id . ', ';
										$tickdescvalue .=$ticval->ticket_description . ', ';
										$tickpricevalue .=$ticval->ticket_price . ', ';
										$tickstartvalue .=$ticval->ticket_start . ', ';
										$tickendvalue .=$ticval->ticket_end . ', ';
										$tickminvalue .=$ticval->ticket_min . ', ';
										$tickmaxvalue .=$ticval->ticket_max . ', ';
										$tickspacevalue .=$ticval->ticket_spaces . ', ';
										$tickmemvalue .=$ticval->ticket_members . ', ';
										$tickmemroles =unserialize($ticval->ticket_members_roles);
										$tickmemroleval=implode('| ',(array)$tickmemroles);
										$tickmemrolevalue .=$tickmemroleval . ', ';
									
										
										$tickguestvalue .=$ticval->ticket_guests . ', ';
										$tickreqvalue .=$ticval->ticket_required . ', ';
										$tickparvalue .=$ticval->ticket_parent . ', ';
										$tickordervalue .=$ticval->ticket_order . ', ';
										$tickmetavalue .=$ticval->ticket_meta . ', ';
									
										$ticknamevalues = rtrim($ticknamevalue, ', ');
										$tickidvalues = rtrim($tickidvalue, ', ');
										$eventidvalues=rtrim($eventidvalue, ', ');
										$tickdescvalues=rtrim($tickdescvalue, ', ');
										$tickpricevalues =rtrim($tickpricevalue, ', ');
										$tickstartvalues   =rtrim($tickstartvalue, ', ');
										$tickendvalues   =rtrim($tickendvalue, ', ');
										$tickminvalues   =rtrim($tickminvalue, ', ');
										$tickmaxvalues =rtrim($tickmaxvalue, ', ');
										$tickspacevalues =rtrim($tickspacevalue, ', ');	
										$tickmemvalues	=rtrim($tickmemvalue, ', ');
										$tickmemrolevalues	=rtrim($tickmemrolevalue, ', ');
										$tickguestvalues	=rtrim($tickguestvalue, ', ');
										$tickreqvalues	=rtrim($tickreqvalue, ', ');
										$tickparvalues	=rtrim($tickparvalue, ', ');
										$tickordervalues	=rtrim($tickordervalue, ', ');	
										$tickmetavalues	=rtrim($tickmetavalue, ', ');	
										
										$tic_key1 = array('ticket_id', 'event_id', 'ticket_name','ticket_description','ticket_price','ticket_start','ticket_end','ticket_min','ticket_max','ticket_spaces','ticket_members','ticket_members_roles','ticket_guests','ticket_required','ticket_parent','ticket_order','ticket_meta');
								        $tic_val1 = array($tickidvalues,$eventidvalues, $ticknamevalues,$tickdescvalues,$tickpricevalues,$tickstartvalues,$tickendvalues,$tickminvalues,$tickmaxvalues,$tickspacevalues,$tickmemvalues,$tickmemrolevalues,$tickguestvalues,$tickreqvalues,$tickparvalues,$tickordervalues,$tickmetavalues);
										$tickets1 = array_combine($tic_key1,$tic_val1);
										$posts=array_merge((array)$posts,(array)$tickets1);
										$ticket_start[] = $ticval->ticket_start;
										//$tickval  = array_values($ticket_start );
										$ticket_start_date = '';
										$ticket_start_time ='';
						                foreach(  $ticket_start as $loc =>$locval){
											$date = strtotime($locval);
											$ticket_start_date .= date('Y-m-d', $date) . ', ';
											
											$ticket_start_time .= date('H:i:s',$date) .', ';	
											
			
										}
										$ticket_start_times = rtrim($ticket_start_time, ', ');
										$ticket_start_dates = rtrim($ticket_start_date, ', ');
										$ticket_end[] = trim($ticval->ticket_end);
										$ticket_end_time = '';
										$ticket_end_date = '';
										foreach($ticket_end as $loc => $locvalend){
											//$ticket_end=implode(',', $location1);
											$time = strtotime($locvalend);
											$ticket_end_date .= date('Y-m-d', $time) .', ';
											$ticket_end_time .= date('H:i:s',$time) .', ';
										   
						
										}	   
										$ticket_end_times = rtrim($ticket_end_time, ', ');
										$ticket_end_dates = rtrim($ticket_end_date, ', ');
										$tic_key = array('ticket_start_date', 'ticket_start_time', 'ticket_end_date','ticket_end_time');
										$tic_val = array($ticket_start_dates,$ticket_start_times, $ticket_end_dates,$ticket_end_times);
										$tickets = array_combine($tic_key,$tic_val);
					                    $posts=array_merge((array)$posts,(array)$tickets);
					
										
									}

								}
								else{
								    foreach($ticket as $tic => $ticval){
										$posts=array_merge((array)$posts,(array)$ticval);
										if(isset($ticval->ticket_start)){
										$ticket_start=$ticval->ticket_start;
										}
										//if($ticket_start != null){
										if(isset($ticket_start) && ($ticket_start != null)){
											$date = strtotime($ticket_start);
											
											//$date=implode(',', $date1);
											$ticket_start_date = date('Y-m-d', $date);
											$ticket_start_time= date('H:i:s',$date);
											$ticket_end=$ticval->ticket_end;
											$time = strtotime($ticket_end);
											$ticket_end_date = date('Y-m-d', $time);
											$ticket_end_time= date('H:i:s',$time);
											$tic_key = array('ticket_start_date', 'ticket_start_time', 'ticket_end_date','ticket_end_time');
											$tic_val = array($ticket_start_date,$ticket_start_time, $ticket_end_date,$ticket_end_time);
											$tickets = array_combine($tic_key,$tic_val);
											$posts=array_merge((array)$posts,(array)$tickets);
										
										}
									}
								}
							
						}
						
						//$p_type=$posts->post_type;
						$post_type=isset($posts->post_type)?$posts->post_type:'';
						$p_type=$post_type;
						$posid = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts  where post_name='$p_type' and post_type='_pods_pod'");
						foreach($posid as $podid){
							$pods_id=$podid->ID;
							$storage = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta  where post_id=$pods_id AND meta_key='storage'");
							foreach($storage as $pod_storage){
								$pod_stype=$pod_storage->meta_value;
							}

						}
						if(isset($pod_stype) && $pod_stype=='table'){
							$tab='pods_'.$p_type;
							$tab_val = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}$tab where id=$id");
							foreach($tab_val as $table_key =>$table_val ){
								$posts=array_merge((array)$posts,(array)$table_val);

							}

						}
						foreach ($posts as $post_key => $post_value) {
							if ($post_key == 'post_status') {
								if (is_sticky($id)) {
									$PostData[$post_key] = 'Sticky';
									$post_status = 'Sticky';
								} else {
									$PostData[$post_key] = $post_value;
									$post_status = $post_value;
								}
							} else {
								$PostData[$post_key] = $post_value;

							}
							if ($post_key == 'post_password') {
								if ($post_value) {
									$PostData['post_status'] = "{" . $post_value . "}";
								} else {
									$PostData['post_status'] = $post_status;
								}
							}	
							if($post_key == 'post_author'){
								$user_info = get_userdata($post_value);
								//$PostData['post_author'] = $user_info->user_login;
								$user_info=isset($user_info)?$user_info:'';
								 $user_login=isset($user_info->user_login)?$user_info->user_login:'';
								 $PostData['post_author'] = $user_login;
							}
						}




					}
				}
				return $PostData;
				
			} 
			
			public function getWPMLData ($id,$optional_type,$exp_module) {
			
				global $wpdb;
				global $sitepress;
				if($sitepress != null) {
					$icl_translation_table = $wpdb->prefix.'icl_translations';
					if($exp_module == 'Categories' || $exp_module == 'Tags' || $exp_module == 'Taxonomies'){
						$get_element_type = 'tax_'.$optional_type;
						$get_element_tax_id = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy WHERE term_id = $id");
					
						//added
						if(!empty($get_element_tax_id)){
							$args = array('element_id' => $get_element_tax_id ,'element_type' => $get_element_type);
						}
						else{
							$args = array('element_id' => $id ,'element_type' => $get_element_type);
						}
					}
					else{
						$get_element_type = 'post_'.$optional_type;
						$args = array('element_id' => $id ,'element_type' => $get_element_type);
					}

					$get_language_code = apply_filters( 'wpml_element_language_code', null, $args );
				
					// $code = apply_filters( 'wpml_post_language_details', null,  $id );
					// $get_language_code = $code['language_code'];
					//$get_language_code = $wpdb->get_var("select language_code from {$icl_translation_table} where element_id ='{$id}'");
					
					//added
					//$get_source_language = $wpdb->get_var("select source_language_code from {$icl_translation_table} where element_id ='{$id}' and language_code ='{$get_language_code}'");
					if($exp_module == 'Categories' || $exp_module == 'Tags' || $exp_module == 'Taxonomies'){
						if(!empty($get_element_tax_id)){
							$get_source_language = $wpdb->get_var("select source_language_code from {$icl_translation_table} where element_id ='{$get_element_tax_id}' and language_code ='{$get_language_code}'");
						}
						else{
							$get_source_language = $wpdb->get_var("select source_language_code from {$icl_translation_table} where element_id ='{$id}' and language_code ='{$get_language_code}'");
						}
					}
					else{
						$get_source_language = $wpdb->get_var("select source_language_code from {$icl_translation_table} where element_id ='{$id}' and language_code ='{$get_language_code}'");
					}
					
					//$get_trid = $wpdb->get_var("select trid from {$icl_translation_table} where element_id ='{$id}'");
					$this->data[$id]['language_code'] = $get_language_code;	

					$get_trid = apply_filters( 'wpml_element_trid', NULL, $id,$get_element_type );
					if(!empty($get_source_language)){
						$original_element_id_prepared = $wpdb->prepare(
							"SELECT element_id
							FROM {$wpdb->prefix}icl_translations
							WHERE trid=%d
							AND source_language_code IS NULL
							LIMIT 1",$get_trid
						);
						$element_id = $wpdb->get_var( $original_element_id_prepared );
						
						if($exp_module == 'Posts' || $exp_module == 'WooCommerce' || $exp_module == 'CustomPosts' || $exp_module == 'Pages'){
							//$element_title = get_the_title( $element_id );
							$element_title = $wpdb->get_var("select post_title from $wpdb->posts where ID ='{$element_id}'");
							$this->data[$id]['translated_post_title'] = $element_title;
						}
						else{
							$element_title =  $wpdb->get_var("select name from $wpdb->terms where term_id ='{$element_id}'");
							$this->data[$id]['translated_taxonomy_title'] = $element_title;
						}
					}
					return $this->data[$id];
				}	
			}
            public function getPolylangData ($id,$optional_type,$exp_module) {
				global $wpdb;
				global $sitepress;
				$terms=$wpdb->get_results("select term_taxonomy_id from $wpdb->term_relationships where object_id ='{$id}'");
				$terms_id=json_decode(json_encode($terms),true);
				foreach($terms_id as $termkey => $termvalue){
					$termids=$termvalue['term_taxonomy_id'];
					$check=$wpdb->get_var("select taxonomy from $wpdb->term_taxonomy where term_id ='{$termids}'");
					if($check == 'category'){
						$category=$wpdb->get_var("select name from $wpdb->terms where term_id ='{$termids}'");
						//$this->data[$id]['post_category'] = $category;
					}
					elseif($check =='language'){
						$language=$wpdb->get_var("select description from $wpdb->term_taxonomy where term_id ='{$termids}'");
						$lang=unserialize($language);
						$langcode=explode('_',$lang['locale']);
						$lang_code=$langcode[0];
						$this->data[$id]['language_code'] = $lang_code;
					}
					elseif($check == 'post_translations'){
						 $description=$wpdb->get_var("select description from $wpdb->term_taxonomy where term_id ='{$termids}'");
						 $desc=unserialize($description);
						 $post_id=array_values($desc);
						 $postid=min($post_id);
						 
						 $post_title=$wpdb->get_var("select post_title from $wpdb->posts where ID ='{$postid}'");
						 $this->data[$id]['translated_post_title'] = $post_title;
					}
					elseif($check == 'post_tag'){
						$tag=$wpdb->get_var("select name from $wpdb->terms where term_id ='{$termids}'");
						//$this->data[$id]['post_tag'] = $tag;
						
					}
				}
			}
			public function getAttachment($id)
			{
				global $wpdb;
				$get_attachment = $wpdb->prepare("select guid from {$wpdb->prefix}posts where ID = %d AND post_type = %s", $id, 'attachment');
				$attachment = $wpdb->get_results($get_attachment);
				$attachment=isset($attachment)?$attachment:'';
				$attachment_file = isset($attachment[0]->guid)?$attachment[0]->guid:'';
				$attachment_file=isset($attachment_file)?$attachment_file:'';
				return $attachment_file;
			}

			public function getRepeater($parent)
			{
				global $wpdb;
				$get_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_parent = %d", $parent), ARRAY_A);
				$i = 0;
				foreach ($get_fields as $key => $value) {
					$array[$i] = $value['post_excerpt'];
					$i++;
				}
				return $array;	
			}

			/**
			 * Get types fields
			 * @return array    - Types fields
			 */
			public function getTypesFields() {
				$getWPTypesFields = get_option('wpcf-fields');
				
				$typesFields = array();
				if(!empty($getWPTypesFields) && is_array($getWPTypesFields)) {
					foreach($getWPTypesFields as $fKey){
						$typesFields[$fKey['meta_key']] = $fKey['name'];
					}
				}
				return $typesFields;
			}

			/**
			 * Final data to be export
			 * @param $data     - Data to be export based on the requested information
			 * @return array    - Final data to be export
			 */
			public function finalDataToExport ($data, $module = false , $optionalType = false) {
				global $wpdb;
				$result = array();
				foreach ($this->headers as $key => $value) {
					if($value == 'price' && $module != 'WooCommerceVariations'){
						unset($this->headers[$key]);	
					}
					if(empty($value)){
						unset($this->headers[$key]);
					}
				}
				
				// Fetch Category Custom Field Values
				if($module){
					if($module == 'Categories' || $module == 'Tags'){
						return $this->fetchCategoryFieldValue($data, $this->module);
					}
				}

				$toolset_relationship_fieldnames = ['types_relationship', 'relationship_slug', 'intermediate'];
			
				foreach ( $data as $recordId => $rowValue ) {
					$optional_type = '';
					if(is_plugin_active('jet-engine/jet-engine.php')){
						global $wpdb;
						$get_slug_name = $wpdb->get_results($wpdb->prepare("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'"));
						foreach($get_slug_name as $key=>$get_slug){
							$value=$get_slug->slug;
							$optionaltype=$value;
							if($optionalType == $optionaltype){
								$optional_type=$optionaltype;
							}
						}
					}

					foreach ($this->headers as $htemp => $hKey) {
					
						if(is_array($rowValue) && array_key_exists($hKey, $rowValue) && (!empty($rowValue[$hKey])) ){
							
							if(isset($this->typeOftypesField) && is_array($this->typeOftypesField) && array_key_exists('wpcf-'.$hKey, $this->typeOftypesField)){
								if($rowValue[$hKey] == 'Array'){
									$result[$recordId][$hKey] = $this->getToolsetRepeaterFieldValue($hKey, $recordId, $rowValue[$hKey]);
								}else{
									$result[$recordId][$hKey] = $this->returnMetaValueAsCustomerInput($rowValue[$hKey], $hKey);
								}
							}
							
							elseif($optionalType == 'elementor_library'){
								if($hKey == '_elementor_version'){
									$version = '0.4';
									$hKey = 'version';
									$rowValue[$hKey] = $version;
								
							
								}
								if($hKey == 'post_title'){
									$post_title = $rowValue[$hKey];
									$hKey = 'title';
									$rowValue[$hKey] = $post_title;
								
									
								}
								if($hKey == '_elementor_template_type'){
									$type = $rowValue[$hKey];
									$hKey = 'type';
									$rowValue[$hKey] = $type;
									

								}
								if($hKey == '_elementor_data'){
								

									$unserialize = json_decode($rowValue[$hKey]);
									$hKey = 'content';
									//$serialize = json_encode($unserialize);
									//$result[$hKey] = $unserialize;	
									$rowValue[$hKey] = $unserialize;
								}
								
								$result['version'] = $version;
								$result['title'] = $post_title;
								$result['type'] = $type;
								$result['content'] = $unserialize;
								// else{
								// //elseif ($hKey ==''){
								// 	$result[$recordId][$hKey] = $rowValue[$hKey];
								// }
	
							}
							elseif($optionalType == $optional_type){
							
								$result = $this->getJetCCTValue($data,$optionalType);
								
								//added
								if(is_array($result)){
									return $result;
									die;
								}
								else{
									return $result;
								}
								
							}
						
							else{
								$result[$recordId][$hKey] = $this->returnMetaValueAsCustomerInput($rowValue[$hKey], $hKey);
							}
						}	
						else{
							
							$key = $hKey;
							$key = $this->replace_prefix_aioseop_from_fieldname($key);
							$key = $this->replace_prefix_yoast_wpseo_from_fieldname($key);
							$key = $this->replace_prefix_wpcf_from_fieldname($key);
							$key = $this->replace_prefix_wpsc_from_fieldname($key);
							$key = $this->replace_underscore_from_fieldname($key);
							$key = $this->replace_wpcr3_from_fieldname($key);
							// Change fieldname depends on the post type
							$rowValue['post_type']=isset($rowValue['post_type'])?$rowValue['post_type']:'';
							$key = $this->change_fieldname_depends_on_post_type($rowValue['post_type'], $key);			
							
							if(isset($this->typeOftypesField) && is_array($this->typeOftypesField) && array_key_exists('wpcf-'.$key, $this->typeOftypesField)){
								$rowValue[$key] = $this->getToolsetRepeaterFieldValue($key, $recordId);
							}else if($key == 'Parent_Group'){
								$rowValue[$key] = $this->getToolsetRepeaterParentValue($module);
							}else if($toolset_group_title = $this->hasToolsetRelationship($key, $recordId)){
								$rowValue[$key] = $toolset_group_title;
							}else if(isset($rowValue['wpcr3_'.$key])){
								$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue['wpcr3_'.$key], $hKey);
							}else{
							
								$rowValue['post_type']=isset($rowValue['post_type'])?$rowValue['post_type']:'';
								$rowValue[$key]=isset($rowValue[$key])?$rowValue[$key]:'';
								if(isset($key,$this->allacf) && is_array($this->allacf) && array_key_exists($key, $this->allacf)){
									$rowValue[$this->allacf[$key]]=isset($rowValue[$this->allacf[$key]])?$rowValue[$this->allacf[$key]]:'';
									$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue[$this->allacf[$key]], $hKey);
								}
								elseif($optionalType == 'elementor_library'){

								}
								else if(isset($rowValue['_yoast_wpseo_'.$key])){ // Is available in yoast plugin
									$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue['_yoast_wpseo_'.$key]);
								}
								else if(isset($rowValue['_aioseop_'.$key])){ // Is available in all seo plugin
									$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue['_aioseop_'.$key]);
								}
								else if(isset($rowValue['_'.$key])){ // Is wp custom fields
									$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue['_'.$key], $hKey);
								}
								else if($fieldvalue = $this->getWoocommerceMetaValue($key, $rowValue['post_type'], $rowValue)){
									$rowValue[$key] = $fieldvalue;
								}
								//else if($aioseo_field_value == $this->getaioseoFieldValue($rowValue['ID'])){
								//changed
								else if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php')){
									if($aioseo_field_value = $this->getaioseoFieldValue($rowValue['ID'])){
										$rowValue['og_title'] = $aioseo_field_value[0]->og_title;
										$rowValue['og_description']= $aioseo_field_value[0]->og_description;
										$rowValue['custom_link'] = $aioseo_field_value[0]->canonical_url;
										$rowValue['og_image_type'] = $aioseo_field_value[0]->og_image_type;
										$rowValue['og_image_custom_fields'] = $aioseo_field_value[0]->og_image_custom_fields;
										$rowValue['og_video'] = $aioseo_field_value[0]->og_video;
										$rowValue['og_object_type'] = $aioseo_field_value[0]->og_object_type;
										$value=$aioseo_field_value[0]->og_article_tags;
										$article_tags = json_decode($value);
										$og_article_tags=$article_tags[0]->value;
										$rowValue['og_article_tags'] = $og_article_tags;
										$rowValue['og_article_section'] = $aioseo_field_value[0]->og_article_section;
										$rowValue['twitter_use_og'] = $aioseo_field_value[0]->twitter_use_og;
										$rowValue['twitter_card'] = $aioseo_field_value[0]->twitter_card;
										$rowValue['twitter_image_type'] = $aioseo_field_value[0]->twitter_image_type;
										$rowValue['twitter_image_custom_fields'] = $aioseo_field_value[0]->twitter_image_custom_fields;
										$rowValue['twitter_title'] = $aioseo_field_value[0]->twitter_title;
										$rowValue['twitter_description'] = $aioseo_field_value[0]->twitter_description;
										$rowValue['robots_default'] = $aioseo_field_value[0]->robots_default;
										// $rowValue['robots_noindex'] = $aioseo_field_value[0]->robots_noindex;
										$rowValue['robots_noarchive'] = $aioseo_field_value[0]->robots_noarchive;
										$rowValue['robots_nosnippet'] = $aioseo_field_value[0]->robots_nosnippet;
										// $rowValue['robots_nofollow'] = $aioseo_field_value[0]->robots_nofollow;
										$rowValue['robots_noimageindex'] = $aioseo_field_value[0]->robots_noimageindex;
										$rowValue['noodp'] = $aioseo_field_value[0]->robots_noodp;
										$rowValue['robots_notranslate'] = $aioseo_field_value[0]->robots_notranslate;
										$rowValue['robots_max_snippet'] = $aioseo_field_value[0]->robots_max_snippet;
										$rowValue['robots_max_videopreview'] = $aioseo_field_value[0]->robots_max_videopreview;
										$rowValue['robots_max_imagepreview'] = $aioseo_field_value[0]->robots_max_imagepreview;
										$rowValue['aioseo_title'] = $aioseo_field_value[0]->title;
										$rowValue['aioseo_description'] = $aioseo_field_value[0]->description;
										
										if(isset($aioseo_field_value[0]->keyphrases)){
											$key = $aioseo_field_value[0]->keyphrases;
											$key1 = json_decode($key);
											$rowValue['keyphrases'] = $key1->focus->keyphrase;	
										}	
									}							
								}
								else{
									$rowValue[$key] = $this->returnMetaValueAsCustomerInput($rowValue[$key], $hKey);
								}
							}
							global  $wpdb;
							if(in_array($hKey, $toolset_relationship_fieldnames)){

								if(in_array($hKey,['relationship_slug', 'intermediate'])){
									$toolset_fieldvalues = $this->getToolSetIntermediateFieldValue($rowValue['ID']);
								}elseif(in_array($hKey,['relationship_slug', 'types_relationship'])){
									$toolset_fieldvalues = $this->getToolSetRelationshipValue($rowValue['ID']);
								}
								if(isset($toolset_fieldvalues['types_relationship'])){
									$rowValue['types_relationship'] = $toolset_fieldvalues['types_relationship'];
								}
								if(isset($toolset_fieldvalues['relationship_slug'])){
									$rowValue['relationship_slug'] = $toolset_fieldvalues['relationship_slug'];
								}
								if(isset($toolset_fieldvalues['intermediate'])){
									$rowValue['intermediate'] = $toolset_fieldvalues['intermediate'];
								}
							}

							//Added for user export
							if($key =='user_login')
							{
								$wpsc_query = $wpdb->prepare("select ID from {$wpdb->prefix}users where user_login =%s", $rowValue['user_login']);
								$wpdb->get_results($wpsc_query,ARRAY_A);
							}	
							 
							if((isset($rowValue['post_excerpt']) && $rowValue['post_excerpt'])||(isset($rowValue['_wp_attachment_image_alt']) && $rowValue['_wp_attachment_image_alt'])||(isset($rowValue['post_content']) && $rowValue['post_content'])||(isset($rowValue['guid']) && $rowValue['guid'])||(isset($rowValue['post_title']) && $rowValue['post_title'])||(isset($rowValue['_wp_attached_file']) && $rowValue['_wp_attached_file'])){
								// if($key='caption'){
								// 	$rowValue['post_excerpt']=isset($rowValue['post_excerpt'])?$rowValue['post_excerpt']:'';
								// 	$rowValue[$key]=$rowValue['post_excerpt'];
								// }
								if($key='alt_text'){
									// $rowValue[$key]=$rowValue['_wp_attachment_image_alt'];
									$rowValue[$key] = isset($rowValue['_wp_attachment_image_alt']) ? $rowValue['_wp_attachment_image_alt'] : '';
								}
								if($key='image_url'){
									$rowValue[$key]=$rowValue['guid'];
								}
								if($key='description'){
									$rowValue['post_content']=isset($rowValue['post_content'])?$rowValue['post_content']:'';
									// $rowValue[$key]=$rowValue['post_content'];
									$rowValue[$key]=$rowValue['post_excerpt'];
								}
								if($key='title'){
									$rowValue['post_title']=isset($rowValue['post_title'])?$rowValue['post_title']:'';
									$rowValue[$key]=$rowValue['post_title'];
								}
								if($key='file_name'){
									$rowValue['_wp_attached_file']=isset($rowValue['_wp_attached_file'])?$rowValue['_wp_attached_file']:'';
									$file_names = explode('/', $rowValue['_wp_attached_file']);
									$file_names[2]=isset($file_names[2])?$file_names[2]:'';
									$file_name= $file_names[2];
									
									$rowValue[$key]=$file_name;
								}
							}
							// if($rowValue['_bbp_forum_type'] =='forum'||$rowValue['_bbp_forum_type']=='category' ){
							if(isset($rowValue['_bbp_forum_type']) && ($rowValue['_bbp_forum_type'] =='forum'||$rowValue['_bbp_forum_type']=='category' )){
								if($key =='Visibility'){
									$rowValue[$key]=$rowValue['post_status'];
								}
							}
							if($key =='topic_status' ||$key =='author' ||$key =='topic_type' ){
								$rowValue['topic_status']=$rowValue['post_status'];
								$rowValue['author']=$rowValue['post_author'];
								if($key =='topic_type'){
									$Topictype =get_post_meta($rowValue['_bbp_forum_id'],'_bbp_sticky_topics');
									$topic_types = get_option('_bbp_super_sticky_topics');
									$rowValue['topic_type']='Normal';
									if($Topictype){
										foreach($Topictype as $t_type){
											if($t_type['0']== $recordId){
												$rowValue['topic_type']='sticky';
											}
										}
									}elseif(!empty($topic_types)){
										foreach($topic_types as $top_type){
											if($top_type == $rowValue['ID']){
												$rowValue['topic_type']='super sticky';
											}
										}
									}
								}	
							}if($key =='reply_status'||$key =='reply_author'){
							$rowValue['reply_status']=$rowValue['post_status'];
							$rowValue['reply_author']=$rowValue['post_author'];
								}
							
							if(array_key_exists($hKey, $rowValue)){
								if($hKey=='focus_keyword'){
									$rowValue[$hKey]= isset($rowValue['_yoast_wpseo_focuskw']) ? $rowValue['_yoast_wpseo_focuskw'] :'';
	
								}
								elseif($hKey=='meta_desc') {
									  $rowValue[$hKey]= isset($rowValue['_yoast_wpseo_metadesc']) ? $rowValue['_yoast_wpseo_metadesc'] :'';
								} 
								elseif($hKey == 'cornerstone-content') {
									$rowValue[$hKey]= isset($rowValue['_yoast_wpseo_is_cornerstone']) ? $rowValue['_yoast_wpseo_is_cornerstone'] :'';
							  	}
								  
								if(isset($rowValue['_yoast_wpseo_title'])){
									$rowValue['title']= isset($rowValue['_yoast_wpseo_title']) ? $rowValue['_yoast_wpseo_title'] :'';
								}
								$result[$recordId][$hKey] = $rowValue[$hKey];
							}else{
									$result[$recordId][$hKey] = '';
							}

							//added - for acf group
							// if(in_array($hKey, $rowValue)){
						
							// 	if(strpos($hKey, 'field_') !== false){
									
							// 		$get_acf_excerpt = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE post_name = '$hKey' ");
							// 		if(!empty($get_acf_excerpt)){
							// 			$get_all_keys = array_keys($rowValue, $hKey);
									
							// 			if(count($get_all_keys) > 1){
							// 				$acf_value = '';
							// 				foreach($get_all_keys as $all_key){
							// 					$get_acf_key = substr($all_key, 1);
							// 					$acf_value .= $rowValue[$get_acf_key] .'->';
							// 				}
							// 				$acf_values = rtrim($acf_value, '->');
							// 				$result[$recordId][$get_acf_excerpt] = $acf_values;
							// 			}
							// 			else{
							// 				$get_acf_key = array_search($hKey, $rowValue);
							// 				$get_acf_key = substr($get_acf_key, 1);
							// 				$result[$recordId][$get_acf_excerpt] = $rowValue[$get_acf_key];
							// 			}
							// 			unset($result[$recordId][$hKey]);
							// 			//replace acf post name with post excerpt in headers
							// 			$this->headers[$htemp] = $get_acf_excerpt;
							// 		}
							// 	}	
							// }
						}
					}	
				}
				return $result;
			}

			public function hasToolsetRelationship($fieldname, $post_id){
				global $wpdb;
				if(is_plugin_active('types/wpcf.php')){
					//include_once('wp-admin/includes/plugin.php' );
					//include_once( 'wp-admin/includes/plugin.php' );
		            $plugins = get_plugins();
				    $plugin_version = $plugins['types/wpcf.php']['Version'];
				    if($plugin_version < '3.4.1'){
						$toolset_relationship_id = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."toolset_relationships WHERE slug = '".$fieldname."'");
						if(!empty($toolset_relationship_id)){
							$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_id' and relationship_id = '$toolset_relationship_id'" );
							//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE relationship_id = ".$toolset_relationship_id." AND parent_id = ".$post_id);
							$relationship_title = '';
							foreach($child_ids as $child_id){
								$relationship_title.= $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID = ".$child_id->child_id).'|';
							}
							return rtrim($relationship_title, '|');
						}
					}
					else{
						$relationstitle = $this->hasToolsetRelationshipNew($fieldname, $post_id);
						return $relationstitle;
					}
				}
			}

			public function hasToolsetRelationshipNew($fieldname, $post_id){
				global $wpdb;
				if(is_plugin_active('types/wpcf.php')){
					$toolset_relationship_id = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."toolset_relationships WHERE slug = '".$fieldname."'");
					if(!empty($toolset_relationship_id)){
						$post_par_id = $wpdb->get_row("SELECT group_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE element_id = ".$post_id );
						$post_par_ids = $post_par_id->group_id;
						$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_par_ids' and relationship_id = '$toolset_relationship_id'" );
					//	$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE relationship_id = ".$toolset_relationship_id." AND parent_id = ".$post_par_ids);
						$relationship_title = '';
						foreach($child_ids as $child_id){
							$post_child_id = $wpdb->get_row("SELECT element_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE group_id = ".$child_id->child_id );
							$post_child_ids = $post_child_id->element_id;
							$relationship_title.= $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID = ".$post_child_ids).'|';
						}
					
						return rtrim($relationship_title, '|');
					}
				}
			}

			public function getToolsetRepeaterParentValue($modes){
				global $wpdb;	
				$check_group_names = '';
				$mode = ExportExtension::$post_export->import_post_types($modes);

				if($modes == 'CustomPosts'){
					$mode = $this->optionalType;
				}

				$get_group = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}posts WHERE post_type = 'wp-types-group' AND post_status = 'publish' ");
				foreach($get_group as $get_group_values){
					$check_group = get_post_meta($get_group_values->id , '_wp_types_group_post_types' , true);
					$check_group = explode(',' , $check_group);
					if(in_array( $mode , $check_group)){
						$check_group_names .= $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $get_group_values->id") . "|";
					}
				}
				return rtrim($check_group_names , '|');
			}

			public function getToolsetRepeaterFieldValue($fieldname, $post_id, $fieldvalue = false){
				global $wpdb;
				//include_once( 'wp-admin/includes/plugin.php' );
				if(is_plugin_active('types/wpcf.php')){
					$plugins = get_plugins();
					$plugin_version = $plugins['types/wpcf.php']['Version'];
					if($plugin_version < '3.4.1'){
						switch($this->alltoolsetfields[$fieldname]['type']){	
						case 'textfield':
						case 'textarea':
						case 'image':
						case 'audio':
						case 'colorpicker':
						case 'image':
						case 'file':
						case 'embed':
						case 'email':
						case 'numeric':
						case 'phone':
						case 'skype':
						case 'url':
						case 'video':
						case 'wysiwyg':
						case 'checkbox':
							$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_id'" );
							//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_id );
							$toolset_fieldvalue = '';
							foreach($child_ids as $child_id){		
								$meta_value = get_post_meta($child_id->child_id, 'wpcf-'.$fieldname, true);
								$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value).'|';
							}
							$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
							if(empty($toolset_fieldvalue)){
								return $fieldvalue;
							}
							return rtrim($toolset_fieldvalue, '|');
						case 'radio': 
						case 'select':
						case 'checkboxes':
							$toolset_fieldvalue = '';
							$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_id'" );
						//	$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_id );	
							foreach($child_ids as $child_id){		
								$meta_value = get_post_meta($child_id->child_id, 'wpcf-'.$fieldname, true);	
								$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value, '', $this->alltoolsetfields[$fieldname]['type']).'|';
							}
							$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
							if(empty($toolset_fieldvalue)){
								return $fieldvalue;
							}
							return rtrim($toolset_fieldvalue, '|');
		
						case 'date':
							$toolset_fieldvalue = '';
							$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_id'" );
						//	$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_id );
							foreach($child_ids as $child_id){
								$meta_value = get_post_meta($child_id->child_id, 'wpcf-'.$fieldname, true);
								if(!empty($meta_value)){
									$meta_value = date('m/d/Y', $meta_value);
									$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value).'|';
								}
							}
							$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
							if(empty($toolset_fieldvalue)){
								return $fieldvalue;
							}
							return rtrim($toolset_fieldvalue, '|');		
						}
					}
					else{
						$toolsetfields =$this->getToolsetRepeaterFieldValueNew($fieldname, $post_id, $fieldvalue = false);
						return $toolsetfields;
					}
				}
				
				return false;
			}

            public function getToolsetRepeaterFieldValueNew($fieldname, $post_id, $fieldvalue = false){
				global $wpdb;
				
				$post_ids = $wpdb->get_row("SELECT group_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE element_id = ".$post_id );
				$post_ids = isset($post_ids) ? $post_ids : '';
				$post_par_ids = $post_ids->group_id;
			
				switch($this->alltoolsetfields[$fieldname]['type']){	
				case 'textfield':
				case 'textarea':
				case 'image':
				case 'audio':
				case 'colorpicker':
				case 'image':
				case 'file':
				case 'embed':
				case 'email':
				case 'numeric':
				case 'phone':
				case 'skype':
				case 'url':
				case 'video':
				case 'wysiwyg':
				case 'checkbox':
					$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_par_ids'" );
					//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_par_ids );
				
					$toolset_fieldvalue = '';
					foreach($child_ids as $child_id){	
						$post_child_id = $wpdb->get_row("SELECT element_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE group_id = ".$child_id->child_id );
						$post_child_ids = $post_child_id->element_id;	
						$meta_value = get_post_meta($post_child_ids, 'wpcf-'.$fieldname, true);
						$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value).'|';
					}
					$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
					if(empty($toolset_fieldvalue)){
						return $fieldvalue;
					}
					return rtrim($toolset_fieldvalue, '|');
				case 'radio': 
				case 'select':
				case 'checkboxes':
					$toolset_fieldvalue = '';
					//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_id );	
					//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_par_ids );	
					$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_par_ids'" );
					foreach($child_ids as $child_id){
						$post_child_id = $wpdb->get_row("SELECT element_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE group_id = ".$child_id->child_id );
						$post_child_ids = $post_child_id->element_id;	
						$meta_value = get_post_meta($post_child_ids, 'wpcf-'.$fieldname, true);	
						$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value, '', $this->alltoolsetfields[$fieldname]['type']).'|';
					}
					$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
					if(empty($toolset_fieldvalue)){
						return $fieldvalue;
					}
					return rtrim($toolset_fieldvalue, '|');

				case 'date':
					$toolset_fieldvalue = '';
					//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_id );
					//$child_ids = $wpdb->get_results("SELECT child_id FROM ".$wpdb->prefix."toolset_associations WHERE parent_id = ".$post_par_ids );
					$child_ids = $wpdb->get_results("select child_id from {$wpdb->prefix}toolset_associations WHERE parent_id = '$post_par_ids'" );
					foreach($child_ids as $child_id){
						$post_child_id = $wpdb->get_row("SELECT element_id FROM ".$wpdb->prefix."toolset_connected_elements WHERE group_id = ".$child_id->child_id );
						$post_child_ids = $post_child_id->element_id;	
						$meta_value = get_post_meta($post_child_ids, 'wpcf-'.$fieldname, true);
						$meta_value = date('m/d/Y', $meta_value);
						$toolset_fieldvalue.=$this->returnMetaValueAsCustomerInput($meta_value).'|';
					}
					$toolset_fieldvalue = ltrim($toolset_fieldvalue, '|');
					if(empty($toolset_fieldvalue)){
						return $fieldvalue;
					}
					return rtrim($toolset_fieldvalue, '|');		
				}
			
				return false;
			}

			public function getWoocommerceMetaValue($fieldname, $post_type, $post){
				$post_type=isset($post_type)?$post_type:'';

				if($post_type == 'shop_order_refund'){
					switch ($fieldname) {
					case 'REFUNDID':
						return $post['ID'];
					default:
						return $post[$fieldname];
					}
				}else if($post_type == 'shop_order'){
					switch ($fieldname) {
					case 'ORDERID':
						return $post['ID'];
					case 'order_status':
						return $post['post_status'];
					case 'customer_note':
						return $post['post_excerpt'];
					case 'order_date':
						return $post['post_date'];
					default:
						return $post[$fieldname];
					}
				}else if($post_type == 'shop_coupon'){
					switch ($fieldname) {
					case 'COUPONID':
						return $post['ID'];
					case 'coupon_status':
						return $post['post_status'];
					case 'description':
						return $post['post_excerpt'];
					case 'coupon_date':
						return $post['post_date'];
					case 'coupon_code':
						return $post['post_title'];
					case 'expiry_date':
						if(isset($post['date_expires'])){
						$timeinfo=date('m/d/Y',$post['date_expires']);
						}
						$timeinfo=isset($timeinfo)?$timeinfo:'';
						return $timeinfo;		
					default:
						return $post[$fieldname];
					}
				}else if($post_type == 'product_variation'){
					switch ($fieldname) {
					case 'VARIATIONID':
						return $post['ID'];
					case 'PRODUCTID':
						return $post['post_parent'];
					case 'VARIATIONSKU':
						return $post['sku'];
					default:
						return $post[$fieldname];
					}
				}
				return false;
			}

			/**
			 * Create CSV data from array
			 * @param array $data       2D array with data
			 * @param array $fields     field names
			 * @param bool $append      if true, field names will not be output
			 * @param bool $is_php      if a php die() call should be put on the first
			 *                          line of the file, this is later ignored when read.
			 * @param null $delimiter   field delimiter to use
			 * @return string           CSV data (text string)
			 */
			public function unParse ( $data = array(), $fields = array(), $append = false , $is_php = false, $delimiter = null) {
				if ( !is_array($data) || empty($data) ) $data = &$this->data;
				if ( !is_array($fields) || empty($fields) ) $fields = &$this->titles;
				if ( $this->delimiter === null ) $this->delimiter = ',';

				$string = ( $is_php ) ? "<?php header('Status: 403'); die(' '); ?>".$this->linefeed : '' ;
				$entry = array();
				// create heading
				if ($this->offset == 0 || $this->checkSplit == 'true') {
					if ( $this->heading && !$append && !empty($fields) ) {
						foreach( $fields as $key => $value ) {
							$entry[] = $this->_enclose_value($value);
			}
			$string .= implode($this->delimiter, $entry).$this->linefeed;
			$entry = array();
			}
			}

			// create data
			foreach( $data as $key => $row ) {
				foreach( $row as $field => $value ) {
					$entry[] = $this->_enclose_value($value);
			}
			$string .= implode($this->delimiter, $entry).$this->linefeed;
			$entry = array();
			}
			return $string;
			}

			/**
			 * Enclose values if needed
			 *  - only used by unParse()
			 * @param null $value
			 * @return mixed|null|string
			 */
			public function _enclose_value ($value = null) {
				if ( $value !== null && $value != '' ) {
					$delimiter = preg_quote($this->delimiter, '/');
					$enclosure = preg_quote($this->enclosure, '/');
					if(isset($value[0]) && $value[0]=='=') $value="'".$value; # Fix for the Comma separated vulnerabilities.
					if ( isset($value) && preg_match("/".$delimiter."|".$enclosure."|\n|\r/i", $value) ||isset($value[0]) && ($value[0] == ' ' ||isset($value) && substr($value, -1) == ' ') ) {
						$value = str_replace($this->enclosure, $this->enclosure.$this->enclosure, $value);
						$value = $this->enclosure.$value.$this->enclosure;
					}
					else
						$value = $this->enclosure.$value.$this->enclosure;
				}
				return $value;
			}

		/**
		 * Apply exclusion before export
		 * @param $headers  - Apply exclusion headers
		 * @return array    - Available headers after applying the exclusions
		 */
			public function applyEventExclusion ($headers) {
				$header_exclusion = array();
				foreach ($headers as $hVal) {
					if(array_key_exists($hVal, $this->eventExclusions['exclusion_headers']['header'])) {
						$header_exclusion[] = $hVal;
					}
				}
				return $header_exclusion;
			}

			public function replace_prefix_aioseop_from_fieldname($fieldname){
				if(preg_match('/_aioseop_/', $fieldname)){
					return preg_replace('/_aioseop_/', '', $fieldname);
				}

				return $fieldname;
			}
			public function getaioseoFieldValue($post_id){
				if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php'))
				{
				global $wpdb;
				$aioseo_slug =$wpdb->get_results("SELECT * FROM {$wpdb->prefix}aioseo_posts WHERE post_id='$post_id' ");
				return $aioseo_slug;
				}
			}
			public function replace_prefix_pods_from_fieldname($fieldname){
				if(preg_match('/_pods_/', $fieldname)){
					return preg_replace('/_pods_/', '', $fieldname);
				}
				return $fieldname;
			}

			public function replace_prefix_yoast_wpseo_from_fieldname($fieldname){

				if(preg_match('/_yoast_wpseo_/', $fieldname)){
					$fieldname = preg_replace('/_yoast_wpseo_/', '', $fieldname);

					if($fieldname == 'focuskw') {
						$fieldname = 'focus_keyword';
					}elseif($fieldname == 'bread-crumbs-title') { // It is comming as bctitle nowadays
						$fieldname = 'bctitle';
					}elseif($fieldname == 'metadesc') {
						$fieldname = 'meta_desc';
					}
				}

				return $fieldname;
			}

			public function replace_prefix_wpcf_from_fieldname($fieldname){
				if(preg_match('/_wpcf/', $fieldname)){
					return preg_replace('/_wpcf/', '', $fieldname);
				}
				return $fieldname;
			}

			public function replace_prefix_wpsc_from_fieldname($fieldname){
				if(preg_match('/_wpsc_/', $fieldname)){
					return preg_replace('/_wpsc_/', '', $fieldname);
				}
				return $fieldname;
			}

			public function replace_wpcr3_from_fieldname($fieldname){
				if(preg_match('/wpcr3_/', $fieldname)){
					$fieldname = preg_replace('/wpcr3_/', '', $fieldname);
				}
				return $fieldname;
			}

			public function change_fieldname_depends_on_post_type($post_type, $fieldname){
				if($post_type == 'wpcr3_review'){
					switch ($fieldname) {
					case 'ID':
						return 'review_id';
					case 'post_status':
						return 'status';
					case 'post_content':
						return 'review_text';
					case 'post_date':
						return 'date_time';
					default:
						return $fieldname;
					}
				}
				if($post_type == 'shop_order_refund'){
					switch ($fieldname) {
					case 'ID':
						return 'REFUNDID';
					default:
						return $fieldname;
					}
				}else if($post_type == 'shop_order'){
					switch ($fieldname) {
					case 'ID':
						return 'ORDERID';
					case 'post_status':
						return 'order_status';
					case 'post_excerpt':
						return 'customer_note';
					case 'post_date':
						return 'order_date';
					default:
						return $fieldname;
					}
				}else if($post_type == 'shop_coupon'){
					switch ($fieldname) {
					case 'ID':
						return 'COUPONID';
					case 'post_status':
						return 'coupon_status';
					case 'post_excerpt':
						return 'description';
					case 'post_date':
						return 'coupon_date';
					case 'post_title':
						return 'coupon_code';
					default:
						return $fieldname;
					}
				}else if($post_type == 'product_variation'){
					switch ($fieldname) {
					case 'ID':
						return 'VARIATIONID';
					case 'post_parent':
						return 'PRODUCTID';
					case 'sku':
						return 'VARIATIONSKU';
					default:
						return $fieldname;
					}
				}
				return $fieldname;
			}

			public function replace_underscore_from_fieldname($fieldname){
				if(preg_match('/_/', $fieldname)){
					$fieldname = preg_replace('/^_/', '', $fieldname);
				}
				return $fieldname;
			}

			public function fetchCategoryFieldValue($categories){
				global $wpdb;
				$bulk_category = [];
				foreach($categories as $category_id => $category){
					$term_meta = get_term_meta($category_id);
					$single_category = [];
					foreach($this->headers as $header){
						if($header == 'name'){
							$cato[] = get_term($category_id);
							$single_category[$header] = $this->hierarchy_based_term_cat_name($cato, 'category');
							//$single_category[$header] = $this->hierarchy_based_term_name(get_term($category_id), 'category');
							continue;
						}

						if(array_key_exists($header, $category)){
							$single_category[$header] = $category[$header];
						}else{
							if(isset($term_meta[$header])){
								$single_category[$header] = $this->returnMetaValueAsCustomerInput($term_meta[$header]);
							}else{
								$single_category[$header] = null;
							}
						}
					}
					array_push($bulk_category, $single_category);
				}
				return $bulk_category;
			}
			public function getJetCCTValue($data, $type, $data_type = false){
				global $wpdb;
				$jet_data = $this->JetEngineCCTFields($type);
				$darray_value=array();
				foreach ($data as $key => $dvalue) {
					foreach($dvalue as $dkey=>$value){
						if($dkey == '_ID'){
							$darray[$dkey] = $value;
						}
						elseif($dkey =='cct_status'){
							$darray[$dkey] = $value;
						}
						if(array_key_exists($dkey,$jet_data['JECCT'])){							
							if($jet_data['JECCT'][$dkey]['type'] == 'text' ||$jet_data['JECCT'][$dkey]['type'] == 'textarea'
							|| $jet_data['JECCT'][$dkey]['type'] == 'colorpicker' || $jet_data['JECCT'][$dkey]['type'] == 'iconpicker'
							|| $jet_data['JECCT'][$dkey]['type'] == 'radio' || $jet_data['JECCT'][$dkey]['type'] == 'number'
							|| $jet_data['JECCT'][$dkey]['type'] == 'wysiwyg' || $jet_data['JECCT'][$dkey]['type'] == 'switcher'
							|| $jet_data['JECCT'][$dkey]['type'] == 'date' || $jet_data['JECCT'][$dkey]['type'] == 'time'
							|| $jet_data['JECCT'][$dkey]['type'] == 'datetime-local' 
							){	
								$darray1[$jet_data['JECCT'][$dkey]['name']] = $value;
							}
							elseif( $jet_data['JECCT'][$dkey]['type'] == 'media'){	
								if(is_numeric($value)){
									$get_guid_name = $wpdb->get_results($wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE id = '$value'"));
									foreach($get_guid_name as $media_key=>$value){
										$darray1[$jet_data['JECCT'][$dkey]['name']]=$value->guid;
									}
								}
								elseif(is_serialized($value)){
									$media_value=unserialize($value);
									$darray1[$jet_data['JECCT'][$dkey]['name']] = $media_value['url'];	
								}
								else{
									$darray1[$jet_data['JECCT'][$dkey]['name']]=$value;
								}
							}
							elseif( $jet_data['JECCT'][$dkey]['type'] == 'gallery'){
								$get_meta_list = explode(',', $value);
								$get_guid ='';
								foreach($get_meta_list as $get_meta){	
									if(is_numeric($get_meta)){
										$get_guid_name = $wpdb->get_results($wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE id = '$get_meta'"));
										foreach($get_guid_name as $gallery_key=>$value){		
											$get_guid.=$value->guid.',';
										}
									}
									elseif(is_serialized($get_meta)){
										$gal_value=unserialize($get_meta);
										foreach($gal_value as $key=>$gal_val){
											$get_guid.=$gal_val['url'].',';
										}	
									}
									else{
										$get_guid .= $get_meta.',';
									}
								}
								$darray1[$jet_data['JECCT'][$dkey]['name']]=rtrim($get_guid,',');
							}						
			
							elseif($jet_data['JECCT'][$dkey]['type'] == 'checkbox'){
								$checkbox_value=unserialize($value);
								$checkbox_key_value='';
								foreach($checkbox_value as $check_key=>$check_val){
									if($check_val == 'true'){
										$checkbox_key_value.=$check_key.',';
									}
								}
								$darray1[$jet_data['JECCT'][$dkey]['name']] = rtrim($checkbox_key_value,',');				
							}
			
							elseif($jet_data['JECCT'][$dkey]['type'] == 'posts'){						
								
									$jet_posts = unserialize($value);
									$jet_posts_value='';
									foreach($jet_posts as $posts_key=>$post_val){										
											$query = "SELECT post_title FROM {$wpdb->prefix}posts WHERE id ='{$post_val}' AND post_status='publish'";
											$name = $wpdb->get_results($query);											
											if (!empty($name)) {
												$jet_posts_value.=$name[0]->post_title.',';
											}									
									}
									$post_names=rtrim($jet_posts_value,',');
								$darray1[$jet_data['JECCT'][$dkey]['name']] = $post_names;
							}
							elseif($jet_data['JECCT'][$dkey]['type'] == 'select'){
								if(is_serialized($value)){
									$select_value='';
									$gal_value=unserialize($value);
									foreach($gal_value as $select_key=>$gal_val){
										$select_value.=$gal_val.',';
									}	
								}
								else{
									$select_value=$value;
								}
								$darray1[$jet_data['JECCT'][$dkey]['name']] = rtrim($select_value,',');
							}
			
							else{
								if($jet_data['JECCT'][$dkey]['type'] == 'repeater'){
									$jet_rf_data = $this->JetEngineCCTRFFields($type);
									$val=unserialize($value);
									$repvalue='';
									$mediarepvalue='';
									$gallery_guid='';
									$checkrepvalue='';
									$postrepvalue='';
									$mediarepvalue1='';
									$mediarepvalue2='';
									$galleryrepvalue='';
									$galleryrepvalue1='';
									$galleryrepvalue2='';
									foreach ($val as  $dvalue_key=>$dvalue) {
										foreach($dvalue  as $dkey => $dvalues){
											
											if(array_key_exists($dkey,$jet_rf_data['JECCTRF'])){
												if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'text' ||$jet_rf_data['JECCTRF'][$dkey]['type'] == 'textarea'
													|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'colorpicker' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'iconpicker'
													|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'radio' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'number'
													|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'wysiwyg' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'switcher'
													|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'date' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'time'
													|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'datetime-local' 
													){
														$repvalue.=$dvalues.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($repvalue,'|');		 
												}	
												elseif( $jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
													
													if($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'id'){
														$get_guid_name = $wpdb->get_results($wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE id = '$dvalues'"));
														foreach($get_guid_name as $media_key=>$value){
															$mediarepvalue.=$value->guid.'|';	
														}
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($mediarepvalue,'|');
													}
													elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
														$val=str_replace("\\",'',$dvalues);
														$media_values = json_decode($val);
														$media_val=$media_values->url;	
														$mediarepvalue1.=$media_val.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($mediarepvalue1,'|');	
													}
													else{
														$mediarepvalue2.=$dvalues.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($mediarepvalue2,'|');
													}
												}
												elseif( $jet_rf_data['JECCTRF'][$dkey]['type'] == 'gallery'){
													if($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
														$val=str_replace("\\",'',$dvalues);
														$gallery_values = json_decode($val);
														$gallery_val='';
														foreach($gallery_values as $galleryval){
															$gallery_val.=$galleryval->url.',';
														}
														$galleryrepvalue.=rtrim($gallery_val,',').'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($galleryrepvalue,'|');
													}
													elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'id'){
														$get_meta_list = explode(',', $dvalues);
														$guid_name=[];
														foreach($get_meta_list as $get_meta){
															$get_guid_name = $wpdb->get_results($wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE id = '$get_meta'"));
															$guid_name[]=$get_guid_name['0']->guid;		
														}
														$galleryrep_value=implode(',',$guid_name);
														$galleryrepvalue1.=$galleryrep_value.'|';	
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($galleryrepvalue1,'|');
													}
													else{
														$galleryrepvalue2.=$dvalues.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']]=rtrim($galleryrepvalue2,'|');
													}
												}
												elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'checkbox'){
													$checkbox_values_key='';
													foreach($dvalues as $check_key1=>$check_val){
														if($check_val=='true'){
															$checkbox_values_key.=$check_key1.',';
														}
													}
													$checkbox_val_key =rtrim($checkbox_values_key,',');
													$checkrepvalue.=$checkbox_val_key.'|';
													$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($checkrepvalue,'|');					
												}
												elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'posts'){						
													$jet_posts =$dvalues;
													if(is_array($jet_posts)){
														$jet_posts_value='';
														foreach($jet_posts as $posts_key1=>$post_val){										
															$query = "SELECT post_title FROM {$wpdb->prefix}posts WHERE id ='{$post_val}' AND post_status='publish'";
															$name = $wpdb->get_results($query);											
															if (!empty($name)) {
																$jet_posts_value.=$name[0]->post_title.',';
															}									
														}
														$post_names=rtrim($jet_posts_value,',');
														$postrepvalue.=$post_names.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($postrepvalue,'|');
													}
													else{
														$query = "SELECT post_title FROM {$wpdb->prefix}posts WHERE id ='{$dvalues}' AND post_status='publish'";
														$name = $wpdb->get_results($query);											
														if (!empty($name)) {
															$postrepvalue1.=$name[0]->post_title.'|';
														}	
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($postrepvalue1,'|');
													}
													
												}
												elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'select'){
													if(is_array($dvalues)){
														$select_value='';
														foreach($dvalues as $select_key1=>$gal_val){
															$select_value.=$gal_val.',';
														}	
														$select_values=rtrim($select_value,',');
														$array_selval .=$select_values.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($array_selval,'|');
													}
													else{
														$array_selval1.=$dvalues.'|';
														$darray2[$jet_rf_data['JECCTRF'][$dkey]['name']] = rtrim($array_selval1,'|');
													}
												}	
																						
											}
										}
									}
								}
							}                   
						}
					}	
					if(!empty($darray1) && empty($darray2)){
					$data_array_values=array_merge($darray,$darray1);
					}
					elseif(empty($darray1) && !empty($darray2)){
						$data_array_values=array_merge($darray,$darray2);
					}
					else{
						$data_array_values=array_merge($darray,$darray1,$darray2);
					}
					$darray_value[$key]=$data_array_values;		
				}
				//added
				if(!empty($darray)){	
					return $darray_value;
				}
				else{
					return ;
				}	
			}

			public function JetEngineCCTRFFields($type){
				global $wpdb;	
				$jet_rf_field = array();
				$get_meta_fields = $wpdb->get_results($wpdb->prepare("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = %s and status = %s", $type, 'content-type'));
				$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		
				foreach($unserialized_meta as $jet_key => $jet_value){
					if($jet_value['type'] == 'repeater'){
						$customFields["JECCT"][ $jet_value['name']]['name']  = $jet_value['name'];
						$fields=$jet_value['repeater-fields'];
						foreach($fields as $rep_fieldkey => $rep_fieldvalue){
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['label'] = $rep_fieldvalue['title'];
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['name']  = $rep_fieldvalue['name'];
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['type']  = $rep_fieldvalue['type'];
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['options']  = isset($rep_fieldvalue['options']) ? $rep_fieldvalue['options'] : '';
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['is_multiple']  = isset($rep_fieldvalue['is_multiple']) ? $rep_fieldvalue['is_multiple'] : '';
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['is_array']  = isset($rep_fieldvalue['is_array']) ? $rep_fieldvalue['is_array'] : '';
							$customFields["JECCTRF"][ $rep_fieldvalue['name']]['value_format'] = isset($rep_fieldvalue['value_format']) ? $rep_fieldvalue['value_format'] : '';
							$jet_rf_field[] = $rep_fieldvalue['name'];
		
						}
					}
				}
				return $customFields;	
			}
			public function JetEngineCCTFields($type){
				global $wpdb;	
				$jet_field = array();
				$get_meta_fields = $wpdb->get_results($wpdb->prepare("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = %s and status = %s", $type, 'content-type'));
				$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		
				foreach($unserialized_meta as $jet_key => $jet_value){
					$customFields["JECCT"][ $jet_value['name']]['label'] = $jet_value['title'];
					$customFields["JECCT"][ $jet_value['name']]['name']  = $jet_value['name'];
					$customFields["JECCT"][ $jet_value['name']]['type']  = $jet_value['type'];
					$customFields["JECCT"][ $jet_value['name']]['options'] = isset($jet_value['options']) ? $jet_value['options'] : '';
					$customFields["JECCT"][ $jet_value['name']]['is_multiple'] = isset($jet_value['is_multiple']) ? $jet_value['is_multiple'] : '';
					$customFields["JECCT"][ $jet_value['name']]['is_array'] = isset($jet_value['is_array']) ? $jet_value['is_array'] : '';
					$jet_field[] = $jet_value['name'];
				}
				return $customFields;	
			}

		
			public function returnMetaValueAsCustomerInput($meta_value, $header = false , $data_type = false){

				if(is_array($meta_value)){
					if($data_type == 'checkboxes'){	
						$metas_value = '';
						foreach($meta_value as $key => $meta_values){
							$meta_value = $meta_values[0];
							if(!empty($meta_value)){
								$metas_value .= $meta_value . ',';
							}
						}
						return rtrim($metas_value , ',');
					}		
					$meta_value[0]=isset($meta_value[0])?$meta_value[0]:'';	
					$meta_value = $meta_value[0];
					if(!empty($meta_value)){
						if(is_serialized($meta_value)){
							return unserialize($meta_value);
						}else if(is_array($meta_value)){
							return implode('|', $meta_value);
						}else if(is_string($meta_value)){
							return $meta_value;
						}else if($this->isJSON($meta_value) === true){
							return json_decode($meta_value);
						}
						return $meta_value;
					}
					return $meta_value;
				}else{
					if(is_serialized($meta_value)){
						
						$meta_value= unserialize($meta_value);
						
						if(isset($meta_value) && is_array($meta_value)){
							foreach($meta_value as $meta){
								if(!is_array($meta)){
									return implode('|', array($meta));	
								}
								else{
									return implode('|',$meta);
								}
							}
						}
						return $meta_value;
					}else if(is_array($meta_value)){
						return implode('|', $meta_value);
					}else if(is_string($meta_value)){
						// added this case for yoast seo premium - focuskeyphrase export
						if(strpos($meta_value, '[{"keyword":') !== FALSE){
							$decode_value = json_decode($meta_value, true);
							$keywords = array_column($decode_value, 'keyword');
							return implode('|', $keywords);
						}
						// added this case for yoast seo premium - synonym export
						elseif(strpos($meta_value, '["",') !== FALSE){
							$decode_value1 = json_decode($meta_value, true);
							array_shift($decode_value1);
							return implode('|', $decode_value1);
						}
						else{
							return rtrim($meta_value , '|');
						}

						//return $meta_value;
					}else if($this->isJSON($meta_value) === true){
						return json_decode($meta_value);
					}
				}
				return $meta_value;
			}

			public function isJSON($meta_value) {
				$json = json_decode($meta_value);
				return $json && $meta_value != $json;
			}

			public function hierarchy_based_term_name($term, $taxanomy_type){

				$tempo = array();
				$termo = '';
				$i=0;
				foreach($term as $termkey => $terms){
					$tempo[] = $terms->name;
					$temp_hierarchy_terms = [];
					
					if(!empty($terms->parent)){
						$temp1 = $terms->name;
						//$termo = '';
						$i++;
						
						$termexp = explode(',',$termo);
						
						
						$termo = implode(',',$termexp);
						//$termo = implode(',',$termunset);
						
						$temp_hierarchy_terms[] = $terms->name;
						$hierarchy_terms = $this->call_back_to_get_parent($terms->parent, $taxanomy_type, $tempo, $temp_hierarchy_terms);
						$parent_name=get_term($terms->parent);
						$termo .= $this->split_terms_by_arrow($hierarchy_terms,$parent_name->name).',';
						
	
					}else{
						
					    //if($terms->parent == 0){
						if(in_array($terms->name,$tempo)){
						
								$termo .= $terms->name.',';
						
						}
					}
				}
				return $termo;
				// $temp_hierarchy_terms = [];
				// if(!empty($term->parent)){
				// 	$temp_hierarchy_terms[] = $term->name;
				// 	$hierarchy_terms = $this->call_back_to_get_parent($term->parent, $taxanomy_type, $temp_hierarchy_terms);
				// 	return $this->split_terms_by_arrow($hierarchy_terms);

				// }else{
				// 	return $term->name;
				// }
			}

			public function hierarchy_based_term_cat_name($term, $taxanomy_type){
				$tempo = array();
				$termo = '';
				foreach($term as $terms){
					$tempo[] = $terms->name;
					$temp_hierarchy_terms = [];
					if(!empty($terms->parent)){
						$temp_hierarchy_terms[] = $terms->name;
						$hierarchy_terms = $this->call_back_to_get_parent($terms->parent, $taxanomy_type, $tempo, $temp_hierarchy_terms);
						$parent_name=get_term($terms->parent);
						 $termo = $this->split_terms_by_arrow($hierarchy_terms,$parent_name->name);

					}else{
						$termo = $terms->name;
						
					}
				}
				return $termo;
			}
			public function call_back_to_get_parent($term_id, $taxanomy_type,$tempo, $temp_hierarchy_terms = []){
				$term = get_term($term_id, $taxanomy_type);
				if(!empty($term->parent)){
					if(in_array($term->name,$tempo)){
						
						$temp_hierarchy_terms[] = $term->name;
					
						$temp_hierarchy_terms = $this->call_back_to_get_parent($term->parent, $taxanomy_type,$tempo, $temp_hierarchy_terms);
					}
					else{
						$temp_hierarchy_terms[] = '';
				
						$temp_hierarchy_terms = $this->call_back_to_get_parent($term->parent, $taxanomy_type,$tempo, $temp_hierarchy_terms);
					}
					
				}else{
					if(in_array($term->name,$tempo)){
						$temp_hierarchy_terms[] = $term->name;
					}
					else{
						$temp_hierarchy_terms[] = '';
					}
				}
				return $temp_hierarchy_terms;
			}

			// public function call_back_to_get_parent($term_id, $taxanomy_type, $temp_hierarchy_terms = []){
			// 	$term = get_term($term_id, $taxanomy_type);
			// 	if(!empty($term->parent)){
			// 		$temp_hierarchy_terms[] = $term->name;
			// 		$temp_hierarchy_terms = $this->call_back_to_get_parent($term->parent, $taxanomy_type, $temp_hierarchy_terms);
			// 	}else{
			// 		$temp_hierarchy_terms[] = $term->name;
			// 	}
			// 	return $temp_hierarchy_terms;
			// }

			public function split_terms_by_arrow($hierarchy_terms,$termParentName){
				krsort($hierarchy_terms);
				$terms_value = $termParentName.'>'.$hierarchy_terms[0];
				//return implode('>', $hierarchy_terms);
				return $terms_value;
			}

			public function getToolSetRelationshipValue($post_id){
				//include_once( 'wp-admin/includes/plugin.php' );
				$plugins = get_plugins();
				$plugin_version = $plugins['types/wpcf.php']['Version'];
				if($plugin_version < '3.4.1'){
					global $wpdb;
					$toolset_relation_values['relationship_slug']='';
					$toolset_intermadiate_values['types_relationship']='';
					//$toolset_relation_values = array();
					//$toolset_intermadiate_values = array();
					$toolset_fieldvalues = array();
					$get_slug = "SELECT distinct relationship_id FROM {$wpdb->prefix}toolset_associations WHERE parent_id ='{$post_id}'";
					$relat_slug = $wpdb->get_results($get_slug,ARRAY_A);
					$get_slug1 = "SELECT distinct relationship_id FROM {$wpdb->prefix}toolset_associations WHERE child_id ='{$post_id}'";
					$relat_slug1 = $wpdb->get_results($get_slug1,ARRAY_A);
					$rel_slug = (object) array_merge( (array) $relat_slug, (array) $relat_slug1); 	
					foreach($rel_slug as $relkey=>$relvalue)
					{	
						$relationship_id = $relvalue['relationship_id'];
						if(!empty($relationship_id)){
							$slug_id="SELECT slug FROM {$wpdb->prefix}toolset_relationships WHERE id IN ($relationship_id) AND origin = 'wizard' ";
							$relationship=$wpdb->get_results($slug_id,ARRAY_A);
						}

						if(is_array($relationship)){
							foreach($relationship as $keys=>$values) {
								$toolset_relation_values['relationship_slug'] .= $values['slug'] . '|';
							}	
						}
						$relationships_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}toolset_relationships WHERE id = $relationship_id AND origin = 'wizard' ");
						//$parents_post = "SELECT post_title FROM {$wpdb->prefix}toolset_associations INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}toolset_associations.child_id WHERE {$wpdb->prefix}toolset_associations.parent_id={$post_id} AND {$wpdb->prefix}toolset_associations.relationship_id={$relationships_id} AND post_status = 'publish'";
						$parents_post = "SELECT post_title FROM {$wpdb->prefix}toolset_associations INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}toolset_associations.child_id WHERE {$wpdb->prefix}toolset_associations.parent_id='$post_id' AND {$wpdb->prefix}toolset_associations.relationship_id='$relationships_id' AND post_status = 'publish'";
						$parent_title1 = $wpdb->get_results($parents_post,ARRAY_A);
						//$parents_post1 = "SELECT post_title FROM {$wpdb->prefix}toolset_associations INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}toolset_associations.parent_id WHERE {$wpdb->prefix}toolset_associations.child_id={$post_id} AND {$wpdb->prefix}toolset_associations.relationship_id={$relationships_id} AND post_status = 'publish'";
						$parents_post1 = "SELECT post_title FROM {$wpdb->prefix}toolset_associations INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}toolset_associations.parent_id WHERE {$wpdb->prefix}toolset_associations.child_id='$post_id' AND {$wpdb->prefix}toolset_associations.relationship_id='$relationships_id' AND post_status = 'publish'";
						$parent_title2 = $wpdb->get_results($parents_post1,ARRAY_A);
	
						$parent_title = array_merge($parent_title1, $parent_title2);
					
						$parent_value = '';
						for($i = 0 ; $i<count($parent_title) ; $i++){
							$parent_value .= $parent_title[$i]['post_title'] . ",";
						}
						$parent_value = rtrim($parent_value , ",");
						$toolset_intermadiate_values['types_relationship'] .= $parent_value . "|";
				
					}	
					if(is_array($toolset_relation_values)){
						foreach($toolset_relation_values as $relation_value){
							$toolset_fieldvalues['relationship_slug'] = rtrim($relation_value , "|");
						}
					}	
					foreach($toolset_intermadiate_values as $types_value){
						$types_value = ltrim($types_value , "|");
						$toolset_fieldvalues['types_relationship'] = rtrim($types_value , "|");
					}
					return $toolset_fieldvalues;
				}
	            else{
					global $wpdb;
					$toolset_relation_values = array();
					$toolset_intermadiate_values = array();
					$toolset_fieldvalues = array();
					
					$get_con_slug = "SELECT id FROM {$wpdb->prefix}toolset_connected_elements WHERE element_id ='{$post_id}'";
					$relat_con_slug = $wpdb->get_results($get_con_slug,ARRAY_A);
					$relat_con_slug[0]['id'] = isset($relat_con_slug[0]['id']) ? $relat_con_slug[0]['id'] : '';
					$con_id =$relat_con_slug[0]['id'];
				
					$get_slug = "SELECT distinct relationship_id FROM {$wpdb->prefix}toolset_associations WHERE parent_id ='{$con_id}'";
					$relat_slug = $wpdb->get_results($get_slug,ARRAY_A);
					
					
					$get_slug1 = "SELECT distinct relationship_id FROM {$wpdb->prefix}toolset_associations WHERE child_id ='{$con_id}'";
					$relat_slug1 = $wpdb->get_results($get_slug1,ARRAY_A);
					$parent_value2 = '';
				
					$rel_slug = (object) array_merge( (array) $relat_slug, (array) $relat_slug1); 
					
					foreach($rel_slug as $relkey=>$relvalue)
					{
						
						$relationship_id = $relvalue['relationship_id'];
					
						if(!empty($relationship_id)){
							$slug_id="SELECT slug FROM {$wpdb->prefix}toolset_relationships WHERE id IN ($relationship_id) AND origin = 'wizard' ";
							$relationship=$wpdb->get_results($slug_id,ARRAY_A);
							
	
						}
					
						if(is_array($relationship)){
							foreach($relationship as $keys=>$values) {
								$toolset_relation_values['relationship_slug'] .= $values['slug'] . '|';
							}	
						}
						
						$relationships_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}toolset_relationships WHERE id = $relationship_id AND origin = 'wizard' ");
						
						$get_child_slug = "SELECT distinct child_id FROM {$wpdb->prefix}toolset_associations WHERE parent_id ='{$con_id}' and relationship_id ='{$relationships_id}'";
						$relat_child_slug = $wpdb->get_results($get_child_slug,ARRAY_A);
						
						$parent_value1 = '';
					
						if($relat_child_slug){
							foreach($relat_child_slug as $chiildkey => $childvalue){
								$childconid = $childvalue['child_id'];
							
								$get_child_slug1 = "SELECT distinct element_id FROM {$wpdb->prefix}toolset_connected_elements WHERE id ='{$childconid}'";
								$relat_child_slug1 = $wpdb->get_results($get_child_slug1,ARRAY_A);
								$childid = $relat_child_slug1[0]['element_id'];
							
								$parents_post = "SELECT post_title FROM {$wpdb->prefix}posts WHERE ID ={$childid} AND post_status = 'publish'";
								$parent_title1 = $wpdb->get_results($parents_post,ARRAY_A);
							   
								$parent_value1 .= $parent_title1[0]['post_title'] . ",";
			
							}
							$parent_value = rtrim($parent_value1 , ",");
		
						}



                        $get_par_slug = "SELECT distinct parent_id FROM {$wpdb->prefix}toolset_associations WHERE child_id ='{$con_id}' and relationship_id ='{$relationships_id}'";
						$relat_par_slug = $wpdb->get_results($get_par_slug,ARRAY_A);
						$parent_value2 = '';
					
						if($relat_par_slug){
							
							foreach($relat_par_slug as $chiildkey => $childvalue){
								$childconid = $childvalue['parent_id'];
								
								$get_child_slug1 = "SELECT distinct element_id FROM {$wpdb->prefix}toolset_connected_elements WHERE id ='{$childconid}'";
								$relat_child_slug1 = $wpdb->get_results($get_child_slug1,ARRAY_A);
								$childid = $relat_child_slug1[0]['element_id'];
								
								$parents_post = "SELECT post_title FROM {$wpdb->prefix}posts WHERE ID ={$childid} AND post_status = 'publish'";
								$parent_title1 = $wpdb->get_results($parents_post,ARRAY_A);
							
								$parent_value2 .= $parent_title1[0]['post_title'] . ",";
								
			
							}
							$parent_value = rtrim($parent_value2 , ",");
		
						}

						$toolset_intermadiate_values['types_relationship'] .= $parent_value . "|";
	
					}
					if(is_array($toolset_relation_values)){
						foreach($toolset_relation_values as $relation_value){
							$toolset_fieldvalues['relationship_slug'] = rtrim($relation_value , "|");
							
						}
					}	
					foreach($toolset_intermadiate_values as $types_value){
						$types_value = ltrim($types_value , "|");
						$toolset_fieldvalues['types_relationship'] = rtrim($types_value , "|");
					}
				
					return $toolset_fieldvalues;
				
				}


			}

			public function getToolSetIntermediateFieldValue($post_id){
				global $wpdb;
			//	include_once( 'wp-admin/includes/plugin.php' );
				//include_once( 'wp-admin/includes/plugin.php' );
				$plugins = get_plugins();
				$plugin_version = $plugins['types/wpcf.php']['Version'];
				if($plugin_version < '3.4.1'){
					$toolset_fieldvalues = [];
					$intermediate_rel=$wpdb->get_var("select relationship_id from {$wpdb->prefix}toolset_associations where intermediary_id ='{$post_id}'");
					if(!empty($intermediate_rel)){
						$intermediate_slug=$wpdb->get_var("select slug from {$wpdb->prefix}toolset_relationships where  id IN ($intermediate_rel)");
					}
					$intern_rel=$intern_relationship=$rel_intermediate=$related_posts= $related_title='';
	
					if(!empty($intermediate_slug)){
						$toolset_fieldvalues['relationship_slug'] = $intermediate_slug;
						$intermediate_post = "select parent_id,child_id,post_title from {$wpdb->prefix}toolset_associations INNER JOIN {$wpdb->prefix}posts on {$wpdb->prefix}posts.ID = {$wpdb->prefix}toolset_associations.child_id WHERE {$wpdb->prefix}toolset_associations.intermediary_id='{$post_id}' AND post_status = 'publish'";
	
						$related_ids = $wpdb->get_results($intermediate_post,ARRAY_A);
	
						foreach($related_ids as $keyd=>$valued)
						{
							$parent_id = $valued['parent_id'];
							$child_id = $valued['child_id'];
							if(!empty($parent_id)){
								$related_posts = $wpdb->get_var("select post_title from {$wpdb->prefix}posts where ID = $parent_id AND post_status = 'publish'");
							}
							if(!empty($child_id)){
								$related_title = $wpdb->get_var("select post_title from {$wpdb->prefix}posts where ID = $child_id AND post_status = 'publish'");
							}
							$rel_intermediate .= $related_posts.','.$related_title;
							$intern_rel =  $rel_intermediate;
							$intern_relationship=rtrim($intern_rel,"| ");   
							$toolset_fieldvalues['intermediate']= $intern_relationship;
						}
					}
				}
				else{
					global $wpdb;
					$toolset_fieldvalues = [];
					
					$get_con_slug = "SELECT id FROM {$wpdb->prefix}toolset_connected_elements WHERE element_id ='{$post_id}'";
					$relat_con_slug = $wpdb->get_results($get_con_slug,ARRAY_A);
					$relat_con_slug = isset($relat_con_slug) ?$relat_con_slug : '';
					$con_id =$relat_con_slug[0]['id'];
					
					if(!empty($con_id)){
						$intermediate_rel=$wpdb->get_var("select relationship_id from {$wpdb->prefix}toolset_associations where intermediary_id ='{$con_id}'");
					}
					 
					if(!empty($intermediate_rel)){
						$intermediate_slug=$wpdb->get_var("select slug from {$wpdb->prefix}toolset_relationships where  id IN ($intermediate_rel)");
					}
					$intern_rel=$intern_relationship=$rel_intermediate=$related_posts= $related_title='';
	
					if(!empty($intermediate_slug)){
						$toolset_fieldvalues['relationship_slug'] = $intermediate_slug;
						$intermediate_post = "select parent_id,child_id from {$wpdb->prefix}toolset_associations where intermediary_id='{$con_id}' and relationship_id = '{$intermediate_rel}'";
	
						$related_ids = $wpdb->get_results($intermediate_post,ARRAY_A);
	
						foreach($related_ids as $keyd=>$valued)
						{
							$parent_con_id = $valued['parent_id'];
							$child_con_id = $valued['child_id'];
							$get_par_con = "SELECT element_id FROM {$wpdb->prefix}toolset_connected_elements WHERE id ='{$parent_con_id}'";
							$relat_par_con = $wpdb->get_results($get_par_con,ARRAY_A);
							$parent_id =$relat_par_con[0]['element_id'];
							$get_child_con = "SELECT element_id FROM {$wpdb->prefix}toolset_connected_elements WHERE id ='{$child_con_id}'";
							$relat_child_con = $wpdb->get_results($get_child_con,ARRAY_A);
							$child_id =$relat_child_con[0]['element_id'];
							if(!empty($parent_id)){
								$related_posts = $wpdb->get_var("select post_title from {$wpdb->prefix}posts where ID = $parent_id AND post_status = 'publish'");
							}
							if(!empty($child_id)){
								$related_title = $wpdb->get_var("select post_title from {$wpdb->prefix}posts where ID = $child_id AND post_status = 'publish'");
							}
							$rel_intermediate .= $related_posts.','.$related_title;
							$intern_rel =  $rel_intermediate;
							$intern_relationship=rtrim($intern_rel,"| ");   
							$toolset_fieldvalues['intermediate']= $intern_relationship;
						}
					}
					return $toolset_fieldvalues;
				}
		
			}

		}

		return new exportExtension();
