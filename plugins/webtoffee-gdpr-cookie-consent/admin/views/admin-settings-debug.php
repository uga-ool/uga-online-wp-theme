<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<style type="text/css">
.cli_settigs_debug form{ width: 100%; border-bottom:dashed 1px #ccc; margin-top: 15px; }
</style>
<div class="cookie-law-info-tab-content cli_settigs_debug" data-id="<?php echo $target_id;?>">
    <h3>Debug</h3>
    <p>Caution: Only for developers.</p>
    
    <form method="post">
        <?php
        // Set nonce:
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
        }
        ?>
    <table class="form-table">
        <?php
        $cli_common_modules=get_option('cli_common_modules');
        if($cli_common_modules===false)
        {
            $cli_common_modules=array();
        }
        ?>
        <tr valign="top">
            <th scope="row">Common modules</th>
            <td>
                <?php
                foreach($cli_common_modules as $k=>$v)
                {
                    
                    echo '<input type="checkbox" name="cli_common_modules['.$k.']" value="1" '.($v==1 ? 'checked' : '').' /> ';
                    echo $k;
                    echo '<br />';
                }
                ?>
            </td>
        </tr>
        <?php
        $cli_admin_modules=get_option('cli_admin_modules');
        if($cli_admin_modules===false)
        {
            $cli_admin_modules=array();
        }
        ?>
        <tr valign="top">
            <th scope="row">Admin modules</th>
            <td>
                <?php
                foreach($cli_admin_modules as $k=>$v)
                {
                    
                    echo '<input type="checkbox" name="cli_admin_modules['.$k.']" value="1" '.($v==1 ? 'checked' : '').' /> ';
                    echo $k;
                    echo '<br />';
                }
                ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">&nbsp;</th>
            <td>
                <input type="submit" name="cli_admin_modules_btn" value="Save" class="button-primary">
            </td>
        </tr>
    </table>
    </form>
    <form method="post">
        <?php
        // Set nonce:
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
        }
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Export settings (JSON)</th>
                <td>
                    <input type="submit" name="cli_export_settings_btn" value="Export" class="button-primary">
                </td>
            </tr>
        </table>   
    </form>
    <form method="post" enctype="multipart/form-data">
        <?php
        // Set nonce:
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
        }
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Import settings (JSON)</th>
                <td>
                    <input type="file" name="cli_import_settings_json">
                    <input type="submit" name="cli_import_settings_btn" value="Import" class="button-primary">
                </td>
            </tr>
        </table>   
    </form>
    <?php
    //advanced settings form fields for module
    do_action('cli_module_settings_debug');
    ?>
</div>