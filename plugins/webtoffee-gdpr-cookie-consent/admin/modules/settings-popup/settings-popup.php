<?php

/**
 * Settings popup as module
 *
 * Manages all settings popup customizations
 *
 * @version 2.3.1
 * @package CookieLawInfo
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Cookie_Law_Info_Categories")) {

    class Cookie_Law_Info_Settings_Popup
    {
        /* styling */
		public $privacy_overview_bg_color;
		public $privacy_overview_text_color;
		public $switch_bg_color;
		public $switch_bg_color_inactive;
		public $switch_bullet_color;
		public $switch_bullet_color_inactive;
		public $button_bg_color;
		public $button_text_color;
		public $accordion_bg_color;
		public $accordion_text_color;
        public $privacy_settings;
        
        /* Texts */
        public $privacy_overview_title;
        public $privacy_overview_content;

        public $accept_btn_enable;
        public $accept_btn_text;
        public $accept_btn_bg_color;
        public $accept_btn_text_color;
        public $accept_all_btn_enable;
        public $accept_all_btn_text;
        public $accept_all_btn_bg_color;
        public $accept_all_btn_text_color;
        public $the_cookie_list;
        public $the_options;

        public $module_id;

        public function __construct()
        {   
            add_action('init',array( $this,'init'));
        }
        public function init() 
        {
            $privacy_settings                   =   get_option('cookielawinfo_privacy_overview_content_settings');
            $default_settings                   =   $this->default_settings();
            $this->module_id                    =   'settings-popup';

            $this->privacy_settings             =   $privacy_settings;
            
            // Privacy overview settings
            $this->privacy_overview_title       =   Wt_Cookie_Law_Info_Security_Helper::sanitize_item( ( isset( $privacy_settings['privacy_overview_title'] ) ? $privacy_settings['privacy_overview_title'] : $default_settings['privacy_overview_title'] ));
            $this->privacy_overview_content     =   Wt_Cookie_Law_Info_Security_Helper::sanitize_item( ( isset( $privacy_settings['privacy_overview_content'] ) ? $privacy_settings['privacy_overview_content'] : $default_settings['privacy_overview_content'] ),'post_content' );           
            
            $this->privacy_overview_bg_color    =   Cookie_Law_Info::sanitise_settings('privacy_overview_bg_color', $default_settings['privacy_overview_bg_color'] );
            $this->privacy_overview_text_color  =   Cookie_Law_Info::sanitise_settings('privacy_overview_text_color', $default_settings['privacy_overview_text_color'] );
            
            $this->accordion_bg_color           =   Cookie_Law_Info::sanitise_settings('accordion_bg_color', $default_settings['accordion_bg_color'] );
            $this->accordion_text_color         =   Cookie_Law_Info::sanitise_settings('accordion_text_color', $default_settings['accordion_text_color'] );
           
            $this->switch_bg_color              =   Cookie_Law_Info::sanitise_settings('switch_bg_color', $default_settings['switch_bg_color'] );
            $this->switch_bg_color_inactive     =   Cookie_Law_Info::sanitise_settings('switch_bg_color_inactive', $default_settings['switch_bg_color_inactive'] );
            $this->switch_bullet_color          =   Cookie_Law_Info::sanitise_settings('switch_bullet_color', $default_settings['switch_bullet_color']);
            $this->switch_bullet_color_inactive =   Cookie_Law_Info::sanitise_settings('switch_bullet_color_inactive', $default_settings['switch_bullet_color_inactive']);
            
            $this->accept_btn_enable            =   Cookie_Law_Info::sanitise_settings('accept_btn_enable', $default_settings['accept_btn_enable'] );
            $this->accept_btn_text              =   Cookie_Law_Info::sanitise_settings('accept_btn_text', $default_settings['accept_btn_text'] );
            $this->accept_btn_bg_color          =   Cookie_Law_Info::sanitise_settings('accept_btn_bg_color', $default_settings['accept_btn_bg_color'] );
            $this->accept_btn_text_color        =   Cookie_Law_Info::sanitise_settings('accept_btn_text_color', $default_settings['accept_btn_text_color'] );
            $this->accept_all_btn_enable        =   Cookie_Law_Info::sanitise_settings('accept_all_btn_enable', $default_settings['accept_all_btn_enable'] );
            $this->accept_all_btn_text          =   Cookie_Law_Info::sanitise_settings('accept_all_btn_text', $default_settings['accept_all_btn_text'] );
            $this->accept_all_btn_bg_color      =   Cookie_Law_Info::sanitise_settings('accept_all_btn_bg_color', $default_settings['accept_all_btn_bg_color'] );
            $this->accept_all_btn_text_color    =   Cookie_Law_Info::sanitise_settings('accept_all_btn_text_color', $default_settings['accept_all_btn_text_color'] );
            
            add_action( 'admin_menu', array( $this, 'register_settings_page' ),20 );
            add_action( 'wt_cli_settings_popup', array( $this, 'settings_popup_html'),10,1);
            add_action( 'wp_enqueue_scripts', array( $this, 'add_inline_css') ,11);
        }
        protected function default_settings() {
            
            $settings = array(

                'privacy_overview_content'          => 'This website uses cookies to improve your experience while you navigate through the website. Out of these cookies, the cookies that are categorized as necessary are stored on your browser as they are essential for the working of basic functionalities of the website. We also use third-party cookies that help us analyze and understand how you use this website. These cookies will be stored in your browser only with your consent. You also have the option to opt-out of these cookies. But opting out of some of these cookies may have an effect on your browsing experience.',
                'privacy_overview_title'            => 'Privacy Overview',
                'privacy_overview_bg_color'         => '#ffffff',
                'privacy_overview_text_color'       => '#000000',
                'accordion_bg_color'                => '#f2f2f2',
                'accordion_text_color'              => '#000000',
                'switch_bg_color'                   => '#28a745',
                'switch_bg_color_inactive'          => '#e3e1e8',
                'switch_bullet_color'               => '#ffffff',
                'switch_bullet_color_inactive'      => '#ffffff',
                'accept_btn_enable'                 => true,
                'accept_btn_text'                   => __('Save & Accept','webtoffee-gdpr-cookie-consent'),
                'accept_btn_bg_color'               => '#00acad',
                'accept_btn_text_color'             => '#ffffff',
                'accept_all_btn_enable'             => false,
                'accept_all_btn_text'               => __('Accept all','webtoffee-gdpr-cookie-consent'),
                'accept_all_btn_bg_color'           => '#00acad',
                'accept_all_btn_text_color'         => '#ffffff',

            );
            return $settings;
        }
        public function register_settings_page() {

            add_submenu_page(
                'edit.php?post_type='.CLI_POST_TYPE,
                __( 'Privacy Overview', 'webtoffee-gdpr-cookie-consent' ),
                __( 'Privacy Overview', 'webtoffee-gdpr-cookie-consent' ),
                'manage_options',
                'cookie-law-info-poverview',
                array( $this, 'privacy_overview_page')
            );
        }
        public function privacy_overview_page()
        {   
            if (!current_user_can('manage_options')) 
            {
                wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
            }
            $privacy_settings = $this->privacy_settings;
            if (isset($_POST['update_privacy_overview_content_settings_form'])) {

                // Check nonce:
                check_admin_referer('cookielawinfo-update-privacy-overview-content');

                $this->privacy_overview_title = $privacy_settings['privacy_overview_title'] = sanitize_text_field( isset( $_POST['privacy_overview_title'] )  ? $_POST['privacy_overview_title'] : '' );
                $this->privacy_overview_content = $privacy_settings['privacy_overview_content'] = wp_kses_post( isset( $_POST['privacy_overview_content'] ) && $_POST['privacy_overview_content'] !== '' ? $_POST['privacy_overview_content'] : '' );
                $this->privacy_settings = $privacy_settings;
                update_option('cookielawinfo_privacy_overview_content_settings', $privacy_settings );
                echo '<div class="updated"><p><strong>' . __('Settings Updated.', 'webtoffee-gdpr-cookie-consent') . '</strong></p></div>';
            }
            $privacy_overview_title     =   $this->privacy_overview_title;
            $privacy_overview_content   =   $this->privacy_overview_content;
            require_once plugin_dir_path( __FILE__ ).'views/settings.php';
        }
        public function settings_popup_html() {
            $popup_html = $this->get_settings_popup_html();
            if ( $popup_html ) {
                echo $popup_html;
            } else {
                echo '';
            }
        }
        public function get_settings_popup_html( $preview = false ) {

            $pop_out                    =       '';
            $cookie_list                =       Cookie_Law_Info_Cookies::get_instance()->get_cookies();
            $the_options                =       Cookie_Law_Info::get_settings();
            $privacy_overview_title     =       $this->privacy_overview_title;
            $privacy_overview_content   =       $this->privacy_overview_content;
            $accept_btn_enable          =       $this->accept_btn_enable;
            $accept_btn_text            =       $this->accept_btn_text;
            $accept_all_btn_enable      =       $this->accept_all_btn_enable;
            $accept_all_btn_text        =       $this->accept_all_btn_text;
            
            $settings_file  = plugin_dir_path( __FILE__ ).'views/cookie_settings.php';
            if( file_exists( $settings_file ) )
            {
                include $settings_file;
            }  
            return $pop_out;
        }
        public function generate_css() {

            $privacy_overview_bg_color       = $this->privacy_overview_bg_color;
            $privacy_overview_text_color     = $this->privacy_overview_text_color;
            $accordion_bg_color              = $this->accordion_bg_color;
            $accordion_text_color            = $this->accordion_text_color;
            $switch_bg_color                 = $this->switch_bg_color;
            $switch_bg_color_inactive        = $this->switch_bg_color_inactive;
            $switch_bullet_color             = $this->switch_bullet_color;
            $switch_bullet_color_inactive    = $this->switch_bullet_color_inactive;
            
            $accept_btn_bg_color    = $this->accept_btn_bg_color;
            $accept_btn_text_color    = $this->accept_btn_text_color;
            
            $accept_all_btn_bg_color    = $this->accept_all_btn_bg_color;
            $accept_all_btn_text_color    = $this->accept_all_btn_text_color;

            $css  = '.cli-modal-content, .cli-tab-content { background-color: '.$privacy_overview_bg_color.'; }';
            $css .= '.cli-privacy-content-text, .cli-modal .cli-modal-dialog, .cli-tab-container p, a.cli-privacy-readmore { color: '.$privacy_overview_text_color.'; }';
            $css .= '.cli-tab-header { background-color: '.$accordion_bg_color.'; }';
            $css .= '.cli-tab-header, .cli-tab-header a.cli-nav-link,span.cli-necessary-caption,.cli-switch .cli-slider:after { color: '.$accordion_text_color.'; }';
            $css .= '.cli-switch .cli-slider:before { background-color: '.$switch_bullet_color_inactive.'; }';
            $css .= '.cli-switch input:checked + .cli-slider:before { background-color: '.$switch_bullet_color.'; }';
            $css .= '.cli-switch .cli-slider { background-color: '.$switch_bg_color_inactive.'; }';
            $css .= '.cli-switch input:checked + .cli-slider { background-color: '.$switch_bg_color.'; }';
            $css .= '.cli-modal-close svg { fill: '.$privacy_overview_text_color.'; }';
            $css .= '.cli-tab-footer .wt-cli-privacy-accept-all-btn { background-color: '.$accept_all_btn_bg_color.'; color: '.$accept_all_btn_text_color.'}';
            $css .= '.cli-tab-footer .wt-cli-privacy-accept-btn { background-color: '.$accept_btn_bg_color.'; color: '.$accept_btn_text_color.'}';
            $css .= '.cli-tab-header a:before{ border-right: 1px solid '.$accordion_text_color.'; border-bottom: 1px solid '.$accordion_text_color.'; }';
            
            return $css;
        }

        public function add_inline_css() {
            if( is_admin() ) {
                return;
            }
            $css = $this->generate_css();
            wp_add_inline_style( 'cookie-law-info-gdpr', $css );
        }
        public function delete_settings() {
            if ( ! Wt_Cookie_Law_Info_Security_Helper::check_write_access( CLI_PLUGIN_FILENAME, $this->module_id ) ) {
             wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent')); 
            }
            if ( apply_filters('wt_cli_delete_all_privacy_settings',true ) === true ) {
                delete_option( 'cookielawinfo_privacy_overview_content_settings' );
            } 
            wp_send_json_success();
        }
    }   
    $settings_popup = new Cookie_Law_Info_Settings_Popup();
}
