<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Cookie_Law_Info_Policy_Generator_Ajax extends Cookie_Law_Info_Cli_Policy_Generator
{
	
	public function __construct()
	{		
		add_action('wp_ajax_cli_policy_generator',array($this,'ajax_policy_generator'));
	}

	/*
	*
	* Main Ajax hook for processing requests
	*/
	public function ajax_policy_generator()
	{	
		check_ajax_referer('cli_policy_generator','security');
		if (!current_user_can('manage_options')) 
		{
		    wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
		}
		$out=array(
			'response'=>false,
			'message'=>__('Unable to handle your request.','webtoffee-gdpr-cookie-consent'),
		);
		$non_json_response=array();
		if(isset($_POST['cli_policy_generator_action']))
		{
			$allowed_actions=array('autosave_content_data','save_contentdata','get_policy_pageid');	
			$cli_policy_generator_action = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( isset( $_POST['cli_policy_generator_action'] ) && in_array( $_POST['cli_policy_generator_action'], $allowed_actions ) ? $_POST['cli_policy_generator_action'] : '' );		
			if(in_array($cli_policy_generator_action,$allowed_actions) && method_exists($this,$cli_policy_generator_action))
			{
				$out=$this->{$cli_policy_generator_action}();
			}
		}
		if(in_array($cli_policy_generator_action,$non_json_response))
		{
			echo is_array($out) ? $out['message'] : $out;
		}else
		{
			echo json_encode($out);
		}		
		exit();
	}

	/*
	*	@since 2.1.9
	*	Get current policy page ID (Ajax-main)
	*	This is used to update the hidden field for policy page id. (In some case user press back button)
	*/
	public function get_policy_pageid()
	{
		$page_id=Cookie_Law_Info_Cli_Policy_Generator::get_cookie_policy_pageid();
		$policy_page_status=get_post_status($page_id);
		if($policy_page_status && $policy_page_status!='trash')
		{

		}else
		{
			$page_id=0;
		}
		return array(
			'response'=>true,
			'page_id'=>$page_id,
		);
	}

	/*
	*	@since 2.1.9
	*	Save current data (Ajax-main)
	*/
	public function save_contentdata()
	{
		$out=array(
			'response'=>true,
			'er'=>''
		);
		$content_data=isset($_POST['content_data']) ? $_POST['content_data'] : array();
		$page_id = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( ( isset( $_POST['page_id'] ) ? $_POST['page_id'] : 0 ) ,'int');
		$id = wp_insert_post(
			array(
				'ID'=>$page_id, //if ID is zero it will create new page otherwise update
				'post_title'=>'Cookie Policy',
				'post_type'=>'page',
				'post_content'=>Cookie_Law_Info_Cli_Policy_Generator::generate_page_content($content_data,0),
				'post_status' => 'draft', //default is draft
			)
		);
		if(is_wp_error($id))
		{
			$out=array(
				'response'=>false,
				'er'=>__('Error','webtoffee-gdpr-cookie-consent'),
				//'er'=>$id->get_error_message(),
			);
		}else
		{
			Cookie_Law_Info_Cli_Policy_Generator::set_cookie_policy_pageid($id);
			$out['url']=get_edit_post_link($id);
		}
		return $out;
	}

	

	/*
	*	@since 2.1.9
	*	Autosave Current content to session (Ajax-main)
	*/
	public function autosave_content_data()
	{
		global $wpdb;
		$scan_table=$wpdb->prefix.$this->main_tb;
		$out=array(
			'response'=>true,
			'er'=>''
		);
		$content_data=isset($_POST['content_data']) ? $_POST['content_data'] : array();
		$page_id = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( ( isset( $_POST['page_id'] ) ? $_POST['page_id'] : 0 ) ,'int');
		if(count($content_data)>0)
		{
			if(!session_id())
			{
			   session_start();
			}
			$_SESSION['cli_pg_content_data'.$page_id]=$content_data;
		}else
		{
			$out=array(
				'response'=>false,
				'er'=>__('Error','webtoffee-gdpr-cookie-consent')
			);
		}
		return $out;
	}

}
new Cookie_Law_Info_Policy_Generator_Ajax();