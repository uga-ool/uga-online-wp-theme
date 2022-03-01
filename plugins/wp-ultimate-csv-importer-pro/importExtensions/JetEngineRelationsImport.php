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

class JetEngineRELImport {

	private static $instance = null;
	
    public static function getInstance() {		
		if (JetEngineRELImport::$instance == null) {
			JetEngineRELImport::$instance = new JetEngineRELImport;
		}
		return JetEngineRELImport::$instance;
	}
	function set_jet_engine_rel_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_rel_import_function($post_values,$type, $post_id, $mode, $hash_key);
	}
	
	public function jet_engine_rel_import_function($data_array, $type, $pID ,$mode, $hash_key) 
	{
		// $media_instance = MediaHandling::getInstance();
		// $jet_data = $this->JetEngineFields($type);
	
       
        $meta_key_exp = explode('|',$data_array['jet_relation_metakey']);
        $meta_values_exp = explode('|',$data_array['jet_related_post']);
        $count = count($meta_key_exp);
        foreach($meta_key_exp as $metkey){
            if($mode != 'Insert'){
                global $wpdb;
               
                $query = "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key ='{$metkey}' and post_id = '{$pID}' ";
                $get_metavalue = $wpdb->get_results($query);
                foreach($get_metavalue as $metval){
                    $metaid = $metval->meta_value;
                    delete_post_meta($metaid,$metkey);
                }
                delete_post_meta($pID,$metkey);
            }
        }
        for($i=0 ;$i<$count ; $i++){
            $meta_keys = $meta_key_exp[$i];
            $meta_values = $meta_values_exp[$i];
            $metaval = explode(',',$meta_values);
            $val_count = count($metaval);
          
            foreach($metaval as $metakeyval => $metavalues){
               $rmeta_keys = rtrim($meta_keys,' ');
              
                if(is_numeric($metavalues)){
                    if($count > 1){
                        add_post_meta($pID,$rmeta_keys,$metavalues);
                        add_post_meta($metavalues,$meta_keys,$pID);
                    }
                    else{
                        update_post_meta($pID,$rmeta_keys,$metavalues);
                        update_post_meta($metavalues,$meta_keys,$pID);
                    }
                    

                }
                else{
                    global $wpdb;
                    $query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$metavalues}' and post_status = 'publish' ORDER BY ID DESC";
                    $get_id = $wpdb->get_results($query);
                    
                    $getids =$get_id[0];
                    $meta_valueid = $getids->id;
                
                    add_post_meta($pID,$rmeta_keys,$meta_valueid);
                    add_post_meta($meta_valueid,$meta_keys,$pID);
                    
            
                }
            }
        }
	}



}