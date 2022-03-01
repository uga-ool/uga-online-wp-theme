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

class ImageSchedule {
    private static $instance=null;
    public static $media_instance = null;
    public static $corefields_instance = null;

    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
            self::$media_instance = MediaHandling::getInstance();
            self::$corefields_instance = CoreFieldsImport::getInstance();

        }
        return self::$instance;
	  }
    
    public function __construct() {
		$this->plugin = Plugin::getInstance();	
    }

    public function image_schedule($schedule_array)
    {
      
        global $wpdb;
        $get_result = $wpdb->get_results("SELECT  post_id FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE status = 'pending' ORDER BY post_id ASC LIMIT 10", ARRAY_A);
      
        if(empty($get_result)){
          
            $schedule_argument = array($schedule_array);
            wp_clear_scheduled_hook('smackcsv_image_schedule_hook', $schedule_argument);
        }
        else{
            $records = array_column($get_result, 'post_id');
            foreach ($records as $title => $id) {
                $get_shortcode = $wpdb->get_var("SELECT image_shortcode FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id AND hash_key = '$schedule_array' AND status = 'pending' ");  
                $get_image_meta = $wpdb->get_var("SELECT image_meta FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id AND hash_key = '$schedule_array' AND status = 'pending' ");
             
                if($get_shortcode == 'featured_image'){
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' ");
                    $post_values['featured_image'] = $get_original_image;
                    $image_type = 'Featured';
    
                    $attach_id = self::$media_instance->media_handling( $get_original_image , $id ,$post_values,'','', $schedule_array);
                    if($attach_id){
                        
                        $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
                        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';

                        $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                        $get_add = $get_success_count + 1;
                        $this->update_success_count_table( $image_type ,$schedule_array,$get_add);
                    }
                    else{
                        
                        $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';

                        $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                        $get_add = $get_fail_count + 1;
                        $this->update_failure_count_table( $image_type ,$schedule_array,$get_add);

                        update_post_meta($id, '_thumbnail_id', '');
                    }
                }
                elseif($get_shortcode == 'inline'){
                   
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' ");
                    $post_values['inline'] = $get_original_image;
                    $image_type = 'inline';
                    $attach_id = self::$media_instance->media_handling( $get_original_image , $id ,$post_values,'','','');
                    $core_instance = CoreFieldsImport::getInstance();
                    $core_instance->image_handling($id);
                   
                    // if($attach_id){
                        
                    //     $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
                    //     $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
                       
                    //     $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$schedule_array'  AND image_type = '$image_type' "); 
                    //     $get_add = $get_success_count + 1;
                         
                    //     $this->update_success_count_table($image_type ,$schedule_array,$get_add);
                    // }
                    // else{
                        
                    //     $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                    //     $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';

                    //     $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type ' ");
                    //     $get_add = $get_fail_count + 1;
                    //     $this->update_failure_count_table($image_type ,$schedule_array,$get_add);
                    // }
                }
                elseif( strpos($get_shortcode, 'acf_image__') !== false) {
                    $image_type = 'acf';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_image__');
                }
                elseif( strpos($get_shortcode, 'acf_group_image__') !== false) {
                    $image_type = 'acf_group';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_group_image__');
                }
                elseif( strpos($get_shortcode, 'acf_repeater_image__') !== false) {
                    $image_type = 'acf_repeater';
                    $this->acf_image_update($id,$image_type ,$get_shortcode, $get_image_meta, 'acf_repeater_image__');
                }
                elseif( strpos($get_shortcode, 'acf_flexible_image__') !== false) {
                    $image_type = 'acf_flexible';
                    $this->acf_image_update($id,$image_type ,$get_shortcode, $get_image_meta, 'acf_flexible_image__');
                }
                elseif( strpos($get_shortcode, 'acf_group_repeater_image__') !== false) {
                    $image_type = 'acf_group_repeater';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_group_repeater_image__');
                }
                elseif( strpos($get_shortcode, 'acf_repeater_group_image__') !== false) {
                    $image_type = 'acf_repeater_group';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_repeater_group_image__');
                }
                elseif( strpos($get_shortcode, 'acf_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'acf_group_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_group_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'acf_repeater_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_repeater_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'acf_group_repeater_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_group_repeater_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'acf_repeater_group_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_repeater_group_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'acf_flexible_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_flexible_gallery_image__');
                }
                elseif( strpos($get_shortcode, 'pods_image__') !== false ){
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' ");
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $image_type = 'pods';
                    // $attach_id = self::$media_instance->media_handling( $get_original_image, $id, $get_image_fieldname[1]);
					$attach_id = self::$media_instance->media_handling( $get_original_image, $id, array());
					
                    if($attach_id){
                        update_post_meta($id, $get_image_fieldname[1], $attach_id);

                        if(!empty($get_image_meta)){
                            $image_meta = unserialize($get_image_meta);
                            self::$media_instance->acfimageMetaImports($attach_id, $image_meta, 'pods');
                        }

                        $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
                        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
                       
                        $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                        $get_add = $get_success_count + 1;
                        $this->update_success_count_table($image_type,$schedule_array,$get_add);
                    }
                    else{
                        $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';

                        $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                        $get_add = $get_fail_count + 1;
                        $this->update_failure_count_table($image_type,$schedule_array,$get_add);

                        update_post_meta($id, $get_image_fieldname[1], '');
                    }
                }

                elseif( strpos($get_shortcode, 'product_image__') !== false ){
                    $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE 'product_image__%' AND post_id = $id AND status = 'pending' ");
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $image_gallery = '';
                    $gallery_ids = [];
                    $image_type = 'product';
        
                    foreach($get_gallery_images as $gallery_key => $gallery_image){
                        $gallery_image_url = $gallery_image->original_image;
                   
                        $image_metas = [];
                        if(!empty($get_image_meta)){
                            $image_metas = unserialize($get_image_meta);
                            if(!empty($image_metas['product_file_name'][$gallery_key])){
                                $image_metas = $image_metas['product_file_name'][$gallery_key];
                            }
                        }
                        // $attach_id = self::$media_instance->media_handling( $gallery_image_url, $id, $get_image_fieldname[1]);
                        $attach_id = self::$media_instance->media_handling( $gallery_image_url, $id, array(), null, null, null, null, null, null, $image_metas);
                        if($attach_id){ 
                         
                            $image_gallery .= $attach_id . ',';
                            $gallery_ids[] = $attach_id;
                            $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
                         
                            $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                            $get_add = $get_success_count + 1;
                            $this->update_success_count_table($image_type,$schedule_array,$get_add);
                        }
                        else{
                            $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                            $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
                           
                            $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                            $get_add = $get_fail_count + 1;
                            $this->update_failure_count_table($image_type,$schedule_array,$get_add);

                            update_post_meta($id, $get_image_fieldname[1], '');
                        }
                    } 
                       

                    if(!empty($image_gallery)){
                        $productImageGallery = substr($image_gallery, 0, -1);
                        update_post_meta($id, '_'.$get_image_fieldname[1], $productImageGallery);
                      
                        if(!empty($get_image_meta)){
                        
                            $image_meta = unserialize($get_image_meta);
                            //self::$media_instance->acfgalleryMetaImports($gallery_ids,$image_meta, 'product');	
                            self::$media_instance->acfImageMetaImports($gallery_ids,$image_meta, 'product');	
                        }
        
                        $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
                       
                    }
                }

                elseif( strpos($get_shortcode, 'types_image__') !== false ){
                    $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE 'types_image__%' AND post_id = $id AND status = 'pending' ",ARRAY_A);
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $gallery_ids = [];
                    $image_type = 'types';
                    foreach($get_original_image as $gallery_image){
                        // $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id, $get_image_fieldname[1]);
                        $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id);

                        if($attach_id){
                            $gallery_ids[] = $attach_id;
                        }
                        else{
                            $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                            $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';

                            $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                            $get_add = $get_fail_count + 1;
                            $this->update_failure_count_table($image_type,$schedule_array,$get_add);
                        }
                    }

                    if($gallery_ids){
                        // delete dummy imagemeta
                        delete_post_meta($id, $get_image_fieldname[1]);
                        foreach($gallery_ids as $gallery_id){
                            $get_guid = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $gallery_id");
                            add_post_meta($id, $get_image_fieldname[1], $get_guid);
                        }
                    }

                    if(!empty($get_image_meta)){
                        $image_meta = unserialize($get_image_meta);
                        self::$media_instance->acfimageMetaImports($gallery_ids, $image_meta, 'types');
                    }

                    if($gallery_ids){
                        $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
                        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
                       
                        $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$schedule_array' AND image_type ='$image_type' ");
                        $get_add = $get_success_count + 1;
                        $this->update_success_count_table($image_type,$schedule_array,$get_add);
                    }
                }            

                else{
                    $core_instance = CoreFieldsImport::getInstance();
                    $post_id = $core_instance->image_handling($id);
                }
            }   
        }
    }

    public function delete_image_schedule()
    {
        global $wpdb;
        // $wpdb->get_results("DELETE FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager");
        $wpdb->get_results("DELETE FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE status = 'completed' ");

        $check_for_pending_images = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE status = 'pending' ");
        if(empty($check_for_pending_images)){
            $check_for_loading_images = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE guid LIKE '%loading-image%' ");
            if(!empty($check_for_loading_images)){
                $delete_post_id = $check_for_loading_images[0]->ID;
                $wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE ID = $delete_post_id ");
                $wpdb->get_results("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = $delete_post_id ");
            }
        }
    }

    public function update_status_shortcode_table($id, $get_shortcode, $status){
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'ultimate_csv_importer_shortcode_manager' , 
            array( 
                'status' => $status,
            ) , 
            array( 'post_id' => $id ,
                'image_shortcode' => $get_shortcode
            ) 
        );
    }

    public function update_success_count_table($image_type,$schedule_array,$get_add){
        global $wpdb; 
        $wpdb->update( $wpdb->prefix . 'ultimate_csv_importer_media_report' , 
            array( 
                'success_count' => $get_add,
            ), 
            array( 'hash_key' => $schedule_array ,
                    'image_type' => $image_type
                        
            ) 
        );
    }

    public function update_failure_count_table($image_type,$schedule_array,$get_add){
        global $wpdb; 
        $wpdb->update( $wpdb->prefix . 'ultimate_csv_importer_media_report' , 
            array( 
                'fail_count' => $get_add,
            ), 
            array( 'hash_key' => $schedule_array ,
                    'image_type' => $image_type             
            ) 
        );
    }

    public function acf_image_update($id, $image_type,$get_shortcode, $get_image_meta, $image_shortcode){
        global $wpdb;
        
        $get_image_fieldname = explode('__', $get_shortcode); 
        if($image_shortcode == 'acf_group_repeater_image__' || $image_shortcode == 'acf_repeater_group_image__' ){
            $shortcode = $get_image_fieldname[1];
            $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id ");
        }
        else{
            $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE '$image_shortcode%' AND post_id = $id ");
        }
        $acf_key = $wpdb->get_var("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id  ");
        // $attach_id = self::$media_instance->media_handling( $get_original_image, $id, $get_image_fieldname[1]);
        $attach_id = self::$media_instance->media_handling( $get_original_image, $id);

        if($attach_id){
            update_post_meta($id, $get_image_fieldname[1], $attach_id);
            if(!empty($get_image_meta)){
                $image_meta = unserialize($get_image_meta);
                self::$media_instance->acfimageMetaImports($attach_id, $image_meta, 'acf');
            }
            $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
            $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
            $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$acf_key' AND image_type ='$image_type' ");
            
            $get_add= $get_success_count + 1;
            $this->update_success_count_table($image_type,$acf_key,$get_add);

        }
        else{
            $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
            $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
            $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$acf_key' AND image_type ='$image_type' ");
            
            $get_add= $get_fail_count + 1;
            $this->update_failure_count_table($image_type,$acf_key,$get_add);

            update_post_meta($id, $get_image_fieldname[1], '');
        }
    }

   
    public function acf_gallery_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode){
        global $wpdb;
        $get_image_fieldname = explode('__', $get_shortcode);
        if($image_shortcode == 'acf_repeater_gallery_image__' || $image_shortcode == 'acf_group_repeater_gallery_image__' || $image_shortcode == 'acf_repeater_group_gallery_image__' ){
            $shortcode = $get_image_fieldname[1];
            $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id ", ARRAY_A);  
        }
        else{
            $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE '$image_shortcode%' AND post_id = $id ", ARRAY_A);
        }
       
    //    $get_gallery_shortcode = $wpdb->get_results("SELECT image_shortcode FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode LIKE '$image_shortcode%' AND post_id = $id ", ARRAY_A);
    //    foreach($get_gallery_shortcode as $gallery_code){

    //    }

        $acf_key = $wpdb->get_var("SELECT hash_key FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = $id  ");
        $image_type = chop($image_shortcode,'_image__');
        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
        $gallery_ids = [];
        foreach($get_gallery_images as $gallery_image){
            // $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id, $get_image_fieldname[1]);
            $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id);

            if($attach_id){ 
                $gallery_ids[] = $attach_id;
            }
            else{
                $this->update_status_shortcode_table($id, $get_shortcode, 'failed');
                $get_fail_count = $wpdb->get_var("SELECT fail_count FROM $image_media_table WHERE hash_key = '$acf_key' AND image_type ='$image_type' ");
                $get_add= $get_fail_count + 1;
                $this->update_failure_count_table($image_type,$acf_key,$get_add);
            }
        } 
        if(!empty($gallery_ids)){
            update_post_meta($id, $get_image_fieldname[1], $gallery_ids);
            if(!empty($get_image_meta)){
                $image_meta = unserialize($get_image_meta);
                self::$media_instance->acfgalleryMetaImports($gallery_ids,$image_meta, 'acf');	
            }
            $this->update_status_shortcode_table($id, $get_shortcode, 'completed');
            foreach($gallery_ids as $gallery_id){
                $get_success_count = $wpdb->get_var("SELECT success_count FROM $image_media_table WHERE hash_key = '$acf_key' AND image_type ='$image_type' ");
                $get_add = $get_success_count + 1;
                $this->update_success_count_table($image_type,$acf_key,$get_add);
            }
        }    
    }
}