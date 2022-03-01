<style>
    .metabox-holder{ width:100%;}
    .column-version{ width: 25%;}
    .column-date{ width: 15% !important;}
    .column-actions{ width: 7% !important;}
    .column-visitor_cookie .cli-report-td{padding: 5px !important; padding-left: 0px !important; line-height: 0.9em !important;}
</style>
<div class="wrap" id="cli_visitor_report_wrap">
    <h1 class="wp-heading-inline"><?php _e('GDPR Consent History', 'webtoffee-gdpr-cookie-consent'); ?></h1>

    <a href="<?php print wp_nonce_url(admin_url('edit.php?post_type='.CLI_POST_TYPE.'&page=cli_visitor_report&report_history=export'), 'export', 'cookie_law_info_nonce');?>" class="page-title-action"><?php _e('Export Report', 'webtoffee-gdpr-cookie-consent'); ?></a>

    <hr class="wp-header-end">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="POST" action="<?php echo admin_url("edit.php?post_type=".CLI_POST_TYPE."&page=cli_visitor_report"); ?>">
                        <input type="hidden" name="page" value="cli_visitor_report">
                            <?php
                            $this->report_history->search = $search;
                            $this->report_history->prepare_items();
                            $this->report_history->search_box('Search Report', 'keyword');
                            $this->report_history->display();
                            ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>