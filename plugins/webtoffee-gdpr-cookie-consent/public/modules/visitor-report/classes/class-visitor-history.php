<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CLI_WT_Visitor_History extends WP_List_Table {

    public $search;
 
    public static function delete_history($id) {
        $data = get_option("cli_visitor_report");
        unset($data[$id]);
        update_option("cli_visitor_report", $data);
    }

    public static function record_count() {
        $data = get_option("cli_visitor_report");
        return count($data);
    }

    public function no_items() {
        _e('No Consent History', 'webtoffee-gdpr-cookie-consent');
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['visitor_id']);

        //global $action_type;
        //return sprintf('<input type="checkbox" name="'.$action_type.'" value="%s" />', $item['visitor_id']);
    }

//    public function column_date($item) {
//        return $this->get_formatted_date($item['date']);
//    }
//    
//    public function column_url($item) {
//	return $item['url'].'<br><span style="color:red;"><b>'.$item['reason_id'].'</b></span><br>'.$item['user_email'];
//    }
//    
//    public function column_info($item) {
//        return $item['reason_info'];
//    }
//    
//    public function column_software($item) {
//        return $item['software'];
//    }
//    
//    public function column_version($item) {
//        $html = '<table>';
//        $html .= '<tr><td>PHP</td><td> : </td><td><b>'.$item['php_version'].'</b></td>';
//        $html .= '<td>MYSQL</td><td> : </td><td><b>'.$item['mysql_version'].'</b></td></tr>';
//        $html .= '<tr><td>WP</td><td> : </td><td><b>'.$item['wp_version'].'</b></td>';
//        $html .= '<td>WFInvoice</td><td> : </td><td><b>'.$item['wfinvoice_version'].'</b></td></tr>';
//        $html .= '<td>WC</td><td> : </td><td><b>'.$item['wc_version'].'</b></td></tr>';
//        $html .= '</table>';
//        return $html;
//    }
//    
//    public function column_locale($item) {
//        return $item['locale'];
//    }
//    
//    public function column_multisite($item) {
//        return $item['multisite'];
//    }

    public function get_columns() {
        
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'visitor_ip' => __('IP Address', 'webtoffee-gdpr-cookie-consent'),
            'visitor_date' => __('Visited Date', 'webtoffee-gdpr-cookie-consent'),
            'visitor_cookie' => __('Cookie Details', 'webtoffee-gdpr-cookie-consent'),
            'user_id' => __('User ID','webtoffee-gdpr-cookie-consent'),
        );

        return $columns;
    }

    public function column_visitor_cookie($item) {
        
        $cookiedetails = maybe_unserialize($item['visitor_cookie']);
        $cookie_details = isset($cookiedetails['visitor_cookie']) && is_array($cookiedetails['visitor_cookie']) ? $cookiedetails['visitor_cookie'] : array();
        $html = '<table>';
        foreach ($cookie_details as $key => $value) 
        {
            $key = str_replace('cookielawinfo-checkbox-', ' ', $key);
            $html .= '<tr><td class="cli-report-td">' . $key . '</td><td class="cli-report-td"> : </td><td class="cli-report-td"><b>' . $value . '</b></td></tr>';
        }
        $html .= '</table>';
        return $html;
    }

    public function get_bulk_actions() {

        $actions = array(
            'bulk-delete' => __('Delete', 'webtoffee-gdpr-cookie-consent')
        );
        return $actions;
    }

    public function get_hidden_columns() {
        // Setup Hidden columns and return them
        return array();
    }

//    public function prepare_items() {
//        
//        $this->_column_headers = $this->get_column_info();
//        $this->process_bulk_action();
//        $per_page = $this->get_items_per_page('results_per_page', 5);
//        $current_page = $this->get_pagenum();
//        $total_items = self::record_count();
//        $this->set_pagination_args([
//            'total_items' => $total_items,
//            'per_page' => $per_page
//        ]);
//        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc';
//        $data = self::get_history($per_page, $current_page, $this->search, $order);
//        $this->items = $data;
//    }


    public function column_default($item, $column_name) {

        switch ($column_name) {

            //case 'visitor_id':
            case 'visitor_ip':
                $mask_ip = apply_filters('wt_cli_enable_ip_masking',true );
                $ip_address = Cookie_Law_Visitor_Report::wt_cli_mask_ip($item[$column_name]);
                if( $mask_ip === false ) {
                    $ip_address = Cookie_Law_Visitor_Report::wt_cli_mask_ip($item[$column_name],false);
                }
                return "<strong>" . $ip_address . "</strong>";
            case 'visitor_date':
            case 'visitor_cookie':
            case 'user_id' :

                return "<strong>" . $item[$column_name] . "</strong>";

            default:

                return print_r($item, true);
        }
    }
    public function prepare_items() 
    {
        global $wpdb;
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = $this->get_hidden_columns();        
        $user = get_current_user_id();             
        $this->_column_headers = array($columns, $hidden, $sortable);
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');
        $perpage = get_user_meta($user, $option, true);
        $perpage = ($perpage) ? $perpage : 30;
        if(empty($perpage) || $perpage < 1) 
        {
            $perpage = $screen->get_option('per_page', 'default');
        }
        $currentPage = $this->get_pagenum();
        $offset=($currentPage - 1)*$perpage;
        $data = $this->table_data($offset,$perpage);
        $totalitems=$data['total'];
        $totalpages = ceil($totalitems/$perpage);
        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));
        $this->items=$data['data'];
    }

    private function table_data($offset,$limit) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix.'cli_visitor_details';
        $out = array('data'=>array(),'total'=>0);
        $orderby = (!empty($_REQUEST['orderby'])) ? ($_REQUEST['orderby']) : 'visitor_id';
        $order = (!empty($_REQUEST['order'])) ? ($_REQUEST['order']) : 'DESC'; //If no order, default to desc
        $sql=" FROM $table_name ";
        if(isset($_POST['s'])) 
        {   
            $search = sanitize_text_field($_POST['s']);
            $search = trim($search);
            $sql.="WHERE visitor_ip LIKE '%$search%' OR visitor_date LIKE '%$search%' ";
        }
        $total_sql="SELECT COUNT(visitor_id) AS ttnum ".$sql; //query for total number
        $data_sql="SELECT visitor_id,visitor_ip,visitor_date,visitor_cookie,user_id ".$sql." ORDER BY $orderby $order LIMIT $offset,$limit"; //query for data
        
        $data_rows=$wpdb->get_results($data_sql,ARRAY_A);
        if(is_array($data_rows) && count($data_rows)>0)
        {
            $out['data']=$data_rows;
        }
        $total_records=$wpdb->get_row($total_sql,ARRAY_A);
        if(is_array($total_records) && count($total_records)>0)
        {
            $out['total']=$total_records['ttnum'];
        }       
        return $out;
    }

    public function process_bulk_action() 
    {
        global $wpdb;
        if (( isset($_POST['action']) && $_POST['action'] == 'bulk-delete' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete' )
        ) 
        {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            foreach ($delete_ids as $id) {
                if (!empty($id)) {

                    $table_name = $wpdb->prefix . 'cli_visitor_details';
                    $wpdb->query('DELETE  FROM ' . $table_name . ' WHERE visitor_id = "' . $id . '"');
                }
            }
            wp_redirect(esc_url_raw(add_query_arg()));
            exit;
        }
    }

    public function get_formatted_date($date) 
    {
        
        $timeformat = get_option('date_format') . ' ' . get_option('time_format');
        $return = ((get_option('timezone_string') !== "") ? get_date_from_gmt($date, $timeformat) : date_i18n($timeformat, strtotime("+" . (get_option('gmt_offset') * 60) . " minutes", strtotime($date))));
        return $return;
    }

}