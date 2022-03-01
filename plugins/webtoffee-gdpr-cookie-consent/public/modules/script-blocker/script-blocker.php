<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
global $wt_cli_integration_list;

$wt_cli_integration_list = apply_filters('wt_cli_plugin_integrations', array(

    'facebook-for-wordpress' => array(
        'identifier' => 'FacebookPixelPlugin\\FacebookForWordpress',
        'label'                => 'Official Facebook Pixel',
        'status'               =>  'yes',
        'description'          => 'Official Facebook Pixel',
        'category'             => 'analytics',
        'type'                 =>  1
    ),
    'facebook-for-woocommerce' => array(
		'identifier' => 'facebook_for_woocommerce',
		'label'                => 'Facebook for WooCommerce',
		'status'               =>  'yes',
        'description'          => 'Facebook for WooCommerce',
        'category'             => 'analytics',
        'type'                 =>  1
	),
    'twitter-feed'   => array(
        'identifier' => 'CTF_VERSION',
        'label'                => 'Custom Twitter Feeds',
        'status'               =>  'yes',
        'description'          => 'Twitter Feed By Smash Baloon',
        'category'             => 'advertisement',
        'type'                 =>  1
    ),
    'instagram-feed'   => array(
        'identifier' => 'SBIVER',
        'label'                => 'Smash Balloon Instagram Feed',
        'status'               =>  'yes',
        'description'          => 'Instagram Feed By Smash Baloon',
        'category'             => 'advertisement',
        'type'                 =>  1
    ),
    'facebook-feed'   => array(
		'identifier' => 'CFFVER',
        'label'                => 'Smash Balloon Custom Facebook Feed',
        'status'               =>  'yes',
        'description'          => 'Facebook Feed By Smash Baloon',
		'category'             => 'advertisement',
        'type'                 =>  1
    ),
    'youtube-feed'   => array(
		'identifier' => 'SBYVER',
        'label'                => 'Feeds for YouTube',
        'status'               =>  'yes',
        'description'          => 'Youtube Feed By Smash Baloon',
		'category'             => 'functional',
        'type'                 =>  1
    ),
    'google-analytics-for-wordpress' => array(
        'identifier'  => 'MonsterInsights',
        'label'       => 'MonsterInsights',
        'status'      => 'yes',
        'description' => 'Google Analytics Dashboard Plugin for WordPress by MonsterInsights',
        'category'    => 'analytics',
        'type'        => 1,
    ),
    'pixel-your-site'   => array(
        'identifier' => 'PYS_PLUGIN_NAME',
        'label'                => 'PixelYourSite',
        'status'               => 'yes',
        'description'          => 'PixelYourSite',
        'category'             => 'analytics',
        'type'                 =>  1
    )
));
//`Coming Soon Page & Maintenance Mode by SeedProd` is active then disable script blocker
if (class_exists('SEED_CSP4')) {
    $seed_csp4_option = get_option('seed_csp4_settings_content');
    if ($seed_csp4_option && $seed_csp4_option['status'] > 0) {
        return;
    }
}

class Cookie_Law_Info_Script_Blocker
{

    public $version;

    public $parent_obj; //instance of the class that includes this class

    public $plugin_obj;

    protected $script_table     = 'cli_scripts';
    protected $module_id        = 'script-blocker';

    public $script_data;
    public function __construct($parent_obj)
    {
        $this->version = $parent_obj->version;
        $this->parent_obj = $parent_obj;
        $this->plugin_obj = $parent_obj->plugin_obj;

        /* creating necessary table for script blocker  */
        register_activation_hook(CLI_PLUGIN_FILENAME, array(__CLASS__, 'activator'));

        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('wp_ajax_cli_change_script_category', array($this, 'cli_change_script_category'));
        add_action('wp_ajax_cli_toggle_script_enabled', array($this, 'toggle_cliscript_enabled'));
        add_filter('wt_cli_script_blocker_scripts',array($this,'get_script_data') );
        //=====Plugin settings page Hooks=====
        if (self::get_buffer_type() == 2) //buffer type 2 means old type buffer
        {
            add_action('cli_module_settings_advanced', array($this, 'settings_advanced'));
            add_action('cli_module_save_settings', array($this, 'save_settings'));
        }

        add_action('cli_module_settings_debug', array($this, 'settings_debug'));
        add_action('cli_module_save_debug_settings', array($this, 'save_debug_settings'));
        add_action('init', array($this, 'load_integrations'), 10);
        add_action('admin_init',array( $this, 'change_script_blocker_status'));

        $this->frontend_module();
    }


    /**
     *  =====Plugin settings page Hook=====
     * save debug settings hook
     **/
    public function save_debug_settings()
    {
        if (isset($_POST['cli_sb_change_buffer_type_btn'])) {
            $allowed_options = array(1, 2);
            if (in_array($_POST['cli_sb_buffer_type'], $allowed_options)) {
                $buffer_option = Wt_Cookie_Law_Info_Security_Helper::sanitize_item($_POST['cli_sb_buffer_type'], 'int');
            } else {
                $buffer_option = 1;
            }
            update_option('cli_sb_buffer_type', $buffer_option);
            wp_redirect($_SERVER['REQUEST_URI']);
            exit();
        }
    }

    /**
     *  =====Plugin settings page Hook=====
     * Insert content to debug tab
     **/
    public function settings_debug()
    {
        $buffer_type = self::get_buffer_type();
?>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Buffer type</th>
                    <td>
                        <input type="radio" name="cli_sb_buffer_type" value="2" <?php echo $buffer_type == 2 ? 'checked' : '' ?>> Old
                        <input type="radio" name="cli_sb_buffer_type" value="1" <?php echo $buffer_type == 1 ? 'checked' : '' ?>> New
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">&nbsp;</th>
                    <td>
                        <input type="submit" name="cli_sb_change_buffer_type_btn" value="Save" class="button-primary">
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }


    /**
     *  =====Plugin settings page Hook=====
     * save settings hook
     **/
    public function save_settings()
    {
        if (isset($_POST['cli_sb_buffer_option'])) {
            $allowed_options = array(1, 2);
            if (in_array($_POST['cli_sb_buffer_option'], $allowed_options)) {
                $buffer_option = $_POST['cli_sb_buffer_option'];
            } else {
                $buffer_option = 1;
            }
            update_option('cli_sb_buffer_option', $buffer_option);
        }
    }
    /**
     *  =====Plugin settings page Hook=====
     *  Insert content to advanced tab
     **/
    public function settings_advanced()
    {
        $buffer_option = get_option('cli_sb_buffer_option');
        if (!$buffer_option) {
            $buffer_option = 1;
        }
    ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Output buffer type', 'webtoffee-gdpr-cookie-consent'); ?></th>
                <td>
                    <input type="radio" id="cli_sb_buffer_type_multi" name="cli_sb_buffer_option" class="styled" value="1" <?php echo ($buffer_option == 1) ? ' checked="checked"' : ''; ?> /><?php _e('Multi', 'webtoffee-gdpr-cookie-consent'); ?>
                    <input type="radio" id="cli_sb_buffer_type_single" name="cli_sb_buffer_option" class="styled" value="2" <?php echo ($buffer_option == 2) ? ' checked="checked" ' : ''; ?> /><?php _e('Single', 'webtoffee-gdpr-cookie-consent'); ?>
                    <span class="cli_form_help"><?php _e('Caution: This may break the site.', 'webtoffee-gdpr-cookie-consent'); ?></span>
                </td>
            </tr>
        </table>
<?php
    }



    public static function decideBuffer()
    {
        $buffer_option = 1; //multi level
        $level = @ob_get_level();
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $buffer_option = 1;
        } else {
            if ($level > 1) {
                $buffer_option = 1;
            } else {
                $buffer_option = 2;
            }
        }
        return $buffer_option;
    }

    public static function get_buffer_type()
    {
        if (!get_option('cli_sb_buffer_type')) {
            update_option('cli_sb_buffer_type', 1);
            return 1;
        } else {
            return get_option('cli_sb_buffer_type');
        }
    }
    public static function activator()
    {
        global $wpdb;
        //setting buffer option
        $buffer_option = self::decideBuffer();
        if (!get_option('cli_sb_buffer_option')) {
            update_option('cli_sb_buffer_option', $buffer_option);
        }
        //setting buffer option

        //setting buffer type
        if (!get_option('cli_sb_buffer_type')) {
            update_option('cli_sb_buffer_type', 1);
        }
        //setting buffer type

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        if (is_multisite()) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::install_tables();
                restore_current_blog();
            }
        } else {
            self::install_tables();
        }
    }
    public static function install_tables()
    {
        global $wpdb;
        //creating table for script blocker================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $like = '%' . $wpdb->prefix . 'cli_scripts%';
        $table_name = $wpdb->prefix . 'cli_scripts';
        $charset_collate = $wpdb->get_charset_collate();

        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {

            $sql_settings = "CREATE TABLE $table_name(
                `id` INT NOT NULL AUTO_INCREMENT,
                `cliscript_title` TEXT NOT NULL,
                `cliscript_category` VARCHAR(100) NOT NULL,
                `cliscript_status` VARCHAR(100) NOT NULL,
                `cliscript_description` LONGTEXT NOT NULL,
                `cliscript_key` VARCHAR(100) NOT NULL,
                PRIMARY KEY(`id`)
            ) $charset_collate;";
            dbDelta($sql_settings);
        }
        if ($wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) //check table exists, in some cases it will not created
        {
            $nonnecessary_category_id = self::get_nonnecessary_category_id();
            self::update_table_columns($table_name);
            self::new_scripts($table_name, $nonnecessary_category_id);
            self::insert_scripts($table_name);
        }
        //inserting data for script blocker=================
    }

    /*
    * new blocking scripts added after first setup is inserted via below function.
    * @since    2.1.6
    */
    public static function new_scripts($table_name, $nonnecessary_category_id)
    {
        global $wpdb;

        /**
         *
         * @since 2.1.6
         */
        $data = array(
            array(
                'cliscript_key' => 'googleanalytics',
                'cliscript_title' => 'Google Analytics',
                'cliscript_category' => 'analytics',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Google Analytic Scripts'
            ),
            array(
                'cliscript_key' => 'facebook_pixel',
                'cliscript_title' => 'Facebook Pixel',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Facebook Pixel Scripts'
            ),
            array(
                'cliscript_key' => 'google_tag_manager',
                'cliscript_title' => 'Google Tag Manager',
                'cliscript_category' => 'analytics',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Google Tag Manager Scripts'
            ),
            array(
                'cliscript_key' => 'hotjar',
                'cliscript_title' => 'Hotjar Analytics',
                'cliscript_category' => 'analytics',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Hotjar Analytic Scripts'
            ),
            array(
                'cliscript_key' => 'google_publisher_tag',
                'cliscript_title' => 'Google Publisher Tag',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Google Publisher Tag (Google Ad Manager)'
            ),
            array(
                'cliscript_key' => 'youtube_embed',
                'cliscript_title' => 'Youtube embed',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Youtube player embed'
            ),
            array(
                'cliscript_key' => 'vimeo_embed',
                'cliscript_title' => 'Vimeo embed',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Vimeo player embed'
            ),
            array(
                'cliscript_key' => 'google_maps',
                'cliscript_title' => 'Google maps',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Google maps embed'
            ),
            array(
                'cliscript_key' => 'addthis_widget',
                'cliscript_title' => 'Addthis widget',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Addthis social widget'
            ),
            array(
                'cliscript_key' => 'sharethis_widget',
                'cliscript_title' => 'Sharethis widget',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Sharethis social widget'
            ),
            array(
                'cliscript_key' => 'twitter_widget',
                'cliscript_title' => 'Twitter widget',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Twitter social widget'
            ),
            array(
                'cliscript_key' => 'soundcloud_embed',
                'cliscript_title' => 'Soundcloud embed',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Soundcloud player embed'
            ),
            array(
                'cliscript_key' => 'slideshare_embed',
                'cliscript_title' => 'Slideshare embed',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Slideshare embed'
            ),
            array(
                'cliscript_key' => 'linkedin_widget',
                'cliscript_title' => 'Linkedin widget',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Linkedin social widget'
            ),
            array(
                'cliscript_key' => 'instagram_embed',
                'cliscript_title' => 'Instagram embed',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Instagram embed'
            ),
            /**
            *
            * @since 2.1.8
            */
            array(
                'cliscript_key' => 'pinterest',
                'cliscript_title' => 'Pinterest widget',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Pinterest widget'
            ),
            /**
            *
            * @since 2.3.1
            */
            array(
                'cliscript_key' => 'google_adsense_new',
                'cliscript_title' => 'Google Adsense',
                'cliscript_category' => 'advertisement',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Google Adsense'
            ),
            array(
                'cliscript_key' => 'hubspot_analytics',
                'cliscript_title' => 'Hubspot Analytics',
                'cliscript_category' => 'analytics',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Hubspot Analytics'
            ),
            array(
                'cliscript_key' => 'matomo_analytics',
                'cliscript_title' => 'Matomo Analytics',
                'cliscript_category' => 'analytics',
                'cliscript_status' => 'yes',
                'cliscript_description' => 'Matomo Analytics'
            ),
            array(
                'cliscript_key' => 'google_recaptcha',
                'cliscript_title' => 'Google Recaptcha',
                'cliscript_category' => 'functional',
                'cliscript_status' => 'no',
                'cliscript_description' => 'Google Recaptcha'
            )
            
        );
        foreach ($data as $key => $value) {
            $data_exists = $wpdb->get_row("SELECT id FROM `$table_name` WHERE `cliscript_key`='" . $value['cliscript_key'] . "'", ARRAY_A);
            if (!$data_exists) {
                if( Cookie_Law_Info::maybe_first_time_install() === false ) {
                    $value['cliscript_status'] = 'no';
                }
                $wpdb->insert($table_name, $value);
            }
        }
    }

    public function frontend_module()
    {
        include(plugin_dir_path(__FILE__) . 'classes/class-script-blocker.php');
    }


    /* 
    * 
    * enable/disable item on list page (ajax) 
    */
    public function toggle_cliscript_enabled()
    {   
        
        if (current_user_can('manage_options') && check_ajax_referer( $this->module_id )) {

            $script_id  =   (int) ( isset( $_POST['script_id'] ) ? $_POST['script_id'] : -1 );
            $status     =   wp_validate_boolean( ( isset( $_POST['status'] ) ? $_POST['status'] : false ) ) ;

            if ( $script_id !== -1 ) {
                if( $status === true ) {
                    self::cli_script_update_status($script_id, 'yes');
                } else {
                    self::cli_script_update_status($script_id, 'no');
                }
                wp_send_json_success();
            }
            wp_send_json_error( __('Invalid script id','webtoffee-gdpr-cookie-consent') );
        }
        wp_send_json_error(__('You do not have sufficient permission to perform this operation','webtoffee-gdpr-cookie-consent') );
       
    }

    /* change category of item on list page (ajax) */
    public function cli_change_script_category()
    {

        if (current_user_can('manage_options') && check_ajax_referer( $this->module_id )) {

            $script_id  =  (int) ( isset( $_POST['script_id'] ) ? $_POST['script_id'] : -1 );
            $category   =  isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

            if ( $script_id !== '' ) {
                self::cli_script_update_category( $script_id, $category );
                wp_send_json_success();
            }
            wp_send_json_error( __('Invalid script id','webtoffee-gdpr-cookie-consent') );
        }
        wp_send_json_error(__('You do not have sufficient permission to perform this operation','webtoffee-gdpr-cookie-consent') );
    }

    public function cli_clean($var)
    {
        if (is_array($var)) {
            return array_map('cli_clean', $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }
    public static function cli_string_to_bool($string)
    {
        return is_bool($string) ? $string : ('yes' === $string || 1 === $string || 'true' === $string || '1' === $string);
    }

    /**
     * Add administration menus
     *
     * @since 2.1.3
     **/
    public function add_admin_pages()
    {
        add_submenu_page(
            'edit.php?post_type=' . CLI_POST_TYPE,
            __('Script Blocker', 'webtoffee-gdpr-cookie-consent'),
            __('Script Blocker', 'webtoffee-gdpr-cookie-consent'),
            'manage_options',
            'cli-script-settings',
            array($this, 'admin_script_blocker_page')
        );
    }

    /*
	* Script Blocker settings
	*/
    public function admin_script_blocker_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
        }
        if (isset($_GET['post_type']) && $_GET['post_type'] == CLI_POST_TYPE && isset($_GET['page']) && $_GET['page'] == 'cli-script-settings') {

            global $wt_cli_integration_list;
            $script_data = array();
            if (!$this->script_data) {
                $this->script_data = $this->get_script_data();
            }
            $script_data = $this->script_data;

            $plugin_list = (isset($script_data['plugins']) && is_array($script_data['plugins'])) ? $script_data['plugins'] : array();
            $scripts_list = (isset($script_data['default']) && is_array($script_data['default'])) ? $script_data['default'] : array();

            $custom_scripts = apply_filters('cli_extend_script_blocker', array());

            foreach ($custom_scripts as $script => $data) {

                $id             =   0; // Custom scripts so no need for an ID
                $slug           =   sanitize_text_field((isset($data['id']) ? $data['id'] : ''));
                $title          =   sanitize_text_field((isset($data['label']) ? $data['label'] : ''));
                $description    =   sanitize_text_field((isset($data['description']) ? $data['description'] : $title));
                $category       =   sanitize_text_field((isset($data['category']) ? $data['category'] : ''));
                $status         =   (isset($data['status']) && $data['status'] === "yes"  ? true : false);
                $type           =   2; // Custom scripts
                $script_data =  array(
                    'id'            =>  $id,
                    'title'         =>  $title,
                    'description'   =>  $description,
                    'category'      =>  $category,
                    'status'        =>  $status,
                    'type'          =>  $type
                );
                $scripts_list[$slug] = $script_data;
            }
            $disabled_plugins = array();
            $enabled_plugins  = array();
            foreach ($wt_cli_integration_list as $plugin => $data) {

                $plugin_data = (isset($plugin_list[$plugin]) ? $plugin_list[$plugin] : '');
                if (!empty($plugin_data)) {
                    if (defined($data['identifier']) || function_exists($data['identifier']) || class_exists($data['identifier'])) {
                        $plugin_data['active'] = true;
                        $enabled_plugins[$plugin] = $plugin_data;
                    } else {
                        $plugin_data['active'] = false;
                        $disabled_plugins[$plugin] = $plugin_data;
                    }
                }
            }
            $plugin_list = $enabled_plugins + $disabled_plugins;
            $args = array(
                'taxonomy' => 'cookielawinfo-category',
                'hide_empty' => false,
            );
            $terms = get_terms($args);
            include(plugin_dir_path(__FILE__) . 'views/admin_script_blocker.php');

            $messages = array(
                    'success' => __('Status updated', 'webtoffee-gdpr-cookie-consent'),
                    'error'   => __('Invalid request', 'webtoffee-gdpr-cookie-consent'),
                );
            $params = array(
                'nonces' => array(
                    'cli_toggle_script' => wp_create_nonce('cli-toggle-script-enabled'),
                    'cli_change_script_category' => wp_create_nonce('cli-change-script-category'),
                ),
                
                'ajax_url'=>admin_url( 'admin-ajax.php' ),
                'nonce'             =>  wp_create_nonce($this->module_id),
                'messages'  => $messages,
            );
            wp_enqueue_style('cookie-law-info');
            wp_enqueue_script( 'cookie-law-info-script-blocker',plugin_dir_url( __FILE__ ).'assets/js/script-blocker.js',array('jquery', 'cookie-law-info'),$this->version,false );
            wp_localize_script( 'cookie-law-info-script-blocker', 'wt_cli_script_blocker_obj', $params );
        }
    }

    public static function cli_script_table_data()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'cli_scripts';

        $data = $wpdb->get_results("SELECT * FROM $table_name");

        return $data;
    }

    public static function get_cookieid_by_cookieslug($slug)
    {

        $id_obj = get_term_by('slug', $slug, 'cookielawinfo-category');
        $id = $id_obj->term_id;
        return $id;
    }

    public static function get_nonnecessary_category_id()
    {

        $id_obj = get_term_by('slug', 'non-necessary', 'cookielawinfo-category');
        $id = 3; // for non-necessary default - this may change
        if ($id_obj) {
            $id = $id_obj->term_id;
        }
        return $id;
    }

    public static function get_cookie_scriptkey_by_category_id($category_id)
    {

        global $wpdb;

        $script_keys = array();

        $table_name = $wpdb->prefix . 'cli_scripts';
        $data = $wpdb->get_results("SELECT cliscript_key FROM $table_name WHERE cliscript_status = 'yes' AND cliscript_category=" . $category_id);

        if (!empty($data)) {
            foreach ($data as $value) {
                $script_keys[] = $value->cliscript_key;
            }
        }

        return array_values($script_keys);
    }

    /*
    * All scripts from DB
    */
    public static function get_blocker_script_list()
    {
        global $wpdb;
        if (isset($wpdb) && $wpdb != null) {
            $table_name = $wpdb->prefix . 'cli_scripts';
            $term_table_name = $wpdb->prefix . 'terms';
            $termmeta_table_name = $wpdb->prefix . 'termmeta';
            $data = null;
            $search_query = "SHOW TABLES LIKE %s";
            $like = '%' . $wpdb->prefix . 'cli_scripts%';
            if ($wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
                if( true === Cookie_Law_Info_Cookies::get_instance()->check_if_old_category_table() ) {
                    $data = $wpdb->get_results("SELECT a.cliscript_status,a.cliscript_key,b.slug AS category_slug,c.meta_value AS loadonstart FROM `$table_name` a LEFT JOIN $term_table_name b ON(a.cliscript_category=b.term_id) LEFT JOIN $termmeta_table_name c ON(b.term_id=c.term_id AND c.meta_key='CLIloadonstart')");
                } else {
                    $data = $wpdb->get_results("SELECT a.cliscript_status,a.cliscript_key,b.slug AS category_slug,c.meta_value AS loadonstart FROM `$table_name` a LEFT JOIN $term_table_name b ON(a.cliscript_category=b.slug) LEFT JOIN $termmeta_table_name c ON(b.term_id=c.term_id AND c.meta_key='CLIloadonstart')");
                }
            }
            return $data;
        } else {
            return null;
        }
    }

    /*
    * get disabled script keys
    */
    public static function get_disabled_blocker_scriptkeys()
    {

        global $wpdb;

        $script_keys = array();

        $table_name = $wpdb->prefix . 'cli_scripts';
        $data = $wpdb->get_results("SELECT cliscript_key FROM $table_name WHERE cliscript_status = 'no'");

        if (!empty($data)) {
            foreach ($data as $value) {
                $script_keys[] = $value->cliscript_key;
            }
        }

        return array_values($script_keys);
    }

    public static function cli_insert_log_event($args = array())
    {


        ini_set('max_execution_time', 300);

        global $wpdb;
        $table = $wpdb->prefix . 'cli_scripts';

        $data = array(
            'visitor_ip' => cli_get_client_ip(false),
            'visitor_date' => gmdate("M d, Y h:i:s A"),
            'visitor_cookie' => maybe_serialize($args),
        );

        $result = $wpdb->insert($table, $data);
        $track_id = (int) $wpdb->insert_id;

        if (!$result) {
            return false;
        }
        return $track_id;
    }

    public static function cli_script_get_data($id = 0)
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'cli_scripts';

        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $id);


        return $data;
    }

    public static function cli_script_update_status($id = 0, $status = 'yes')
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'cli_scripts';

        $wpdb->query($wpdb->prepare("UPDATE $table_name SET cliscript_status = %s WHERE id = %s", $status, $id));
    }

    //$cat = 3 // default non-necessary
    public static function cli_script_update_category($id = 0, $cat = 3)
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'cli_scripts';

        $wpdb->query($wpdb->prepare("UPDATE $table_name SET cliscript_category = %s WHERE id = %s", $cat, $id));
    }

    /**
     * Add new columns to the script blocker table
     *
     * @access private
     * @return void
     * @since  2.3.1
     */
    private static function update_table_columns($table_name)
    {
        global $wpdb;
        $search_query = "SHOW COLUMNS FROM `$table_name` LIKE 'cliscript_type'";
        if (!$wpdb->get_results($search_query, ARRAY_N)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `cliscript_type` INT DEFAULT 0 AFTER `cliscript_category`");
        }
    }
    /**
     * Load integration if it is currently activated
     * @since  2.3.2
     * @access public
     */
    public static function insert_scripts($table_name)
    {

        global $wpdb;
        global $wt_cli_integration_list;
        $nonnecessary_category_id = self::get_nonnecessary_category_id();
        foreach ($wt_cli_integration_list as $key => $value) {
            $data = array(
                'cliscript_key' => isset($key) ? $key : '',
                'cliscript_title' => isset($value['label']) ? $value['label'] : '',
                'cliscript_category' => isset($value['category']) ? $value['category'] : '',
                'cliscript_type' => isset($value['type']) ? $value['type'] : 0,
                'cliscript_status' => isset($value['status']) ? $value['status'] : 'yes',
                'cliscript_description' => isset($value['description']) ? $value['description'] : '',
            );
            $data_exists = $wpdb->get_row("SELECT id FROM `$table_name` WHERE `cliscript_key`='" . $key . "'", ARRAY_A);
            if (!$data_exists) {
                if( Cookie_Law_Info::maybe_first_time_install() === false ) {
                    $data['cliscript_status'] = 'no';
                }
                $wpdb->insert($table_name, $data);
            }
        }
    }

    /**
     * Load integration if it is currently activated
     * @since  1.9.2
     * @access public
     */
    public function load_integrations()
    {
        global $wt_cli_integration_list;
        foreach ($wt_cli_integration_list as $plugin => $details) {
            if ($this->wt_cli_plugin_is_active($plugin)) {

                $file = plugin_dir_path(__FILE__) . "integrations/$plugin.php";
                if (file_exists($file)) {
                    require_once($file);
                } else {
                    error_log("searched for $plugin integration at $file, but did not find it");
                }
            }
        }
    }
    /**
     * Check if the listed integration is active on the website
     * @since  1.9.2
     * @access public
     */
    public function wt_cli_plugin_is_active($plugin)
    {
        global $wt_cli_integration_list;
        $script_data = array();
        if (!$this->script_data) {
            $this->script_data = $this->get_script_data();
        }
        $script_data = $this->script_data;
        
        if (empty($script_data)) {
            return false;
        }
        if (!isset($script_data['plugins']) && empty($script_data['plugins'])) {
            return false;
        }
        if (!isset($wt_cli_integration_list[$plugin])) return false;
        $script_data = $script_data['plugins'];
        $details = $wt_cli_integration_list[$plugin];

        $enabled = isset($script_data[$plugin]['status']) ? wp_validate_boolean($script_data[$plugin]['status'])  : false;
        if ((defined($details['identifier'])
            || function_exists($details['identifier'])
            || class_exists($details['identifier'])) && $enabled === true) {
            return true;
        }
        return false;
    }
    /**
     * Get the current status of the integrations
     * @since  1.9.2
     * @access public
     * @return array
     */
    public function get_script_data() {

        global $wpdb;
        $script_table = $wpdb->prefix.$this->script_table;
        $scripts = array();
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $script_table ) );
      
        if( $wpdb->get_var( $query ) ) {

            $script_data = $wpdb->get_results( "select * from {$script_table}", ARRAY_A);
            $cookie_list = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
            foreach( $script_data as $key => $data ) {

                $id              =   sanitize_text_field( ( isset( $data['id'] ) ? $data['id'] : '' ) );
                $slug            =   sanitize_text_field( ( isset( $data['cliscript_key'] ) ? $data['cliscript_key'] : '' ) );
                $title           =   sanitize_text_field( ( isset( $data['cliscript_title'] ) ? $data['cliscript_title'] : '' ) );
                $description     =   sanitize_text_field( ( isset( $data['cliscript_description'] ) ? $data['cliscript_description'] : '' ) );
                $category_id     =   isset( $data['cliscript_category'] ) ?  $data['cliscript_category'] : '';
                $status          =   (isset($data['cliscript_status']) && ( $data['cliscript_status'] === "yes" || $data['cliscript_status'] === "1")  ? true : false);
                $type            =   intval((isset($data['cliscript_type']) ? $data['cliscript_type'] : 0));
                $category_slug   =   '';
                $load_on_start   =   false;
                $ccpa_optout     =   false;

                if( '' !== $category_id ) {
                    if ( is_numeric( $category_id ) ) {
                        $category_slug = $this->get_script_category_slug_by_id( $category_id );

                    } else {
                        $category_slug = $category_id;
                    }
                   
                    $category_data = isset( $cookie_list[ $category_slug ] ) ? $cookie_list[ $category_slug ] : '' ;
                   
                    if( !empty( $category_data ) ) {
                        $load_on_start = isset( $category_data['loadonstart'] ) ? $category_data['loadonstart'] : false ;
                        $ccpa_optout = isset( $category_data['ccpa_optout'] ) ? $category_data['ccpa_optout'] : false ;
                    }
                }
                
                if( !empty( $id ) ) {
                    $integration_data =  array(
                        'id'              =>  $id,
                        'title'           =>  $title,
                        'description'     =>  $description,
                        'category'        =>  $category_slug,
                        'status'          =>  $status,
                        'type'            =>  $type,
                        'loadonstart'     =>  $load_on_start,
                        'ccpa_optout'     =>  $ccpa_optout
                    );
                    if ($type === 1) {
                        $scripts['plugins'][$slug] = $integration_data;
                    } else {
                        $scripts['default'][$slug] = $integration_data;
                    }
                }
            }
        }
     
        return $scripts;
    }
    public function get_script_category_slug_by_id( $category_id ) {

        $category_slug = '';
        $category_id = intval( $category_id );
        if ( $category_id === -1 ) { // Existing cusomters.
            $category_slug = 'non-necessary';
        } else {
            if ( Cookie_Law_Info_Languages::get_instance()->is_multilanguage_plugin_active() === true ) {

                $default_language = Cookie_Law_Info_Languages::get_instance()->get_default_language_code();
                $current_language = Cookie_Law_Info_Languages::get_instance()->get_current_language_code();

                if ( $current_language !== $default_language ) {
                    $default_term = Cookie_Law_Info_Languages::get_instance()->get_term_by_language( $category_id, $default_language );

                    if ( $default_term && $default_term->term_id ) {
                        $category_slug = $default_term->slug;
                    }
                } else {
                    $term = get_term_by( 'id', $category_id, 'cookielawinfo-category' );
                    if ( isset( $term ) && is_object( $term ) ) {
                        $category_slug = $term->slug;
                    }
                }
            } else {
                $term = get_term_by( 'id', $category_id, 'cookielawinfo-category' );
                if ( isset( $term ) && is_object( $term ) ) {
                    $category_slug = $term->slug;
                }
            }
        }
        return $category_slug;
    }
    /**
     * Change script blocker status
     *
     * @return void
     */
    public function change_script_blocker_status(){
        $cli_sb_status = get_option('cli_script_blocker_status');
		if(!$cli_sb_status)
		{	
			update_option('cli_script_blocker_status','enabled');
		}
		if(isset($_POST['cli_update_script_blocker']))
		{	
			if ( ! Wt_Cookie_Law_Info_Security_Helper::check_write_access( CLI_PLUGIN_FILENAME, 'cookielawinfo-update-' . CLI_SETTINGS_FIELD ) ) {
				wp_die(__('You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent'));
			}  
			$cli_sb_status = sanitize_text_field($_POST['cli_script_blocker_state']);
			update_option('cli_script_blocker_status',$cli_sb_status);
		}	
    }
}
new Cookie_Law_Info_Script_Blocker($this);
