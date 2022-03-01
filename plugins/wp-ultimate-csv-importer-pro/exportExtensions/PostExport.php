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

/**
 * Class PostExport
 * @package Smackcoders\WCSV
 */
class PostExport {

	protected static $instance = null,$mapping_instance,$export_handler,$export_instance;
	public $offset = 0;	
	public $limit;
	public $totalRowCount;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$export_instance = ExportExtension::getInstance();
		}
		return self::$instance;
	}

	/**
	 * PostExport constructor.
	 */
	public function __construct() {
		$this->plugin = Plugin::getInstance();
	}

	/**
	 * Get records based on the post types
	 * @param $module
	 * @param $optionalType
	 * @param $conditions
	 * @return array
	 */
	public function getRecordsBasedOnPostTypes ($module, $optionalType, $conditions ,$offset , $limit,$category_module,$category_export) {
		global $wpdb;
		if(!empty($category_export)){
			trim($category_export);
			if($optionalType =='posts'){
				$optionalType='post';
			}
			$terms_id = [];
			foreach(explode(',',$category_export) as $category_export){
				$category_export = trim($category_export);
				$pos = strpos($category_export,'&');
				if ($pos === false) {
					$terms_id[] =  $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name='$category_export'");
				}else{
					$amp=$category_export;
					$terms_id[] =  $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name='$amp'");
				}
			}
			$offset = self::$export_instance->offset;
			$limit = self::$export_instance->limit;
			foreach($terms_id as $termid){
				$taxo_id=$termid[0]->term_id;
				$rel_id =  $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships where term_taxonomy_id='$taxo_id'",ARRAY_A);
				foreach($rel_id as $rel_key => $rel_val){
					foreach($rel_val as $object_key => $object_val){
						$taxonomyexp[] =  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts where ID = $object_val AND post_type = '$optionalType' And post_status !='trash' order by ID asc limit $offset, $limit",ARRAY_A);
					}
				}
			}
		
			foreach($taxonomyexp as $tax_key => $tax_val){
					foreach($tax_val as $tax_exp){
                        $result[]=$tax_exp['ID'];
					}
			}
			self::$export_instance->totalRowCount = count($taxonomyexp);
		}else{
		
			if($module == 'CustomPosts' && $optionalType == 'nav_menu_item'){
				$get_menu_id = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms AS t LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'nav_menu' ", ARRAY_A);
				$get_menu_arr = array_column($get_menu_id, 'term_id');
				self::$export_instance->totalRowCount = count($get_menu_arr);
				return $get_menu_arr;			
			}

			if($module == 'CustomPosts' && $optionalType == 'widgets'){
				$get_widget_id = $wpdb->get_row("SELECT option_id FROM {$wpdb->prefix}options where option_name = 'widget_recent-posts' ", ARRAY_A);
				self::$export_instance->totalRowCount = 1;
				return $get_widget_id;			
			}

			if($module == 'CustomPosts') {
				$module = $optionalType;
			} elseif ($module == 'WooCommerceOrders') {
				$module = 'shop_order';
			}
			elseif ($module == 'Marketpress') {
				$module = 'product';
			}
			elseif ($module == 'WooCommerceCoupons') {
				$module = 'shop_coupon';
			}
			elseif ($module == 'WooCommerceRefunds') {
				$module = 'shop_order_refund';
			}
			elseif ($module == 'WooCommerceVariations') {
				$module = 'product_variation';
			}
			elseif($module == 'WPeCommerceCoupons'){
				$module = 'wpsc-coupon';
			}
			elseif($module == 'Images'){
				$module='attachment';
				
			}
			else {
				$module = self::import_post_types($module);
			}
			$get_post_ids = "select DISTINCT ID from {$wpdb->prefix}posts";
			//$get_post_ids .= " where post_type = '$module' and post_status in ('publish','draft','future','private','pending','inherit')";
			$get_post_ids .= " where post_type = '$module' ";
			/**
			 * Check for specific status
			 */
			if($module == 'shop_order'){
				if(!empty($conditions['specific_status']['status'])) {
					if($conditions['specific_status']['status'] == 'All') {
						$get_post_ids .= " and post_status in ('wc-completed','wc-cancelled','wc-refunded','wc-on-hold','wc-processing','wc-pending')";
					} elseif($conditions['specific_status']['status'] == 'Completed Orders') {
						$get_post_ids .= " and post_status in ('wc-completed')";
					} elseif($conditions['specific_status']['status'] == 'Cancelled Orders') {
						$get_post_ids .= " and post_status in ('wc-cancelled')";
					} elseif($conditions['specific_status']['status'] == 'On Hold Orders') {
						$get_post_ids .= " and post_status in ('wc-on-hold')";	
					} elseif($conditions['specific_status']['status'] == 'Processing Orders') {
						$get_post_ids .= " and post_status in ('wc-processing')";	
					} elseif($conditions['specific_status']['status'] == 'Pending Orders') {
						$get_post_ids .= " and post_status in ('wc-pending')";
					} 
				} else {
					$get_post_ids .= " and post_status in ('wc-completed','wc-cancelled','wc-on-hold','wc-processing','wc-pending')";
				}
			}elseif ($module == 'shop_coupon') {
				if(!empty($conditions['specific_status']['status'])) {
					if($conditions['specific_status']['status'] == 'All') {
						$get_post_ids .= " and post_status in ('publish','draft','pending')";
					} elseif($conditions['specific_status']['status']== 'Publish') {
						$get_post_ids .= " and post_status in ('publish')";
					} elseif($conditions['specific_status']['status'] == 'Draft') {
						$get_post_ids .= " and post_status in ('draft')";
					} elseif($conditions['specific_status']['status'] == 'Pending') {
						$get_post_ids .= " and post_status in ('pending')";
					} 
				} else {
					$get_post_ids .= " and post_status in ('publish','draft','pending')";
				}

			}elseif ($module == 'shop_order_refund') {

			}
			elseif( $module == 'lp_order'){
				$get_post_ids .= " and post_status in ('lp-pending', 'lp-processing', 'lp-completed', 'lp-cancelled', 'lp-failed')";
			}
			else {
				if(!empty($conditions['specific_status']['status'])) {
					if($conditions['specific_status']['status'] == 'All') {
						$get_post_ids .= " and post_status in ('publish','draft','future','private','pending')";
					} elseif($conditions['specific_status']['status'] == 'Publish' || $conditions['specific_status']['status'] == 'Sticky') {
						$get_post_ids .= " and post_status in ('publish')";
					} elseif($conditions['specific_status']['status'] == 'Draft') {
						$get_post_ids .= " and post_status in ('draft')";
					} elseif($conditions['specific_status']['status'] == 'Scheduled') {
						$get_post_ids .= " and post_status in ('future')";
					} elseif($conditions['specific_status']['status'] == 'Private') {
						$get_post_ids .= " and post_status in ('private')";
					} elseif($conditions['specific_status']['status'] == 'Pending') {
						$get_post_ids .= " and post_status in ('pending')";
					} elseif($conditions['specific_status']['status'] == 'Protected') {
						$get_post_ids .= " and post_status in ('publish') and post_password != ''";
					}
				} 
				else {
					
					// if(!$module=='attachment'){
					// 	$get_post_ids .= " and post_status in ('publish','draft','future','private','pending')";
					// }
					if($module!='attachment'){
						$get_post_ids .= " and post_status in ('publish','draft','future','private','pending')";
					}
					else{
						$get_post_ids .= " and post_status in ('publish','draft','future','private','pending','inherit')";
					}
				}
			}
			// Check for specific period
			if(!empty($conditions['specific_period']['is_check']) && $conditions['specific_period']['is_check'] == 'true') {
				if($conditions['specific_period']['from'] == $conditions['specific_period']['to']){
					$get_post_ids .= " and post_date >= '" . $conditions['specific_period']['from'] . "'";
				}else{
					// $get_post_ids .= " and post_date >= '" . $conditions['specific_period']['from'] . "' and post_date <= '" . $conditions['specific_period']['to'] . "'";
					$get_post_ids .= " and post_date >= '" . $conditions['specific_period']['from'] . "' and post_date <= '" . $conditions['specific_period']['to'] . " 23:00:00'";
				}
			}
			if($module == 'eshop')
				$get_post_ids .= " and pm.meta_key = '_eshop_product'";
			if($module == 'woocommerce')
				$get_post_ids .= " and pm.meta_key = '_sku'";
			if($module == 'wpcommerce')
				$get_post_ids .= " and pm.meta_key = '_wpsc_sku'";

			// Check for specific authors
			if(!empty($conditions['specific_authors']['is_check'] == '1')) {
				if(isset($conditions['specific_authors']['author'])) {
					$get_post_ids .= " and post_author = {$conditions['specific_authors']['author']}";
				}
			}
			//WpeCommercecoupons
			if($module == 'wpsc-coupon'){
				$get_post_ids = "select DISTINCT ID from {$wpdb->prefix}wpsc_coupon_codes";
			}
		
			//WpeCommercecoupons
			$get_total_row_count = $wpdb->get_col($get_post_ids);
			self::$export_instance->totalRowCount = count($get_total_row_count);
			$offset = self::$export_instance->offset;
			$limit = self::$export_instance->limit;
			$offset_limit = " order by ID asc limit $offset, $limit";
			$query_with_offset_limit = $get_post_ids . $offset_limit;
			$result = $wpdb->get_col($query_with_offset_limit);
			if(is_plugin_active('jet-engine/jet-engine.php')){
				$get_slug_name = $wpdb->get_results($wpdb->prepare("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'"));
				foreach($get_slug_name as $key=>$get_slug){
					$value=$get_slug->slug;
					$optional_type=$value;	
					if($optionalType ==$optional_type){
						$table_name='wp_jet_cct_'.$optional_type;
						$get_total_row_count= $wpdb->get_results($wpdb->prepare("SELECT _ID FROM $table_name WHERE cct_status = 'publish'"));
						self::$export_instance->totalRowCount = count($get_total_row_count);
					}
				}
			}
		
			// Get sticky post alone on the specific post status
			if(isset($conditions['specific_period']['is_check']) && $conditions['specific_status']['is_check'] == 'true') {
				if(isset($conditions['specific_status']['status']) && $conditions['specific_status']['status'] == 'Sticky') {
					$get_sticky_posts = get_option('sticky_posts');
					foreach($get_sticky_posts as $sticky_post_id) {
						if(in_array($sticky_post_id, $result))
							$sticky_posts[] = $sticky_post_id;
					}
					return $sticky_posts;
				}
			}
		
		}

		return $result;
	}

	public function import_post_types($import_type, $importAs = null) {	
		$import_type = trim($import_type);
		$module = array('Posts' => 'post', 'Pages' => 'page', 'Users' => 'user', 'Comments' => 'comments', 'Taxonomies' => $importAs, 'CustomerReviews' =>'wpcr3_review', 'Categories' => 'categories', 'Tags' => 'tags', 'eShop' => 'post', 'WooCommerce' => 'product', 'WPeCommerce' => 'wpsc-product','WPeCommerceCoupons' => 'wpsc-product', 'Marketpress' => 'product', 'MarketPressVariations' => 'mp_product_variation','WooCommerceVariations' => 'product', 'WooCommerceOrders' => 'product', 'WooCommerceCoupons' => 'product', 'WooCommerceRefunds' => 'product', 'CustomPosts' => $importAs);
		foreach (get_taxonomies() as $key => $taxonomy) {
			$module[$taxonomy] = $taxonomy;
		}
		if(array_key_exists($import_type, $module)) {
			return $module[$import_type];
		}
		else {
			return $import_type;
		}
	}

	/**
	 * Function to export the meta information based on Fetch ACF field information to be export
	 * @param $id
	 * @return mixed
	 */
	public function getPostsMetaDataBasedOnRecordId ($id, $module, $optionalType) {	
		global $wpdb;
		$allacf = $alltype = $checkRep = $parent = array();

		if($module == 'Users'){
			$query = $wpdb->prepare("SELECT user_id,meta_key,meta_value FROM {$wpdb->prefix}users wp JOIN {$wpdb->prefix}usermeta wpm ON wpm.user_id = wp.ID where meta_key NOT IN (%s,%s) AND ID=%d", '_edit_lock', '_edit_last', $id);
		}else if($module == 'Categories' || $module == 'Taxonomies' || $module == 'Tags'){
			$query = $wpdb->prepare("SELECT wp.term_id,meta_key,meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key NOT IN (%s,%s) AND wp.term_id = %d", '_edit_lock', '_edit_last', $id);
		}else{	
			$query = $wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key NOT IN (%s,%s) AND ID=%d", '_edit_lock', '_edit_last', $id);
		}
	
		$get_acf_fields = $wpdb->get_results("SELECT ID, post_excerpt, post_content, post_name, post_parent, post_type FROM {$wpdb->prefix}posts where post_type = 'acf-field'", ARRAY_A);
		$group_unset = array('customer_email', 'product_categories', 'exclude_product_categories');
	
		if(!empty($get_acf_fields)){
			foreach ($get_acf_fields as $key => $value) {
				if(!empty($value['post_parent'])){
					$parent = get_post($value['post_parent']);
					if(!empty($parent)){
						if($parent->post_type == 'acf-field'){
							$allacf[$value['post_excerpt']] = $parent->post_excerpt.'_'.$value['post_excerpt']; 
						}else{
							$allacf[$value['post_excerpt']] = $value['post_excerpt']; 	
						}
					}else{
						$allacf[$value['post_excerpt']] = $value['post_excerpt']; 
					}
				}else{
					$allacf[$value['post_excerpt']] = $value['post_excerpt']; 
				}
		
				self::$export_instance->allacf = $allacf;
		
				$content = unserialize($value['post_content']);
				$alltype[$value['post_excerpt']] = $content['type'];

				if($content['type'] == 'repeater' || $content['type'] == 'flexible_content'|| $content['type'] == 'group' ){
					$checkRep[$value['post_excerpt']] = $this->getRepeater($value['ID']);
				}else{
					$checkRep[$value['post_excerpt']] = "";
				}
			}
		}
		self::$export_instance->allpodsfields = $this->getAllPodsFields();

		if($module == 'Categories' || $module == 'Tags' || $module == 'Taxonomies'){
			self::$export_instance->alltoolsetfields = get_option('wpcf-termmeta');
		}
		elseif($module == 'Users'){
			self::$export_instance->alltoolsetfields = get_option('wpcf-usermeta');
		
		}
		else{
			self::$export_instance->alltoolsetfields = get_option('wpcf-fields');
		}

		if(!empty(self::$export_instance->alltoolsetfields)){
			$i = 1;
			foreach (self::$export_instance->alltoolsetfields as $key => $value) {
				$typesf[$i] = 'wpcf-'.$key;
				$typeOftypesField[$typesf[$i]] = $value['type']; 
				$i++;
			}
		}
		$typeOftypesField=isset($typeOftypesField)?$typeOftypesField:'';
		self::$export_instance->typeOftypesField = $typeOftypesField;	
		$result = $wpdb->get_results($query);	
    	// jeteng fields

		if(is_plugin_active('jet-engine/jet-engine.php')){
			//$jet_enginefields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status != 'trash' AND slug = '$optionalType'"),ARRAY_A);
			$jet_enginefields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status = %s AND slug = %s",'publish',$optionalType),ARRAY_A);
			$jet_enginefields[0]['meta_fields']=isset($jet_enginefields[0]['meta_fields'])?$jet_enginefields[0]['meta_fields']:'';
			
			$unserialized_meta = maybe_unserialize($jet_enginefields[0]['meta_fields']);
			$unserialized_meta=isset($unserialized_meta)?$unserialized_meta:'';
	
			if(is_array($unserialized_meta)){
				foreach($unserialized_meta as $jet_key => $jet_value){
					$jet_field_label = $jet_value['title'];
					$jet_field_type = $jet_value['type'];
					if($jet_field_type != 'repeater'){					
						$jet_field_namearr[] = $jet_value['name'];
					}
					else{
						$jet_field_namearr[] = $jet_value['name'];
						$fields=$jet_value['repeater-fields'];
						foreach($fields as $rep_fieldkey => $rep_fieldvalue){
							$jet_field_namearr1[] = $rep_fieldvalue['name'];
						
						}
					}
				}	
			}
		
			if(isset($jet_field_namearr1) && is_array($jet_field_namearr1) ){
				if(is_array($jet_field_namearr)){
					$jet_cpt_fields_name=array_merge($jet_field_namearr,$jet_field_namearr1);
				}
				else{
					$jet_cpt_fields_name= $jet_field_namearr1;
				}
				
			}
			else{
				$jet_field_namearr = isset($jet_field_namearr) ? $jet_field_namearr : '';
				$jet_cpt_fields_name= $jet_field_namearr;
			}
			
		    //jeteng metabox fields

			global $wpdb;	
			//$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name='jet_engine_meta_boxes'"),ARRAY_A);
			$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s",'jet_engine_meta_boxes'),ARRAY_A);
			if(!empty($get_meta_fields)){
				$unserialized_meta = maybe_unserialize($get_meta_fields[0]['option_value']);
			}
			else{
				$unserialized_meta = '';
			}
			
			if(is_array($unserialized_meta)){
				$arraykeys = array_keys($unserialized_meta);
		
				foreach($arraykeys as $val){
					$values = explode('-',$val);
					$v = $values[1];
				}
			}
			
		
			for($i=1 ; $i<=$v ; $i++){
				$unserialized_meta['meta-'.$i]= isset($unserialized_meta['meta-'.$i])? $unserialized_meta['meta-'.$i] : '';
				$fields= $unserialized_meta['meta-'.$i];

				if(!empty($fields)){
					foreach($fields['meta_fields'] as $jet_key => $jet_value){
						if($jet_value['type'] != 'repeater'){
							$jet_field_name1[] = $jet_value['name'];
						}
						else{
							$jet_field_name1[] = $jet_value['name'];
							$jet_rep_fields = $jet_value['repeater-fields'];
							foreach($jet_rep_fields as $jet_rep_fkey => $jet_rep_fvalue){
								$jet_field_name2[] = $jet_rep_fvalue['name'];
							}
						}
					}
				}
				
			}
			if( isset($jet_field_name2) && is_array($jet_field_name2)){
				if(is_array($jet_field_name1)){
					$jet_field_name = array_merge($jet_field_name1,$jet_field_name2);
				}
				else{
					$jet_field_name= $jet_field_name2;
				}
			}
			else{
				$jet_field_name= $jet_field_name1;
			}
			
				
			//}

			///jetengine custom taxonomy fields
			//$jet_taxfields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_taxonomies WHERE status != 'trash' AND slug = '$optionalType'"),ARRAY_A);
			$jet_taxfields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_taxonomies WHERE status != %s AND slug = %s",'trash',$optionalType),ARRAY_A);
			if(!empty($jet_taxfields)){
				$unserialized_taxmeta= maybe_unserialize($jet_taxfields[0]['meta_fields']);
			}
			else{
				$unserialized_taxmeta= '';
			}
			
			if(is_array($unserialized_taxmeta))	{
				foreach($unserialized_taxmeta as $jet_taxkey => $jet_taxvalue){
					
					$jet_field_tax_label = $jet_taxvalue['title'];
					$jet_field_tax_type = $jet_taxvalue['type'];
					if($jet_field_tax_type != 'repeater'){
						$jet_field_tax_namearr[] = $jet_taxvalue['name'];
					}
					else{
						$jet_field_tax_namearr[] = $jet_taxvalue['name'];
						$taxfields=$jet_taxvalue['repeater-fields'];
						foreach($taxfields as $rep_taxfieldkey => $rep_taxfieldvalue){
							if(isset($rep_taxfieldvalue['name'])){
								$jet_field_tax_namearr1[] = $rep_taxfieldvalue['name'];
							}
						}
					}	
				}
			}
			
			if( isset($jet_field_tax_namearr1)){
				if(is_array($jet_field_tax_namearr)){
					$jet_tax_fields_name=array_merge($jet_field_tax_namearr,$jet_field_tax_namearr1);
				}
				else{
					$jet_tax_fields_name=$jet_field_tax_namearr1;
				}
				
			}
			else{
				$jet_field_tax_namearr = isset($jet_field_tax_namearr) ? $jet_field_tax_namearr : '';
				$jet_tax_fields_name=$jet_field_tax_namearr ;
			}
			
		}
       else{
			$jet_cpt_fields_name =$jet_field_name= $jet_tax_fields_name = '';
	   }

		if(!empty($result)) {
		
			foreach($result as $key => $value) {
				if(is_array($jet_cpt_fields_name)&& isset($value->meta_key)){
					if(in_array($value->meta_key,$jet_cpt_fields_name)){
						//$jet_enginefields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status != 'trash' AND slug = '$optionalType'"),ARRAY_A);
						
						$jet_enginefields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status != %s AND slug = %s",'trash',$optionalType),ARRAY_A);
						if(!empty($jet_enginefields)){
							$unserialized_meta = maybe_unserialize($jet_enginefields[0]['meta_fields']);
						}
						else{
							$unserialized_meta = '';
						}
						$jet_types=array();
						$jet_rep_cpttypes=array();
						foreach($unserialized_meta as $jet_key => $jet_value){
							$jet_field_label = $jet_value['title'];
							$jet_cptfield_names = $jet_value['name'];
							$jet_field_type = $jet_value['type'];
							if($jet_field_type != 'repeater'){
								$jet_cptfields[$jet_cptfield_names]=$jet_cptfield_names;
								$jet_types[$jet_cptfield_names] = $jet_field_type;
							}
							else{
								$jet_cptfields[$jet_cptfield_names]=$jet_cptfield_names;
								$jet_types[$jet_cptfield_names] = $jet_field_type;
								$fields=$jet_value['repeater-fields'];
								foreach($fields as $rep_fieldkey => $rep_fieldvalue){
									$jet_rep_cptfields_label = $rep_fieldvalue['name'];
									$jet_rep_cptfields_type  = $rep_fieldvalue['type'];
									$jet_rep_cptfields[$jet_rep_cptfields_label] = $jet_rep_cptfields_label;
									$jet_rep_cpttypes[$jet_rep_cptfields_label]  = $jet_rep_cptfields_type;
								}
							}
				
						}
						self::$export_instance->jet_cptfields = $jet_cptfields;
						self::$export_instance->jet_types = $jet_types;
						if($jet_rep_cptfields){
							self::$export_instance->jet_rep_cptfields = $jet_rep_cptfields;
							self::$export_instance->jet_rep_cpttypes  = $jet_rep_cpttypes;
						}
						else{
							$jet_rep_cptfields = '';
							$jet_rep_cpttypes = '';
						}
						$this->getCustomFieldValue($id, $value, $checkRep, $allacf, $typeOftypesField, $alltype, $jet_cptfields, $jet_types, $jet_rep_cptfields, $jet_rep_cpttypes,  $parent, $typesf, $group_unset , $optionalType , self::$export_instance->allpodsfields, $module);
					}
				}
				if((isset($value->meta_key) && is_array($jet_field_name))){
					if(in_array($value->meta_key,$jet_field_name)){
						//$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name='jet_engine_meta_boxes'"),ARRAY_A);
						$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s",'jet_engine_meta_boxes'),ARRAY_A);
						$get_meta_fields[0]['option_value'] = isset($get_meta_fields[0]) ? $get_meta_fields[0]['option_value'] : '';
						$unserialized_meta = maybe_unserialize($get_meta_fields[0]['option_value']);
						//$count =count($unserialized_meta);
						if(is_array($unserialized_meta)){
							$arraykeys = array_keys($unserialized_meta);
			
							foreach($arraykeys as $val){
								$values = explode('-',$val);
								$v = $values[1];
							}
						}
						
				
						//$jet_rep_fields=[];
						for($i=1 ; $i<=$v ; $i++){
							$unserialized_meta['meta-'.$i] = isset($unserialized_meta['meta-'.$i])? $unserialized_meta['meta-'.$i] :'';
							$fields = $unserialized_meta['meta-'.$i];
							if(!empty($fields)){
								$jet_metatypes=array();
								$jet_reptype=array();
								foreach($fields['meta_fields'] as $jet_key => $jet_value){
									$jet_field_label = $jet_value['title'];
									$jet_field_names = $jet_value['name'];
									$jet_field_type = $jet_value['type'];
									if($jet_field_type != 'repeater'){
		
										$jet_metafields[$jet_field_names]=$jet_field_names;
									
										$jet_metatypes[$jet_field_names] = $jet_field_type;
										
									}
									else{
										$jet_metafields[$jet_field_names]=$jet_field_names;
										$jet_metatypes[$jet_field_names] = $jet_field_type;
										$repfields=$jet_value['repeater-fields'];
										$jet_repfield=array();
										//$jet_reptype=array();
										foreach($repfields as $rep_fieldkey => $rep_fieldvalue){
											$jet_rep_fields_label = $rep_fieldvalue['name'];
											$jet_rep_fields_type  = $rep_fieldvalue['type'];
										
											$jet_repfield[$jet_rep_fields_label] = $jet_rep_fields_label;
											$jet_reptype[$jet_rep_fields_label]  = $jet_rep_fields_type;
										}
									}		
								}
							}
							
							self::$export_instance->jet_metafields = $jet_metafields;
							self::$export_instance->jet_metatypes = $jet_metatypes;
							if(!empty($jet_repfield)){
								self::$export_instance->jet_repfield = $jet_repfield;
								self::$export_instance->jet_reptype  = $jet_reptype;
							}
							else{
								$jet_repfield = '';
								$jet_reptype = '';
							}
							$this->getCustomFieldValue($id, $value, $checkRep, $allacf, $typeOftypesField, $alltype, $jet_metafields, $jet_metatypes, $jet_repfield, $jet_reptype,  $parent, $typesf, $group_unset , $optionalType , self::$export_instance->allpodsfields, $module);
						}	
					}
				}
				if(isset($value->meta_key)&& is_array($jet_tax_fields_name)){
					if(in_array($value->meta_key,$jet_tax_fields_name)){
						//$jety_taxfields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_taxonomies WHERE status != 'trash' AND slug = '$optionalType'"),ARRAY_A);
						$jety_taxfields=$wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_taxonomies WHERE status != %s AND slug = %s",'trash',$optionalType),ARRAY_A);
						
						$taxunserialized_meta = maybe_unserialize($jety_taxfields[0]['meta_fields']);
						foreach($taxunserialized_meta as $tax_key => $tax_value){
							$jet_taxfield_label = $tax_value['title'];
							$jet_taxfield_names = $tax_value['name'];
							$jet_taxfield_type = $tax_value['type'];
							if($jet_taxfield_type != 'repeater'){
								$jet_ctax_fields[$jet_taxfield_names]=$jet_taxfield_names;
								$jet_tax_types[$jet_taxfield_names] = $jet_taxfield_type;
							}
							else{
								$jet_ctax_fields[$jet_taxfield_names]=$jet_taxfield_names;
								$jet_tax_types[$jet_taxfield_names] = $jet_taxfield_type;
								$taxfields=$tax_value['repeater-fields'];
								foreach($taxfields as $rep_taxfieldkey => $rep_taxfieldvalue){
									$jet_rep_taxfields_label = $rep_taxfieldvalue['name'];
									$jet_rep_taxfields_type  = $rep_taxfieldvalue['type'];
									$jet_rep_taxfields[$jet_rep_taxfields_label] = $jet_rep_taxfields_label;
									$jet_rep_taxtypes[$jet_rep_taxfields_label]  = $jet_rep_taxfields_type;
								}
							}
				
						}
						self::$export_instance->jet_taxfields = $jet_ctax_fields;
						self::$export_instance->jet_taxtypes = $jet_tax_types;
						if($jet_rep_taxfields){
							self::$export_instance->jet_rep_taxfields = $jet_rep_taxfields;
							self::$export_instance->jet_rep_taxtypes  = $jet_rep_taxtypes;
						}
						else{
							$jet_rep_taxfields = '';
							$jet_rep_taxtypes = '';
						}
						$this->getCustomFieldValue($id, $value, $checkRep, $allacf, $typeOftypesField, $alltype, $jet_ctax_fields, $jet_tax_types, $jet_rep_taxfields, $jet_rep_taxtypes,  $parent, $typesf, $group_unset , $optionalType , self::$export_instance->allpodsfields, $module);

					}
				}
				if(is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
					if($value->meta_key == 'rank_math_schema_BlogPosting'){
						$rank_value=$value->meta_value;	
						$rank_math=unserialize($rank_value);
						$Selector=$rank_math['speakable']['cssSelector'];
						$cssSelector=implode(',',$Selector);
						
						//image details
						$rank_math_image_type=$rank_math['image'];
						unset($rank_math_image_type['@type']);
						unset($rank_math_image_type['url']);
						$rank_math_image_group=array();
						$rank_math_image_property=array();
						$rank_math_image_value='';
						foreach($rank_math_image_type as $key=>$rank_math_image){						
							if(is_array($rank_math_image)){
								$rank_math_image_group[]=$rank_math_image;							
							}
							else{
								$rank_math_image_property[$key]=$rank_math_image;							
							}
						}
						foreach($rank_math_image_property as $key=>$rank_math_image_property_values){
							$rank_math_image_value.=$key.':'.$rank_math_image_property_values.';';
						}
						$rank_math_image_values=rtrim('image->'.$rank_math_image_value,';');
						
						//image group details
						$rank_math_image_gp_values='';
						foreach($rank_math_image_group as $rank_math_image_group_values){
							$rank_math_image_group_value='';
							foreach($rank_math_image_group_values as $key=>$rank_math_image_group_val){
								$rank_math_image_group_value.=$key.':'.$rank_math_image_group_val.';';
							}
							$rank_math_image_gp_value=rtrim($rank_math_image_group_value,';');
							$rank_math_image_gp_values.=$rank_math_image_gp_value.',';
						}					
						$rank_math_image_group_values=rtrim('image->'.$rank_math_image_gp_values,',');
						
						//author details
						$rank_math_author_type=$rank_math['author'];
						unset($rank_math_author_type['@type']);
						unset($rank_math_author_type['url']);
						$rank_math_author_group=array();
						$rank_math_author_property=array();
						$rank_math_author_value='';
						foreach($rank_math_author_type as $key=>$rank_math_author){
							if(is_array($rank_math_author)){
								$rank_math_author_group[]=$rank_math_author;							
							}
							else{
								$rank_math_author_property[$key]=$rank_math_author;							
							}
						}
						foreach($rank_math_author_property as $key=>$rank_math_author_property_values){
							$rank_math_author_value.=$key.':'.$rank_math_author_property_values.';';
						}
						$rank_math_author_values=rtrim('author->'.$rank_math_author_value,';');
						
						//author group details
						$rank_math_author_gp_values='';
						foreach($rank_math_author_group as $rank_math_author_group_values){
							$rank_math_author_group_value='';
							foreach($rank_math_author_group_values as $key=>$rank_math_author_group_val){
								$rank_math_author_group_value.=$key.':'.$rank_math_author_group_val.';';
							}
							$rank_math_author_gp_value=rtrim($rank_math_author_group_value,';');
							$rank_math_author_gp_values.=$rank_math_author_gp_value.',';
						}
						$rank_math_author_group_values=rtrim('author->'.$rank_math_author_gp_values,',');					
						
						//speakable details
						$rank_math_speakable_type=$rank_math['speakable'];
						unset($rank_math_speakable_type['@type']);
						unset($rank_math_speakable_type['cssSelector']);
						$rank_math_speakable_group=array();
						$rank_math_speakable_property=array();
						$rank_math_speakable_value='';
						foreach($rank_math_speakable_type as $key=>$rank_math_speakable){
							
							if(is_array($rank_math_speakable)){
								$rank_math_speakable_group[]=$rank_math_speakable;							
							}
							else{
								$rank_math_speakable_property[$key]=$rank_math_speakable;							
							}
						}
						foreach($rank_math_speakable_property as $key=>$rank_math_speakable_property_values){
							$rank_math_speakable_value.=$key.':'.$rank_math_speakable_property_values.';';
						}
						$rank_math_speakable_values=rtrim('speakable->'.$rank_math_speakable_value,';');
						
						//speakable group details
						$rank_math_speakable_gp_values='';
						foreach($rank_math_speakable_group as $rank_math_speakable_group_values){
							$rank_math_speakable_group_value='';
							foreach($rank_math_speakable_group_values as $key=>$rank_math_speakable_group_val){
								$rank_math_speakable_group_value.=$key.':'.$rank_math_speakable_group_val.';';
							}
							$rank_math_speakable_gp_value=rtrim($rank_math_speakable_group_value,';');
							$rank_math_speakable_gp_values.=$rank_math_speakable_gp_value.',';
						}					
						$rank_math_speakable_group_values=rtrim('speakable->'.$rank_math_speakable_gp_values,',');
						
						//other details
						$rank_math_new_type=$rank_math;
						unset($rank_math_new_type['image']);
						unset($rank_math_new_type['author']);
						unset($rank_math_new_type['speakable']);
						unset($rank_math_new_type['headline']);
						unset($rank_math_new_type['description']);
						unset($rank_math_new_type['@type']);
						unset($rank_math_new_type['enableSpeakable']);
						unset($rank_math_new_type['dateModified']);
						unset($rank_math_new_type['datePublished']);
						unset($rank_math_new_type['metadata']);
						$rank_math_ne_group=array();
						$rank_math_new_property=array();
						$rank_math_new_value = '';
						foreach($rank_math_new_type as $key=>$rank_math_new){
								
							if(is_array($rank_math_new)){
								$rank_math_new_group[]=$rank_math_new;	
							}
							else{
								$rank_math_new_property[$key]=$rank_math_new;								
							}
						}
						foreach($rank_math_new_property as $key=>$rank_math_new_property_values){
							$rank_math_new_value.=$key.':'.$rank_math_new_property_values.';';
						}
						$rank_math_new_values=rtrim('title->'.$rank_math_new_value,';');

						//other group details
						$rank_math_new_gp_values='';
						foreach($rank_math_new_group as $rank_math_new_group_values){
							$rank_math_new_group_value='';
							foreach($rank_math_new_group_values as $key=>$rank_math_new_group_val){
								$rank_math_new_group_value.=$key.':'.$rank_math_new_group_val.';';
							}
							$rank_math_new_gp_value=rtrim($rank_math_new_group_value,';');
							$rank_math_new_gp_values.=$rank_math_new_gp_value.',';
						}
						$rank_math_new_group_values=rtrim('title->'.$rank_math_new_gp_values,',');
							
						$advanced_editor=$rank_math_image_values.'|'.$rank_math_author_values.'|'.$rank_math_speakable_values.'|'.$rank_math_new_values;
						$advanced_editor_group_values=$rank_math_image_group_values.'|'	.$rank_math_author_group_values.'|'.$rank_math_speakable_group_values.'|'.$rank_math_new_group_values;
						$image_type=$rank_math['image']['@type'];
						$image_url=$rank_math['image']['url'];
						$author_type=$rank_math['author']['@type'];
						$author_name=$rank_math['author']['name'];
						$speakable_type=$rank_math['speakable']['@type'];
						$enable_speakable=$rank_math['enableSpeakable'];
						$date_modified=$rank_math['dateModified'];
						$date_published=$rank_math['datePublished'];

						
						self::$export_instance->data[$id]['cssSelector'] = $cssSelector;
						self::$export_instance->data[$id]['image_type'] = $image_type;
						self::$export_instance->data[$id]['image_url'] = $image_url;
						self::$export_instance->data[$id]['author_type'] = $author_type;
						self::$export_instance->data[$id]['author_name'] = $author_name;
						self::$export_instance->data[$id]['speakable_type'] = $speakable_type;
						self::$export_instance->data[$id]['enable_speakable'] = $enable_speakable;
						self::$export_instance->data[$id]['date_modified'] = $date_modified;
						self::$export_instance->data[$id]['date_published'] = $date_published;
						self::$export_instance->data[$id]['advanced_editor'] = $advanced_editor;
						self::$export_instance->data[$id]['advanced_editor_group_values'] = $advanced_editor_group_values;
					}
					if($value->meta_key == 'rank_math_advanced_robots'){
						$rank_robots_value=$value->meta_value;
						$rank_robots=unserialize($rank_robots_value);
						$max_snippet=$rank_robots['max-snippet'];
						$max_video_preview=$rank_robots['max-video-preview'];
						$max_image_preview=$rank_robots['max-image-preview'];
						$rank_math_advanced_robots=$max_snippet.','.$max_video_preview.','.$max_image_preview;
						self::$export_instance->data[$id]['rank_math_advanced_robots'] = $rank_math_advanced_robots;
					}
				}

				if($value->meta_key == 'rank_math_robots'){
					$rank_robots_meta_value = $value->meta_value;
					$rank_robots_metas = unserialize($rank_robots_meta_value);
					foreach($rank_robots_metas as $robots_meta){
						self::$export_instance->data[$id][$robots_meta] = 1;
					}
				}
			
				if($value->meta_key == 'rank_math_schema_BlogPosting'){
					$rank_value=$value->meta_value;	
					$rank_math=unserialize($rank_value)	;
					$headline=$rank_math['headline'];
					$schema_description=$rank_math['description'];
					$article_type=$rank_math['@type'];
					$re_id =  $wpdb->get_results("SELECT redirection_id FROM {$wpdb->prefix}rank_math_redirections_cache where object_id='$id'");	
					$redirect_id=$re_id[0];
					$redirection_id=$redirect_id->redirection_id;
					$result =  $wpdb->get_results("SELECT url_to,header_code FROM {$wpdb->prefix}rank_math_redirections where id='$redirection_id'");	
					$rank_math_redirections=$result[0];
					$url_to=$rank_math_redirections->url_to;
					$header_code=$rank_math_redirections->header_code;
					
					self::$export_instance->data[$id]['headline'] = $headline;
					self::$export_instance->data[$id]['schema_description'] = $schema_description;
					self::$export_instance->data[$id]['article_type'] = $article_type;
					self::$export_instance->data[$id]['destination_url'] = $url_to;
					self::$export_instance->data[$id]['redirection_type'] = $header_code;
				}
				if($value->meta_key == 'rank_math_advanced_robots'){
					$rank_robots_value=$value->meta_value;
					$rank_robots=unserialize($rank_robots_value);
					$max_snippet=$rank_robots['max-snippet'];
					$max_video_preview=$rank_robots['max-video-preview'];
					$max_image_preview=$rank_robots['max-image-preview'];
					$rank_math_advanced_robots=$max_snippet.','.$max_video_preview.','.$max_image_preview;
					self::$export_instance->data[$id]['rank_math_advanced_robots'] = $rank_math_advanced_robots;
				}
			
				else{
					$jet_fields = $jet_field_type = $jet_rep_fields = $jet_rep_types = '';
					$typesf=isset($typesf)?$typesf:'';
					$jet_types=isset($jet_types)?$jet_types:'';
					$this->getCustomFieldValue($id, $value, $checkRep, $allacf, $typeOftypesField, $alltype, $jet_fields, $jet_types, $jet_rep_fields, $jet_rep_types,  $parent, $typesf, $group_unset , $optionalType , self::$export_instance->allpodsfields, $module);
				}
			}
		}
		return self::$export_instance->data;
	}

	public function getAllPodsFields(){		
		$pods_fields = [];
		if(is_plugin_active('pods/init.php')){
			global $wpdb;
			$pods_fields_query_result = $wpdb->get_results("SELECT post_name FROM ".$wpdb->prefix."posts WHERE post_type = '_pods_field'");	
			foreach($pods_fields_query_result as $single_result){
				$pods_fields[] = $single_result->post_name;	
			}
		}
		return $pods_fields;
	}

	public function getCustomFieldValue($id, $value, $checkRep, $allacf, $typeOftypesField, $alltype, $jet_fields, $jet_types, $jet_rep_fields, $jet_rep_types, $parent, $typesf, $group_unset , $optionalType , $pods_type, $module){
		global $wpdb; 
		$taxonomies = get_taxonomies();
		$down_file = false;
		if ($value->meta_key == '_thumbnail_id') {
			$attachment_file = null;
			$get_attachment = $wpdb->prepare("select guid from {$wpdb->prefix}posts where ID = %d AND post_type = %s", $value->meta_value, 'attachment');
			$attachment_file = $wpdb->get_var($get_attachment);
			self::$export_instance->data[$id][$value->meta_key] = '';
			$value->meta_key = 'featured_image';
			self::$export_instance->data[$id][$value->meta_key] = $attachment_file;
		}else if($value->meta_key == '_downloadable_files'){ 
			$downfiles = unserialize($value->meta_value); 
			if(!empty($downfiles) && is_array($downfiles)){
				foreach($downfiles as $dk => $dv){
					$down_file .= $dv['name'].','.$dv['file'].'|';
				}
			}	
			self::$export_instance->data[$id]['downloadable_files'] = rtrim($down_file,"|");
		}
		elseif($value->meta_key == '_upsell_ids'){
			$upselldata = unserialize($value->meta_value);
			if(!empty($upselldata) && is_array($upselldata)){
				foreach($upselldata as $upselldata_value){
					$upselldata_query = $wpdb->prepare("SELECT post_title FROM {$wpdb->prefix}posts where id = %d", $upselldata_value);
					$upselldata_value=$wpdb->get_results($upselldata_query);	
					$upselldata_item[] = $upselldata_value[0]->post_title;
				}
				$upsellids = implode(',',$upselldata_item);
			}
			else{
				$upsellids = $upselldata;
			}
			self::$export_instance->data[$id]['upsell_ids'] =  $upsellids;
		}
		elseif($value->meta_key == '_crosssell_ids'){
			$cross_selldata = unserialize($value->meta_value);
			if(!empty($cross_selldata) && is_array($cross_selldata)){
				foreach($cross_selldata as $cross_selldata_value){
					$cross_selldata_query = $wpdb->prepare("SELECT post_title FROM {$wpdb->prefix}posts where id = %d", $cross_selldata_value);
					$cross_selldata_value=$wpdb->get_results($cross_selldata_query);
					
					$cross_selldata_item[] = $cross_selldata_value[0]->post_title;
				}
				$cross_sellids = implode(',',$cross_selldata_item);
			}
			else{
				$cross_sellids = $cross_selldata;
			}
			self::$export_instance->data[$id]['crosssell_ids'] =  $cross_sellids;
		}
		elseif($value->meta_key == '_children'){
			$grpdata = unserialize($value->meta_value);
			$grpids = implode(',',$grpdata);
			self::$export_instance->data[$id]['grouping_product'] =  $grpids;
		}elseif($value->meta_key == '_product_image_gallery'){
			if(strpos($value->meta_value, ',') !== false) {
				$file_data = explode(',',$value->meta_value);
				foreach($file_data as $k => $v){
				
					$ids=$v;
						$types_caption=$wpdb->get_results("select post_excerpt from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
						$types_description=$wpdb->get_results("select post_content from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
						$types_title=$wpdb->get_results("select post_title from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
						$types_alt_text=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attachment_image_alt' AND post_id='$ids'" ,ARRAY_A);
						$types_filename=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attached_file' AND post_id='$ids'" ,ARRAY_A);
						$types_filename[0]['meta_value']=isset($types_filename[0]['meta_value'])?$types_filename[0]['meta_value']:'';
						$filename=$types_filename[0]['meta_value'];
						$file_names=explode('/', $filename);
						$file_names[2]=isset($file_names[2])?$file_names[2]:'';
						$file_name= $file_names[2];
						self::$export_instance->data[$id]['product_caption'] = $types_caption;
						self::$export_instance->data[$id]['product_description'] = $types_description;
						self::$export_instance->data[$id]['product_title'] = $types_title;
						self::$export_instance->data[$id]['product_alt_text'] = $types_alt_text;
						self::$export_instance->data[$id]['product_file_name'] = $file_name;
					$attachment = wp_get_attachment_image_src($v);
					$attachment[0]=isset($attachment[0])?$attachment[0]:'';
					$attach[$k] = $attachment[0];
				}
				if(isset($attach)){
					foreach($attach as $values){
						$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$values'" ,ARRAY_A);
						global $wpdb;
						$gallery_data .= $values.'|';
					}
				}
				$gallery_data=isset($gallery_data)?$gallery_data:'';
				$gallery_data = rtrim($gallery_data , '|');
				self::$export_instance->data[$id]['product_image_gallery'] = $gallery_data;
			}else{
				$attachment = wp_get_attachment_image_src($value->meta_value);
				self::$export_instance->data[$id]['product_image_gallery'] = $attachment[0];
			}
		}elseif($value->meta_key == '_sale_price_dates_from'){
			if(!empty($value->meta_value)){
				self::$export_instance->data[$id]['sale_price_dates_from'] = date('Y-m-d',$value->meta_value);
			}
		}
		elseif($value->meta_key == '_sale_price_dates_to'){
			if(!empty($value->meta_value)){
				self::$export_instance->data[$id]['sale_price_dates_to'] = date('Y-m-d',$value->meta_value);
			}
		}
		elseif(strpos($value->meta_key,'relation_') !== false){
			$metquery = "SELECT meta_key  FROM {$wpdb->prefix}postmeta WHERE post_id='{$id}'  ";
			$relatedposttitle = '';
			$get_keyval = $wpdb->get_results($metquery);
			foreach($get_keyval as $keys){
				$get_key = $keys->meta_key;
			   if(strpos($get_key,'relation_') !== false) {
					$relkey []=$get_key;
					
			   }
			}
			$relationkeys= array_values($relkey);
			
			$arraykey = array_unique($relationkeys);
			
			$jetrelkey =implode('|',$arraykey);
			
			
			foreach($arraykey as $arraycomkey => $arraycomval){
				
				$metval = "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id='{$id}' AND meta_key ='$arraycomval'  ";
				$get_val = $wpdb->get_results($metval);
				$metavalues = array();
				foreach($get_val as $getvals){
					$metavalues []= $getvals->meta_value;
					
				}
				$countpage = count($metavalues);
				foreach($metavalues as $metavals){
					if($countpage>1){
						$metquery = "SELECT post_title FROM {$wpdb->prefix}posts WHERE id ='{$metavals}'  ";
						$get_title = $wpdb->get_results($metquery);
						$related_title[]=$get_title[0]->post_title;
						
						$related_titles = implode(',',$related_title)	;
					}
					else{
						$metquery = "SELECT post_title FROM {$wpdb->prefix}posts WHERE id ='{$metavals}'  ";
						$get_title = $wpdb->get_results($metquery);
						$related_titles=$get_title[0]->post_title;
						
					}
					
				}
				if(isset($related_titles)){
					$relatedposttitle .= $related_titles.'|';
				}
				else{
					$relatedposttitle = '';
				}
				
			    
			}
			$relatedposttitle = isset($relatedposttitle) ? $relatedposttitle : '';
			self::$export_instance->data[$id]['jet_related_post'] = rtrim($relatedposttitle, '|');	
			self::$export_instance->data[$id]['jet_relation_metakey'] = $jetrelkey;
			
		}
		else {       
			if(isset($allacf) && is_array($allacf) && array_search($value->meta_key, $allacf)){         
				$repeaterOfrepeater = false;
				$alltype[$value->meta_key]=isset($alltype[$value->meta_key])?$alltype[$value->meta_key]:'';
				$getType = $alltype[$value->meta_key];
			
				if(empty($getType)){
					$temp_fieldname = array_search($value->meta_key, $allacf);
					$getType = $alltype[$temp_fieldname];
				}
				if($getType == 'taxonomy'){
					if(is_serialized($value->meta_value)){
						$value->meta_value = unserialize($value->meta_value);
						foreach($value->meta_value as $meta){
							$termname = $wpdb->get_row($wpdb->prepare("select name from {$wpdb->prefix}terms where term_id= %d",$meta));
							$terms[]=$termname->name;	
						}
						$value->meta_value = implode(',',$terms );	
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
						
					}
				}
				if($getType == 'table'){
						$tab_key=$value->meta_key;
						$tab_id=$value->post_id;
						$tab_value = $wpdb->get_results($wpdb->prepare("SELECT meta_value from {$wpdb->prefix}postmeta where meta_key = %s" , $tab_key ), ARRAY_A);
						$acf_table_value='';
						foreach($tab_value as  $value_type){
							
							$get_type_field = $value_type['meta_value'];	
							$table_values=unserialize($get_type_field);
							$table_title=$table_values['p']['o']['uh'];
							$header = $table_values['h'];
							$header_arr = array_column($table_values['h'], 'c');
							
							$table_value_arr = [];
							foreach($table_values['b'] as $table_field_values){
								
								foreach($table_field_values as $table_keys => $table_values){
									
									$table_value_arr[$table_keys][] = $table_values['c'];
								}
							}
							$temp = 0;
							$body_value = '';
							foreach($header_arr as $header_key => $header_arr_val){
								$header_arr_values = implode('|', $table_value_arr[$header_key]);
								$body_value .= $header_arr_val . '->' . $header_arr_values . '--';
							}

							$table_body_value = rtrim($body_value, '--');
							$acf_table_value.=$table_title.','.$table_body_value;
							$value->meta_value=	$acf_table_value;
							self::$export_instance->data[$id][$value->meta_key] = $value->meta_value;
						
						}	
						
						
				}
				if($getType =='user'){
					if(is_serialized($value->meta_value)){
						$meta_value = unserialize($value->meta_value);
					
						foreach($meta_value as $val){
							$user = $wpdb->get_row($wpdb->prepare("select user_login from {$wpdb->prefix}users where ID= %d",$val));
						$username[]=$user->user_login;
						}
						$value->meta_value = implode(',',$username);	
						
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}
				}
				if($getType =='relationship'){
					if(is_serialized($value->meta_value)){
						$rel_value = unserialize($value->meta_value);
						foreach($rel_value as $rel){
							$relname = $wpdb->get_row($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID= %d",$rel));
							$relation[]=$relname->post_title;
						}
						$value->meta_value = implode(',',$relation);	
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}
				}
				if($getType == 'group'){
					global $wpdb;
					$repkey=$value->meta_key;
            		$queid=$wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_excerpt=%s", $repkey));
					$queid[0]->ID = isset($queid) ? $queid[0]->ID : '';
					$grpid=$queid[0]->ID;
					$quechild=$wpdb->get_results($wpdb->prepare("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE ID=%d", $grpid));
					$repchildkey=$quechild[0]->post_excerpt;
					$que=$wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $repchildkey, $id));
					$que[0]->meta_value = isset($que[0]) ? $que[0]->meta_value : '';
					$queval = $que[0]->meta_value;
					$quechild=$wpdb->get_results($wpdb->prepare("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE post_parent=%d", $grpid));
					$repchildkey=$quechild[0]->post_excerpt;
					if(empty($queval))
					    $value->meta_value=1;
					
					self::$export_instance->data[$id][$value->meta_key] = self::$export_instance->returnMetaValueAsCustomerInput($queval);
					if(is_serialized($value->meta_value)){
						$value->meta_value = unserialize($value->meta_value);
						$count = count($value->meta_value);
					}else{
						$count = $value->meta_value;
					}
					$value->meta_key=$repkey;
					$getRF = $checkRep[$value->meta_key];
					if(is_array($getRF)){
						foreach ($getRF as $rep => $rep1) {

							$repType = $alltype[$rep1];
							$reval = "";
							for($z=0;$z<$count;$z++){
								$var = $value->meta_key.'_'.$rep1;
								if(in_array($optionalType , $taxonomies)){
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var, $id));
								}
								elseif($optionalType == 'users'){
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}users wp JOIN {$wpdb->prefix}usermeta wpm ON wpm.user_id = wp.ID where meta_key = %s AND ID = %d", $var, $id));
								}
								else{
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var, $id));
								}
								if(isset($qry)){
									if(isset($qry[0]->meta_value)){
										$meta = $qry[0]->meta_value;
									}
								}
								else{
									$meta='';
								}
								if(isset($meta) && is_numeric($meta) && $repType != 'image' && $repType != 'file' && $repType !='number' && $repType != 'range' && $repType != 'text' && $repType != 'repeater'){
									$meta_title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$meta));
									foreach($meta_title as $meta_tit){
										$meta=$meta_tit;	
									}	
								}
								if($repType == 'group'){
									$groupOfgroup = true;
									$grp_grp_fields = $this->getgroupofgroup($rep1);
									foreach($grp_grp_fields as $grpkey => $grpval){
										$group_type = $alltype[$grpval];
										$var_grp = $value->meta_key.'_'.$rep1.'_'.$grpval;
										if(in_array($optionalType , $taxonomies)){
	
											$grp_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var_grp, $id));
										}else{
											$grp_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var_grp, $id));
										}
										$grp_meta = $grp_qry[0]->meta_value;
										if($group_type == 'image')
											$grp_meta = $this->getAttachment($grp_meta);
										if($group_type == 'file')
											$grp_meta =$this->getAttachment($grp_meta);
										if(is_serialized($grp_meta))
										{	
											$unmeta = unserialize($grp_meta);
											$coun =count($unmeta);
											$grp_meta = "";
											$grp_gal_val = '';
											foreach ($unmeta as $unmetakey => $unmeta1) {
											
												if($group_type == 'image'){
													$grp_val .= $this->getAttachment($unmeta1).',';
												}elseif( $group_type == 'gallery'){	
													$grp_gal_val .= $this->getAttachment($unmeta1).',';
												}
												elseif($group_type == 'relationship'  || $group_type == 'post_object'){
													if(is_numeric($unmeta1)){
														$meta_title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$unmeta1));
														foreach($meta_title as $meta_tit){
															$meta .=$meta_tit.',';
															
														}
														$grp_val = $grp_val = rtrim($meta , ',');	
													}
													else{
														$grpmeta .=$unmeta1.',';
														$grp_val = rtrim($grpmeta , ',');
													}
													
												}
												else{
													$grp_val = $unmeta1;
												}
												
											}											
											if($group_type == 'gallery'){
												$grp_val .= rtrim($grp_gal_val , ',') ;
											}
										}elseif($grp_meta != ''){
												$grp_val = $grp_meta ;	
										}
										//$grp_data[$grpval][] = rtrim($grp_val,'|');
										self::$export_instance->data[$id][ $grpval ] = $grp_val;
										
									}
									
								}
								if($repType == 'image')
									$meta=isset($meta)?$meta:'';
									$meta = $this->getAttachment($meta);
								if($repType == 'file')
									$meta=isset($meta)?$meta:'';
									$meta =$this->getAttachment($meta);
								if($repType == 'repeater'){
									$repeaterOfrepeater = true;
									$rep_rep_fields = $this->getRepeaterofRepeater($rep1);
									if(is_array($rep_rep_fields )){
										foreach($rep_rep_fields as $repeat => $repeat1){
											$repeat_type = $alltype[$repeat1];
								
											$repeater_count = get_post_meta($id , $var , true);
											$repeat_val = "";
											for($r = 0; $r<$repeater_count; $r++){
												$var_rep = $value->meta_key.'_'.$rep1.'_'.$r.'_'.$repeat1;
												if(in_array($optionalType , $taxonomies)){
	
													$rep_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var_rep, $id));
												}else{
													$rep_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var_rep, $id));
												}
												$rep_meta = $rep_qry[0]->meta_value;
												if($repeat_type == 'image')
													$rep_meta = $this->getAttachment($rep_meta);
												if($repeat_type == 'file')
													$rep_meta =$this->getAttachment($rep_meta);
												if(is_serialized($rep_meta))
												{	
													$unmeta = unserialize($rep_meta);
													$coun =count($unmeta);
													$rep_meta = "";
													$repeat_gal_val = '';
													foreach ($unmeta as $unmetakey => $unmeta1) {
														if($coun > 1){
															if($unmetakey == 0){
																if($repeat_type == 'image'){
																	$repeat_val .= $this->getAttachment($unmeta1).',';
																}elseif( $repeat_type == 'gallery'){	
																	$repeat_gal_val .= $this->getAttachment($unmeta1).',';
															}
															else{
																$repeat_val .= $unmeta1.',';
															}
														}
														else{
															if($repeat_type == 'image'){
																$repeat_val .= $this->getAttachment($unmeta1).',';
															}elseif( $repeat_type == 'gallery'){	
																$repeat_gal_val .= $this->getAttachment($unmeta1).',';
															}
															else{
																$repeat_val .= $unmeta1.'|';
															}
														}
													}
													else{
														if($repeat_type == 'image'){
															$repeat_val .= $this->getAttachment($unmeta1).',';
														}elseif( $repeat_type == 'gallery'){	
															$repeat_gal_val .= $this->getAttachment($unmeta1).',';
														}
														else{
															$repeat_val .= $unmeta1.'|';
														}
													}
													
													
												}											
												if($repeat_type == 'gallery'){
													$repeat_val .= rtrim($repeat_gal_val , ',') . '|';
												}
											}elseif($rep_meta != ''){
												$repeat_val .= $rep_meta . '|';	
											}	
										}
									   $repeater_data[$repeat1][] = rtrim($repeat_val,'|');
									}
									//self::$export_instance->data[$id][$repeat1] = self::$export_instance->returnMetaValueAsCustomerInput(rtrim($reval,'|'));
								}
							
								if($meta != ""){
									if(isset($repeat_val)){
										$reval .= $repeat_val.'|';
									}
								}
							}
						}
					}
					
						if($repeaterOfrepeater){
							if(!empty($repeater_data)){
								foreach($repeater_data as $repeater_key => $repeater_value){
									$repeaterOfvalue = '';
									foreach($repeater_value as $rep_rep_value){
										$repeaterOfvalue .= $rep_rep_value . '|';
									}
									self::$export_instance->data[$id][$repeater_key] = self::$export_instance->returnMetaValueAsCustomerInput(rtrim($repeaterOfvalue,'|'));
								}
							}
						}
							self::$export_instance->data[$id][$rep1] = self::$export_instance->returnMetaValueAsCustomerInput(rtrim($reval,'|'));
							
					}
				}
				if ($getType == 'flexible_content' || $getType == 'repeater') { 
					global $wpdb;
					$repkey=$value->meta_key;
					$que=$wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $repkey, $id));
					$queval = $que[0]->meta_value;
					self::$export_instance->data[$id][$value->meta_key] = self::$export_instance->returnMetaValueAsCustomerInput($queval);
					if(is_serialized($value->meta_value)){
						$value->meta_value = unserialize($value->meta_value);
						$count = count($value->meta_value);
					}else{
						$count = $value->meta_value;
					}
				    // $checkRep[$value->meta_key] = isset($checkRep[$value->meta_key])? $checkRep[$value->meta_key] : '';
					$getRF = $checkRep[$value->meta_key];
					$repeater_data = [];
					if($getType == 'flexible_content'){
						
						$flexible_value = '';
						if(is_array($value->meta_value)){
							foreach($value->meta_value as $values){
								$flexible_value .= $values.',';
							}
						}
						$flexible_value = rtrim($flexible_value , ',');	
						self::$export_instance->data[$id][$value->meta_key] = self::$export_instance->returnMetaValueAsCustomerInput($flexible_value);
					}
			        // if(is_array($getRF)){
					foreach ($getRF as $rep => $rep1) {
						$repType = $alltype[$rep1];				
						$reval = "";
						for($z=0;$z<$count;$z++){
								$var = $value->meta_key.'_'.$z.'_'.$rep1;
								if(in_array($optionalType , $taxonomies)){
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var, $id));
								}
								elseif($optionalType == 'users'){
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}users wp JOIN {$wpdb->prefix}usermeta wpm ON wpm.user_id = wp.ID where meta_key = %s AND ID = %d", $var, $id));
								}
								else{
									$qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var, $id));
								}
								$meta = isset($qry[0]->meta_value) ? $qry[0]->meta_value :'';
								
								if(is_numeric($meta) && $repType != 'image' && $repType != 'file' && $repType !='number' && $repType != 'range' && $repType != 'text' && $repType != 'select'){
									$meta_title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$meta));
									foreach($meta_title as $meta_tit){
										$meta=$meta_tit;
										
									}	
								}
								if($repType == 'image')
									$meta = $this->getAttachment($meta);
								if($repType == 'file')
									$meta =$this->getAttachment($meta);
								if($repType == 'repeater' || $repType == 'flexible_content')
									$repeaterOfrepeater = true;
									$rep_rep_fields = $this->getRepeaterofRepeater($rep1);
									if(!empty($rep_rep_fields)){
										foreach($rep_rep_fields as $repeat => $repeat1){
											$repeat_type = $alltype[$repeat1];
											$repeater_count = get_post_meta($id , $var , true);
											$repeat_val = "";
											for($r = 0; $r<$repeater_count; $r++){
												$var_rep = $value->meta_key.'_'.$z.'_'.$rep1.'_'.$r.'_'.$repeat1;
											
												if(in_array($optionalType , $taxonomies)){

													$rep_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var_rep, $id));
												}else{
													$rep_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var_rep, $id));
												}
												if(isset($rep_qry[0]->meta_value)){
													$rep_meta = $rep_qry[0]->meta_value;
												}
												else{
													$rep_meta='';
												}
												if($repeat_type == 'image')
													$rep_meta = $this->getAttachment($rep_meta);
												if($repeat_type == 'file')
													$rep_meta =$this->getAttachment($rep_meta);

												if(is_serialized($rep_meta))
												{	
													$unmeta = unserialize($rep_meta);
													$rep_meta = "";
													$repeat_gal_val = '';
													foreach ($unmeta as $unmeta1) {
														if($repeat_type == 'image'){
															$repeat_val .= $this->getAttachment($unmeta1).'|';
														}elseif( $repeat_type == 'gallery'){	
															$repeat_gal_val .= $this->getAttachment($unmeta1).',';
														}
														else{
															$repeat_val .= $unmeta1.'|';
														}
													}											
													if($repeat_type == 'gallery'){
														$repeat_val .= rtrim($repeat_gal_val , ',') . '|';
													}
												}elseif($rep_meta != ''){
													$repeat_val .= $rep_meta . '|';		
												}	
											}
											$repeater_data[$repeat1][] = rtrim($repeat_val,'|');
										
										// self::$export_instance->data[$id][$repeat1] = $repeater_data[$repeat1];
										
										}
									}

							
									
								if(is_serialized($meta))
								{
									$unmeta = unserialize($meta);
									
									$meta = "";
									foreach ($unmeta as $unmeta1) {
										if($repType == 'image' || $repType == 'gallery')
											$meta .= $this->getAttachment($unmeta1).',';
										elseif($repType == 'taxonomy') {
											$meta .=$unmeta1.',';
										
										}
										elseif($repType == 'user') {
											$meta .=$unmeta1.',';
										}
										elseif($repType == 'post_object') {
											if(is_numeric($unmeta1)){
												$meta_title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$unmeta1));
												foreach($meta_title as $meta_tit){
													$meta .=$meta_tit.',';
													
												}	
											}	
										}
										elseif($repType == 'relationship') {
											$meta .=$unmeta1.',';
										}
										elseif($repType == 'page_link') {
											$meta .=$unmeta1.',';
										}
										elseif($repType == 'link') {
											$meta .=$unmeta1 . ',';
										}
										else
											$meta .= $unmeta1.",";
									}

									if($repType == 'image' || $repType == 'gallery'){
										$meta = rtrim($meta,',');
									}else{
										$meta = rtrim($meta,',');
									}
									
								}
								if($meta != "")
									$reval .= $meta."|";
						}
						if($repType == 'group'){
									
								$rep_grp_fields = $this->getRepeaterofGroup($rep1);
								foreach($rep_grp_fields as $repgrpkey => $repgrpval){
									$rep_type = $alltype[$repgrpval];
									$con = $queval;
									$rep_grp_val = '';
									
									for($y=0;$y<$con;$y++){

										$var_grp_rep = $value->meta_key.'_'.$y.'_'.$rep1.'_'.$repgrpval;
										if(in_array($optionalType , $taxonomies)){

											$rep_grp_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}terms wp JOIN {$wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key = %s AND wp.term_id = %d", $var_grp_rep, $id));
										}else{
											$rep_grp_qry = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}posts wp JOIN {$wpdb->prefix}postmeta wpm ON wpm.post_id = wp.ID where meta_key = %s AND ID=%d", $var_grp_rep, $id));
										}
										$rep_grp_meta = $rep_grp_qry[0]->meta_value;
										if($rep_type == 'image')
										$rep_grp_meta = $this->getAttachment($rep_grp_meta);
										if($rep_type == 'file')
											$rep_grp_meta =$this->getAttachment($rep_grp_meta);
					

										if(is_serialized($rep_grp_meta))
										{	
											$unmeta = unserialize($rep_grp_meta);
											$rep_grp_meta = "";
											$rep_grp_gal_val = '';
											foreach ($unmeta as $unmeta1) {
												if($rep_type == 'image'){
													$rep_grp_val = $this->getAttachment($unmeta1);
												}elseif( $rep_type == 'gallery'){	
													$rep_grp_gal_val .= $this->getAttachment($unmeta1).',';
												}
												elseif($rep_type == 'taxonomy') {
													$rep_grp_val .=$unmeta1.',';
												
												}	
												elseif($rep_type == 'user' || $rep_type == 'post_object' || $rep_type == 'relationship' || $rep_type == 'select' || $rep_type == 'page_link') {
													if(is_numeric($unmeta1)){
														$meta_title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$unmeta1));
														foreach($meta_title as $meta_tit){
															$rep_grp_val .=$meta_tit.',';
														}
														
														
													}
													else{
														$rep_grp_val = $unmeta1 . '|';
													}
													
													
													
													
												}
												elseif($rep_type == 'link') {
													$rep_grp_val .=$unmeta1 . ',';
												}
												
												else{
													$rep_grp_val .= $unmeta1.'|';
												}
											}	
																			
											if($rep_type == 'gallery'){
												$rep_grp_val .= rtrim($rep_grp_gal_val , ','). '|'; 
											}
											else{
												if(strpos($rep_grp_val , ',') !== false){
													$rep_grp_val = rtrim($rep_grp_val , ','). '|';
												}
												else{
													$rep_grp_val = $rep_grp_val ;
												}
												
											}
										}elseif($rep_grp_meta != ''){
											
											$rep_grp_val .= $rep_grp_meta . '|';
											
												
										} 	 
									}
									$repeater_data[$repgrpval] = rtrim($rep_grp_val,'|');
									self::$export_instance->data[$id][$repgrpval] = $repeater_data[$repgrpval];
								}
						}
						
						if($repeaterOfrepeater){
								if(!empty($repeater_data)){
									foreach($repeater_data as $repeater_key => $repeater_value){
										$repeaterOfvalue = '';
										foreach($repeater_value as $rep_rep_value){
											$repeaterOfvalue .= $rep_rep_value . '->';
										}
										self::$export_instance->data[$id][$repeater_key] = self::$export_instance->returnMetaValueAsCustomerInput(rtrim($repeaterOfvalue,'->'));
									}
								}
						}
						self::$export_instance->data[$id][$rep1] = self::$export_instance->returnMetaValueAsCustomerInput(rtrim($reval,'|'));
					}
					// }
				}
				elseif($getType == 'post_object'){
					$check = false;
					if(is_serialized($value->meta_value)){
						$value->meta_value = @unserialize($value->meta_value);
							
						foreach($value->meta_value as $meta){
							$data[]=$meta;
							$check = true;
						}
					}
					if($check){
						foreach($data as $metas){
							if(is_numeric($metas)){

								$title = $wpdb->get_row($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$metas));
								$test[] = $title->post_title;
							}
						}
							
						$value->meta_value = implode(',',$test );			
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}else{
						if(is_numeric($value->meta_value)){	
							//if(is_array($value->meta_value)){
								// foreach($value->meta_value as $meta){
									$meta=$value->meta_value;
									$title = $wpdb->get_col($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$meta));	
								//}
							//}
							if(isset($title )){
							
								foreach($title as $value->meta_value){
									self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
								}
							}
						}
					}
						
				}
			
				elseif( is_serialized($value->meta_value)){
					$acfva = unserialize($value->meta_value);
					$acfdata = "";
					foreach ($acfva as $key1 => $value1) {
						if($getType == 'checkbox'){
							$acfdata .= $value1.',';
						}
							
						elseif($getType == 'gallery' || $getType == 'image'){
							$attach = $this->getAttachment($value1);
							$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$attach'" ,ARRAY_A);
							global $wpdb;
							$ids=$getid[0]['ID'];
							$types_caption=$wpdb->get_results("select post_excerpt from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_description=$wpdb->get_results("select post_content from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_title=$wpdb->get_results("select post_title from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_alt_text=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attachment_image_alt' AND post_id='$ids'" ,ARRAY_A);
							$types_filename=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attached_file' AND post_id='$ids'" ,ARRAY_A);
							$filename=$types_filename[0]['meta_value'];
							$file_names=explode('/', $filename);
							$file_name=$file_names[2];
							$typecap=$types_caption.',';
							self::$export_instance->data[$id]['acf_caption'] = $typecap;
							self::$export_instance->data[$id]['acf_description'] =$types_description;
							self::$export_instance->data[$id]['acf_title'] = $types_title;
							self::$export_instance->data[$id]['acf_alt_text'] =$types_alt_text;
							self::$export_instance->data[$id]['acf_file_name'] = $file_name;
							$acfdata .= $attach.',';
						}
						elseif($getType == 'google_map')
						{
							$acfdata=$acfva['address'].'|'.$acfva['lat'].'|'.$acfva['lng'];
						}
						else{
							if(!empty($value1)) { 
								$acfdata .= $value1.',';
							}
						}
					}
				
					if($getType == 'gallery' || $getType == 'image'){
         				$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$acfdata'" ,ARRAY_A);
						global $wpdb;
						foreach($getid as $getkey => $getval){
							$ids=$getval['ID'];
							$types_caption=$wpdb->get_results("select post_excerpt from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_description=$wpdb->get_results("select post_content from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_title=$wpdb->get_results("select post_title from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_alt_text=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attachment_image_alt' AND post_id='$ids'" ,ARRAY_A);
							$types_filename=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attached_file' AND post_id='$ids'" ,ARRAY_A);
							$filename=$types_filename[0]['meta_value'];
							$file_names=explode('/', $filename);
							$file_name= $file_names[2];
							$typecap=$types_caption[0]['post_excerpt'].',';
							self::$export_instance->data[$id]['acf_caption'] = $typecap;
							self::$export_instance->data[$id]['acf_description'] = $types_description[0]['post_content'];
							self::$export_instance->data[$id]['acf_title'] = $types_title[0]['post_title'];
							self::$export_instance->data[$id]['acf_alt_text'] = $types_alt_text[0]['meta_value'];
							self::$export_instance->data[$id]['acf_file_name'] = $file_name;	
						
						}
						$acfdata = rtrim($acfdata , ',');
					}else{
						$acfdata = rtrim($acfdata , ',');
					}
					self::$export_instance->data[$id][ $value->meta_key ] = self::$export_instance->returnMetaValueAsCustomerInput($acfdata);
				}
				elseif($getType == 'gallery' || $getType == 'image'|| $getType == 'file'  ){

					$attach1 = $this->getAttachment($value->meta_value);
					global $wpdb;
					$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$attach1'" ,ARRAY_A);
						foreach($getid as $getkey => $getval){
							$ids=$getval['ID'];
							$types_caption=$wpdb->get_results("select post_excerpt from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_description=$wpdb->get_results("select post_content from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_title=$wpdb->get_results("select post_title from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
							$types_alt_text=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attachment_image_alt' AND post_id='$ids'" ,ARRAY_A);
							$types_filename=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attached_file' AND post_id='$ids'" ,ARRAY_A);
							if(isset($types_filename[0]['meta_value'])){
								$filename=$types_filename[0]['meta_value'];
							}
							else{
								$filename='';
							}
							if(isset($filename)){
								$file_names=explode('/', $filename);
							}
							if(isset($file_names[2])){
								$file_name= $file_names[2];
							}
							self::$export_instance->data[$id]['acf_caption'] = $types_caption[0]['post_excerpt'];
							self::$export_instance->data[$id]['acf_description'] = $types_description[0]['post_content'];
							self::$export_instance->data[$id]['acf_title'] = $types_title[0]['post_title'];
							$types_alt_text[0]['meta_value']=isset($types_alt_text[0]['meta_value'])?$types_alt_text[0]['meta_value']:'';
							self::$export_instance->data[$id]['acf_alt_text'] = $types_alt_text[0]['meta_value'];
							$file_name=isset($file_name)?$file_name:'';
							self::$export_instance->data[$id]['acf_file_name'] = $file_name;
						}	
						
					self::$export_instance->data[$id][ $value->meta_key ] = $attach1;
				}
				elseif($getType == 'image_aspect_ratio_crop'){
					$attach2=$this->getAttachment($value->meta_value);
					$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$attach2'" ,ARRAY_A);
					global $wpdb;
					foreach($getid as $getkey=>$getval){
						$id=$getval['ID'];
						$acf_image_id=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= 'acf_image_aspect_ratio_crop_original_image_id' AND post_id='$id'" ,ARRAY_A);
						$acf_post_id=$acf_image_id[0]['meta_value'];
						$image = $this->getAttachment($acf_post_id);
						$acf_image_aspect_ratio_crop_parent_post_id =$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key='acf_image_aspect_ratio_crop_parent_post_id 'AND post_ID= '$id'" ,ARRAY_A);
						$img_id=$acf_image_aspect_ratio_crop_parent_post_id[0]['meta_value'];
						$acf_image_coordinates=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key='acf_image_aspect_ratio_crop_coordinates'AND post_ID= '$id'" ,ARRAY_A);
						$acf_coordinates=unserialize($acf_image_coordinates[0]['meta_value']);
						$width=$acf_coordinates['width'];
						$height=$acf_coordinates['height'];				
						$acf_crop=$width.'|'.$height .','. $image;
						self::$export_instance->data[$img_id][ $value->meta_key ] = $acf_crop;
					}
				}
			
				else{
					self::$export_instance->data[$id][ $value->meta_key ] = self::$export_instance->returnMetaValueAsCustomerInput($value->meta_value);
				}
			}

			elseif(is_array($jet_fields) && in_array($value->meta_key, $jet_fields)){
				$getjetType = $jet_types[$value->meta_key];
				if(empty($getjetType)){
					$temp_fieldname = array_search($value->meta_key, $jet_fields);
					$getjetType = $jet_types[$temp_fieldname];
				}
		
				if($getjetType == 'checkbox'){
					$value->meta_value = unserialize($value->meta_value);
				    $check = '';
					foreach($value->meta_value as $key => $metvalue){
						if(is_numeric($key)){
							$check .= $metvalue.',';	
							$rcheck = substr($check,0,-1);
						    self::$export_instance->data[$id][ $value->meta_key ] = $rcheck;
						}
						else{
							if($metvalue == 'true'){

								$exp_value[] = $key;
							}
							if(isset($exp_value) && is_array($exp_value)){
								$value->meta_value = implode(',',$exp_value );
							}
								
							self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
						}
                       
					}

				}
				elseif($getjetType == 'gallery'){	
					$gallery= explode(',',$value->meta_value);
					foreach($gallery as $gallerykey => $galleryval){
						if(is_numeric($galleryval)){
							$galleries[] = $this->getAttachment($galleryval);
						}
						elseif(is_serialized($galleryval)){
							$gal_value=unserialize($galleryval);
							foreach($gal_value as $key=>$gal_val){
								$galleries[] = $gal_val['url'];
							}	
						}
						else{
							$galleries[] = $galleryval;
						}
						$value->meta_value = implode(',',$galleries );	
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}	
				}
				elseif( $getjetType == 'media'){
					$array_val= $value->meta_value;
					
					if(is_numeric($array_val)){
						$value->meta_value = $this->getAttachment($array_val);
					}
					elseif(is_serialized($array_val)){
						$media_value=unserialize($array_val);
						$value->meta_value = $media_value['url'];	
						
					}
					else{
						$value->meta_value=$array_val;
					}
					self::$export_instance->data[$id][$value->meta_key] = $value->meta_value;
				}
				
				
				elseif($getjetType == 'posts'){
					if(is_serialized($value->meta_value)){
						$value->meta_value = unserialize($value->meta_value);
						foreach($value->meta_value as $postkey => $metpostvalue){
							if(is_numeric($metpostvalue)){
								$title = $wpdb->get_row($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$metpostvalue));
								$test[] = $title->post_title;
						    }
					    }
						$value->meta_value = implode(',',$test );			
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
						
					}
				}
				elseif($getjetType == 'select'){
					if(is_serialized($value->meta_value)){
						$value->meta_value = unserialize($value->meta_value);
						foreach($value->meta_value as $metkey => $metselectvalue){
							$select[] = $metselectvalue;
							$value->meta_value = implode(',',$select );	
							self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
						}
					}
					else{
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}
				}
				elseif($getjetType == 'select'){

				}
				elseif($getjetType == 'date'){
					if(!empty($value->meta_value)){
						if(strpos($value->meta_value, '-') !== FALSE){
						}else{
							$value->meta_value = date('Y-m-d', $value->meta_value);
						}
					}
					self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
				}
				elseif($getjetType == 'repeater'){
					global $wpdb;
					
					foreach($jet_types as $jettypename => $jettypeval){
                        if($jettypeval == 'repeater'){
							$jet_fields_name =$jettypename;
							if($module == 'Users'){
                                $fieldarr = $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = %s AND user_id = %d ",$jet_fields_name,$id),ARRAY_A);
							}
						    elseif($module == 'Categories' || $module == 'Taxonomies' || $module == 'Tags'){
							    $fieldarr = $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}termmeta WHERE meta_key = %s AND term_id = %d ",$jet_fields_name,$id),ARRAY_A);
							}
							else{
								$fieldarr = $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND post_id = %d ",$jet_fields_name,$id),ARRAY_A);
							}
							$arr =json_decode(json_encode($fieldarr),true);
							$unser = unserialize($arr[0]['meta_value']);
							if(!empty($unser)){
								$arraykey = array_keys($unser);
								foreach($arraykey as $val){
								   $values = explode('-',$val);
								   $v = $values[1];
								}
							}
							else{
								$v =0;
							}
							
							
							$array_valuenum = $array_valuetext = $array_checkval = $array_wysval = $array_timval = $array_datval = $array_dattimval = $array_radval = $array_colorval = $array_switval = $array_iconval = $array_valuetextarea = $array_selval = $array_postval= $array_mediaval = $array_galval = '';
							for($i=0 ; $i<$v; $i++){
								$j =0;
								$idkey = 'item-'.$i;
								$array=$unser[$idkey];
								
								if(!empty($array)){
								
									$array_keys =array_keys($array);
									foreach($array_keys as $arrkey){
										$arrcol[$arrkey] = array_column($unser,$arrkey);
									}
									foreach($arrcol as $array_key => $array_val){
										
										$array_valuenum = $array_valuetext = $array_checkval = $array_wysval = $array_timval = $array_datval = $array_dattimval = $array_radval = $array_colorval = $array_switval = $array_iconval = $array_valuetextarea = $array_selval = $array_postval= $array_mediaval = $array_galval = '';
										
										if($jet_rep_types[$array_key] == 'text'){
											
											foreach($array_val as $arrval){
												$array_valuetext .= $arrval.'|';
											}
											
											self::$export_instance->data[$id][ $array_key ] = $array_valuetext;
										}
										elseif($jet_rep_types[$array_key] == 'checkbox'){
											foreach($array_val as $arrval){
												$exp_value = [];
											
												foreach($arrval as $key => $metvalue){
													if($metvalue == 'true'){
														$exp_value[] = $key;
														
													}
													
												}
												$checkval = implode(',',$exp_value );	
											
												$array_checkval .=$checkval.'|';
												
												self::$export_instance->data[$id][$array_key] = $array_checkval;

											} 
										}
										
										elseif( $jet_rep_types[$array_key] == 'media'){
											$medias = [];
											foreach($array_val as $arrval){
												if(is_numeric($arrval)){
													$medias[] = $this->getAttachment($arrval);
												}
												elseif(is_array($arrval)){
													$medias[] = $arrval['url'];	
													
												}
												else{
													$medias[]=$arrval;
												}
												
											}
											
											$mediaval = implode('|',$medias );	
												
											$array_mediaval .=$mediaval.'|';
											
											self::$export_instance->data[$id][$array_key] = $mediaval;
										}
												
												
										elseif( $jet_rep_types[$array_key] == 'gallery'){
											foreach($array_val as $arrval){
												$galleries =[];
												
												if(is_array($arrval)){
													foreach($arrval as $key => $gallryvalue){
														$galleries[] = $gallryvalue['url'];
													}
													
												}
												else{
													$gallery= explode(',',$arrval);
													foreach($gallery as $gallerykey => $galleryval){
														if(is_numeric($galleryval)){
															$galleries[] = $this->getAttachment($galleryval);
														}
														else{
															$galleries[]=$galleryval;
														}
														
														
													}
												}
												$gal_val = implode(',',$galleries );
												$array_galval .=$gal_val.'|';	
												self::$export_instance->data[$id][$array_key] = $array_galval;
											}
										}
										elseif($jet_rep_types[$array_key] == 'posts'){
											$test =[];
											$posts_val ='';
											foreach($array_val as $arrval){
												$test =[];
												if(is_array($arrval)){
													
													foreach($arrval as $postkey => $metpostvalue){
														if(is_numeric($metpostvalue)){
															$title = $wpdb->get_row($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d ORDER BY ID DESC",$metpostvalue));
															$test[] = $title->post_title;

														}	
															
													}
													$postval = implode(',',$test );
													
													$posts_val .=$postval.'|';	
													self::$export_instance->data[$id][$array_key] = $posts_val;
													
												}
												else{
												
													if(is_numeric($arrval)){
														$title = $wpdb->get_row($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d ORDER BY ID DESC",$arrval));
														$testing = $title->post_title;
													}
													$posts_val .=$testing.'|';
													self::$export_instance->data[$id][$array_key] = $posts_val;
												}
											
											}
										
										}
										elseif($jet_rep_types[$array_key] == 'select'){
											$array_selval ='';
											foreach($array_val as $arrval){
												if(is_array($arrval)){
													$select =[];
													foreach($arrval as $metselectvalue){
														//foreach($metvalue as $metselectvalue){
															$select[] = $metselectvalue;
															$array_vals = implode(',',$select );
														//}
														
													}
													
													$array_selval .=$array_vals.'|';
													self::$export_instance->data[$id][$array_key] = $array_selval;
												}
												else{
													$array_selval .=$arrval.'|';
													self::$export_instance->data[$id][$array_key] = $array_selval;
												}

											}
										
										}
										elseif($jet_rep_types[$array_key] == 'date'){
											foreach($array_val as $arrval){
												if(strpos($arrval, '-') !== FALSE){
												}else{
													$arrval = date('Y-m-d', $arrval);
												}
												$array_datval .= $arrval.'|';
											}
										
											self::$export_instance->data[$id][ $array_key ] = $array_datval;
										}
										elseif($jet_rep_types[$array_key] == 'time'){
											foreach($array_val as $arrval){
												$array_timval .= $arrval.'|';
											}
											//$array_timval = substr($array_timval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_timval;
										}
										elseif($jet_rep_types[$array_key] == 'wysiwyg'){
											foreach($array_val as $arrval){
												$array_wysval .= $arrval.'|';
											}
											//$array_wysval = substr($array_wysval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_wysval;
										}
										elseif($jet_rep_types[$array_key] == 'datetime-local'){
											foreach($array_val as $arrval){
											
												$array_dattimval .= $arrval.'|';
											}
											//$array_dattimval = substr($array_dattimval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_dattimval;
										}
										elseif($jet_rep_types[$array_key] == 'iconpicker'){
											foreach($array_val as $arrval){
												$array_iconval .= $arrval.'|';
											}
											//$array_iconval = substr($array_iconval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_iconval;
										}
										elseif($jet_rep_types[$array_key] == 'switcher'){
											foreach($array_val as $arrval){
												$array_switval .= $arrval.'|';
											}
											//$array_switval = substr($array_switval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_switval;
										}
										elseif($jet_rep_types[$array_key] == 'colorpicker'){
											foreach($array_val as $arrval){
												$array_colorval .= $arrval.'|';
											}
											//$array_colorval = substr($array_colorval,0,-1);
											self::$export_instance->data[$id][ $array_key ] = $array_colorval;
										}
										
										elseif($jet_rep_types[$array_key] == 'number'){
											foreach($array_val as $arrval){
												$array_valuenum .= $arrval.'|';
												$array_valuenum = rtrim($array_valuenum);
											}
											//$array_valuenum = substr($array_valuenum,0,-1);
											self::$export_instance->data[$id][$array_key] = $array_valuenum;

										}
										elseif($jet_rep_types[$array_key] == 'textarea'){
											foreach($array_val as $arrval){
												$array_valuetextarea .= $arrval.'|';
											}
											//$array_valuetextarea = substr($array_valuetextarea,0,-1);
											self::$export_instance->data[$id][$array_key] = $array_valuetextarea;

										}
										else{
											if(array_search("radio",$jet_rep_types)){
												
													$array_radval .= '|';
												
													if($jet_rep_types[$array_key] == 'radio'){
														foreach($array_val as $arrval){
															$array_radval .= $arrval.'|';
														}
													}
												
												self::$export_instance->data[$id][ $array_key ] = $array_radval;
											}
										}
									}
							    }
							}
						}
					}
				}
				else{	
					self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
				}
			}

			elseif (!empty($typesf) && in_array($value->meta_key, $typesf)) {
				global $wpdb;
				$type_value = '';	
				$typeoftype = $typeOftypesField[$value->meta_key];
				if(in_array($optionalType , $taxonomies)){
					$type_data =  get_term_meta($id,$value->meta_key);
				}
				elseif($optionalType == 'users'){
					$type_data =  get_user_meta($id,$value->meta_key);
				}
				else{
					$type_data =  get_post_meta($id,$value->meta_key);
					$typcap = "";
					foreach($type_data as $type_key =>$type_value){
						if(!is_array($type_value)){
							$substring='http';
							$string=substr($type_value,0,4);
							if($string==$substring){	
								$getid=$wpdb->get_results("select ID from {$wpdb->prefix}posts where guid= '$type_value'" ,ARRAY_A);
								foreach($getid as $getkey => $getval){
									global $wpdb;
									$ids=$getval['ID'];
									$types_caption=$wpdb->get_results("select post_excerpt from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
									$types_description=$wpdb->get_results("select post_content from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
									$types_title=$wpdb->get_results("select post_title from {$wpdb->prefix}posts where ID= '$ids'" ,ARRAY_A);
									$types_alt_text=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attachment_image_alt' AND post_id='$ids'" ,ARRAY_A);
									$types_filename=$wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where meta_key= '_wp_attached_file' AND post_id='$ids'" ,ARRAY_A);
									if(isset($types_filename[0])){
									$filename=$types_filename[0]['meta_value'];
									}
									if(isset($filename)){
										$file_names=explode('/', $filename);
									}
									if(isset($file_names[2])){
										$file_name= $file_names[2];
									}
									$file_name=isset($file_name)?$file_name:'';
									self::$export_instance->data[$id]['types_caption'] = $types_caption[0]['post_excerpt'];
									self::$export_instance->data[$id]['types_description'] = $types_description;
									self::$export_instance->data[$id]['types_title'] = $types_title;
									self::$export_instance->data[$id]['types_alt_text'] = $types_alt_text;
									self::$export_instance->data[$id]['types_file_name'] = $file_name;
									
							
								}
							}
						
							$type_value = rtrim($type_value , '|');
						
						}
						
					}
				
					self::$export_instance->data[$id][ $value->meta_key ] = $type_value;
					
				}
				
				if(is_array($type_data)){	
					$type_value="";
					foreach($type_data as $k => $mid){	
						if(is_array($mid) && !empty($mid)){
							if($typeoftype == 'skype'){	
								$type_value .= $mid['skypename'] . '|';
							}
							elseif($typeoftype == 'checkboxes'){
								$check_type_value = '';	
								foreach($mid as $mid_value){
										$check_type_value .= $mid_value[0] . ',';
								}
								$type_value .= rtrim($check_type_value , ',');
							}	
						}
						elseif($typeoftype == 'date'){
							$type_value .= date('m/d/Y', $mid) . '|';
						}
						else{
							if(!is_array($mid)){
								$type_value .= $mid . '|';
							}	
						}
					}
					if(preg_match('/wpcf-/',$value->meta_key)){	
						$value->meta_key = preg_replace('/wpcf-/','', $value->meta_key );	
						self::$export_instance->data[$id][ $value->meta_key ] = rtrim($type_value , '|');					
					}
				}	
				
				if(preg_match('/group_/',$value->meta_key)){
					$getType = $alltype[$value->meta_key];
					if($value->meta_key == 'group_gallery' || $value->meta_key == 'group_image'|| $value->meta_key == 'file'  ){
						$groupattach = $this->getAttachment($value->meta_value);
						self::$export_instance->data[$id][ $value->meta_key ] = $groupattach;
					}
					else{
						$value->meta_key = preg_replace('/group_/','', $value->meta_key );
						self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
					}
				}
				
				//TYPES Allow multiple-instances of this field
			}elseif(in_array($value->meta_key, $group_unset) && is_serialized($value->meta_value)) {
				$unser = unserialize($value->meta_value);
				$data = "";
				foreach ($unser as $key4 => $value4) 
					$data .= $value4.',';
				self::$export_instance->data[$id][ $value->meta_key ] = substr($data, 0, -1);
			}
			elseif(in_array($value->meta_key , $pods_type)){
				foreach($pods_type as $pods){
					$pods_id =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts where post_title='$pods'");	
					
					foreach($pods_id as $pod_id){
						$pods_id_value=$pod_id->ID;
						$pods_types =  $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta where post_id='$pods_id_value' and meta_key='type'");	
						foreach($pods_types as $pod_type){
							$ptype[]=$pod_type->meta_value;	
						}	
					}
				}
				if(!isset(self::$export_instance->data[$id][$value->meta_key])){
					if(in_array($optionalType , $taxonomies)){
						$pods_file_data = get_term_meta($id,$value->meta_key);
					}else{
						$pods_file_data = get_post_meta($id,$value->meta_key);	
					}	
					$pods_value = '';
					foreach($pods_file_data as $pods_file_value){
						if(!empty($pods_file_value)){
							if(is_array($pods_file_value)){
								$pods_file_value['post_type']=isset($pods_file_value['post_type'])?$pods_file_value['post_type']:'';
								$posts_type=$pods_file_value['post_type'];
								if($posts_type=='attachment'){
									$pods_value .= $pods_file_value['guid'] . ',';
								}
								elseif($posts_type!=='attachment'){
									$pods_file_value['guid']=isset($pods_file_value['guid'])?$pods_file_value['guid']:'';
									$p_guid=$pods_file_value['guid'];
									$pod_tit =  $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts where guid='$p_guid'");	
									foreach($pod_tit as $pods_title){
										$pods_title_value=$pods_title->post_title;
										$pods_value .= $pods_title_value . ',';
									}
								}
								if(empty($pods_value)){
									$podstaxval = $pods_file_value['name'];
									  $pods_value .= $podstaxval. ',';
								}
								
							}else{
								$pods_value .= $pods_file_value . ',';
								
							}
						}	
					}
					
					self::$export_instance->data[$id][$value->meta_key] = rtrim($pods_value , ',');		
				}
				$podsFields = array();
					$post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", $optionalType, '_pods_pod'));
		
					if(!empty($post_id)) {
						$lastId  = $post_id[0]->ID;
						$get_pods_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_name FROM {$wpdb->prefix}posts where post_parent = %d AND post_type = %s", $lastId, '_pods_field' ) );
						if ( ! empty( $get_pods_fields ) ) :
							foreach ( $get_pods_fields as $pods_field ) {
								$get_pods_types = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta where post_id = %d AND meta_key = %s", $pods_field->ID, 'type' ) );
								$get_pods_object = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta where post_id = %d AND meta_key = %s", $pods_field->ID, 'pick_object' ) );
								$podsFields["PODS"][ $pods_field->post_name ]['label'] = $pods_field->post_name;
								$podsFields["PODS"][ $pods_field->post_name ]['type']  = $get_pods_types[0]->meta_value;
								if(isset($get_pods_object[0]->meta_value)){
									$podsFields["PODS"][ $pods_field->post_name ]['pick_object']=$get_pods_object[0]->meta_value;
								}
								if($podsFields["PODS"][ $pods_field->post_name ]['type'] == 'pick'){
									$get_pods_objecttype = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta where post_id = %d AND meta_key = %s", $pods_field->ID, 'pick_format_type' ) );
									$podsFields["PODS"][ $pods_field->post_name ]['pick_objecttype']=$get_pods_objecttype[0]->meta_value;
								}
							}
							foreach ( $get_pods_fields as $pods_field ) {
								$pick_obj=$podsFields["PODS"][$pods_field->post_name]['pick_object'];
								$pick_objtype = $podsFields["PODS"][$pods_field->post_name]['pick_objecttype'];
								
								$pick_lable = $podsFields["PODS"][$pods_field->post_name]['label'];
								if($pick_obj=='user'){
									if($pick_lable == $value->meta_key){ 
										if($pick_objtype == 'multi'){
											$val='_pods_'.$pick_lable;
											$get_pods_type = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta where post_id = %d AND meta_key = %s", $id, $val ) );
											$serialize_value=unserialize($get_pods_type[0]->meta_value);
											foreach($serialize_value as $key=>$unser_value){
												$multi_value .= $unser_value.',';
												
											}
											
											self::$export_instance->data[$id][$pods_field->post_name] =rtrim($multi_value,',');
										}
										else{	
											self::$export_instance->data[$id][$pods_field->post_name] =$value->meta_value;
										}
									}
								
								}
							}
							
						endif;
					}
			}
			
			else{
				self::$export_instance->data[$id][ $value->meta_key ] = $value->meta_value;
			}
		}
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

	public function getRepeaterofRepeater($parent)
	{
		global $wpdb;	
		$get_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_excerpt = %s", $parent), ARRAY_A);
		$test = $get_fields[0]['ID'] ;	
		$get_fieldss = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_parent = %d", $test), ARRAY_A);
		$i = 0;
		foreach ($get_fieldss as $key => $value) {
			$array[$i] = $value['post_excerpt'];			
			$i++;
		}
		$array=isset($array)?$array:'';
		return $array;	
	}
	public function getgroupofgroup($parent)
	{
		global $wpdb;	
		$get_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_excerpt = %s", $parent), ARRAY_A);
		$test = $get_fields[0]['ID'] ;	
		$get_fieldss = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_parent = %d", $test), ARRAY_A);
		$i = 0;
		foreach ($get_fieldss as $key => $value) {
			$array[$i] = $value['post_excerpt'];			
			$i++;
		}
		return $array;	
	}
	public function getRepeaterofGroup($parent){
		global $wpdb;
		$get_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_excerpt = %s", $parent), ARRAY_A);
		$test = $get_fields[0]['ID'] ;	
		$get_fieldss = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts where post_parent = %d", $test), ARRAY_A);
		$i = 0;
		foreach ($get_fieldss as $key => $value) {
			$array[$i] = $value['post_excerpt'];			
			$i++;
		}
		return $array;	

	}


	/**
	 * Fetch all Categories
	 * @param $mode
	 * @param $module
	 * @param $optionalType
	 * @return array
	 */
	public function FetchCategories($module,$optionalType, $is_filter,$mode = null) {
		self::$export_instance->generateHeaders($module, $optionalType);
		$get_all_terms = get_categories('hide_empty=0');
		self::$export_instance->totalRowCount = count($get_all_terms);
		$offset = self::$export_instance->offset;
		$limit = self::$export_instance->limit;	
			global $wpdb;
		
		$query="SELECT term_id FROM {$wpdb->prefix}term_taxonomy where taxonomy='category'";
		
		$offset_limit = " order by term_id asc limit $offset, $limit";
		$query_with_offset_limit = $query.$offset_limit;
			 
		$result= $wpdb->get_col($query_with_offset_limit);
		$query1=array();
		foreach($result as $res=>$re){
			 $query1[]=$wpdb->get_results(" SELECT t.name, t.slug, tx.description, tx.parent, t.term_id FROM {$wpdb->prefix}terms as t join {$wpdb->prefix}term_taxonomy as tx on t.term_id = tx.term_id where t.term_id = '$re'");
		}
		$new=array();
		foreach($query1 as $qkey => $qval){		
			foreach($qval as $qid){
				$new[]=$qid;
			}
			
		}	
		if(!empty($new)) {
			foreach( $new as $termKey => $termValue ) {
				$termID = $termValue->term_id;
				$termValue->cat_name=isset($termValue->cat_name)?$termValue->cat_name:'';
				$termName = $termValue->cat_name;
				$termSlug = $termValue->slug;
				$termDesc = $termValue->description;
				$termParent = $termValue->parent;
				if($termParent == 0) {
					self::$export_instance->data[$termID]['name'] = $termName;
				} else {
					$termParentName = get_cat_name( $termParent );
					self::$export_instance->data[$termID]['name'] = $termParentName . '|' . $termName;					
				}
				self::$export_instance->data[$termID]['slug'] = $termSlug;
				self::$export_instance->data[$termID]['description'] = $termDesc;
				self::$export_instance->data[$termID]['parent'] = $termParent;
				self::$export_instance->data[$termID]['TERMID'] = $termID;

				self::$export_instance->getWPMLData($termID,$optionalType,$module);

				$this->getPostsMetaDataBasedOnRecordId ($termID, $module, $optionalType);
				if(is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')){
					$seo_yoast_taxonomies = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $seo_yoast_taxonomies['category'] ) ) {
							self::$export_instance->data[ $termID ]['title'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_title'];
							self::$export_instance->data[ $termID ]['meta_desc'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_desc'];
							self::$export_instance->data[ $termID ]['canonical'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_canonical'];
							self::$export_instance->data[ $termID ]['bctitle'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_bctitle'];
							self::$export_instance->data[ $termID ]['meta-robots-noindex'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_noindex'];
							//self::$export_instance->data[ $termID ]['sitemap-include'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_sitemap_include'];
							self::$export_instance->data[ $termID ]['opengraph-title'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_opengraph-title'];
							self::$export_instance->data[ $termID ]['opengraph-description'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_opengraph-description'];
							self::$export_instance->data[ $termID ]['opengraph-image'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_opengraph-image'];
							self::$export_instance->data[ $termID ]['twitter-title'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_twitter-title'];
							self::$export_instance->data[ $termID ]['twitter-description'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_twitter-description'];
							self::$export_instance->data[ $termID ]['twitter-image'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_twitter-image'];
							self::$export_instance->data[ $termID ]['focus_keyword'] = $seo_yoast_taxonomies['category'][$termID]['wpseo_focuskw'];

							if(isset($seo_yoast_taxonomies['category'][$termID]['wpseo_focuskeywords']) && !empty($seo_yoast_taxonomies['category'][$termID]['wpseo_focuskeywords'])){
								$decode_value = json_decode($seo_yoast_taxonomies['category'][$termID]['wpseo_focuskeywords'], true);
								$keywords = array_column($decode_value, 'keyword');
								self::$export_instance->data[ $termID ]['focuskeywords'] = implode('|', $keywords);
							}

							if(isset($seo_yoast_taxonomies['category'][$termID]['wpseo_keywordsynonyms']) && !empty($seo_yoast_taxonomies['category'][$termID]['wpseo_keywordsynonyms'])){
								$decode_value1 = json_decode($seo_yoast_taxonomies['category'][$termID]['wpseo_keywordsynonyms'], true);
								array_shift($decode_value1);
								self::$export_instance->data[ $termID ]['keywordsynonyms'] = implode('|', $decode_value1);
							}
					}
				}			
			}
		}
		
		$result = self::$export_instance->finalDataToExport(self::$export_instance->data, $module);
		
		if($is_filter == 'filter_action'){
			return $result;
		}

		if($mode == null){
			self::$export_instance->proceedExport($result);
		}else{
			return $result;
		}
	}


	public function get_common_post_metadata($meta_id){
		global $wpdb;
		$mdata = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_id = %d", $meta_id) ,ARRAY_A);
		return $mdata[0];
	}

	public function getAttachment($id)
	{
		global $wpdb;
		$get_attachment = $wpdb->prepare("select guid from {$wpdb->prefix}posts where ID = %d AND post_type = %s", $id, 'attachment');
		$attachment = $wpdb->get_results($get_attachment);
		if(isset($attachment[0]->guid)){
			$attachment_file = $attachment[0]->guid;
		}
		$attachment_file=isset($attachment_file)?$attachment_file:'';
		return $attachment_file;

	}

	/**
	 * Fetch all Tags
	 * @param $mode
	 * @param $module
	 * @param $optionalType
	 * @return array
	 */
	public function FetchTags($module,$optionalType, $is_filter,$mode = null) {
		
		self::$export_instance->generateHeaders($module, $optionalType);
		$get_all_terms = get_tags('hide_empty=0');
		self::$export_instance->totalRowCount = count($get_all_terms);
		$offset = self::$export_instance->offset;
		$limit = self::$export_instance->limit;	
		global $wpdb;
		$query="SELECT term_id FROM {$wpdb->prefix}term_taxonomy where taxonomy='post_tag'";
			
		$offset_limit = " order by term_id asc limit $offset, $limit";
		$query_with_offset_limit = $query.$offset_limit;
			 
		$result= $wpdb->get_col($query_with_offset_limit);
		$query1=array();
		foreach($result as $res=>$id){
				 $query1[]=$wpdb->get_results(" SELECT t.name, t.slug, tx.description, tx.parent, t.term_id FROM {$wpdb->prefix}terms as t join {$wpdb->prefix}term_taxonomy as tx on t.term_id = tx.term_id where t.term_id = '$id'");
		}
		$new=array();
		foreach($query1 as $qkey => $qval){		
			foreach($qval as $qid){
				$new[]=$qid;
			}
			
		}	
		if(!empty($new)) {
			foreach( $new as $termKey => $termValue ) {
				$termID = $termValue->term_id;
				$termName = $termValue->name;
				$termSlug = $termValue->slug;
				$termDesc = $termValue->description;
				self::$export_instance->data[$termID]['name'] = $termName;
				self::$export_instance->data[$termID]['slug'] = $termSlug;
				self::$export_instance->data[$termID]['description'] = $termDesc;
				self::$export_instance->data[$termID]['TERMID'] = $termID;

				$this->getPostsMetaDataBasedOnRecordId ($termID, $module, $optionalType);
				
				self::$export_instance->getWPMLData($termID,$optionalType,$module);

				if(is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')){
					$seo_yoast_taxonomies = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $seo_yoast_taxonomies['post_tag'] ) ) {
						self::$export_instance->data[ $termID ]['title'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_title'];
						self::$export_instance->data[ $termID ]['meta_desc'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_desc'];
						self::$export_instance->data[ $termID ]['canonical'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_canonical'];
						self::$export_instance->data[ $termID ]['bctitle'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_bctitle'];
						self::$export_instance->data[ $termID ]['meta-robots-noindex'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_noindex'];
						//self::$export_instance->data[ $termID ]['sitemap-include'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_sitemap_include'];
						self::$export_instance->data[ $termID ]['opengraph-title'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_opengraph-title'];
						self::$export_instance->data[ $termID ]['opengraph-description'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_opengraph-description'];
						self::$export_instance->data[ $termID ]['opengraph-image'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_opengraph-image'];
						self::$export_instance->data[ $termID ]['twitter-title'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_twitter-title'];
						self::$export_instance->data[ $termID ]['twitter-description'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_twitter-description'];
						self::$export_instance->data[ $termID ]['twitter-image'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_twitter-image'];
						self::$export_instance->data[ $termID ]['focus_keyword'] = $seo_yoast_taxonomies['post_tag'][$termID]['wpseo_focuskw'];	
						
						if(isset($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_focuskeywords']) && !empty($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_focuskeywords'])){
						
							$decode_value = json_decode($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_focuskeywords'], true);
							$keywords = array_column($decode_value, 'keyword');
							self::$export_instance->data[ $termID ]['focuskeywords'] = implode('|', $keywords);
						}

						if(isset($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_keywordsynonyms']) && !empty($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_keywordsynonyms'])){
							$decode_value1 = json_decode($seo_yoast_taxonomies['post_tag'][$termID]['wpseo_keywordsynonyms'], true);
							array_shift($decode_value1);
							self::$export_instance->data[ $termID ]['keywordsynonyms'] = implode('|', $decode_value1);
						}
					}
				}
			}	
		}
				$result = self::$export_instance->finalDataToExport(self::$export_instance->data, $module);
		
		if($is_filter == 'filter_action'){
			return $result;
		}
		
		if($mode == null)
			self::$export_instance->proceedExport($result);
		else
			return $result;	
	}	
}