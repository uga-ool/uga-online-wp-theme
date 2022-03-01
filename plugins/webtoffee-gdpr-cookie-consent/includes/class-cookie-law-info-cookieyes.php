<?php

/**
 *
 * Cookieyes Integration
 *
 * @version 2.3.2
 * @package CookieLawInfo
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Cookie_Law_Info_Cookieyes")) {

    class Cookie_Law_Info_Cookieyes
    {
        protected $license;
        protected $license_status;
        protected $cookieyes_options;
        protected $website_url;
        protected $license_email;
        protected $instance_id;
        protected $license_key;
        protected $token;
        protected $ckyes_status;
        protected $ckyes_actions;

        public $ckyes_scan_data;
        public $module_id;


        const API_BASE_URI     = 'https://app.cookieyes.com/api/wp/';
        const API_BASE_PATH    = 'https://app.cookieyes.com/api/wp/v1/';
        const API_BASE_PATH_V2 = 'https://app.cookieyes.com/api/wp/v2/';

        const EC_WT_CKYES_CONNECTION_FAILED         =   100;
        const EC_WT_CKYES_INVALID_CREDENTIALS       =   101;
        const EC_WT_CKYES_ALREADY_EXIST             =   102;
        const EC_WT_CKYES_LICENSE_NOT_ACTIVATED     =   103;
        const EC_WT_CKYES_SCAN_LIMIT_REACHED        =   104;
        const EC_WT_CKYES_DISCONNECTED              =   105;
        const EC_WT_CKYES_ACTIVE_SCAN               =   106;
        const EC_WT_CKYES_LICENSE_VERIFICATION_FAILED    =   109;

        const WT_CKYES_CONNECTION_SUCCESS           =   200;
        const WT_CKYES_SCAN_INITIATED               =   201;
        const WT_CKYES_PWD_RESET_SENT               =   202;
		const WT_CKYES_EMAIL_VERIFICATION_SENT      =   203;
        const WT_CKYES_ABORT_SUCCESSFULL            =   204;


        public function __construct()
        {
            $this->license_status   =   $this->get_license_status();
            $this->license          =   $this->get_license_data();
            $this->ckyes_actions    =   $this->get_ckyes_actions();
            $this->module_id        =   'cookieyes';

            add_action('init', array($this, 'init'));
            add_action('wp_ajax_cookieyes_ajax_main_controller', array($this, 'ajax_main_controller'), 10, 0);
            add_action('cli_module_settings_advanced',array($this,'ckyes_settings'));
		    add_action('cli_module_save_settings',array( $this,'ckyes_save_settings'));
            
            register_activation_hook(CLI_PLUGIN_FILENAME,array($this,'activator'));
        }
        public function init()
        {
            add_action('admin_footer', array($this, 'ckyes_forms'));
            add_filter('wt_cli_enable_ckyes_branding', array( $this,'show_ckyes_branding' ) );
            add_filter('wt_cli_ckyes_account_widget', array($this, 'add_ckyes_account_widget'));
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
        public function activator() {
            if( Cookie_Law_Info::maybe_first_time_install() === false  && $this->get_cookieyes_status() !== false && $this->get_ckyes_branding() === false ) {
                $this->set_ckyes_branding( false );
            }
        }
        protected function default_settings()
        {

        }
        public function register_settings_page()
        {
            
        }
        public function get_ckyes_actions()
        {
            return array(
                'login',
                'reset_password',
                'connect_disconnect',
                'delete_account'
            );
        }

        public function ajax_main_controller()
        {
            if (!Wt_Cookie_Law_Info_Security_Helper::check_write_access(CLI_PLUGIN_FILENAME, $this->module_id)) {
                wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
            }
            if (isset($_POST['sub_action'])) {

                $sub_action = Wt_Cookie_Law_Info_Security_Helper::sanitize_item($_POST['sub_action']);

                if (in_array($sub_action, $this->ckyes_actions) && method_exists($this, $sub_action)) {

                    $response = $this->{$sub_action}();
                    $data           =   array();
                    $status         =   ( isset($response['status']) ? $response['status'] : false);
                    $status_code    =   ( isset($response['code']) ? $response['code'] : '');
                    $message        =   ( isset($response['message']) ? $response['message'] : false );
                    $data['status'] =   $status;
                    if (!empty($status_code)) {
                        $data['code']        =     $status_code;
                        if( $message === false ){
                            $data['message']    =   $this->get_ckyes_message($status_code);
                        } else {
                            $data['message']    =    $message;
                        }
                    }
                    if ($status === true) {
                        wp_send_json_success($data);
                    }
                    wp_send_json_error($data);
                }
            }
            $data['message'] = __('Invalid request', 'webtoffee-gdpr-cookie-consent');
            wp_send_json_error($data);
            exit();
        }
        public function add_ckyes_account_widget()
        {
            if( $this->get_cookieyes_status() === false || $this->get_cookieyes_status() === 0) {
                return;
            }
            if( $this->get_cookieyes_status() === false ) {
                return;
            }
            $ckyes_account_status_text  =   __('Connected to Cookieyes', 'webtoffee-gdpr-cookie-consent');
            $ckyes_account_action       =   'disconnect';
            $ckyes_account_action_text  =   __('Disconnect', 'webtoffee-gdpr-cookie-consent');
            $image_directory            =   plugin_dir_url(CLI_PLUGIN_FILENAME).'admin/images/';
            $ckyes_account_status_icon  =   $image_directory.'add.svg';

            if( $this->get_cookieyes_status() === 0 ) {
                $ckyes_account_action       =   'connect';
                $ckyes_account_action_text  =   '';
                $ckyes_account_status_icon  =   $image_directory.'remove.svg';
                $ckyes_account_status_text  =   __('Disconnected from Cookieyes', 'webtoffee-gdpr-cookie-consent');
            }
            $html = '<span class="wt-cli-ckyes-account-widget-container">';
            $html .= '<span class="wt-cli-ckyes-status-icon"><img src="'.$ckyes_account_status_icon.'" style="max-width:100%;   " alt=""></span>';
            $html .= '<span class="wt-cli-ckyes-status-text">'.$ckyes_account_status_text.'</span>';
            $html .= '<span><a href="#" class="wt-cli-ckyes-account-action" data-action="'.$ckyes_account_action.'">'.$ckyes_account_action_text.'</a></span>';
            $html .= '</span>';
            return $html;
        }
        public function ckyes_forms()
        {   
            $allowed_pages = apply_filters( 'wt_cli_ckyes_allowed_pages',array('cookie-law-info-cookie-scaner') );
            
            if ( isset($_GET['post_type']) && $_GET['post_type'] == CLI_POST_TYPE && isset( $_GET['page'] ) && in_array( $_GET['page'],$allowed_pages ) ) :
            ?>
                <style>
                    .wt-cli-ckyes-login-icon>.dashicons {
                        font-size: 50px;
                        width: initial;
                        height: initial;
                    }

                    .wt-cli-ckyes-login-icon {
                        width: 80px;
                        height: 80px;
                        margin: 0 auto;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                        background: #f2f2f2;
                    }

                    .wt-cli-form-input {
                        display: block;
                        width: 100%;
                        height: 45px;
                        border: 1px solid #4041424a !important;
                        margin-top: 10px;
                    }

                    .wt-cli-action-container {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-top: 10px;
                    }

                    button.wt-cli-action.button {
                        padding: 2px 24px;
                        min-width: 100px;
                        font-weight: 500;
                    }

                    #wt-cli-ckyes-modal-login {
                        padding: 45px 25px;
                        width: 430px;
                    }
                                    </style>
                <div class='wt-cli-modal' id='wt-cli-ckyes-modal-login'>
                    <span class="wt-cli-modal-js-close">×</span>
                    <div class="wt-cli-modal-body">
                        <div class="wt-cli-ckyes-login-icon">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <h4><?php echo sprintf(__('Looks like you already have an account with CookieYes for email id %s, please login to continue.', 'webtoffee-gdpr-cookie-consent'), $this->get_license_email()); ?></h4>
                        <form id="wt-cli-ckyes-form-login">
                            <div class="wt-cli-form-row">
                                <input type="email" name="ckyes-email" class="wt-cli-form-input" placeholder="Email" value="<?php echo $this->get_license_email(); ?>" disabled/>
                                <input type="password" name="ckyes-password" class="wt-cli-form-input" placeholder="Password" />
                            </div>
                            <p style="color: #757575"> <?php echo __( 'Please check if you have received an email with your password from CookieYes.', 'webtoffee-gdpr-cookie-consent' ); ?>
                            <p style="color: #757575"> <?php echo __( 'If you did not get the email, click “Reset password” to create a new password.', 'webtoffee-gdpr-cookie-consent' ); ?>
                            <div class="wt-cli-action-container">
                                <div class="wt-cli-action-group">
                                    <a href="#" id="wt-cli-ckyes-pwd-reset-link" class="wt-cli-action-link"><?php echo __('Reset password', 'webtoffee-gdpr-cookie-consent'); ?></a>
                                </div>
                                <div class="wt-cli-action-group">
                                    <button id="wt-cli-ckyes-login-btn" class="wt-cli-action button button-primary"><?php echo __('Login', 'webtoffee-gdpr-cookie-consent'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class='wt-cli-modal' id='wt-cli-ckyes-modal-password-reset'>
                    <div class="wt-cli-modal-header">
                        <h4><?php echo __('Reset Password'); ?></h4>
                    </div>
                    <div class="wt-cli-modal-body">
                        <form id="wt-cli-ckyes-form-password-reset">
                            <input type="email" name="ckyes-reset-email" class="wt-cli-form-input" placeholder="Email" value="<?php echo $this->get_license_email(); ?>" />
                            <div class="wt-cli-action-container">
                                <button id="wt-cli-ckyes-password-reset-btn" class="wt-cli-action button button-primary"><?php echo __('Send password reset email', 'webtoffee-gdpr-cookie-consent'); ?></button>
                            </div>

                        </form>
                    </div>
                </div>
<?php
            endif;
        }
        public function ckyes_login_form()
        {
        }
        public function get_ckyes_message($msg_code)
        {
            switch ($msg_code) {
                case self::EC_WT_CKYES_CONNECTION_FAILED:
                    $msg = __('Could not establish connection with scanner! please try again later', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_INVALID_CREDENTIALS:
                    $msg = __('Invalid credentials', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_ALREADY_EXIST:
                    $msg = __('You already have an account with CookieYes.', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_LICENSE_NOT_ACTIVATED:
                    $msg = __('License is not activated, please activate your license and try again', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_DISCONNECTED:
                    $msg = __('Disconnected with cookieyes, please connect and scan again', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_SCAN_LIMIT_REACHED:
                    $msg = __('Your monthly scan limit is reached please try again later', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_ACTIVE_SCAN:
                    $msg = __('A scanning is already in progress please try again after some time', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::WT_CKYES_CONNECTION_SUCCESS:
                    $msg = __('Successfully connected with CookieYes', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::WT_CKYES_PWD_RESET_SENT:
                    $msg = __('A password reset message has been sent to your email address. Click the link in the email to reset your password', 'webtoffee-gdpr-cookie-consent');
                    break;
                case self::EC_WT_CKYES_LICENSE_VERIFICATION_FAILED:
                    $msg = __('License validation has failed, please try reactivating your license', 'webtoffee-gdpr-cookie-consent');
                    break;
                default:
                    $msg = '';
                    break;
            }
            return $msg;
        }

        public function get_license_data()
        {

            $license = array(
                'status'        =>  false,
                'license_key'   =>  '',
                'instance_id'   =>  '',
                'license_email' =>  '',
            );

            if (defined('CLI_VERSION')) {
                $plugin_activation_id = CLI_ACTIVATION_ID;
            } else {
                $plugin_activation_id = 'wtgdprcookieconsent';
            }
            if (!empty($plugin_activation_id)) {

                $license['status']          =   get_option($plugin_activation_id . '_' . 'activation_status', false);
                $license['license_key']     =   get_option($plugin_activation_id . '_' . 'licence_key', '');
                $license['instance_id']     =   get_option($plugin_activation_id . '_' . 'instance_id', '');
                $license['license_email']   =   get_option($plugin_activation_id . '_' . 'email', '');
            }
            return $license;
        }
        public function get_license()
        {
            if (!($this->license)) {
                $this->license = $this->get_license_data();
            }
            return $this->license;
        }
        /**
         * Check if plugin the plugin license is activated
         *
         * @since  2.3.2
         * @return bool
         */
        public function get_license_status()
        {
            if (empty($this->license_status)) {
                $this->license_status = false;
                $license = $this->get_license();
                if (isset($license['status'])  && $license['status'] === 'active') {
                    $this->license_status =  true;
                }
            }
            return $this->license_status;
        }
        public function get_license_email()
        {
            if (!$this->license_email) {
                $this->license_email = '';
                $license = $this->get_license();
                $this->license_email = (isset($license['license_email']) ? $license['license_email'] : '');
            }
            return $this->license_email;
        }
        public function get_instance_id(){
            if (!$this->instance_id) {
                $this->instance_id = '';
                $license = $this->get_license();
                $this->instance_id = (isset($license['instance_id']) ? $license['instance_id'] : '');
            }
            return $this->instance_id;
        }
        public function get_license_key()
        {
            if (!$this->license_key) {
                $this->license_key = '';
                $license = $this->get_license();
                $this->license_key = (isset($license['license_key']) ? $license['license_key'] : '');
            }
            return $this->license_key;
        }
        public function get_access_token()
        {
            if (!$this->token) {
                $cookieyes_options = $this->get_cookieyes_options();
                $this->token = (isset($cookieyes_options['token']) ? $cookieyes_options['token'] : '');
            }
            return $this->token;
        }

        public function set_access_token($token)
        {
            if (is_string($token)) {
                if ($json = json_decode($token, true)) {
                    $token = $json;
                } else {
                    // assume $token is just the token string
                    $token = array(
                        'access_token' => $token,
                    );
                }
            }
            if ($token == null) {
                throw new Exception( __('Invalid json token','webtoffee-gdpr-cookie-consent') );
            }
            if (!isset($token['access_token'])) {
                throw new Exception( __('Invalid token format','webtoffee-gdpr-cookie-consent') );
            }
            $this->token = $token;
        }
        public function reset_token(){
            delete_option('wt_cli_cookieyes_options');
        }
        public function get_cookieyes_options()
        {   
            if( !$this->cookieyes_options ) {
                $cky_license = array(
                    'status'   => 0,
                    'token'    => ''
                );
                $cookieyes_options = get_option('wt_cli_cookieyes_options', false);
                if ($cookieyes_options !== false && is_array($cookieyes_options)) {
    
                    $cky_license['status']  =   intval(isset($cookieyes_options['status']) ? $cookieyes_options['status'] : 0);
                    $cky_license['token']   =   isset($cookieyes_options['token']) ? $cookieyes_options['token'] : '';
                } else {
                    return false;
                }
                $this->cookieyes_options = $cky_license;
            }
            
            return $this->cookieyes_options;
        }

        public function get_cookieyes_status()
        {

            if (!$this->ckyes_status) {
                $cookieyes_options = $this->get_cookieyes_options();
                $this->ckyes_status = (isset($cookieyes_options['status']) ? intval($cookieyes_options['status']) : false);
            }
            return $this->ckyes_status;
        }

        public function set_cookieyes_options($options)
        {

            $cookieyes_options = get_option('wt_cli_cookieyes_options', false);

            $cky_license = array(
                'status'   => 0,
                'token'    => ''
            );

            $this->ckyes_status = $cky_license['status']  =   (isset($options['status']) ? intval($options['status']) : 0);
            $this->token = $cky_license['token']   =   isset($options['token']) ? $options['token'] : '';

            update_option('wt_cli_cookieyes_options', $cky_license);
        }

        public function get_base_path()
        {   
           return self::API_BASE_PATH;
        }
        public function get_alternate_base_path() {
            $api_base_path = self::API_BASE_PATH;
            return $api_base_path;
        }
        public function get_base_uri() {
            return self::API_BASE_URI;
        }
        public function get_website_url()
        {
            if (!$this->website_url) {
                $this->website_url      =   home_url();
            }
            $this->website_url;
            return $this->website_url;
        }

        public function parse_raw_response( $raw_response, $object ){

            $response_code = wp_remote_retrieve_response_code($raw_response);
            if (200 !== $response_code) {
                if( 401 === $response_code ) {
                    $this->reset_token();
                }
                return false;
            }
            if( true === $object ) {
                $response = json_decode( wp_remote_retrieve_body($raw_response) );

            } else {
                $response = json_decode( wp_remote_retrieve_body($raw_response), true );

            }
            return $response;
        }

        public function get_default_response() {
            $api_response   =   array(
                'status'    =>  false,
                'code'      =>  100
            );
            return $api_response;
        }
        public function wt_remote_request( $request_type = 'GET', $endpoint = '',$body = false , $auth_token = false, $object = false ){
            
            $request_args = array(
                'timeout'     =>  60,
                'headers'     =>  array()
            );
            $request_args['headers']['Content-Type'] = 'application/json';
            $request_args['headers']['Accept'] = 'application/json';
           
            if( $body !== false ) {
                $request_args['body']   = json_encode($body);
            }
            if( $auth_token !== false ) {
                $request_args['headers']['Authorization'] = 'Bearer ' . $auth_token;
            }
            // Request types
            switch ( $request_type )
            {
                case 'GET':
                    $raw_response = wp_remote_get(
                        $endpoint,
                        $request_args
                    );
                    break;

                case 'PUT':
                case 'POST':
                    $raw_response = wp_remote_post(
                        $endpoint,
                        $request_args
                    );
                    break;
                default:
                    break;
            }
           if( $raw_response ) {
            $response = $this->parse_raw_response($raw_response, $object );
            return $response;
           }
           return false; 
        }
        public function register()
        {
            $api_response   =  $this->get_default_response();

            if ($this->get_license_status() === true) {

                $endpoint           =   $this->get_base_path() . 'users/register';
                $license            =   $this->get_license();

                $url                =   $this->get_website_url();
                $license_email      =   isset($license['license_email']) ? $license['license_email'] : '';
                $license_key        =   isset($license['license_key']) ? $license['license_key'] : '';
                $instance_id        =   isset($license['instance_id']) ? $license['instance_id'] : '';

                if (empty($license_email) || empty($license_key) || empty($url)) {
                    $api_response['code']   =   101;
                    return $api_response;
                }
                $request_body       =   array(
                    'email'         => $license_email,
                    'license_key'   => $license_key,
                    'url'           => $url,
                    'license_version'   => $this->get_ckyes_license_version(),
                );
                if ( version_compare( $this->get_ckyes_license_version(), '2.0', '<' ) ) {
                    $request_body['instance_id'] =  $instance_id;
                }
                $response = $this->wt_remote_request( 'POST', $endpoint, $request_body );

                if (isset($response) && is_array($response)) {
                    if (isset($response['token'])) {
                        $cky_options = array(
                            'status' => true,
                            'token'  => $response['token']
                        );
                        $this->set_cookieyes_options($cky_options);
                        $this->set_ckyes_branding_default();
                        $api_response['status'] =   true;
                        $api_response['code']   =   200;
                    } else {
                        if (isset($response['status']) && $response['status'] == 'error') {
                            if (isset($response['error_code']) && $response['error_code'] == 1002) {
                                $api_response['status'] =   false;
                                $api_response['code']   =   102;
                            }
                        }
                    }
                } else {
                    return $api_response;
                }
            } else {
                $api_response['code']   =   self::EC_WT_CKYES_LICENSE_NOT_ACTIVATED;
            }
            return $api_response;
        }

        public function login()
        {   
            $api_response   =  $this->get_default_response();

            if ($this->get_license_status() === true) {

                $endpoint           =   $this->get_base_path() . 'users/login';
                $license            =   $this->get_license();

                $url                =   $this->get_website_url();
                $license_email      =   isset($license['license_email'])  ? $license['license_email'] : '';
                $license_key        =   isset($license['license_key']) ? $license['license_key'] : '';
                $instance_id        =   isset($license['instance_id']) ? $license['instance_id'] : '';

                $email              =   isset($_POST['email']) ? $_POST['email'] : $license_email;
                $password           =   isset($_POST['password']) ? $_POST['password'] : '';

                $email              =   Wt_Cookie_Law_Info_Security_Helper::sanitize_item($email);

                if (empty($email) || empty($license_key) || empty($url) || empty($password)) {
                    $api_response['code']   =   101;
                    return $api_response;
                }
                $request_body       =   array(
                    'email'         => $email,
                    'license_key'   => $license_key,
                    'url'           => $url,
                    'password'      => $password,
                    'license_version'   => $this->get_ckyes_license_version(),
                );
                if ( version_compare( $this->get_ckyes_license_version(), '2.0', '<' ) ) {
                    $request_body['instance_id'] =  $instance_id;
                }
                $response = $this->wt_remote_request( 'POST', $endpoint, $request_body );

                if (isset($response) && is_array($response)) {

                    if (isset($response['status']) && $response['status'] === "error") {

                        if( isset($response['error_code']) && $response['error_code'] == 1003 ) {
                            $api_response['code']   =   101;
                        }
                    } else {
                        if (isset($response['token'])) {
                            $cky_options = array(
                                'status' => true,
                                'token'  => $response['token']
                            );
                            $this->set_cookieyes_options($cky_options);
                            $this->set_ckyes_branding_default();
                            $api_response['status'] =   true;
                            $api_response['code']   =   200;
                        }
                    }
                } else {
                    return $api_response;
                }
                
            } else {
                $api_response['code']   =   self::EC_WT_CKYES_LICENSE_NOT_ACTIVATED;
            }
            return $api_response;
        }

        public function get_next_scan_id($total_urls)
        {

            $api_response   =   array(
                'status'        =>  false,
                'code'          =>  100,
                'scan_id'       =>  '',
                'scan_token'    =>   ''
            );
            if ($this->get_license_status() === true) {
                if ( $this->get_cookieyes_status() === 1 ) {
                   
                    $token = $this->get_access_token();
                    if( empty( $token )){
                        return $api_response;
                    }
                    $endpoint           =  $this->get_alternate_base_path() . 'scan/create';
                    $request_body       =  array(
                        'url'               => $this->get_website_url(),
                        'page_limit'        => $total_urls,
                        'license_key'       => $this->get_license_key(),
                        'scan_result_token' => $this->set_ckyes_scan_instance(),
                        'license_version'   => $this->get_ckyes_license_version(),
                    );
                    if ( version_compare( $this->get_ckyes_license_version(), '2.0', '<' ) ) {
                        $request_body['instance_id'] = $this->get_instance_id();
                    }
                    $response = $this->wt_remote_request( 'POST', $endpoint, $request_body, $token );
                    if (isset($response) && is_array($response)) {

                        if (isset($response['status']) && $response['status'] === "error") {
                            if (isset($response['error_code']) && $response['error_code'] == 1005) {
                               $response = $this->refresh_scan_token();
                            } else if( isset($response['error_code']) && $response['error_code'] == 1001 ) {
                                $api_response['code']       =   self::EC_WT_CKYES_LICENSE_VERIFICATION_FAILED;
                            }
                        }
                        if( isset( $response['scan_id']) && $response['scan_token'] ) {
                            $api_response['status']     =   true;
                            $api_response['scan_id']    =   $response['scan_id'];
                            $api_response['scan_token'] =   $response['scan_token'];   
                            $api_response['code']       =   201;
                        }

                    } else {
                        return $api_response;
                    }
                } else {
                    $api_response['code']   =   self::EC_WT_CKYES_DISCONNECTED;
                }
            } else {
                $api_response['code']   =   self::EC_WT_CKYES_LICENSE_NOT_ACTIVATED;
            }
            return $api_response;
        }
        public function reset_password()
        {
            $api_response   =  $this->get_default_response();

            if ($this->get_license_status() === true) {

                $endpoint           =   $this->get_base_path() . 'password/reset';

                $email              =   isset($_POST['email']) ? $_POST['email'] : '';
                $email              =   Wt_Cookie_Law_Info_Security_Helper::sanitize_item($email);
                
                if (empty($email)) {
                    $api_response['code']   =   101;
                    return $api_response;
                }
                $request_body       =   array(
                    'email'         => $email,
                );
                $response = $this->wt_remote_request( 'POST', $endpoint, $request_body );
               
                if (isset($response) && is_array($response)) {
                    if (isset($response['status']) && $response['status'] === "success") {

                        $api_response['status'] =   true;
                        $api_response['code']   =   self::WT_CKYES_PWD_RESET_SENT;
                    }
                } else {
                    return $api_response;
                }
                
            } else {
                $api_response['code']   =   self::EC_WT_CKYES_LICENSE_NOT_ACTIVATED;
            }
            return $api_response;
        }

        public function connect_disconnect(){

            $api_response   =   array(
                'status'    =>  false,
                'code'      =>  100,
                'message'   =>  ''
            );
            $message    =   __('Successfully disconnected with Cookieyes','webtoffee-gdpr-cookie-consent');
            $action     =   isset($_POST['account_action']) ? $_POST['account_action'] : '';
            if( empty( $action )) {
                $api_response['message']    =   __('Could not identify the action','webtoffee-gdpr-cookie-consent');
                return $api_response;
            }
            if( $action === 'connect') {
                $this->change_status( true );
                $message    =   __('Successfully connected with Cookieyes','webtoffee-gdpr-cookie-consent');
            } else {
                $this->change_status( false );
            }
            $api_response['status']     =  true;
            $api_response['message']    =   $message;
            return $api_response;
        }

        public function ckyes_connect(){
            $api_response   =   array(
                'status'    =>  false,
            );
            $this->change_status( true );
            $api_response['status']     =  true;
            return $api_response;
        }

        public function change_status( $status = false ){
            $ckye_status = 0;
            if( $status === true ) {
                $ckye_status = 1;
            }
            $ckyes_options = $this->get_cookieyes_options();
            $ckyes_options['status'] = $ckye_status;
            $this->set_cookieyes_options( $ckyes_options );
        }

        protected function refresh_scan_token(){
            
            $token = $this->get_access_token();

            if( empty( $token )){
                return false;
            }
            $endpoint           =   $this->get_alternate_base_path() . 'scan/token';
            $response = $this->wt_remote_request( 'GET', $endpoint, false, $token );
            return $response;
        }
        
        protected function get_scan_status( $scan_id ) {

            $token = $this->get_access_token();

            if( empty( $token )){
                return false;
            }
            $endpoint = $this->get_alternate_base_path() . 'scan/'.$scan_id.'/status';
            $response = $this->wt_remote_request( 'GET', $endpoint, false, $token );
            return $response;
        }

        protected function get_scan_results( $scan_id ) {

            $token = $this->get_access_token();

            if( empty( $token )){
                return false;
            }
            $endpoint = $this->get_alternate_base_path() . 'scan/'.$scan_id.'/result';
            
            $response = $this->wt_remote_request( 'GET', $endpoint, false, $token );
            return $response;
        }
        /**
        * Add option to enable / disable cookeiyes branding on settings popup
        *
        * @since  2.3.4
        * @access public
        */
        public function ckyes_settings(){
            if ( $this->get_cookieyes_status() === 1 ):
                ?>
                <table class="form-table">
					<tr valign="top">
						<th scope="row"></th>
						<td>
							<button class="wt-cli-ckyes-delete-btn button" data-action="show-prompt"><?php echo __('Delete site data from CookieYes	'); ?></button>
						</td>
					</tr>
				</table>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wt-cli-ckyes-branding"><?php _e('Disable CookieYes branding', 'webtoffee-gdpr-cookie-consent'); ?><span class="wt-cli-tootip" data-wt-cli-tooltip="<?php _e('By enabling this option, a small link is automatically displayed at the bottom left of the cookie settings pop-up', 'webtoffee-gdpr-cookie-consent'); ?>"><span class="wt-cli-tootip-icon"></span></span></label></th>
                        <td>
                            <input name="wt-cli-ckyes-branding" type="checkbox" value="no" id="wt-cli-ckyes-branding-no" <?php checked( $this->get_ckyes_branding(), 'no'); ?>>
                        </td>
                    </tr>
                </table>
                <div class='wt-cli-modal' id='wt-cli-ckyes-modal-delete-account'>
					<span class="wt-cli-modal-js-close">×</span>
					<div class="wt-cli-modal-header"><h4><?php echo __( 'Do you really want to delete your website from CookieYes', 'webtoffee-gdpr-cookie-consent' ); ?></h4></div>
					<div class="wt-cli-modal-body">
						<p><?php echo sprintf( __( 'This action will clear all your website data from CookieYes. If you have multiple websites added to your CookieYes account, then only the data associated with this website get deleted. Otherwise, your entire account will be deleted.', 'webtoffee-gdpr-cookie-consent' ), $this->get_license_email() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<button class="wt-cli-action wt-cli-ckyes-delete-btn button button-primary" data-action="delete-account" ><?php echo __( 'Delete this website', 'webtoffee-gdpr-cookie-consent' );  // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					</div>
				</div>
            <?php
            endif;
        }
        
        public function ckyes_save_settings(){

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
            }
            check_admin_referer('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
            if(isset( $_POST['wt-cli-ckyes-branding'] ) && $_POST['wt-cli-ckyes-branding'] === 'no' ) {	
                $this->set_ckyes_branding( false );
            } else {
                $this->set_ckyes_branding( true );
            }
        }
        public function get_ckyes_branding(){
            $ckyes_branding = get_option('wt_cli_ckyes_branding',false);
            if( $ckyes_branding !== false ) {
                return sanitize_text_field( $ckyes_branding );
            }
            return false;
        }

        public function set_ckyes_branding( $value ){
            if( $value === true ) {
                update_option('wt_cli_ckyes_branding', 'yes');
            } else {
                update_option('wt_cli_ckyes_branding', 'no');
            }
        }
        public function set_ckyes_branding_default(){
            if( $this->get_ckyes_branding() === false ){
                $this->set_ckyes_branding( true );
            }
        }
        public function show_ckyes_branding(){
            if( $this->get_ckyes_branding() === 'yes' && $this->get_cookieyes_status() === 1 ) {
                return true;
            }
            return false;
        }
        /**
         * Enqueue javascript files.
         *
         * @return void
         */
        public function enqueue_scripts() {

			$allowed_pages = apply_filters( 'wt_cli_ckyes_allowed_pages', array( 'cookie-law-info-cookie-scaner','cookie-law-info' ) );
			if ( isset( $_GET['post_type'] ) && CLI_POST_TYPE === $_GET['post_type'] && isset( $_GET['page'] ) && in_array( $_GET['page'], $allowed_pages, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$params = array(
					'nonce'    => wp_create_nonce( esc_html( $this->module_id ) ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'messages' => array(
						'error' => __( 'Invalid request', 'webtoffee-gdpr-cookie-consent' ),
						'delete_success' => __( 'Successfully deleted!', 'webtoffee-gdpr-cookie-consent' ),
						'delete_failed' => __( 'Delete failed, please try again later', 'webtoffee-gdpr-cookie-consent' ),
					),
				);
				wp_enqueue_script( 'cookie-law-info-ckyes-admin', CLI_PLUGIN_URL . 'admin/js/cookie-law-info-ckyes.js', array( 'cookie-law-info' ), CLI_VERSION, true );
				wp_localize_script( 'cookie-law-info-ckyes-admin', 'ckyes_admin', $params );
			}
        }
        /**
		 * API request to abort the CookieYes scan.
		 *
		 * @param int $scan_id scan ID.
		 * @return array
		 */
		public function ckyes_abort_scan( $scan_id ) {
			$api_response = $this->get_default_response();
			$token        = $this->get_access_token();

			if ( empty( $token ) ) {
				return false;
			}
			$endpoint = $this->get_alternate_base_path() . 'scan/' . $scan_id . '/abort';
			$response = $this->wt_remote_request( 'POST', $endpoint, false, $token );
			if ( isset( $response['scan_result'] ) && 'cancelled' === $response['scan_result'] ) {
				$api_response['status'] = true;
				$api_response['code']   = self::WT_CKYES_ABORT_SUCCESSFULL;
			}
			return $api_response;
        }
        /**
         * Delete the existing account.
         *
         * @return void
         */
        public function delete_account() {

			$api_response = $this->get_default_response();
			if( 1 === apply_filters('wt_cli_cookie_scan_status',0) ) {
				$ckyes_scan_id = $this->get_ckyes_scan_id();
				if( $ckyes_scan_id ) {
					$response = $this->ckyes_abort_scan( $ckyes_scan_id );
					$status   = isset( $response['status'] ) ? $response['status'] : false;
					if( false === $status ) {
						wp_send_json_error();
					}
					do_action('wt_cli_ckyes_abort_scan' );
				}
			}
			$this->delete_ckyes_account();
		}

		public function delete_ckyes_account(){
			$api_response = $this->get_default_response();
			$token        = $this->get_access_token();

			if ( empty( $token ) ) {
				return $api_response;
			}

			$endpoint = $this->get_base_path() . 'users/delete';
			$response = $this->wt_remote_request( 'POST', $endpoint, false, $token );
          
			if ( isset( $response['status'] ) && 'deleted_successfully' === $response['status'] ) {
				$api_response['status'] = true;
				$this->reset_token();
				wp_send_json_success();
			}
			wp_send_json_error();
		}
        public function get_ckyes_scan_data() {

            if( !$this->ckyes_scan_data ) {
                $scan_data = array(
                    'scan_id'       => 0,
                    'scan_status'    => '',
                    'scan_token'  => '',
                    'scan_estimate' => ''
                );
                $ckyes_scan_data = get_option('wt_cli_ckyes_scan_options', false);

                if ($ckyes_scan_data !== false && is_array($ckyes_scan_data)) {
    
                    $scan_data['scan_id']       = intval(isset($ckyes_scan_data['scan_id']) ? $ckyes_scan_data['scan_id'] : 0);
                    $scan_data['scan_status']   = isset($ckyes_scan_data['scan_status']) ? $ckyes_scan_data['scan_status'] : 0;
                    $scan_data['scan_token']    = isset($ckyes_scan_data['scan_token']) ? $ckyes_scan_data['scan_token'] : '';
                    $scan_data['scan_estimate'] = isset($ckyes_scan_data['scan_estimate']) ? $ckyes_scan_data['scan_estimate'] : 0;
                    $scan_data['scan_instance'] = isset($ckyes_scan_data['scan_instance']) ? $ckyes_scan_data['scan_instance'] : 0;
                
                } else {
                    return false;
                }
                $this->ckyes_scan_data = $scan_data;
            }
            return $this->ckyes_scan_data;
        }
        public function get_ckyes_scan_id() {
            $ckyes_scan_data = $this->get_ckyes_scan_data();
            return ( isset( $ckyes_scan_data['scan_id'] ) ? $ckyes_scan_data['scan_id'] : 0 );
        }

        public function get_ckyes_scan_status() {
            $ckyes_scan_data = $this->get_ckyes_scan_data();
            return ( isset( $ckyes_scan_data['scan_status'] ) ? intval( $ckyes_scan_data['scan_status'] ) : 0 );
        }

        public function get_ckyes_scan_token() {
            $ckyes_scan_data = $this->get_ckyes_scan_data();
            return ( isset( $ckyes_scan_data['scan_token'] ) ? $ckyes_scan_data['scan_token'] : '' );
        }

        public function get_ckyes_scan_estimate() {
            $ckyes_scan_data = $this->get_ckyes_scan_data();
            return ( isset( $ckyes_scan_data['scan_estimate'] ) ? $ckyes_scan_data['scan_estimate'] : 0 );
        }

        public function set_ckyes_scan_id( $value = 0  ) {
            $this->set_ckyes_scan_data( 'scan_id', $value );
        }

        public function set_ckyes_scan_status( $value = 0  ){
            $this->set_ckyes_scan_data( 'scan_status', $value );
        }

        public function set_ckyes_scan_token( $value = ''  ){
            $this->set_ckyes_scan_data( 'scan_token', $value );
        }

        public function set_ckyes_scan_estimate( $value = 0  ){
            $this->set_ckyes_scan_data( 'scan_estimate', $value );
        }

        public function set_ckyes_scan_data( $option_name, $value  ) {
            $options = $this->get_ckyes_scan_data();
            $options[ $option_name ] = $value;
            update_option('wt_cli_ckyes_scan_options', $options );
            $this->ckyes_scan_data = $options;
        }
        public function reset_scan_token(){
            delete_option('wt_cli_ckyes_scan_options');
        }

        public function set_ckyes_scan_instance(){
            $instance_id = 'wt-cli-scan-'.wp_create_nonce( $this->module_id );
            $instance_id = base64_encode( $instance_id );
            $this->set_ckyes_scan_data( 'scan_instance', $instance_id );
            return $instance_id;
        }
        public function get_ckyes_scan_instance(){
            $ckyes_scan_data = $this->get_ckyes_scan_data();
            return ( isset( $ckyes_scan_data['scan_instance'] ) ? $ckyes_scan_data['scan_instance'] : 0 );
        }
        /**
		 * Returns the license version
		 *
		 * @return int
		 */
		public function get_ckyes_license_version() {
            $license_version       = get_option( 'wtgdprcookieconsent_license_version', '1.0' );
            $license_version = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( $license_version );
			return $license_version;
		}
    }
        
    $settings_popup = new Cookie_Law_Info_Cookieyes();
}
