<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.3
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/admin
 * @author     WebToffee <info@webtoffee.com>
 */
class Cookie_Law_Info_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $plugin_obj;

	/*
	 * admin module list, Module folder and main file must be same as that of module name
	 * Please check the `admin_modules` method for more details
	 */
	private $modules=array(
		'cookies',
		'csv-import', //CSV import
		'csv-export', //CSV export
		'cookie-scaner',
		'ccpa',
		'settings-popup',
		'cli-themes',
		'cli-policy-generator',
	);

	public static $existing_modules=array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.1.3
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version,$plugin_obj ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_obj = $plugin_obj;
		add_action('admin_init',array( $this, 'load_plugin' ));
		register_activation_hook(CLI_PLUGIN_FILENAME,array($this,'activator'));
		/* @since 2.3.4 */
		add_action('wt_cli_initialize_plugin',array( $this, 'initialize_plugin_settings' ));
	}
	/**
	 * Initialize all the data to the plugin.
	 *
	 * @return void
	 */
	public function initialize_plugin_settings(){
		$this->set_default_settings();
		$this->init_admin_modules();
	}
	/**
	 * Load default plugin settings.
	 *
	 * @return void
	 */
	public function set_default_settings() {
		$options = get_option(CLI_SETTINGS_FIELD); 
		if( $options === false ) {
			$default	=	Cookie_Law_Info::get_settings();
			update_option(CLI_SETTINGS_FIELD,$default);
		} 
	}
	/**
	 * Called when activation hook has fired.
	 *
	 * @return void
	 */
	public function activator() {

		if( Cookie_Law_Info::maybe_first_time_install() === true ) {
			add_option( 'wt_cli_first_time_activated_plugin', 'true' );
		}
	}
	/**
	 * Register the stylesheets for the admin area.
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
		if(isset($_GET['post_type']) && $_GET['post_type']==CLI_POST_TYPE)
		{
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) ."css/cookie-law-info-admin.css", array(),$this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
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
		if(isset($_GET['post_type']) && $_GET['post_type']==CLI_POST_TYPE)
		{
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cookie-law-info-admin.js', array( 'jquery' ,'wp-color-picker'),$this->version, false );
		}

	}

	public function redirect_to_settings_page()
	{
		if(!isset($_GET['post_type']) && isset($_GET['page']) && $_GET['page']=='cookie-law-info')
		{
			wp_redirect(admin_url('edit.php?post_type='.CLI_POST_TYPE.'&page=cookie-law-info'));
			exit();
		}
	}

	/**
	 Registers admin modules	 
	 */
	public function admin_modules()
	{
		$cli_admin_modules=get_option('cli_admin_modules');
		if($cli_admin_modules===false)
		{
			$cli_admin_modules=array();
		}
		foreach ($this->modules as $module) //loop through module list and include its file
		{
			$is_active=1;
			if(isset($cli_admin_modules[$module]))
			{
				$is_active=$cli_admin_modules[$module]; //checking module status
			}else
			{
				$cli_admin_modules[$module]=1; //default status is active
			}
			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file) && $is_active==1)
			{
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			}else
			{
				$cli_admin_modules[$module]=0;	
			} 
		}
		$out=array();
		foreach($cli_admin_modules as $k=>$m)
		{
			if(in_array($k,$this->modules))
			{
				$out[$k]=$m;
			}
		}
	}
	public function init_admin_modules(){
		$admin_modules = array();
		foreach ($this->modules as $module) {
			$admin_modules[ $module ] = 1;
		}
		update_option('cli_admin_modules',$admin_modules);
	}
	public static function module_exists($module)
	{
		return in_array($module,self::$existing_modules);
	}

	/**
	 Registers menu options
	 Hooked into admin_menu
	 */
	public function admin_menu() {
		global $submenu;
		add_submenu_page(
			'edit.php?post_type='.CLI_POST_TYPE,
			__('Settings','webtoffee-gdpr-cookie-consent'),
			__('Settings','webtoffee-gdpr-cookie-consent'),
			'manage_options',
			'cookie-law-info',
			array($this,'admin_settings_page')
		);		
		//rearrange settings menu
		if(isset($submenu) && !empty($submenu) && is_array($submenu))
		{
			$out=array();
			$back_up_settings_menu=array();
			if(isset($submenu['edit.php?post_type='.CLI_POST_TYPE]) && is_array($submenu['edit.php?post_type='.CLI_POST_TYPE]))
			{
				foreach ($submenu['edit.php?post_type='.CLI_POST_TYPE] as $key => $value) 
				{
					if($value[2]=='cookie-law-info')
					{
						$back_up_settings_menu=$value;
					}else
					{
						$out[$key]=$value;
					}
				}
				array_unshift($out,$back_up_settings_menu);
				$submenu['edit.php?post_type='.CLI_POST_TYPE]=$out;
			}
		}
	}

	public function plugin_action_links( $links ) 
	{
	   $links[] = '<a href="'. get_admin_url(null,'edit.php?post_type='.CLI_POST_TYPE.'&page=cookie-law-info') .'">'.__('Settings','webtoffee-gdpr-cookie-consent').'</a>';
	   $links[] = '<a href="https://www.webtoffee.com/product/gdpr-cookie-consent/" target="_blank">'.__('Support','webtoffee-gdpr-cookie-consent').'</a>';
	   return $links;
	}
	/*
	* Form action for debug settings tab
	*
	*/
	public function debug_save()
	{
		if(isset($_POST['cli_export_settings_btn']))
		{
			// Check nonce:
	        check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
			}
			$the_options =Cookie_Law_Info::get_settings();
			header('Content-Type: application/json');
			header('Content-disposition: attachment; filename="cli_settings.json"');
			echo json_encode($the_options);
			exit();
		}
		if(isset($_POST['cli_import_settings_btn']))
		{
			// Check nonce:
	        check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
			}
			if(!empty($_FILES['cli_import_settings_json']['tmp_name'])) 
			{
				$filename=$_FILES['cli_import_settings_json']['tmp_name'];
				$json_file=@fopen($filename,'r');
				$json_data=fread($json_file,filesize( $filename ));
				$json_data_arr=json_decode($json_data,true);
				$the_options =Cookie_Law_Info::get_settings();
				foreach($the_options as $key => $value) 
		        {
		            if(isset($json_data_arr[$key])) 
		            {
		                // Store sanitised values only:
		                $the_options[$key] = Cookie_Law_Info::sanitise_settings($key,$json_data_arr[$key]);
		            }
		        }
				update_option(CLI_SETTINGS_FIELD, $the_options);
			}
		}
		if(isset($_POST['cli_admin_modules_btn']))
		{
		    // Check nonce:
			if ( ! Wt_Cookie_Law_Info_Security_Helper::check_write_access( CLI_PLUGIN_FILENAME, 'cookielawinfo-update-' . CLI_SETTINGS_FIELD ) ) {
				wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
			}   
		    $cli_common_modules = get_option('cli_common_modules');
		    if($cli_common_modules===false)
		    {
		        $cli_common_modules=array();
		    }
		    if(isset($_POST['cli_common_modules']))
		    {	
	
				$cli_post = Wt_Cookie_Law_Info_Security_Helper::sanitize_item($_POST['cli_common_modules'],'text_arr');
		        foreach($cli_common_modules as $k=>$v)
		        {
		            if(isset($cli_post[$k]) && $cli_post[$k]==1)
		            {
		                $cli_common_modules[$k]=1;
		            }else
		            {
		                $cli_common_modules[$k]=0;
		            }
		        }
		    }else
		    {
		    	foreach($cli_common_modules as $k=>$v)
		        {
					$cli_common_modules[$k]=0;
		        }
		    }

		    $cli_admin_modules=get_option('cli_admin_modules');
		    if($cli_admin_modules===false)
		    {
		        $cli_admin_modules=array();
		    }
		    if(isset($_POST['cli_admin_modules']))
		    {
		        $cli_post = Wt_Cookie_Law_Info_Security_Helper::sanitize_item($_POST['cli_admin_modules'],'text_arr');
		        foreach($cli_admin_modules as $k=>$v)
		        {
		            if(isset($cli_post[$k]) && $cli_post[$k]==1)
		            {
		                $cli_admin_modules[$k]=1;
		            }else
		            {
		                $cli_admin_modules[$k]=0;
		            }
		        }
		    }else
		    {
		    	foreach($cli_admin_modules as $k=>$v)
		        {
					$cli_admin_modules[$k]=0;
		        }
		    }
		    update_option('cli_admin_modules',$cli_admin_modules);
		    update_option('cli_common_modules',$cli_common_modules);
		    wp_redirect($_SERVER['REQUEST_URI']); exit();
		}
		if(current_user_can('manage_options')) 
		{
			do_action('cli_module_save_debug_settings');
		}
	    

	}
	/*
	* admin settings page
	*/
	public function admin_settings_page()
	{
		// Lock out non-admins:
		if (!current_user_can('manage_options')) 
		{
		    wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
		}
		
    	// Check if form has been set:
	    if(isset($_POST['update_admin_settings_form']) || //normal php submit
	    (isset($_POST['cli_settings_ajax_update']) && $_POST['cli_settings_ajax_update']=='update_admin_settings_form'))  //ajax submit
	    {
	        // Check nonce:
	        check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);

	        //module settings saving hook
			do_action('cli_module_save_settings');

			// Get options:
	        $the_options = Cookie_Law_Info::get_settings();
	        foreach($the_options as $key => $value) 
	        {
	            if(isset($_POST[$key . '_field'])) 
	            {
	                // Store sanitised values only:
	                $the_options[$key] = Cookie_Law_Info::sanitise_settings($key, $_POST[$key . '_field']);
	            }
			}
			if(Cookie_Law_Info::wt_cli_category_widget_exist($the_options['notify_message']))
			{
				$the_options['accept_all'] = false;
			}
			$the_options = apply_filters('wt_cli_before_save_settings',$the_options, $_POST);
			update_option(CLI_SETTINGS_FIELD, $the_options);

			do_action('wt_cli_ajax_settings_update',$_POST);
	        echo '<div class="updated"><p><strong>' . __('Settings Updated.', 'webtoffee-gdpr-cookie-consent') . '</strong></p></div>';
	        if(!empty($_SERVER[ 'HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest')
	        {	            
	        	exit();
	        }
	    } 
	    elseif (isset($_POST['delete_all_settings']) || //normal php submit
	    (isset($_POST['cli_settings_ajax_update']) && $_POST['cli_settings_ajax_update']=='delete_all_settings'))  //ajax submit 
	    {
	        // Check nonce:
	        check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
			$this->delete_settings();
			do_action('wt_cli_after_delete_settings');
	        //$the_options = Cookie_Law_Info::get_settings();
	        //exit();
	    }
	    elseif (isset($_POST['revert_to_previous_settings']))  //disabled on new update
	    {	
			check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
	        if (!$this->copy_old_settings_to_new()) 
	        {
	            echo '<h3>' . __('ERROR MIGRATING SETTINGS (ERROR: 2)', 'webtoffee-gdpr-cookie-consent') . '</h3>';
	        }
	        $the_options = Cookie_Law_Info::get_settings();
		}
		elseif (isset($_POST['cli_renew_consent']) || //normal php submit
	    (isset($_POST['cli_settings_ajax_update']) && $_POST['cli_settings_ajax_update']=='cli_renew_consent'))  //ajax submit 
	    {
	        // Check nonce:
	        check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
	        $this->wt_cli_renew_consent();
	    } 
		// Get options:
		$the_options = Cookie_Law_Info::get_settings();
		require_once plugin_dir_path( __FILE__ ).'partials/cookie-law-info-admin_settings.php';
	}
	/**
	 * The version of this plugin.
	 *
	 * @since    2.2.2
	 * @access   public
	 */
	public function wt_cli_renew_consent()
	{
		$consent_version = Cookie_Law_Info::wt_cli_get_consent_version();
		if(!empty($consent_version))
		{	
			$consent_version = $consent_version;
			$consent_version = $consent_version + 1;
			update_option('wt_cli_consent_version',$consent_version);
		}
	}
	function remove_cli_addnew_link() 
	{
	    global $submenu;
	    if(isset($submenu) && !empty($submenu) && is_array($submenu))
		{
	    	unset($submenu['edit.php?post_type='.CLI_POST_TYPE][10]);
		}
	}
	

	/** Updates latest version number of plugin */
	public function update_to_latest_version_number() {
		update_option( CLI_MIGRATED_VERSION, CLI_LATEST_VERSION_NUMBER );
	}
	/**
	 Delete the values in all fields
	 WARNING - this has a predictable result i.e. will delete saved settings! Once deleted,
	 the get_admin_options() function will not find saved settings so will return default values
	 */
	public function delete_settings() 
	{
		if(defined( 'CLI_ADMIN_OPTIONS_NAME' )) 
		{
			delete_option( CLI_ADMIN_OPTIONS_NAME );
		}
		if ( defined ( 'CLI_SETTINGS_FIELD' ) ) 
		{
			delete_option( CLI_SETTINGS_FIELD );
		}
	}
	
	public function copy_old_settings_to_new() {
		$new_settings = Cookie_Law_Info::get_settings();
		$old_settings = get_option( CLI_ADMIN_OPTIONS_NAME );
		
		if ( empty( $old_settings ) ) {
			// Something went wrong:
			return false;
		}
		else {
			// Copy over settings:
			$new_settings['background'] 			= $old_settings['colour_bg'];
			$new_settings['border'] 				= $old_settings['colour_border'];
			$new_settings['button_1_action']		= 'CONSTANT_OPEN_URL';
			$new_settings['button_1_text'] 			= $old_settings['link_text'];
			$new_settings['button_1_url'] 			= $old_settings['link_url'];
			$new_settings['button_1_link_colour'] 	= $old_settings['colour_link'];
			$new_settings['button_1_new_win'] 		= $old_settings['link_opens_new_window'];
			$new_settings['button_1_as_button']		= $old_settings['show_as_button'];
			$new_settings['button_1_button_colour']	= $old_settings['colour_button_bg'];
			$new_settings['notify_message'] 		= $old_settings['message_text'];
			$new_settings['text'] 					= $old_settings['colour_text'];
			
			// Save new values:
			update_option( CLI_SETTINGS_FIELD, $new_settings );
		}
		return true;
	}
	/** Migrates settings from version 0.8.3 to version 0.9 */
	public function migrate_to_new_version() {
		
		if (!current_user_can('manage_options')) 
		{
		    wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
		}

		if ( $this->has_migrated() ) {
			return false;
		}
		
		if ( !$this->copy_old_settings_to_new() ) {
			return false;
		}
		
		// Register that have completed:
		$this->update_to_latest_version_number();
		return true;
	}

	/** Returns true if user is on latest version of plugin */
	public function has_migrated() {
		// Test for previous version. If doesn't exist then safe to say are fresh install:
		$old_settings = get_option( CLI_ADMIN_OPTIONS_NAME );
		if ( empty( $old_settings ) ) {
			return true;
		}
		// Test for latest version number
		$version = get_option( CLI_MIGRATED_VERSION );
		if ( empty ( $version ) ) {
			// No version stored; not yet migrated:
			return false;
		}
		if ( $version == CLI_LATEST_VERSION_NUMBER ) {
			// Are on latest version
			return true;
		}
		echo 'VERSION: ' . $version . '<br /> V2: ' . CLI_LATEST_VERSION_NUMBER;
		// If you got this far then you're on an inbetween version
		return false;
	}

	/**
	 Prints a combobox based on options and selected=match value
	 
	 Parameters:
	 	$options = array of options (suggest using helper functions)
	 	$selected = which of those options should be selected (allows just one; is case sensitive)
	 
	 Outputs (based on array ( $key => $value ):
	 	<option value=$value>$key</option>
	 	<option value=$value selected="selected">$key</option>
	 */
	public function print_combobox_options( $options, $selected ) 
	{
		foreach ( $options as $option ) {
			echo '<option value="' . $option['value'] . '"';
			if ( $option['value'] == $selected ) {
				echo ' selected="selected"';
			}
			echo '>' . $option['text'] . '</option>';
		}
	}

	/**
	 Returns list of available jQuery actions
	 Used by buttons/links in header
	 */
	public function get_js_actions() {
		$js_actions = array(
			'close_header' => array(
				'text'=>__('Close Header','webtoffee-gdpr-cookie-consent'),
				'value'=>'#cookie_action_close_header'
				),
			'open_url' => array(
				'text' => __('Open URL','webtoffee-gdpr-cookie-consent'),
				'value'=>'CONSTANT_OPEN_URL')	// Don't change this value, is used by jQuery
		);
		return $js_actions;
	}

	/**
	 Returns button sizes (dependent upon CSS implemented - careful if editing)
	 Used when printing admin form (for combo boxes)
	 */
	public function get_button_sizes() {
		$sizes = Array(
			'super'=> array(
				'text'=>__('Extra Large','webtoffee-gdpr-cookie-consent'),
				'value'=>'super'
				),
			'large'	=> array(
				'text'=>__('Large','webtoffee-gdpr-cookie-consent'),
				'value'=>'large'
				),
			'medium'	=> array(
				'text'=>__('Medium','webtoffee-gdpr-cookie-consent'),
				'value'=>'medium'
				),
			'small'	=> array(
				'text'=>__('Small','webtoffee-gdpr-cookie-consent'),
				'value'=>'small'
				),
		);
		return $sizes;
	}

	/**
	 Function returns list of supported fonts
	 Used when printing admin form (for combo box)
	 */
	public function get_fonts() {
		$fonts = Array(
			'default'=> array(
						'text'=>__('Default theme font','webtoffee-gdpr-cookie-consent'),
						'value'=>'inherit'
						),
			'sans_serif'=> array(
						'text'=>__('Sans Serif','webtoffee-gdpr-cookie-consent'),
						'value'=>'Helvetica, Arial, sans-serif'
						),
			'serif'=> array(
						'text'=>__('Serif','webtoffee-gdpr-cookie-consent'),
						'value'=>'Georgia, Times New Roman, Times, serif'
						),
			'arial'=> array(
						'text'=>__('Arial','webtoffee-gdpr-cookie-consent'),
						'value'=>'Arial, Helvetica, sans-serif'
						),
			'arial_black'=> array(
						'text'=>__('Arial Black','webtoffee-gdpr-cookie-consent'),
						'value'=>'Arial Black,Gadget,sans-serif'
						),
			'georgia'=> array(
						'text'=>__('Georgia, serif','webtoffee-gdpr-cookie-consent'),
						'value'=>'Georgia, serif'
						),
			'helvetica'=> array(
						'text'=>__('Helvetica','webtoffee-gdpr-cookie-consent'),
						'value'=>'Helvetica, sans-serif'
						),
			'lucida'=> array(
						'text'=>__('Lucida','webtoffee-gdpr-cookie-consent'),
						'value'=>'Lucida Sans Unicode, Lucida Grande, sans-serif'
						),
			'tahoma'=> array(
						'text'=>__('Tahoma','webtoffee-gdpr-cookie-consent'),
						'value'=>'Tahoma, Geneva, sans-serif'
						),
			'times_new_roman'=> array(
						'text'=>__('Times New Roman','webtoffee-gdpr-cookie-consent'),
						'value'=>'Times New Roman, Times, serif'
						),
			'trebuchet'=> array(
						'text'=>__('Trebuchet','webtoffee-gdpr-cookie-consent'),
						'value'=>'Trebuchet MS, sans-serif'
						),	
			'verdana'=> array(
						'text'=>__('Verdana','webtoffee-gdpr-cookie-consent'),
						'value'=>'Verdana, Geneva'
						),										
			);
		return $fonts;
	}
	public static function wt_cli_admin_notice( $type='info', $message='', $icon= false ){
		$icon_class = ( true === $icon ) ? 'wt-cli-callout-icon':'';
		$html  =  '<div class="wt-cli-callout wt-cli-callout-'.$type.' '.$icon_class.' ">'.$message.'</div>';
		return $html;
	}  
	public function load_plugin(){
		if ( is_admin() && get_option( 'wt_cli_first_time_activated_plugin' ) == 'true' ) {
			do_action('wt_cli_initialize_plugin');
			delete_option('wt_cli_first_time_activated_plugin');
		}
		$this->redirect_to_settings_page();
	}
}
