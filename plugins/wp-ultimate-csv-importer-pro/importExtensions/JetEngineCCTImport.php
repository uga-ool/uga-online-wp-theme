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

class JetEngineCCTImport {

	private static $instance = null;

	public static function getInstance() {		
		if (JetEngineCCTImport::$instance == null) {
			JetEngineCCTImport::$instance = new JetEngineCCTImport;
		}
		return JetEngineCCTImport::$instance;
	}

	function set_jet_engine_cct_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_cct_import_function($post_values,$type, $post_id, $mode, $hash_key);
	}
	function set_jet_engine_cct_rf_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_cct_rf_import_function($post_values,$type, $post_id, $mode, $hash_key);
	}
	public function jet_engine_cct_import_function($data_array, $type, $pID ,$mode, $hash_key) 
	{
		global $wpdb;
		$media_instance = MediaHandling::getInstance();
		$jet_data = $this->JetEngineCCTFields($type);
		$get_gallery_id = $gallery_ids = '';
		foreach ($data_array as $dkey => $dvalue) {
			if(array_key_exists($dkey,$jet_data['JECCT'])){
				
				if($jet_data['JECCT'][$dkey]['type'] == 'text' ||$jet_data['JECCT'][$dkey]['type'] == 'textarea'
				|| $jet_data['JECCT'][$dkey]['type'] == 'colorpicker' || $jet_data['JECCT'][$dkey]['type'] == 'iconpicker'
				|| $jet_data['JECCT'][$dkey]['type'] == 'radio' || $jet_data['JECCT'][$dkey]['type'] == 'number'
				|| $jet_data['JECCT'][$dkey]['type'] == 'wysiwyg' || $jet_data['JECCT'][$dkey]['type'] == 'switcher'){
					
					$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;

				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'date'){
					if(!empty($dvalue)){
						$var = trim($dvalue);
						$date = str_replace('/', '-', "$var");
						if($jet_data['JECCT'][$dkey]['is_timestamp']){
							$date_of = strtotime($date);
						}else{
							$date_of = $date;
						}
						$darray[$jet_data['JECCT'][$dkey]['name']] = $date_of;
					}
					else{
						$darray[$jet_data['JECCT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'gallery' || $jet_data['JECCT'][$dkey]['type'] == 'media'){
					$gallery_ids = $media_ids = '';
					$exploded_gallery_items = explode( ',', $dvalue );
                    $galleryvalue=array();
					foreach ( $exploded_gallery_items as $gallery ) {
						$gallery = trim( $gallery );
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
							$get_gallery_id = $media_instance->media_handling( $gallery, $pID);	
							$media_id = $media_instance->media_handling( $gallery, $pID);
							if ( $get_gallery_id != '' ) {
								if($jet_data['JECCT'][$dkey]['type'] == 'media'){
									$media_ids .= $media_id. ',';
								}
								elseif($jet_data['JECCT'][$dkey]['value_format'] == 'url'){
									global $wpdb;
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
								}
                                elseif($jet_data['JECCT'][$dkey]['value_format'] == 'both'){
									global $wpdb;
									$gallery_id1 ['id']= $get_gallery_id;
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_id2['url']= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value;
									$galleryvalue[] = array_merge($gallery_id1,$gallery_id2);
									$gallery_ids=$galleryvalue;
								}
								else{
									$gallery_ids .= $get_gallery_id.',';
								}
							}
						} else {
							$galleryLen         = strlen( $gallery );
							$checkgalleryid     = intval( $gallery );
							$verifiedGalleryLen = strlen( $checkgalleryid );
							if ( $galleryLen == $verifiedGalleryLen ) {
								if($jet_data['JECCT'][$dkey]['type'] == 'media'){
									$media_ids .= $gallery. ',';
								}
								else{
									$gallery_ids .= $gallery. ',';
								}

							}
						}
					}
					if(is_array($gallery_ids)){
						$gallery_id  = $gallery_ids;
					}
					if (!is_array($gallery_ids)) {
						$gallery_id = rtrim($gallery_ids,',');
					}
					if(isset($media_ids)){
						$media_id1 = rtrim($media_ids,',');
					}
					if($jet_data['JECCT'][$dkey]['value_format'] == 'url'){
						global $wpdb;
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						if(!empty($get_media_fields[0]->meta_value)){
                            $media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
                        }        
                        else{
                            $media_id='';
                        }
					}
                    elseif($jet_data['JECCT'][$dkey]['value_format'] == 'both'){
						global $wpdb;
						$media_ids1['id']=$media_id;
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						if(!empty($get_media_fields[0]->meta_value)){
							$media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
						}        
						else{
							$media_ids2['url']='';
						}
						$mediavalue= array_merge($media_ids1,$media_ids2);
						$media_id=array($mediavalue);
					}	
					else{
						$media_id=$media_id;
					}
					if($jet_data['JECCT'][$dkey]['type'] == 'media'){
						
						$darray[$jet_data['JECCT'][$dkey]['name']] = $media_id;
					}
					else{	
						$darray[$jet_data['JECCT'][$dkey]['name']] = $gallery_id;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'datetime-local'){
					$dt_var = trim($dvalue);
					
					if($jet_rf_data['JECCT'][$dkey]['is_timestamp']){
						$date_time_of =  strtotime($dt_var) ;
					}else{
						$date_time_of = $dt_var ;
					}
					$darray[$jet_data['JECCT'][$dkey]['name']] = $date_time_of;

				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'time'){
					$var = trim($dvalue);
					$time = date('H:i', strtotime($var));
					$darray[$jet_data['JECCT'][$dkey]['name']] = $time;
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'checkbox'){
					if($jet_data['JECCT'][$dkey]['is_array'] == 1){
						$dvalexp = explode(',' , $dvalue);
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalexp;
					}
					else{
						$options = $jet_data['JECCT'][$dkey]['options'];
						$arr = [];
						$opt = [];
						$dvalexp = explode(',' , $dvalue);
						foreach($options as $option_key => $option_val){
							//$opt[$option_key]	= $option_val['key'];
							$arr[$option_val['key']] = 'false';
						}
						foreach($dvalexp as $dvalkey => $dvalueval){
							$keys = array_keys($arr);
							foreach($keys as $keys1){
								if($dvalueval == $keys1){
									$arr[$keys1] = 'true';
								}
							}
						}
						$check_val=serialize($arr);
						$darray[$jet_data['JECCT'][$dkey]['name']] = $check_val;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'select'){
					$dselect = [];
					if($jet_data['JECCT'][$dkey]['is_multiple'] == 0){
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;	
					}
					else{
						$exp =explode(',',$dvalue);
						$dselect = $exp;
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dselect;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'posts'){
					global $wpdb;
					if($jet_data['JECCT'][$dkey]['is_multiple'] == 0){
						$jet_posts = trim($dvalue);
						//$jet_posts = $wpdb->_real_escape($jet_posts);
						if(is_string($jet_posts)){
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status='publish'";
							$name = $wpdb->get_results($query);
							if (!empty($name)) {
								$jet_posts_value=$name[0]->id;
							}
						}
						elseif (is_numeric($jet_posts)) {
							$jet_posts_value=$jet_posts;
						}
					}
					else{
						$jet_posts_exp = explode(',',trim($dvalue));
						$jet_posts_value = array();
						foreach($jet_posts_exp as $jet_posts_value){
							$jet_posts_value = trim($jet_posts_value);
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status='publish' ORDER BY ID DESC";
							$multiple_id = $wpdb->get_results($query);
							$multiple_id[0] = isset($multiple_id[0]) ? $multiple_id[0] : '';
							$multiple_ids =$multiple_id[0];
							if(!$multiple_id){
								$jet_posts_field_value[]=$jet_posts_value;
							}
							else{
								$jet_posts_field_value[]=trim($multiple_ids->id);
							}
						}
					}
					$jet_posts_value=serialize($jet_posts_field_value);
					$darray[$jet_data['JECCT'][$dkey]['name']] = $jet_posts_value;
				}
				else{
					if($jet_data['JECCT'][$dkey]['type'] != 'repeater'){
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;
					}
				}
				// html                          	
			}
		}
		$darray['cct_status']='publish';
		$darray['cct_author_id'] = 1;
		$darray['cct_modified']= date("Y-m-d\TH:i");	
		$table_name.='wp_jet_cct_'.$type;
		if($darray){	
			foreach($darray as $mkey => $mval){  
				if(is_array($mval)){	
					$key_name.=$mkey.',';
					$val=serialize($mval);
					$data_values.="'".$val."'".',';	
				}
				else{
					$key_name.=$mkey.',';
					$data_values.="'".$mval."'".',';	
				}    
			}
			$key=rtrim($key_name,',');
			$data_value=rtrim($data_values,','); 
			if($mode == 'Insert'){
				$wpdb->get_results("INSERT INTO $table_name($key) values($data_value)");       
			}
			if($mode == 'Update'){
				foreach($darray as $mkey => $mval){  
					$sql = $wpdb->prepare(
						"UPDATE $table_name SET $mkey = '$mval' WHERE _ID = %d;",
						$pID
					);
					$wpdb->query( $sql );
				}
			}
		}
	}
	public function jet_engine_cct_rf_import_function($data_array, $type, $pID ,$mode, $hash_key) 
	{
		global $wpdb;
		$media_instance = MediaHandling::getInstance();
		$jet_rf_data = $this->JetEngineCCTRFFields($type);
		foreach ($data_array as $dkey => $dvalue) {
			$dvalue =trim($dvalue);
			$dvaluexp = explode( '|', $dvalue);
			foreach($dvaluexp  as $dvalueexpkey => $dvalues){
				$array = [];
				$item = 'item-'.$dvalueexpkey;
				$gallery_ids = '';
				$media_ids = '';
				if(array_key_exists($dkey,$jet_rf_data['JECCTRF'])){
					if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'text' ||$jet_rf_data['JECCTRF'][$dkey]['type'] == 'textarea'
						|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'colorpicker' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'iconpicker'
						|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'radio' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'number'
						|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'wysiwyg' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'switcher'){									
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
							$darray=serialize($value);
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'date'){
						if(!empty($dvalues)){
							$var = trim($dvalues);
							$date = str_replace('/', '-', "$var");
							if($jet_rf_data['JECCTRF'][$dkey]['is_timestamp']){
								$date_of =  strtotime($date);
							}else{
								$date_of = $date;
							}
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $date_of;
							$darray=serialize($value);
						}
						else{
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = '';
							$darray=serialize($value);
						}
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'gallery' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
							$exploded_gallery_items = explode( ',', $dvalues );
                        	$galleryvalue=array();
							foreach ( $exploded_gallery_items as $gallery ) {
								$gallery = trim( $gallery );
								if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
									$get_gallery_id = $media_instance->media_handling( $gallery, $pID);	
									$media_id = $media_instance->media_handling( $gallery, $pID);		
									if ( $get_gallery_id != '' ) {
										if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
											$media_ids .= $media_id. ',';
										}
										elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'url'){
											global $wpdb;
											$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
											$dir = wp_upload_dir();
											$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
										}
                                        elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
                                            global $wpdb;
                                            $gallery_id1 ['id']= $get_gallery_id;    
                                            $get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
                                            $dir = wp_upload_dir();
                                            $gallery_id2['url']= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value;    
                                            $galleryvalue[] = array_merge($gallery_id1,$gallery_id2);
                                            $gallery_ids=$galleryvalue;
                                        }
										else{
											$gallery_ids .= $get_gallery_id.',';
										}
									}
								} else {
									$galleryLen         = strlen( $gallery );
									$checkgalleryid     = intval( $gallery );
									$verifiedGalleryLen = strlen( $checkgalleryid );
									if ( $galleryLen == $verifiedGalleryLen ) {
										if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
											$media_ids .= $gallery. ',';
										}
										else{
											$gallery_ids .= $gallery. ',';
										}
									}
								}
							}
							if(is_array($gallery_ids)){
                                $gallery_id  = $gallery_ids;
                            }
                            if (!is_array($gallery_ids)) {
                                $gallery_id = rtrim($gallery_ids,',');
                            }
							if(isset($media_ids)){
								$media_id = rtrim($media_ids,',');
							}
							if($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'url'){
								global $wpdb;
								$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
								$dir = wp_upload_dir();		
								if(!empty($get_media_fields[0]->meta_value)){
									$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
								}        
								else{
									$media_id='';
								}
							}
                            elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
                                global $wpdb;
                                $media_ids1['id']=$media_id;
                                $get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
                                $dir = wp_upload_dir();			
                                if(!empty($get_media_fields[0]->meta_value)){
                                    $media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
                                }        
                                else{
                                    $media_ids2['url']='';
                                }
                                $mediavalue= array_merge($media_ids1,$media_ids2);
                                $media_id=array($mediavalue);
                            }
							else{
								$media_id=$media_id;
							}
							if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $media_id;
								$darray=serialize($value);
							}
							else{
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $gallery_id;
								$darray=serialize($value);
							}
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'datetime-local'){
						$dt_var = trim($dvalues);
						$date_time_of =  strtotime($dt_var) ;
						$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $date_time_of;
						$darray=serialize($value);
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'time'){
						$var = trim($dvalues);
						$time = date('H:i', strtotime($var));
						$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $time;
						$darray=serialize($value);
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'checkbox'){
						if($jet_rf_data['JECCTRF'][$dkey]['is_array'] == 1){
							$dvalexp = explode(',' , $dvalues);
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalexp;
							$darray=serialize($value);
						}
						else{
							$options = $jet_rf_data['JECCTRF'][$dkey]['options'];
							$arr = [];
							$opt = [];
							$dvalexp = explode(',' , $dvalues);
							foreach($options as $option_key => $option_val){
								$arr[$option_val['key']] = 'false';
							}
							foreach($dvalexp as $dvalkey => $dvalueval){
								$keys = array_keys($arr);
								foreach($keys as $keys1){
									if($dvalueval == $keys1){
										$arr[$keys1] = 'true';
									}
								}
							}
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $arr;
							$darray=serialize($value);
						}
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'select'){
						$dselect = [];
						if($jet_rf_data['JECCTRF'][$dkey]['is_multiple'] == 0){
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
							$darray=serialize($value);	
						}
						else{
							$exp =explode(',',$dvalues);
							$dselect = $exp;
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dselect;
							$darray=serialize($value);
						}
					}
					elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'posts'){
						global $wpdb;
						if($jet_rf_data['JECCTRF'][$dkey]['is_multiple'] == 0){
							$jet_posts = trim($dvalues);
							if(is_string($jet_posts)){
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status='publish'";
								$name = $wpdb->get_results($query);
								if (!empty($name)) {
									$jet_posts_field_value=$name[0]->id;
								}
							}
							elseif (is_numeric($jet_posts)) {
								$jet_posts_field_value=$jet_posts;
							}
						}
						else{
							$jet_posts_field_value = [];
							$jet_posts_exp = explode(',',trim($dvalues));
							$jet_posts_value = array();
							foreach($jet_posts_exp as $jet_posts_value){
								$jet_posts_value = trim($jet_posts_value);
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status='publish' ORDER BY ID DESC";
								$multiple_id = $wpdb->get_results($query);
								$multiple_ids =isset($multiple_id[0])? $multiple_id[0] : '' ;
								if(!$multiple_id){
									$jet_posts_field_value[]=$jet_posts_value;
								}
								else{
									$jet_posts_field_value[]=trim($multiple_ids->id);
								}
							}
						}
						$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $jet_posts_field_value;
						$darray=serialize($value);
					}
					else{
						$dvalues = trim($dvalues);
						$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
						$darray=serialize($value);
					}	
				}
			}
		}
		$repfield =$jet_rf_data['JECCT'];
		$table_name.='wp_jet_cct_'.$type;
		foreach($repfield as $rep_fkey => $rep_fvalue){
			$key_name=$rep_fvalue['name'].',';
			$data_values.=$darray;
		}
		$key=rtrim($key_name,',');
		$get_result =  $wpdb->get_results("SELECT _ID FROM $table_name WHERE  cct_status = 'publish' order by _ID ASC ");			
		foreach($get_result as $vkey=>$get_slug){
			$post_id=$get_slug->_ID;
		}	
		if($mode == 'Insert'){
			$sql = $wpdb->prepare(
			"UPDATE $table_name SET $key = '$data_values' WHERE _ID = %d;",
			$post_id
			);
			$wpdb->query( $sql );
		}					
		if($mode == 'Update'){
			$data_values.=$darray;
			foreach($repfield as $rep_fkey => $rep_fvalue){
				$key_name=$rep_fvalue['name'];
				$sql = $wpdb->prepare(
				"UPDATE $table_name SET $key_name = '$data_values' WHERE _ID = %d;",
				$pID
				);
				$wpdb->query( $sql );
			}					
		}				
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
			$customFields["JECCT"][ $jet_value['name']]['value_format'] = isset($jet_value['value_format']) ? $jet_value['value_format'] : '';
			$jet_field[] = $jet_value['name'];
		}
		return $customFields;	
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
}
