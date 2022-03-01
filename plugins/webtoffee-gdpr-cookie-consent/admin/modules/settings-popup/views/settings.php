<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();?>

<div class="wrap">

    <div class="cookie-law-info-form-container">
        <div class="cli-plugin-toolbar top">
            <h3><?php _e('Privacy Overview', 'webtoffee-gdpr-cookie-consent');?></h3>
        </div>
        <form method="post" action="<?php echo esc_url($_SERVER["REQUEST_URI"]); ?>">
        <?php wp_nonce_field('cookielawinfo-update-privacy-overview-content'); ?>
            <table class="form-table cli_privacy_overview_form">
                <tr valign="top">
                    <td>
                        <label for="privacy_overview_title"><?php _e('Privacy Overview Title', 'webtoffee-gdpr-cookie-consent'); ?></label>
                        <input type="text" name="privacy_overview_title" value="<?php echo $privacy_overview_title; ?>" class="cli-textbox" />
                    </td>
                 </tr>
                <tr valign="top">
                    <td>
                    <label for="privacy_overview_content"><?php _e('This will be shown in the settings visible for user on consent screen.', 'webtoffee-gdpr-cookie-consent'); ?></label>
                        <?php 
                        $cli_use_editor= apply_filters('cli_use_editor_in_po',true);
                        if($cli_use_editor)
                        {
                            wp_editor( stripslashes( $privacy_overview_content ) , 'cli_privacy_overview_content', $wpe_settings = array('textarea_name'=>'privacy_overview_content','textarea_rows' => 10));
                        }else
                        {
                            ?>
                            <textarea style="width:100%; height:250px;" name="privacy_overview_content"><?php echo stripslashes( $privacy_overview_content ) ;?></textarea>
                            <?php
                        }
                        ?>     

                        <div class="clearfix"></div>
                        <span class="cli_form_help"><?php _e('This will be shown in the settings visible for user on consent screen.', 'webtoffee-gdpr-cookie-consent'); ?></span>
                    </td>
                </tr>

            </table>
            <div class="cli-plugin-toolbar bottom">
                <div class="left">
                </div>
                <div class="right">
                    <input type="submit" name="update_privacy_overview_content_settings_form" value="<?php _e('Save Settings', 'webtoffee-gdpr-cookie-consent'); ?>" style="float: right;" class="button-primary" />
                    <span class="spinner" style="margin-top:9px"></span>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$ccpa_settings = ob_get_contents();
ob_end_clean();
echo $ccpa_settings;