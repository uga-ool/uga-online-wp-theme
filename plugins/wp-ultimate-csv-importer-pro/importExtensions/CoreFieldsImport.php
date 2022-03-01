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

class CoreFieldsImport {
	private static $core_instance = null,$media_instance,$nextgen_instance;
	public $detailed_log;

	public static function getInstance() {
		if (CoreFieldsImport::$core_instance == null) {
			CoreFieldsImport::$core_instance = new CoreFieldsImport;
			CoreFieldsImport::$media_instance = new MediaHandling;
			CoreFieldsImport::$nextgen_instance = new NextGenGalleryImport;
			return CoreFieldsImport::$core_instance;
		}
		return CoreFieldsImport::$core_instance;
	}

	public static function filter_function(){

	}

	function set_core_values($header_array ,$value_array , $map , $type , $mode , $line_number , $unmatched_row, $check , $hash_key,$acf,$pods, $toolset, $update_based_on, $gmode, $variation_count, $wpml_array = null){
		global $wpdb;
	
		$helpers_instance = ImportHelpers::getInstance();
		CoreFieldsImport::$media_instance->header_array = $header_array;
		CoreFieldsImport::$media_instance->value_array = $value_array;
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$media_handle = get_option('smack_image_options');
		
		$taxonomies = get_taxonomies();
		
		if (in_array($type, $taxonomies)) {

			$import_type = $type;
			if($import_type == 'category' || $import_type == 'product_category' || $import_type == 'product_cat' || $import_type == 'wpsc_product_category' || $import_type == 'event-categories'):
				$type = 'Categories';
			elseif($import_type == 'product_tag' || $import_type == 'event-tags' || $import_type == 'post_tag'):
				$type = 'Tags';
		else:
			$type = 'Taxonomies';
			endif;
		}

		if(($type == 'WooCommerce Product Variations' ) || ($type == 'WooCommerce Orders') || ($type == 'WooCommerce Coupons') || ($type == 'WooCommerce Refunds') || ($type == 'WooCommerce Attributes') || ($type == 'WooCommerce Tags') || ($type == 'WooCommerce Product') || ($type == 'Categories') || ($type == 'Tags') || ($type == 'Taxonomies') || ($type == 'Comments') || ($type == 'Users') || ($type == 'Customer Reviews') || ($type == 'WPeCommerce Products') || ($type == 'WPeCommerce Coupons') || ($type == 'MarketPress Product') || ($type == 'MarketPress Product Variations') || ($type == 'eShop Products') || ($type == 'lp_order')  || ($type == 'nav_menu_item') || ($type == 'widgets')){

			$woocommerce_core_instance = WooCommerceCoreImport::getInstance();
			$taxonomies_instance = TaxonomiesImport::getInstance();
			$users_instance = UsersImport::getInstance();
			$comments_instance = CommentsImport::getInstance();
			$wpecommerce_instance = WPeCommerceImport::getInstance();
			$marketpress_instance = MarketPressImport::getInstance();	
			$customer_reviews_instance = CustomerReviewsImport::getInstance();
			$eshop_instance = EshopImport::getInstance();
			$learnpress_instance = LearnPressImport::getInstance();
			$post_values = [];
			$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
			
			
			$wpml_values = $helpers_instance->get_header_values($wpml_array , $header_array , $value_array);
			if($type == 'WooCommerce Product'){
				$result = $woocommerce_core_instance->woocommerce_product_import($post_values , $mode , $type, $unmatched_row, $check , $hash_key , $line_number, $acf ,$pods, $toolset,$header_array, $value_array,  $wpml_values);
			}
			if($type == 'WooCommerce Orders'){
				$result = $woocommerce_core_instance->woocommerce_orders_import($post_values , $mode , $check , $hash_key , $line_number);
			}
			if($type == 'WooCommerce Product Variations'){
				$result = $woocommerce_core_instance->woocommerce_variations_import($post_values , $mode , $check ,$hash_key , $line_number, $variation_count);
			}
			if($type == 'WooCommerce Coupons'){
				$result = $woocommerce_core_instance->woocommerce_coupons_import($post_values , $mode , $check , $hash_key , $line_number);
			}
			if($type == 'WooCommerce Refunds'){
				$result = $woocommerce_core_instance->woocommerce_refunds_import($post_values , $mode , $check , $hash_key , $line_number);
			}
			if($type == 'WooCommerce Attributes'){
				$result = $woocommerce_core_instance->woocommerce_attributes_import($post_values , $mode , $check ,$hash_key , $line_number);
			}
			if($type == 'WooCommerce Tags'){
				$result = $woocommerce_core_instance->woocommerce_tags_import($post_values , $mode , $check , $hash_key , $line_number);
			}

			if(($type == 'Categories') || ($type == 'Tags') || ($type == 'Taxonomies') ){
				$result = $taxonomies_instance->taxonomies_import_function($post_values , $mode , $import_type , $unmatched_row, $check , $hash_key ,$line_number ,$header_array ,$value_array);
			}
			if($type == 'Users'){
				$result = $users_instance->users_import_function($post_values , $mode ,$hash_key , $line_number);
			}
			if($type == 'Comments'){
				$result = $comments_instance->comments_import_function($post_values , $mode ,$hash_key , $line_number);
			}
			if($type == 'WPeCommerce Products'){
				$result = $wpecommerce_instance->wpecommerce_product_import($post_values , $mode , $check , $hash_key , $line_number);
			}
			if($type == 'WPeCommerce Coupons'){
				$result = $wpecommerce_instance->wpecommerce_coupons_import($post_values , $mode ,$hash_key , $line_number);
			}
			if($type == 'MarketPress Product'){
				$result = $marketpress_instance->marketpress_product_import($post_values , $mode , $check , $hash_key , $line_number);
			}
			if($type == 'MarketPress Product Variations'){
				$result = $marketpress_instance->marketpress_variation_import($post_values , $mode ,$hash_key  ,$line_number);
			}	
			if($type == 'Customer Reviews'){
				$result = $customer_reviews_instance->customer_reviews_import($post_values , $mode , $check ,$hash_key , $line_number);
			}
			if($type == 'eShop Products'){
				$result = $eshop_instance->eshop_product_import($post_values , $mode , $check ,$hash_key , $line_number);
			}

			if($type == 'lp_order'){
				$result = $learnpress_instance->learnpress_orders_import($post_values , $mode , $check, $hash_key , $line_number);
			}

			if($type == 'nav_menu_item'){
				$comments_instance->menu_import_function($post_values , $mode ,$hash_key , $line_number);
			}

			if($type == 'widgets'){
				$comments_instance->widget_import_function($post_values , $mode ,$hash_key , $line_number);
			}
			$post_id = isset($result['ID']) ? $result['ID'] :'';
			$helpers_instance->get_post_ids($post_id ,$hash_key);

			// if(isset($post_values['featured_image'])) {	
			// 	if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $post_values['featured_image'], $matchedlist, PREG_PATTERN_ORDER ) ) {	
			// 		$image_type = 'Featured';		
			// 		$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array);	
			// 	}
			// }

			if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['featured_image'])){
				update_option('ultimate_csv_importer_pro_featured_image', $post_values['featured_image']);
			}

			//added this condition, bcoz header and value array are not available during images schedule - so storing those datas prior in db
			if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['featured_image']) && (!empty($media_handle['media_settings']['file_name']) || !empty($media_handle['media_settings']['alttext']) || !empty($media_handle['media_settings']['description']) || !empty($media_handle['media_settings']['caption']) || !empty($media_handle['media_settings']['title']))){
				$media_seo_array = [];
				$media_seo_array['header_array'] = $header_array;
				$media_seo_array['value_array'] = $value_array;
				update_option('smack_media_seo'.$hash_key, $media_seo_array);
			}

			if(isset($post_values['featured_image'])) {	
				if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $post_values['featured_image'], $matchedlist, PREG_PATTERN_ORDER ) ) {	
					if($media_handle['media_settings']['media_handle_option'] == 'true' ){
						$post_values['featured_image'] = $this->check_for_featured_image_url($post_values['featured_image']);
						$attach_id = $this->featured_image_handling($media_handle, $post_values, $post_id, $type, $hash_key, $header_array, $value_array);
					}	
				}
			}
			
			if(preg_match("(Can't|Skipped|Duplicate)", $this->detailed_log[$line_number]['Message']) === 0) {  
				if ( $type == 'WooCommerce Product' || $type == 'MarketPress Product' || $type == 'eShop Products' || $type == 'WPeCommerce Products') {
					if ( ! isset( $post_values['post_title'] ) ) {
						$post_values['post_title'] = '';
					}
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				}
				elseif( $type == 'Users'){
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_edit_user_link( $post_id , true ) . "' target='_blank' title='" . esc_attr( 'Edit this item' ) . "'> User Profile </a>";
				}
				elseif($type == 'WooCommerce Orders' || $type == 'WooCommerce Coupons' || $type == 'lp_order'){
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				}
				// elseif($type = 'WooCommerce Product Variations'){
				// 	$post_values['post_title']=isset($post_values['post_title'])?$post_values['post_title']:'';
				// 	if(empty($variation_count)){
				// 		$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> ";
				// 	}
				// 	else{
				// 		$parent_id = $wpdb->get_var( "SELECT post_parent FROM {$wpdb->prefix}posts WHERE id = '$post_id' " );
				// 		$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $parent_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				// 	}
				// }
				elseif($type == 'WooCommerce Product Variations' ){
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> ";
				}
				elseif($type == 'Tags' || $type == 'Categories' || $type == 'Taxonomies' || $type == 'post_tag' || $type =='Post_category'){
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_edit_term_link( $post_id ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				}
				else{
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				}
				if(isset($post_values['post_status'])){
					$this->detailed_log[$line_number]['  Status'] = $post_values['post_status'];
				}	
			}
			
			return $post_id;
		}
		global $wpdb;
		$optional_type = '';
		if(is_plugin_active('jet-engine/jet-engine.php')){
			$get_slug_name = $wpdb->get_results($wpdb->prepare("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'"));
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				$optionaltype=$value;						
				if($optionaltype == $type){
					$optional_type=$optionaltype;
				}
			}
		}	
		if($optional_type == $type){
			$table_name.='wp_jet_cct_'.$type;
			$wpdb->get_results("INSERT INTO $table_name(cct_status,cct_author_id) values('publish',1)");       			
			$get_result =  $wpdb->get_results($wpdb->prepare("SELECT _ID FROM $table_name WHERE  cct_status = 'publish' order by _ID DESC "));			
			$id=$get_result[0];
			$post_id=$id->_ID + 1;
			$page.='jet-cct-'.$type;
			$dir=site_url().'/wp-admin';
			$this->detailed_log[$line_number]['Message'] = 'Inserted Images '  . ' ID: ' . $post_id ;
			$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='$dir/admin.php?page=$page&cct_action=edit&item_id=$post_id' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='$dir/admin.php?page=$page&cct_action=edit&item_id=$post_id' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Admin View</a>";			$this->detailed_log[$line_number][' Status'] = 'publish';
			$wpdb->query("DELETE FROM $table_name WHERE _ID = '$id->_ID'");
			
			return $post_id;
		}
		
		elseif($type == 'Images' || $type == 'ngg_pictures'){
			$post_values = [];
			foreach($map as $key => $value){
				$csv_value= trim($map[$key]);
				if(!empty($csv_value)){
					$get_key= array_search($csv_value , $header_array);
					if(isset($value_array[$get_key])){
						$csv_element = $value_array[$get_key];	
						$wp_element= trim($key);
						if(!empty($csv_element) && !empty($wp_element)){
							$post_values[$wp_element] = $csv_element;
						}
					}
				}
			}

			if($type == 'Images'){
				//changed
				if(array_key_exists( 'image_url', $post_values)) {
					$keys = array_keys($post_values);
					$keys[array_search('image_url', $keys)] = 'featured_image';
					$post_values = array_combine($keys, $post_values);	
				}
			
				if(!empty($post_values['featured_image'])){
					$post_values['featured_image'] = $this->check_for_featured_image_url($post_values['featured_image']);
				}
				$result = CoreFieldsImport::$media_instance->image_import($post_values,$check,$mode,$line_number);
			}
			if($type == 'ngg_pictures'){
				$result = CoreFieldsImport::$nextgen_instance->nextgenGallery($post_values,$check,$mode);
			}
		}

		else{
			$post_values = [];
			foreach($map as $key => $value){
				$csv_value = trim($map[$key]);
				$extension_object = new ExtensionHandler;
				$import_type = $extension_object->import_type_as($type);
				$import_as = $extension_object->import_post_types($import_type );
				if(!empty($csv_value)){
					//$pattern = "/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/";
					$pattern = '/{([^}]*)}/';

					if(preg_match_all($pattern, $csv_value, $matches, PREG_PATTERN_ORDER)){		
					
						$csv_element = $csv_value;
						//foreach($matches[2] as $value){
						foreach($matches[1] as $value){
							$get_key = array_search($value , $header_array);
							if(isset($value_array[$get_key])){
								$csv_value_element = $value_array[$get_key];	
								
								$value = '{'.$value.'}';
								$csv_element = str_replace($value, $csv_value_element, $csv_element);	
							}
						}

						$math = 'MATH';
						if (strpos($csv_element, $math) !== false) {
									
							$equation = str_replace('MATH', '', $csv_element);
							$csv_element = $helpers_instance->evalmath($equation);
						}
						$wp_element= trim($key);
						if(!empty($csv_element) && !empty($wp_element)){
							$post_values[$wp_element] = $csv_element;	
							$post_values['post_type'] = $import_as;
							$post_values = $this->import_core_fields($post_values,$mode);
						}
					}

					elseif(!in_array($csv_value , $header_array)){
						$wp_element= trim($key);
						$post_values[$wp_element] = $csv_value;
						$post_values['post_type'] = $import_as;
						$post_values = $this->import_core_fields($post_values,$mode);
					}
					
					else{
						
						$get_key= array_search($csv_value , $header_array);
						if(isset($value_array[$get_key])){
							$csv_element = $value_array[$get_key];	
							$wp_element= trim($key);
							$extension_object = new ExtensionHandler;
							$import_type = $extension_object->import_type_as($type);
							$import_as = $extension_object->import_post_types($import_type );
							if($mode == 'Insert'){
								if(!empty($csv_element) && !empty($wp_element)){
									$post_values[$wp_element] = $csv_element;
									$post_values['post_type'] = $import_as;
									$post_values = $this->import_core_fields($post_values,$mode);
								}
							}
							else{
								if(!empty($csv_element) || !empty($wp_element)){
									$post_values[$wp_element] = $csv_element;
									$post_values['post_type'] = $import_as;
									$post_values = $this->import_core_fields($post_values,$mode);
								}
							}
							if($import_as == 'page'){
								if(isset($post_values['post_parent'])){
									if(!is_numeric($post_values['post_parent'])){
											$post_parent_title = $post_values['post_parent'];
											$post_parent_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$post_parent_title' AND post_type = 'page'");
											$post_values['post_parent'] = $post_parent_id;
									}
								}
							}
						}
					}
				}
			}
			if($check == 'ID'){	
				$ID = $post_values['ID'];	
				$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID = $ID AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");			
			}
			if($check == '_ID'){
				$ID = $post_values['_ID'];	
				$table_name.='wp_jet_cct_'.$type;
				$get_result =  $wpdb->get_results("SELECT _ID FROM $table_name WHERE _ID = $ID AND cct_status != 'trash' order by _ID DESC ");			
				foreach($get_result as $key=>$get_slug){
					$post_id=$get_slug->_ID;
				}
				return $post_id;				
			}
			if($check == 'post_title'){
				$title = $post_values['post_title'];
				$title = $wpdb->_real_escape($title);
				$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$title' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");		
			}
			if($check == 'post_name'){
				$name = $post_values['post_name'];
				$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = '$name' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");	
			}
			if($check == 'post_content'){
				$content = $post_values['post_content'];
				$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_content = '$content' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");	
			}
			$update = array('ID','post_title','post_name','post_content');
	
			if(!in_array($check, $update)){
				if($update_based_on == 'acf'){
					if(is_plugin_active('advanced-custom-fields-pro/acf.php')||is_plugin_active('advanced-custom-fields/acf.php')){
						$get_result = $this->custom_fields_update_based_on($update_based_on, $acf, $check, $header_array, $value_array);
					}
				}
				elseif($update_based_on == 'toolset'){
					if(is_plugin_active('types/wpcf.php')){
						$get_result = $this->custom_fields_update_based_on($update_based_on, $toolset, $check, $header_array, $value_array);
					}
				}
				if($update_based_on == 'pods'){
					if(is_plugin_active('pods/init.php')){
						$get_result = $this->custom_fields_update_based_on($update_based_on, $pods, $check, $header_array, $value_array);
					}
				}	
			}
		
			$updated_row_counts = $helpers_instance->update_count($hash_key);
			$created_count = $updated_row_counts['created'];
			$updated_count = $updated_row_counts['updated'];
			$skipped_count = $updated_row_counts['skipped'];

			if($mode == 'Insert'){
		
				if (isset($get_result) && is_array($get_result) && !empty($get_result)) {
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE hash_key = '$hash_key'");
					$this->detailed_log[$line_number]['Message'] =  "Skipped, Due to duplicate found!.";
				}else{
					
					$media_handle = get_option('smack_image_options');
					if($media_handle['media_settings']['media_handle_option'] == 'true' && $media_handle['media_settings']['enable_postcontent_image'] == 'true'){
					
						if(preg_match("/<img/", $post_values['post_content'])) {

							$content = "<p>".$post_values['post_content']."</p>";
							$doc = new \DOMDocument();
							if(function_exists('mb_convert_encoding')) {
								@$doc->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
							}else{
								@$doc->loadHTML( $content);
							}
							$searchNode = $doc->getElementsByTagName( "img" );
							if ( ! empty( $searchNode ) ) {
								foreach ( $searchNode as $searchNode ) {
									$orig_img_src[] = $searchNode->getAttribute( 'src' ); 			
									$media_dir = wp_get_upload_dir();
									$names = $media_dir['url'];
									$image_type = 'inline';
									if (strpos($orig_img_src , $names) !== false) {
										$shortcode_img = $orig_img_src;
										$check_inline_image = $wpdb->get_results("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_media_report WHERE hash_key = '$hash_key'  AND image_type = 'inline' "); 
										if(empty($check_inline_image)){
											$image_table = $wpdb->prefix . "ultimate_csv_importer_media_report";
											$wpdb->get_results("INSERT INTO $image_table (`hash_key`,`module`,`image_type`,`status`) VALUES ( '{$hash_key}','{$type}','{$image_type}','Completed')");
										}
									}
									else{
										$rand = mt_rand(1, 999);	
										$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
										$get_shortcode = $wpdb->get_results("SELECT `image_shortcode` FROM $shortcode_table WHERE original_image = '{$orig_img_src}' ",ARRAY_A);
										if(!empty($get_shortcode)) 
										{
											$shortcode_img = $get_shortcode[0]['image_shortcode'];
										}		
										else{
											$shortcode_img = 'inline_'.$rand.'_'.$orig_img_src;
										}
									}

									$temp_img = plugins_url("../assets/images/loading-image.jpg", __FILE__);
									$searchNode->setAttribute( 'src', $temp_img);
									$searchNode->setAttribute( 'alt', $shortcode_img );

								}
								$post_content              = $doc->saveHTML();
								$post_values['post_content'] = $post_content;
								$update_content['ID']           = $post_id;
								$update_content['post_content'] = $post_content;
								wp_update_post( $update_content );
							}
						}
					}

					// image handling code
					if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['featured_image'])){
						update_option('ultimate_csv_importer_pro_featured_image', $post_values['featured_image']);
					}

					// if(!is_numeric($post_values['post_parent'])&&!empty($post_values['post_parent'])){
					if( (isset($post_values['post_parent'])) && (!is_numeric($post_values['post_parent'])) && (!empty($post_values['post_parent']))){
						$p_type=$post_values['post_type'];
						$parent_title=$post_values['post_parent'];
						$parent_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '$parent_title' and post_status !='trash' and post_type='$p_type'" );
						$post_values['post_parent']=$parent_id;
					}
					if($post_values['post_status']!='delete'){
						if(is_plugin_active('multilanguage/multilanguage.php')) {
							$post_id = $this->multiLang($post_values);
						}
						else{
							$post_values['post_content']=isset($post_values['post_content'])?$post_values['post_content']:'';
							$post_values['post_content'] = html_entity_decode($post_values['post_content']);
							
							$post_id = wp_insert_post($post_values);
							$status = $post_values['post_status'];
							$update=$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
							}
						if(!empty($post_values['wp_page_template'])){
							update_post_meta($post_id, '_wp_page_template', $post_values['wp_page_template']);
						}
					}

					if($post_values['post_status'] == 'delete'){
						$post_title = $post_values['post_title'];
						$post_id = $wpdb->get_results("select ID from {$wpdb->prefix}posts where post_title = '$post_title'");
						foreach($post_id as $value){
							$posts = $value->ID;
							wp_delete_post($posts,true); 
						}
					}
		
					if($unmatched_row == 'true'){
						global $wpdb;
						$post_entries_table = $wpdb->prefix ."post_entries_table";
						$file_table_name = $wpdb->prefix."smackcsv_file_events";
						$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
						$file_name = $get_id[0]->file_name;
						$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Inserted')");
					}

					if(isset($post_values['post_format'])){
						$this->post_format_function($post_id, $post_values['post_format']);
					}

					if(is_plugin_active('post-expirator/post-expirator.php')) {
						$this->postExpirator($post_id,$post_values);
					}
					
					$fields = $wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE hash_key = '$hash_key'");
						
					if(preg_match("/<img/", $post_values['post_content'])) {
						$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
						if(isset($orig_img_src)){
							foreach ($orig_img_src as $img => $img_val){
								//$shortcode  = $shortcode_img[$img][$img];
								$shortcode  = 'inline';
								$wpdb->get_results("INSERT INTO $shortcode_table (image_shortcode , original_image , post_id,hash_key) VALUES ( '{$shortcode}', '{$img_val}', $post_id  ,'{$hash_key}')");
							}
							$doc = new \DOMDocument();
							$searchNode = $doc->getElementsByTagName( "img" );
							if ( ! empty( $searchNode ) ) {
								foreach ( $searchNode as $searchNode ) {
									$orig_img_src = $searchNode->getAttribute( 'src' ); 
								}
							}			
							$media_dir = wp_get_upload_dir();
							$names = $media_dir['url'];
						}
					}			
					$media_dir = wp_get_upload_dir();
					$names = $media_dir['url'];
						
					// image handling code
					if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['featured_image'])){
						$post_values['featured_image'] = $this->check_for_featured_image_url($post_values['featured_image']);
						$attach_id = $this->featured_image_handling($media_handle, $post_values, $post_id, $type, $hash_key, $header_array, $value_array);
					}

					if(is_wp_error($post_id) || $post_id == '') {
						if(is_wp_error($post_id)) {
							$this->detailed_log[$line_number]['Message'] = "Can't insert this " . $post_values['post_type'] . ". " . $post_id->get_error_message();
						}
						else {
							$this->detailed_log[$line_number]['Message'] =  "Can't insert this " . $post_values['post_type'];
						}
						$fields = $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE hash_key = '$hash_key'");
					}	
					else{
						$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
					}
				}
			}
		
            if($mode == 'Update'){
	
				if (is_array($get_result) && !empty($get_result)) {
					if(!in_array($check, $update)){
						$post_id = $get_result[0]->post_id;	
						$post_values['ID'] = $post_id;
					}else{
						$post_id = $get_result[0]->ID;		
						$post_values['ID'] = $post_id;
						//$orig_img_src = $searchNode->getAttribute( 'src' ); 	
					}
					if($post_values['post_status']== 'delete'){
						wp_delete_post($post_values['ID'],true);
					}else{
						wp_update_post($post_values);
					}
					
					if(isset($post_values['post_format'])){
						$this->post_format_function($post_id, $post_values['post_format']);
					}

					$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE hash_key = '$hash_key'");
					$this->detailed_log[$line_number]['Message'] = 'Updated' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
				
				}else{

					$media_handle = get_option('smack_image_options');
					if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['featured_image'])){
						update_option('ultimate_csv_importer_pro_featured_image', $post_values['featured_image']);
					}
					
					//added featured image code in update
					// if(isset($post_values['featured_image'])) {	
					// 	if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $post_values['featured_image'], $matchedlist, PREG_PATTERN_ORDER ) ) {	
					// 		$image_type = 'Featured';		
					// 		$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array,$mode);	
					// 	}	
					// }
				
					if($media_handle['media_settings']['media_handle_option'] == 'true' && $media_handle['media_settings']['enable_postcontent_image'] == 'true'){
					
						if(preg_match("/<img/", $post_values['post_content'])) {

							$content = "<p>".$post_values['post_content']."</p>";
							$doc = new \DOMDocument();
							if(function_exists('mb_convert_encoding')) {
								@$doc->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
							}else{
								@$doc->loadHTML( $content);
							}
							$searchNode = $doc->getElementsByTagName( "img" );
							if ( ! empty( $searchNode ) ) {
								foreach ( $searchNode as $searchNode ) {
									$orig_img_src[] = $searchNode->getAttribute( 'src' );
									$media_dir = wp_get_upload_dir();
									$names = $media_dir['url'];
									$image_type = 'inline';
									if (strpos($orig_img_src , $names) !== false) {
										$shortcode_img = $orig_img_src;
										$check_inline_image = $wpdb->get_results("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_media_report WHERE hash_key = '$hash_key'  AND image_type = 'inline' "); 
										//if(empty($check_inline_image)){
											$image_table = $wpdb->prefix . "ultimate_csv_importer_media_report";
											$wpdb->get_results("INSERT INTO $image_table (`hash_key`,`module`,`image_type`,`status`) VALUES ( '{$hash_key}','{$type}','{$image_type}','Completed')");
										//}
									}
									else{
										$rand = mt_rand(1, 999);	
										$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
										$get_shortcode = $wpdb->get_results("SELECT `image_shortcode` FROM $shortcode_table WHERE original_image = '{$orig_img_src}' ",ARRAY_A);
										if(!empty($get_shortcode)) 
										{
											$shortcode_img = $get_shortcode[0]['image_shortcode'];
										}		
										else{
											$shortcode_img = 'inline_'.$rand.'_'.$orig_img_src;
										}
									}

									$temp_img = plugins_url("../assets/images/loading-image.jpg", __FILE__);
									$searchNode->setAttribute( 'src', $temp_img);
									$searchNode->setAttribute( 'alt', $shortcode_img );

								}
								$post_content              = $doc->saveHTML();
								$post_values['post_content'] = $post_content;
								$update_content['ID']           = $post_id;
								$update_content['post_content'] = $post_content;
								wp_update_post( $update_content );
							}
						}
					}
				
					$post_id = wp_insert_post($post_values);
					if(!empty($post_values['wp_page_template']) && $type == 'Pages'){
						update_post_meta($post_id, '_wp_page_template', $post_values['wp_page_template']);
					}
					if(isset($post_values['post_format'])){
						if($post_values['post_format'] == 'post-format-video' ){
							$format = 'video';
						}
						else{
							$format=trim($post_values['post_format'],"post-format-");
						}
						set_post_format($post_id , $format);
					}
					$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE hash_key = '$hash_key'");
					
					if(preg_match("/<img/", $post_values['post_content'])) {
						$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
						$wpdb->get_results("INSERT INTO $shortcode_table (image_shortcode , original_image , post_id,hash_key) VALUES ( '{$shortcode_img}', '{$orig_img_src}', $post_id  ,'{$hash_key}')");
					}
					if(is_wp_error($post_id) || $post_id == '') {
						if(is_wp_error($post_id)) {
							$this->detailed_log[$line_number]['Message'] = "Can't insert this " . $post_values['post_type'] . ". " . $post_id->get_error_message();
						}
						else {
							$this->detailed_log[$line_number]['Message'] =  "Can't insert this " . $post_values['post_type'];
						}
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE hash_key = '$hash_key'");
					}
					else{
						$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];	
					}

					if($post_values['post_type'] == 'event' || $post_values['post_type'] == 'event-recurring'){
						$status = $post_values['post_status'];
						$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
					}
				}

				if($unmatched_row == 'true'){
					global $wpdb;
					$post_entries_table = $wpdb->prefix ."post_entries_table";
					$file_table_name = $wpdb->prefix."smackcsv_file_events";
					$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
					$file_name = $get_id[0]->file_name;
					$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Updated')");
				}
			}

			if(preg_match("(Can't|Skipped|Duplicate)", $this->detailed_log[$line_number]['Message']) === 0) {  
				if ( $type == 'Posts' || $type == 'CustomPosts' || $type == 'Pages' || $type == 'Tickets') {
					if ( ! isset( $post_values['post_title'] ) ) {
						$post_values['post_title'] = '';
					}
					if ($gmode == 'Normal'){
						$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";	
					}
					else{
						if(empty($post_id)){
							$this->detailed_log[$line_number][' Message'] = 'Skipped';
						}
						else{
							$get_guid =$wpdb->get_results("select guid from {$wpdb->prefix}posts where ID= '$post_id'" ,ARRAY_A);
							$link =$get_guid[0]['guid'];
							$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . $link . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";	
						}
					}
			    }
				else{
					$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
				}
				$this->detailed_log[$line_number][' Status'] = $post_values['post_status'];
			}
			
			if(isset($post_values['featured_image'])) {	
				if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $post_values['featured_image'], $matchedlist, PREG_PATTERN_ORDER ) ) {	
					$image_type = 'Featured';		
					$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array,$mode);	
				}
			}

			return $post_id;
		}
	}

	public function multiLang($post_values){
		global $wpdb;
		if (strpos($post_values['post_title'], '|') !== false) {
			$exploded_title = explode('|', $post_values['post_title']);
			$post_values['post_title'] = $exploded_title[0];
			$lang_title = $exploded_title[1];

		}
		if (strpos($post_values['post_content'], '|') !== false) {
			$exploded_content = explode('|', $post_values['post_content']);
			$post_values['post_content'] = $exploded_content[0];
			$lang_content = $exploded_content[1];
		}
		if (strpos($post_values['post_excerpt'], '|') !== false) {
			$exploded_excerpt = explode('|', $post_values['post_excerpt']);
			$post_values['post_excerpt'] = $exploded_excerpt[0];
			$lang_excerpt = $exploded_excerpt[1];
		}
		$lang_code = $post_values['lang_code'];
		$post_id = wp_insert_post($post_values);
		$wpdb->get_results("INSERT INTO {$wpdb->prefix}mltlngg_translate (post_ID , post_content , post_excerpt, post_title,`language`) VALUES ( $post_id, '{$lang_content}', '{$lang_excerpt}' , '{$lang_title}', '{$lang_code}')");
		return $post_id;
	}

	public function postExpirator($post_id,$post_values){
		if(!empty($post_values['post_expirator_status'])){
			$post_values['post_expirator_status'] = array('expireType' => $post_values['post_expirator_status'],'id' => $post_id);
		}
		else{
			$post_values['post_expirator_status'] = array('expireType' => 'draft' ,'id' => $post_id);
		}

		if(!empty($post_values['post_expirator'])){
			update_post_meta($post_id, '_expiration-date-status', 'saved');
			$estimate_date = $post_values['post_expirator'];
			$estimator_date = get_gmt_from_date("$estimate_date",'U');
			update_post_meta($post_id, '_expiration-date', $estimator_date);
			update_post_meta($post_id, '_expiration-date-options', $post_values['post_expirator_status']);			
		}	
	}


	function image_handling($id){

		global $wpdb;	
		$post_values = [];
		$get_result =  $wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts where ID = $id ",ARRAY_A);   
		if(empty($get_result)){
			$get_result[0]['post_content'] = '';
			$post_values['post_content']=htmlspecialchars_decode($get_result[0]['post_content']);
		}
		else{
			$post_values['post_content']=htmlspecialchars_decode($get_result[0]['post_content']);
		}
		$get_result =  $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager where post_id = $id and image_shortcode ='inline'",ARRAY_A);   	
		foreach($get_result as $result){
			$orig_img_src[] = $result['original_image'];
		}
		$get_results =  $wpdb->get_results("SELECT image_shortcode FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager where post_id = $id",ARRAY_A);   
		foreach ($get_results as $results){
			$origs_img_src[] = $results['image_shortcode'];
		}

		$image_type = 'Inline' ;
		if(!empty($orig_img_src)){
			foreach($orig_img_src as $src){
				$attach_id[] = CoreFieldsImport::$media_instance->media_handling( $src , $id ,$post_values,'',$image_type,'');
			}
		}
		if(!empty($attach_id)){
			foreach($attach_id as $att_key => $att_val){
				$get_guid[] = $wpdb->get_results("SELECT `guid` FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' and ID =  $att_val ",ARRAY_A);
				foreach($origs_img_src as $img_src){
					$result  = str_replace($img_src , ' ' , $post_values['post_content']);
				}
			}
		}
		$image_name = isset($result) ? $result :'';
		$doc = new \DOMDocument();
		if(!empty($image_name)){
			if(function_exists('mb_convert_encoding')) {
				@$doc->loadHTML( mb_convert_encoding( $image_name, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			}else{
				@$doc->loadHTML( $image_name);
			}
		}
		$img_tags = $doc->getElementsByTagName('img');
		$i=0;
		foreach ($img_tags as $t )
		{
			$savepath = $get_guid[$i][0]['guid'];	
			$t->setAttribute('src',$savepath);
			$i++;
		}
		$result = $doc->saveHTML();
		$update_content['ID']           = $id;
		$update_content['post_content'] = $result;
		wp_update_post( $update_content );
	
		if($result){
			$wpdb->update( $wpdb->prefix . 'ultimate_csv_importer_shortcode_manager' , 
            array( 
                'status' => 'completed',
            ) , 
            array( 'post_id' => $id ,
                'image_shortcode' => 'inline'
            ) 
           );
			
		}
		return $id;
	}

	function import_core_fields($data_array,$mode = null){
		$helpers_instance = ImportHelpers::getInstance();
		if($mode == 'Insert'){
			if(!isset( $data_array['post_date'] )) {
				$data_array['post_date'] = current_time('Y-m-d H:i:s');
			} else {	
				if(strtotime( $data_array['post_date'] )) {
					$data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['post_date'] ) );
				} else {
					$data_array['post_date'] = current_time('Y-m-d H:i:s');
				}
			}
		}

		if(!isset($data_array['post_author']) && $mode != 'Update') {
			$data_array['post_author'] = 1;
		} else {
			if(isset( $data_array['post_author'] )) {
				$user_records = $helpers_instance->get_from_user_details( $data_array['post_author'] );
				$data_array['post_author'] = $user_records['user_id'];
				$data_array['specific_author'] = $user_records['message'];
			}
		}
		if ( !empty($data_array['post_status']) ) {
			$data_array = $helpers_instance->assign_post_status( $data_array );
		}else{
			$data_array['post_status'] = 'publish';
		}
		return $data_array;
	}

	public function custom_fields_update_based_on($update_based_on, $custom_array, $check, $header_array, $value_array){
		global $wpdb;

		if(is_array($custom_array)){		
			foreach($custom_array as $custom_key => $custom_value){
				if (strpos($custom_value, '{') !== false && strpos($custom_value, '}') !== false) {
					$custom_value = $custom_key;
				}
				if($custom_key == $check){
					$get_key= array_search($custom_value , $header_array);
				}
				if(isset($value_array[$get_key])){
					$csv_element = $value_array[$get_key];	
				}

				if($update_based_on == 'acf' || $update_based_on == 'pods'){
					$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$check' AND a.meta_value = '$csv_element' AND b.post_status != 'trash' order by a.post_id DESC ");
				}
				elseif($update_based_on == 'toolset'){
					$meta_key = 'wpcf-'.$check;
					$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$meta_key' AND a.meta_value = '$csv_element' AND b.post_status != 'trash' order by a.post_id DESC ");
				}
			}	
			return $get_result;
		}		
	}

	public function post_format_function($post_id, $post_format_value){
		$format=str_replace("post-format-","",$post_format_value);
			set_post_format($post_id ,$format );
	}
	
	public function featured_image_handling($media_handle, $post_values, $post_id, $type, $hash_key, $header_array, $value_array){
		global $wpdb;
		if($media_handle['media_settings']['use_ExistingImage'] == 'true'){
			$image_type = 'Featured';		
		
			$f_image = $post_values['featured_image'];
			$image_name = pathinfo($f_image);
			$fimg_name = $image_name['filename'];
			
			$check_featured_image = $wpdb->get_results("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_media_report WHERE hash_key = '$hash_key'  AND image_type = 'Featured' "); 
			if(empty($check_featured_image)){
				
				$image_media_table = $wpdb->prefix . "ultimate_csv_importer_media_report";
				$wpdb->get_results("INSERT INTO $image_media_table (`hash_key`,`module`,`image_type`,`status`) VALUES ( '{$hash_key}','{$type}','{$image_type}','Completed') ");
			}

			$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid LIKE '%$fimg_name%'", ARRAY_A);

			if(!empty($attachment_id[0]['ID'])){
				set_post_thumbnail($post_id, $attachment_id[0]['ID'] );
                //$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array);
			}
			else{
				$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
				$original_featured_image = get_option('ultimate_csv_importer_pro_featured_image');
		
				if($post_id && ($post_id != 0)){
					
					$wpdb->insert($shortcode_table,
						array('image_shortcode' => 'featured_image',
									'original_image' => $original_featured_image,
									'post_id' => $post_id,
									'hash_key' => $hash_key,
						),
						array('%s','%s','%d','%s')
					);
				}	
				delete_option('ultimate_csv_importer_pro_featured_image');	

				$post_values['featured_image'] = WP_PLUGIN_URL . '/wp-ultimate-csv-importer-pro/assets/images/loading-image.jpg';	
				$image_type = 'Featured';		
				$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array);	
				set_post_thumbnail( $post_id, $attach_id );
			}
		}
		else{
			
			$image_type = 'Featured';	
			$check_featured_image = $wpdb->get_results("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_media_report WHERE hash_key = '$hash_key'  AND image_type = 'Featured' "); 
			if(empty($check_featured_image)){
				
				$image_media_table = $wpdb->prefix . "ultimate_csv_importer_media_report";
				$wpdb->get_results("INSERT INTO $image_media_table (`hash_key`,`module`,`image_type`,`status`) VALUES ( '{$hash_key}','{$type}','{$image_type}','Completed') ");
			}
		
			$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
			$original_featured_image = get_option('ultimate_csv_importer_pro_featured_image');
	
			if($post_id && ($post_id != 0)){
				$wpdb->insert($shortcode_table,
					array('image_shortcode' => 'featured_image',
								'original_image' => $original_featured_image,
								'post_id' => $post_id,
								'hash_key' => $hash_key,
					),
					array('%s','%s','%d','%s')
				);
			}
			delete_option('ultimate_csv_importer_pro_featured_image');

			$post_values['featured_image'] = WP_PLUGIN_URL . '/wp-ultimate-csv-importer-pro/assets/images/loading-image.jpg';	
			$image_type = 'Featured';		
			$attach_id = CoreFieldsImport::$media_instance->media_handling( $post_values['featured_image'] , $post_id ,$post_values,$type,$image_type,$hash_key,$header_array,$value_array);	
			set_post_thumbnail( $post_id, $attach_id );
		}
		$attach_id=isset($attach_id)?$attach_id:'';
		return $attach_id;
	}

	public function check_for_featured_image_url($featured_image){
		if (strpos($featured_image, '|') !== false) {
			$featured_img = explode('|', $featured_image);
			$featured_image_url = $featured_img[0];					
		}
		else if (strpos($featured_image, ',') !== false) {
			$feature_img = explode(',', $featured_image);
			$featured_image_url = $feature_img[0];
		}
		else{
			$featured_image_url = $featured_image;
		}
		return $featured_image_url;
	}
}