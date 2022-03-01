<?php
/**
 * HTML scanner feature for template suggestions
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.8
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Cookie_Law_Info_Html_Scanner
{
	/**
	 * Initialization
	 *
	 * @since 2.1.8
	 **/
	public function __construct()
	{
		//=====frontend htmlscanner hook======
		//add_action('wp_enqueue_scripts',array($this,'htmlscanner_js'));
		//add_action('wp_ajax_cli_htmlscanner',array($this,'htmlscanner_ajax'));
		//add_action('wp_ajax_nopriv_cli_htmlscanner',array($this,'htmlscanner_ajax'));
	}

	/*
	*	Ajax hook for HTMLscanner
	* 	@since 2.1.8
	*/
	public function htmlscanner_ajax()
	{
		global $wpdb;
		if (!current_user_can('manage_options')) 
		{
			wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
		}
		check_ajax_referer('cli_htmlscanner','security');

		$data = Wt_Cookie_Law_Info_Security_Helper::sanitize_item($_POST['btn_data']);
		$serialized=array_map('serialize',$data);
		$total_data=array_count_values($serialized);
		arsort($total_data);
		
		foreach($total_data as $key=>$value) 
		{
			$btn_data=unserialize($key);
			$btn_data['font-family']=str_replace('"',"'",$btn_data['font-family']);
		}

		exit();
	}

	private function processColors()
	{
		
	}



	/*
	*	Adding JS for HTMLscanner
	* 	@since 2.1.8
	*/
	public function htmlscanner_js()
	{
		wp_enqueue_script('cli_htmlscanner',plugin_dir_url( dirname(__FILE__) ) . 'assets/js/htmlscanner.js',array('jquery'),$this->version,true);
		$cli_htmlscanner=array(
			'nonces' => wp_create_nonce('cli_htmlscanner'),
			'ajax_url' => admin_url('admin-ajax.php'),
		);
		wp_localize_script('cli_htmlscanner','cli_htmlscanner',$cli_htmlscanner);
	}
}
new Cookie_Law_Info_Html_Scanner();