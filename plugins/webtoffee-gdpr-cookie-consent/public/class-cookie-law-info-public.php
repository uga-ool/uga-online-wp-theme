<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.3
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/public
 * @author     WebToffee <info@webtoffee.com>
 */
class Cookie_Law_Info_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	public $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $version;

	public $plugin_obj;

	public static $cookie_list_arr=null;

	/*
	 * module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 */
	private $modules=array(
		'script-blocker',		
		'shortcode',
		'visitor-report', //vistor report
	);

	public static $existing_modules=array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.1.3
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_obj) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_obj = $plugin_obj;
		$cookie_domain = self::get_cookie_domain();
		if(isset($_GET['cli_bypass']) && get_option('CLI_BYPASS')==1) //just bypassing the blocker for scanning cookies
	    {
	        setcookie("viewed_cookie_policy",'yes',time()+31556926,'/',$cookie_domain);
	        foreach($this->modules as $k=>$module)
	        {
	        	if($module=='script-blocker')
	        	{
	        		unset($this->modules[$k]); //disabling script blocker
	        	}
	        }
		}
		add_action('wt_cli_initialize_plugin',array( $this, 'initialize_plugin_settings' ));
	}
	/**
	* Load cookie bar contents
	*
	* @since  2.3.1
	*/
	public function init() {
		$show_cookie_bar_head = apply_filters('wt_cli_show_cookiebar_on_head',false);
		if( $show_cookie_bar_head === false ) {
			add_action( 'wp_footer',array($this,'cookielawinfo_inject_cli_script'));
			add_action( 'wp_footer',array($this,'add_plugin_settings_json'));

		} else {
			add_action( 'wp_head',array($this,'cookielawinfo_inject_cli_script'));
			add_action( 'wp_head',array($this,'add_plugin_settings_json'));
		}
	}
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.1.3
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cookie_Law_Info_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cookie_Law_Info_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$the_options = Cookie_Law_Info::get_settings();
		if ( $the_options['is_on'] == true ) 
		{
			
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cookie-law-info-public.css', array(),$this->version, 'all' );
			wp_enqueue_style( $this->plugin_name.'-gdpr', plugin_dir_url( __FILE__ ) . 'css/cookie-law-info-gdpr.css', array(),$this->version, 'all' );
			//this style will include only when shortcode is called
			wp_register_style( $this->plugin_name.'-table', plugin_dir_url(__FILE__) . 'css/cookie-law-info-table.css', array(),$this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.1.3
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cookie_Law_Info_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cookie_Law_Info_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$the_options = Cookie_Law_Info::get_settings();
		$eu_countries =array();
		$eu_countries = apply_filters('wt_gdpr_eu_countrylist',array('GB'));
		$geoIP = 'disabled';
		$ccpa_enabled = ( isset( $the_options['ccpa_enabled'] ) ? $the_options['ccpa_enabled'] : false );
		$ccpa_region_based = ( isset( $the_options['ccpa_region_based'] ) ? $the_options['ccpa_region_based'] : false );
		$ccpa_enable_bar = ( isset( $the_options['ccpa_enable_bar'] ) ? $the_options['ccpa_enable_bar'] : false );
		$ccpa_type = ( isset( $the_options['consent_type'] ) ? $the_options['consent_type'] : 'gdpr' );
		if($the_options['is_eu_on'])
		{
			$geoIP = 'enabled';
		}
		if ( $the_options['is_on'] == true ) 
		{	
			$non_necessary_cookie_ids = Cookie_Law_Info::get_non_necessary_cookie_ids();
			$cookies_by_cateogry = Cookie_Law_Info::get_cookies_by_category();
			$ajax_nonce = wp_create_nonce( "cli-blocker" );
			$consent_version = Cookie_Law_Info::wt_cli_get_consent_version();
			$strictly_enabled = Cookie_Law_Info::get_strictly_necessory_categories();

			$trigger_dom_reload = apply_filters('wt_cli_script_blocker_trigger_dom_refresh',false);
			$custom_geo_service = apply_filters('wt_cli_use_custom_geolocation_api',false);
			$custom_geolocation_api = apply_filters('wt_cli_custom_geolocation_api','https://geoip.cookieyes.com/geoip/checker/result.php');
			$cli_cookie_datas = array(
			'nn_cookie_ids' => !empty($non_necessary_cookie_ids) ? $non_necessary_cookie_ids : array(),
			'non_necessary_cookies' => !empty($cookies_by_cateogry) ? $cookies_by_cateogry : array(),
			'cookielist' => $this->get_category_options_front_end(),
			'ajax_url'=> admin_url( 'admin-ajax.php' ),
			'current_lang'=> Cookie_Law_Info_Languages::get_instance()->get_current_language_code(),
			'security'=>$ajax_nonce,
			'eu_countries' => $eu_countries,
			'geoIP' => $geoIP,
			'use_custom_geolocation_api' => $custom_geo_service,
			'custom_geolocation_api' => $custom_geolocation_api,
			'consentVersion' => $consent_version,
			'strictlyEnabled' => $strictly_enabled,
			'cookieDomain'			=> self::get_cookie_domain(),
			'privacy_length' => apply_filters('wt_cli_privacy_overview_length',250),
			'ccpaEnabled' => $ccpa_enabled,
			'ccpaRegionBased' => $ccpa_region_based,
			'ccpaBarEnabled' => $ccpa_enable_bar,
			'ccpaType' => $ccpa_type,
			'triggerDomRefresh' => $trigger_dom_reload,
			'secure_cookies'	=>	apply_filters('wt_cli_set_secure_cookies', false ),
			);
			$cli_cookie_datas = apply_filters( 'wt_cli_front_end_options', $cli_cookie_datas );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cookie-law-info-public.js', array( 'jquery' ),$this->version );
			wp_localize_script( $this->plugin_name, 'Cli_Data', $cli_cookie_datas );
			wp_localize_script( $this->plugin_name, 'log_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		

	}
	/**
	 Ajax hook to give json settings	 
	 */
	public function cli_get_settings_json()
	{
		echo Cookie_Law_Info::get_json_settings();
		exit();
	}
	/**
	 Registers modules: public+admin	 
	 */
	public function common_modules()
	{	
		
		$cli_common_modules = get_option('cli_common_modules');
		if($cli_common_modules===false)
		{
			$cli_common_modules=array();
		}
		foreach ($this->modules as $module) //loop through module list and include its file
		{
			$is_active=1;
			if(isset($cli_common_modules[$module]))
			{
				$is_active=$cli_common_modules[$module]; //checking module status
			}else
			{
				$cli_common_modules[$module]=1; //default status is active
			}
			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file) && $is_active==1)
			{
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			}else
			{
				$cli_common_modules[$module]=0;	
			}
		}
		$out=array();
		foreach($cli_common_modules as $k=>$m)
		{
			if(in_array($k,$this->modules))
			{
				$out[$k]=$m;
			}
		}
		// update_option('cli_common_modules',$out);
	}
	public static function module_exists($module)
	{
		return in_array($module,self::$existing_modules);
	}
	/** Removes leading # characters from a string */
	public static function cookielawinfo_remove_hash( $str ) 
	{
	  if( $str[0] == "#" ) 
	  {
	    $str = substr( $str, 1, strlen($str) );
	  }
	  else {
	    return $str;
	  }
	  return self::cookielawinfo_remove_hash( $str );
	}

	/**
	 Outputs the cookie control script in the footer
	 N.B. This script MUST be output in the footer.
	 
	 This function should be attached to the wp_footer action hook.
	*/
	public function cookielawinfo_inject_cli_script() 
	{
		$the_options = Cookie_Law_Info::get_settings();
	  	$geo_loc_enabled=0; //this is for debugging purpose
		$cookie_domain = self::get_cookie_domain();
		$show_cookie_bar = true;
		if(apply_filters('wt_cli_hide_bar_on_page_editor', true) && $this->is_page_editor_active()) {
			$show_cookie_bar = false;
		}
		if ( $the_options['is_on'] == true && $show_cookie_bar ) 
	  	{             
	        // Output the HTML in the footer:
	        $message = nl2br($the_options['notify_message']);
	        $message = __($message, 'webtoffee-gdpr-cookie-consent');

	        //removing close button shortcode from main text and saving close button html to a variable
	        $cli_close_btn_html='';
	        if(strpos($message,'[cookie_close]')!==false)
	        {
	        	$message=str_replace('[cookie_close]','',$message);
	        	$cli_close_btn_html=do_shortcode('[cookie_close]');
	        } 

	    	$str = do_shortcode(stripslashes($message));
	                    
	        $head= trim(stripslashes($the_options['bar_heading_text']));

	        //setting custom style
	        $cli_bar_style='';
	        if(Cookie_Law_Info_Admin::module_exists('cli-themes') && isset($the_options['bar_style']))
	        {
	           $cli_bar_style=stripslashes(Cookie_Law_Info_Cli_Themes::create_style_attr($the_options['bar_style']));
	        } 

	        //setting custom hd style
			$cli_bar_hd_style='';
			$pop_out='';
	        if(Cookie_Law_Info_Admin::module_exists('cli-themes') && isset($the_options['bar_hd_style']))
	        {
	           $cli_bar_hd_style=Cookie_Law_Info_Cli_Themes::create_style_attr($the_options['bar_hd_style']);
			} 
			if($the_options['accept_all']!= true && $the_options['cookie_setting_popup']!= true )
			{	
				ob_start();
				do_action('wt_cli_settings_popup');
				$pop_out = ob_get_contents();
				ob_end_clean();
			}     
	              
		    $notify_html = '<div id="' .$this->cookielawinfo_remove_hash( $the_options["notify_div_id"] ) . '" role="dialog" aria-live="polite" aria-label="cookieconsent" aria-describedby="wt-cli-cookie-banner" data-cli-geo-loc="'.$geo_loc_enabled.'" style="'.$cli_bar_style.'" class="wt-cli-cookie-bar"><div class="cli-wrapper">'.
		    $cli_close_btn_html.
		    ($head!="" ? '<h5 role="heading" aria-level="5" tabindex="0" id="wt-cli-cookie-banner-title" style="'.$cli_bar_hd_style.'">'.$head.'</h5>' : '')
		    .'<span id="wt-cli-cookie-banner">' . $str . '</span>'.$pop_out.'</div></div>';
		    
		    //if($the_options['showagain_tab'] === true) 
		    //{
		    	$show_again=__($the_options["showagain_text"],'webtoffee-gdpr-cookie-consent');
				$notify_html .= '<div tabindex="0" id="' . $this->cookielawinfo_remove_hash( $the_options["showagain_div_id"] ) . '" style="display:none;"><span id="cookie_hdr_showagain">'.$show_again.'</span></div>';
		    //}
		    global $wp_query;
		    $current_obj = get_queried_object();
		    $post_slug ='';
		    if(is_object($current_obj))
		    {
			    if(is_category() || is_tag())
			    {
			    	$post_slug =isset($current_obj->slug) ? $current_obj->slug : '';
			    }
			    elseif(is_archive())
			    {
			    	$post_slug =isset($current_obj->rewrite) && isset($current_obj->rewrite['slug']) ? $current_obj->rewrite['slug'] : '';
			    }
			    else
			    {
			    	if(isset($current_obj->post_name))
			    	{
			    		$post_slug =$current_obj->post_name;
			    	}			    	
			    }
			}
			$notify_html = apply_filters('cli_show_cookie_bar_only_on_selected_pages',$notify_html,$post_slug);
		    if($notify_html!="")
		    {
				require_once plugin_dir_path( __FILE__ ).'views/cookie-law-info_bar.php';
		    }
	  	}
	}

	
	/* Print scripts or data in the head tag on the front end. */
	public function include_user_accepted_cookielawinfo()
	{
		$this->wt_cli_print_scripts( true );
	    
	}
	public function include_user_accepted_cookielawinfo_in_body()
	{
		$this->wt_cli_print_scripts();
	}
	public function wt_cli_print_scripts( $head = false ) {

		$the_options 				= Cookie_Law_Info::get_settings();

		if ($the_options['is_on'] == true && !is_admin()) {
			$scripts = $this->get_cookie_scripts();
			foreach( $scripts as $slug => $script ) {
				if( true === $head ) {
					echo isset( $script['head_scripts'] ) ? $script['head_scripts'] : '' ;
				} else {
					echo isset( $script['body_scripts'] ) ? $script['body_scripts'] : '' ;
				}
			}
		}
	}
	public function get_cookie_scripts() {

		$cookie_scripts = array();
		$cookie_categories		=    Cookie_Law_Info_Cookies::get_instance()->get_cookies();

		if( !empty( $cookie_categories ) ) {

			foreach( $cookie_categories as $slug => $data ) {

				$scripts       = array();
				$head_scripts  = $data['head_scripts'];
				$body_scripts  = $data['body_scripts'];
				$load_on_start = $data['loadonstart'];
				$ccpa_optout   = $data['ccpa_optout'];
				$cookies       = $data['cookies'];
				

				if( !empty( $cookies ) ) {
					foreach ( $cookies as $cookie_post ) {     

						$head_script_meta = $cookie_post['head_scripts'];
						$body_script_meta = $cookie_post['body_scripts'];
						
						if( !empty( $head_script_meta ) ) {	
							$head_scripts .= $head_script_meta;
						}

						if( !empty( $body_script_meta ) ) {	
							$body_scripts .= $body_script_meta;
						}
					}
				}
				// Now process the scripts
				$head_scripts = $this->pre_process_scripts( $slug, $head_scripts, true, $ccpa_optout, $load_on_start );
				$body_scripts = $this->pre_process_scripts( $slug, $body_scripts, false, $ccpa_optout, $load_on_start );

				$cookie_scripts[ $slug ]['head_scripts'] = $head_scripts;
				$cookie_scripts[ $slug ]['body_scripts'] = $body_scripts;
			}

		}
		return $cookie_scripts;
	}
	public function pre_process_scripts( $slug, $script, $head, $ccpa_optout, $load_on_start ) {
		
		$position	=	'body';

		if( $head === true ){
			$position	=	'head';
		}

		$is_script_block = 'true'; 
		$is_cca_applicable = 'false';         

		if( true === $load_on_start ) {
			$is_script_block = 'false';  
		}
		if( true === $ccpa_optout ) {
			$is_cca_applicable = 'true';  
		}

		$replace = 'data-cli-class="cli-blocker-script"  data-cli-category="'.$slug.'" data-cli-script-type="'.$slug.'" data-cli-block="'.$is_script_block.'" data-cli-block-if-ccpa-optout="'.$is_cca_applicable.'" data-cli-element-position="'.$position.'"';
		$scripts = $this->replace_script_attribute_type( $script, $replace );

		return $scripts;
	}
	public function replace_script_attribute_type($script, $replace ) {

		$textarr = wp_html_split($script);
		$replace_script = $script;
		$script_array = ( isset( $textarr ) && is_array( $textarr ) ) ? $textarr : array();
		$changed = false;
		$script_type = "text/plain";
		foreach ($script_array as $i => $html) { 
			if( preg_match( '/<script[^\>]*?\>/m', $script_array[$i] ) ) {
				$changed = true;
				if (preg_match('/<script.*(type=(?:"|\')(.*?)(?:"|\')).*?>/', $script_array[$i]) && preg_match('/<script.*(type=(?:"|\')text\/javascript(.*?)(?:"|\')).*?>/', $script_array[$i] )) {
					preg_match('/<script.*(type=(?:"|\')text\/javascript(.*?)(?:"|\')).*?>/', $script_array[$i], $output_array);
					$re = preg_quote($output_array[1],'/');
					if(!empty($output_array)) {   
                        
                    	$script_array[$i] = preg_replace('/' .$re .'/', 'type="'.$script_type.'"'.' '.$replace, $script_array[$i],1);

                    }
				} else {
                    
                    $script_array[$i] = str_replace('<script', '<script type="'.$script_type.'"'.' '.$replace, $script_array[$i]);   
                
                }
				
			}
			
		}
		if ( $changed === true ) {
            $replace_script = implode($script_array);
        }
		return $replace_script;
	}
	public function other_plugin_clear_cache()
	{
		$cli_flush_cache=Cookie_Law_Info::is_cache_plugin_installed() ? 1 : 2;
		//$cli_flush_cache=2;
		?>
		<script type="text/javascript">
			var cli_flush_cache=<?php echo $cli_flush_cache; ?>;
		</script>
		<?php
	}
	/**
	* Returns the cookie domain
	*
	* @since  2.3.1
	* @access public
	* @return string
	*/
	public static function get_cookie_domain(){
		$cookie_domain = ( defined( 'COOKIE_DOMAIN' ) ? (string) COOKIE_DOMAIN : '' );
		return apply_filters('wt_cli_cookie_domain',$cookie_domain);
	}
	public function get_category_options_front_end(){
		$category_options = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
		if( !empty( $category_options ) ) {
			foreach ($category_options as $key => $category_data ) {
				if( !empty( $category_data ) ) {
					unset( $category_data['description'] );
					unset( $category_data['head_scripts'] );
					unset( $category_data['body_scripts'] );
					unset( $category_data['cookies'] );
					$category_options[ $key ] = $category_data;
				}
			}
		}
		return $category_options;
	}
	/**
	 * Plugin json settings || Needs to be added using wp_localize_script() later
	 *
	 * @return void
	 */
	public static function add_plugin_settings_json() {
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
			cli_cookiebar_settings='<?php echo Cookie_Law_Info::get_json_settings(); ?>';
		/* ]]> */
		</script>
		<?php
	}
	public function initialize_plugin_settings(){

	}
	public function init_public_modules(){
		$admin_modules = array();
		foreach ($this->modules as $module) {
			$admin_modules[ $module ] = 1;
		}
		update_option('cli_common_modules',$admin_modules);
	}

	/**
	* Check whether any page editor is active or not
	*
	* @since  2.3.6
	* @return bool
	*/
	public function is_page_editor_active() {

		if( isset($_GET['et_fb']) 
			|| (defined( 'ET_FB_ENABLED' ) && ET_FB_ENABLED)
			|| isset($_GET['elementor-preview']) 
			|| isset($_POST['cs_preview_state'])
		) {
			return true;
		}

		return false;
	}
}
