<?php
if (!defined('ABSPATH')) {
    exit;
}

class Cookie_Law_Visitor_Report {

    public $report_history;
    public function __construct() 
    {
        /* creating necessary table for visitor report  */
        register_activation_hook(CLI_PLUGIN_FILENAME,array($this,'activator'));

        add_action('admin_menu',array($this,'cli_visitor_report_admin_menu'));
        add_filter('set-screen-option',array($this,'cli_visitor_set_screen'), 10, 3); 
        add_action('admin_init',array($this,'do_export'));
        add_action( 'wp_ajax_nopriv_wt_log_visitor_action',array($this,'wt_do_log_visitor_action'));
        add_action( 'wp_ajax_wt_log_visitor_action',array($this,'wt_do_log_visitor_action'));
    }

    /*
    *called on activation
    */
    public function activator()
    {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach($blog_ids as $blog_id ) 
            {
                switch_to_blog( $blog_id );
                self::install_tables();
                restore_current_blog();
            }
        }
        else 
        {
            self::install_tables();
        }
    }
    public static function install_tables()
    {
        global $wpdb;
        //creating vistor log table ========================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $like = '%'.$wpdb->prefix.'cli_visitor_details%';
        $table_name = $wpdb->prefix . 'cli_visitor_details';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            
            $sql_settings = "CREATE TABLE $table_name 
                (
                    `user_id` INT NOT NULL DEFAULT 0, 
                    `visitor_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
                    `visitor_ip` TEXT NOT NULL , 
                    `visitor_date` VARCHAR(100) NOT NULL  , 
                    `visitor_cookie` LONGTEXT  NOT NULL ,                     
                    PRIMARY KEY (`visitor_id`)
                )$charset_collate;";
            dbDelta($sql_settings);
        }
       
        $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = 'user_id'";
        $row = $wpdb->get_results($query);
        
        if(empty($row)){
            $qry = "ALTER TABLE $table_name ADD user_id INT NOT NULL DEFAULT 0";
            $wpdb->query($qry);
        }
        //creating vistor log table ========================
    }

    /*
    *inserting user log
    */
    public static function cli_insert_log_event($args = array()) 
    {   
        ini_set('max_execution_time', 300);
        global $wpdb;
        $table = $wpdb->prefix . 'cli_visitor_details';
        $data = array(
            'visitor_ip' => Cookie_Law_Info::cli_get_client_ip(true),
            'visitor_date' => apply_filters('wt_cli_change_time_format',gmdate("M d, Y h:i:s A")),
            'visitor_cookie' => maybe_serialize($args),
            'user_id' => get_current_user_id(),
        );

        $result = $wpdb->insert($table, $data);
        $track_id = (int) $wpdb->insert_id;

        if (!$result) {
            return false;
        }
        return $track_id;
    }

    /*
    * Ajax logging end point
    *
    */
    public function wt_do_log_visitor_action() 
    {
        $settings = Cookie_Law_Info::get_settings();
        $stored_options = get_option( CLI_SETTINGS_FIELD );
        if(!empty($_POST) && $settings['logging_on'])
        {
            $clicked_button = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( $_POST['wt_clicked_button_id'] );
            $js_cookie_list = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( $_POST['cookie_list'], 'text_arr' );
            $cli_cookie_details = array();

            if (isset($js_cookie_list)) 
            {
                foreach ($js_cookie_list as $key => $val) {
                    if (strpos($key, 'cookielawinfo-checkbox') !== false) {
                        $cli_cookie_details[$key] = $val;
                    }
                }
                $cli_cookie_details['viewed_cookie_policy'] = ( isset( $js_cookie_list['viewed_cookie_policy'] ) ? $js_cookie_list['viewed_cookie_policy'] : 'no' );
                $cli_cookie_details['ccpaOptout'] = ( isset( $js_cookie_list['ccpaOptout'] ) ? $js_cookie_list['ccpaOptout'] : false );
                
            }
            
            $args['visitor_cookie'] = $cli_cookie_details;
            
            if(self::cli_insert_log_event($args))
            {    
                $data = array("messge" => __('Event Logged Successfully', 'webtoffee-gdpr-cookie-consent'));
            }else
            {
                $data = array("messge" => __('Error', 'webtoffee-gdpr-cookie-consent'));
            }
            wp_send_json_success($data);

        }else
        {
          $data = array("messge" => __('Logging is not enabled', 'webtoffee-gdpr-cookie-consent'));
          wp_send_json_success($settings);  
        }
        
    }

    public function cli_visitor_set_screen($status, $option, $value) {
        return $value;
    }

    public function cli_visitor_report_admin_menu() 
    {
        $hook = add_submenu_page(
                'edit.php?post_type='.CLI_POST_TYPE, 
                __('Consent Report','webtoffee-gdpr-cookie-consent'), 
                __('Consent Report','webtoffee-gdpr-cookie-consent'), 'manage_options', 'cli_visitor_report', array($this, 'cli_visitor_report_menu_callback')
        );
        //$hook = add_options_page("Cli Visitor Report", "Cli Visitor Report", "manage_options", "cli_visitor_report", array($this, "cli_visitor_report_menu_callback"));
        add_action("load-$hook", array($this, 'cli_visitor_screen_option'));
    }

    public function cli_visitor_screen_option() {

        $option = 'per_page';
        $args = array(
            'label' => 'Result per page',
            'default' => 5,
            'option' => 'results_per_page'
        );
        add_screen_option($option, $args);
        
        include(plugin_dir_path( __FILE__ ) . 'classes/class-visitor-history.php' );
        $this->report_history = new CLI_WT_Visitor_History();
        $this->report_history->process_bulk_action();
    }

    private function cli_table_data() 
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'cli_visitor_details';

        $data = array();
        
        if (isset($_GET['s'])) {
            
            $search = sanitize_text_field($_GET['s']);
            $search = trim($search);

            $wk_post = $wpdb->get_results("SELECT visitor_id,visitor_ip,visitor_date,visitor_cookie,user_id FROM $table_name WHERE visitor_ip LIKE '%$search%' ORDER BY visitor_id DESC");
        } else {
            $wk_post = $wpdb->get_results("SELECT visitor_id,visitor_ip,visitor_date,visitor_cookie,user_id FROM $table_name ORDER BY visitor_id DESC");
        }


        $visitor_ip = array();
        $visitor_date = array();
        $visitor_cookie = array();
        $user_id = array();

        $i = 0;

        foreach ($wk_post as $wk_posts) {

            $visitor_id[] = $wk_posts->visitor_id;
            $visitor_ip[] = $wk_posts->visitor_ip;
            $visitor_date[] = $wk_posts->visitor_date;
            $visitor_cookie[] = $wk_posts->visitor_cookie;
            $user_id[] = $wk_posts->user_id;

            $data[] = array(
                'visitor_id' => $visitor_id[$i],
                'visitor_ip' => $visitor_ip[$i],
                'visitor_date' => $visitor_date[$i],
                'visitor_cookie' => $visitor_cookie[$i],
                'user_id'  => $user_id[$i],
            );

            $i++;
        }

        return $data;
    }

    public function cli_visitor_report_menu_callback() 
    {        
        
        $search = "";
        if (isset($_GET['report_history']) && $_GET['report_history'] === "clear") {
            
            header("Location:" . admin_url("edit.php?post_type=".CLI_POST_TYPE."&page=cli_visitor_report"));
        }
        if (isset($_POST['s'])) {
            $search = sanitize_text_field($_POST['s']);
        }
        include( plugin_dir_path( __FILE__ ) . 'views/visitor_report.php' );
    }

    /*
    * export csv file
    */
    public function do_export()
    {
        /*  must check the page params, because the method is attached to admin_init hook */
        if(
            isset($_GET['report_history']) && $_GET['report_history'] === "export" &&
            isset($_GET['post_type']) && $_GET['post_type'] === CLI_POST_TYPE && 
            isset($_GET['page']) && $_GET['page'] === "cli_visitor_report"
        ) 
        {
            if(check_admin_referer('export', 'cookie_law_info_nonce') && current_user_can('manage_options') )
            {
                $data = $this->cli_table_data();
                $result = array();
                $i = 0;
                foreach ($data as $key => $value) {
                    $result[$i]['visitor_id'] = $value['visitor_id'];
                    $result[$i]['visitor_ip'] = $value['visitor_ip'];
                    $result[$i]['visitor_date'] = $value['visitor_date'];
                    $result[$i]['user_id'] = $value['user_id'];
                    $visitorcookie = maybe_unserialize($value['visitor_cookie']);
                    $cookie_string = '';
                    foreach ($visitorcookie['visitor_cookie'] as $k => $v) {
                        $cookie_string .= $k . ':' . $v . '|';
                    }
                    $result[$i]['visitor_cookie'] = str_replace('cookielawinfo-checkbox-', ' ', $cookie_string);

                    $i++;
                }

                $filename = "consent_history_" . time() . ".csv";
                ob_clean();
                header("Content-Disposition: attachment; filename=" . $filename . "");
                header("Content-Type: text/csv");
                $f = fopen("php://output", 'x+');            
                fputcsv($f, array('VisitorID', 'Visitor IP Address', 'Visited Date', 'User ID', 'Cookie Details',), ",");
                foreach ($result as $line) {
                    fputcsv($f, $line, ",");
                }
                
                fclose($f);
                exit;
            }
            
        }
    }
    public static function wt_cli_mask_ip( $ipaddress, $mask=true) {
        $ip_arr = explode(".",$ipaddress);    
        if( count($ip_arr) < 2) //ipv6
        {
            $ip_arr = explode(":", $ipaddress);
        }
        $last_index = count($ip_arr)-1;
        $last_key = $ip_arr[ $last_index ];
        if( $mask === true ) {
            $last_key = '***';
        } else {
            $last_key = Cookie_Law_Info::wt_cli_decrypt( $last_key );
        }
        $ip_arr[ $last_index ] = $last_key;
        if( count($ip_arr) < 1) //ipv6
        {
            $ipaddress = implode(":", $ip_arr);
        } else {
            $ipaddress = implode(".", $ip_arr);
        }
        return $ipaddress;
    }
}
new Cookie_Law_Visitor_Report();