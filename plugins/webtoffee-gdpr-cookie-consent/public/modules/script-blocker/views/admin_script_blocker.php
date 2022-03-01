<?php
if (!defined('ABSPATH')) {
    exit;
}
$cli_img_path = CLI_PLUGIN_URL . 'images/';
$cli_sb_status = get_option('cli_script_blocker_status');
$cli_icon = ($cli_sb_status === "enabled"  ? '<span class="dashicons dashicons-yes cli-enabled ">' : '<span class="dashicons dashicons-no-alt cli-disabled"></span>');
$action_text = ($cli_sb_status === "enabled"  ? __('disable', 'webtoffee-gdpr-cookie-consent') : __('enable', 'webtoffee-gdpr-cookie-consent'));
$action_value = ($cli_sb_status === "enabled"  ? 'disabled' : 'enabled');
$cli_sb_status_text = sprintf(__('Script blocker is currently %s', 'webtoffee-gdpr-cookie-consent'), $cli_sb_status);
$cli_notice_text = sprintf(__('<a href="javascript: submitform()">click here</a> to %s ', 'webtoffee-gdpr-cookie-consent'), $action_text);
$count = 0;
$plugin_help_url = 'https://www.webtoffee.com/how-to-add-custom-script-blocker/';
?>
<style>
    table.cli_script_items td,
    table.cli_script_items th {
        display: table-cell !important;
        padding: 1em !important;
        vertical-align: top;
        line-height: 1.75em;
    }

    .cli-switch {
        display: inline-block;
        position: relative;
        min-height: 20px;
        padding-left: 38px;
        font-size: 14px;

    }

    .cli-switch input[type="checkbox"] {
        display: none;
    }

    .cli-switch .cli-slider {
        background-color: #e3e1e8;
        height: 20px;
        width: 38px;
        bottom: 0;
        cursor: pointer;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        transition: .4s;
    }

    .cli-switch .cli-slider:before {
        background-color: #fff;
        bottom: 2px;
        content: "";
        height: 15px;
        left: 3px;
        position: absolute;
        transition: .4s;
        width: 15px;
    }

    .cli-switch input:checked+.cli-slider {
        background-color: #28a745;
    }

    .cli-switch input:checked+.cli-slider:before {
        transform: translateX(18px);
    }

    .cli-switch .cli-slider {
        border-radius: 34px;
        font-size: 0;
    }

    .cli-switch .cli-slider:before {
        border-radius: 50%;
    }

    .dashicons.cli-enabled {
        color: #46b450;
    }

    .dashicons.cli-disabled {
        color: #dc3232;
    }

    .wt-cli-script-blocker-disabled,
    .wt-cli-plugin-inactive .cli-switch {
        opacity: 0.5;
    }

    .cli_script_items [data-wt-cli-tooltip]:before {
        min-width: 220px;
    }

    .wt-cli-notice.wt-cli-info {
        padding: 15px 15px 15px 41px;
        background: #e5f5fa;
        position: relative;
        border-left: 4px solid;
        border-color: #00a0d2;
        margin-bottom: 15px;
        width:100%;
    }

    .wt-cli-notice.wt-cli-info:before {
        content: "\f348";
        color: #00a0d2;
        font-family: "dashicons";
        position: absolute;
        left: 15px;
        font-size: 16px;
    }
    .wt-cli-plugin-inactive span.wt-cli-tootip-icon {
    display: none;
}
.wt-cli-plugin-inactive:hover{
    
}
    
</style>
<div class="wrap cliscript-container">
    <h2><?php _e('Manage Script Blocking', 'webtoffee-gdpr-cookie-consent'); ?></h2>
    <div class="notice-info notice">
        <p><label><?php echo $cli_icon; ?></label>
            <?php echo $cli_sb_status_text; ?> <?php echo $cli_notice_text; ?></p>
    </div>
    <div class="wt-cli-notice wt-cli-info">
    <?php echo __( 'Enabled services/plugins will be blocked by default in the front-end of your website and will be rendered respectively only based on user consent.', 'webtoffee-gdpr-cookie-consent' ); ?>
</br>
        <?php echo sprintf(__('Apart from the below-listed pre-defined scripts you can also block your custom scripts by using plugin filters hooks. Please refer the <a target="_blank" href="%s">article</a> for more.', 'webtoffee-gdpr-cookie-consent'), esc_url( $plugin_help_url));?>
    </div>
    <form method="post" name="script_blocker_form">
        <?php
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('cookielawinfo-update-' . CLI_SETTINGS_FIELD);
        }
        ?>
        <input type="hidden" id="cli_script_blocker_state" name="cli_script_blocker_state" class="styled" value="<?php echo $action_value; ?>" />
        <input type="hidden" id="cli_update_script_blocker" name="cli_update_script_blocker" />
    </form>
    <div class="nav-tab-wrapper wp-clearfix cookie-law-info-tab-head">
        <a class="nav-tab" href="#wt-cli-script-blocker-scripts"><?php echo __('Scripts', 'webtoffee-gdpr-cookie-consent'); ?></a>
        <a class="nav-tab" href="#wt-cli-script-blocker-plugins"><?php echo __('Plugins', 'webtoffee-gdpr-cookie-consent'); ?></a>
    </div>
    <div class="cookie-law-info-tab-container">
        <div class="cookie-law-info-tab-content" data-id="wt-cli-script-blocker-scripts">
            <table class="cli_script_items widefat wt-cli-script-blocker-plugins-table" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __('No', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Name', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Enabled', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Description', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Category', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Key', 'webtoffee-gdpr-cookie-consent'); ?></th>
                    </tr>
                <tbody>

                    <?php
                    if (!empty($scripts_list)) :
                        foreach ($scripts_list as $script => $script_data) :

                            $count++;
                            $script_type = ( isset( $script_data['type'] ) ? $script_data['type'] : 0 );
                            $script_class = ( $script_type === 2 ? 'wt-cli-custom-script' : '');
                            $script_caption = ( $script_type === 2 ? '<span style="color:#00a0d2;margin-left:5px;">( '. __('via code filter','webtoffee-gdpr-cookie-consent'). ' )</span>' : '');
                    ?>
                            <tr data-script_id="<?php echo $script_data['id']; ?>" class="<?php echo $script_class;?>">
                                <td><?php echo $count; ?></td>
                                <td><?php echo __($script_data['title'],'webtoffee-gdpr-cookie-consent'); echo $script_caption?>
                                </td>
                                <td>
                                <?php if( $script_type !==2 ): ?>

                                    <div class="cli-switch">
                                        <input type="checkbox" id="wt-cli-checkbox-<?php echo esc_attr($script); ?>" data-script-id="<?php echo esc_attr($script_data['id']) ?>" class="wt-cli-plugin-status" <?php checked(wp_validate_boolean($script_data['status']), true)  ?> />
                                        <label for="wt-cli-checkbox-<?php echo esc_attr($script); ?>" class="cli-slider"></label>
                                    </div>
                                <?php else: ?>
                                <?php if( $script_data['status'] === true ):?>
                                <?php echo __('Enabled','webtoffee-gdpr-cookie-consent');?>
                                <?php else: ?>
                                <?php echo __('Disabled','webtoffee-gdpr-cookie-consent');?>
                                <?php endif; ?>
                                <?php endif; ?>

                                </td>
                                <td><?php echo __($script_data['description'],'webtoffee-gdpr-cookie-consent'); ?></td>
                                
                                <td>
                                <?php if( $script_type !==2 ): ?>
                                    <select name="cliscript_category" id="cliscript_category">
                                        <option value="0">--Select Category--</option>
                                        <?php foreach ($terms as $key => $term) : ?>
                                            <option value="<?php echo $term->slug; ?>" <?php echo selected($script_data['category'], $term->slug, false) ?>><?php echo $term->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else :?>
                                <?php if( $script_data['category'] !== 0 ): ?>
                                    <?php foreach ($terms as $key => $term) : 
                                    if( $term->slug === $script_data['category'] ) {
                                        echo $term->name;
                                    }    
                                    ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <?php echo __('Uncategorized','webtoffee-gdpr-cookie-consent');?>
                                <?php endif; ?>
                                <?php endif; ?>

                                </td>
                                <td><?php echo $script_data['description']; ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
                </thead>
            </table>
        </div>
        <div class="cookie-law-info-tab-content" data-id="wt-cli-script-blocker-plugins">
            <table class="cli_script_items widefat wt-cli-script-blocker-plugins-table" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __('No', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Name', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Enabled', 'webtoffee-gdpr-cookie-consent'); ?><span class="wt-cli-tootip" data-wt-cli-tooltip="<?php _e('Enabled: Plugins will be blocked by default prior to obtaining user consent.', 'webtoffee-gdpr-cookie-consent'); ?> <?php _e('Disabled: Plugins will be rendered prior to obtaining consent.', 'webtoffee-gdpr-cookie-consent'); ?>"><span class="wt-cli-tootip-icon"></span></span></th>
                        <th><?php echo __('Description', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Category', 'webtoffee-gdpr-cookie-consent'); ?></th>
                        <th><?php echo __('Key', 'webtoffee-gdpr-cookie-consent'); ?></th>
                    </tr>
                <tbody>

                    <?php
                    $count = 0;
                    if (!empty($plugin_list)) :
                        foreach ($plugin_list as $plugin => $plugin_data) :

                            $count++;
                            $plugin_status          =   isset($plugin_data['active']) ? wp_validate_boolean($plugin_data['active'])  : false;
                            $plugins_status_text    =   ($plugin_status === false ? __('Inactive', 'webtoffee-gdpr-cookie-consent') : '');
                            $plugins_status_class   =   ($plugin_status === false ? 'wt-cli-plugin-inactive' : 'wt-cli-plugin-active');

                    ?>
                            <tr data-script_id="<?php echo $plugin_data['id']; ?>" class="<?php echo $plugins_status_class; ?>">
                                <td><?php echo $count; ?></td>
                                <td>
                                
                                    <?php echo $plugin_data['title']; ?>
                                    <?php if( $plugin_status === false ):?>
                                        <span class="wt-cli-tootip" data-wt-cli-tooltip="<?php _e('Plugins marked inactive are either not installed or activated on your website', 'webtoffee-gdpr-cookie-consent'); ?>"><span class="wt-cli-tootip-icon"></span></span>
                                    <?php endif;?>
                                    <?php if (!empty($plugins_status_text)) : ?>
                                        <span style="color:#dc3232; margin-left:3px;">( <?php echo $plugins_status_text; ?> )</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="cli-switch">
                                        <input type="checkbox" id="wt-cli-checkbox-<?php echo esc_attr($plugin); ?>" data-script-id="<?php echo esc_attr($plugin_data['id']) ?>" class="wt-cli-plugin-status" <?php checked(wp_validate_boolean($plugin_data['status']), true)  ?> />
                                        <label for="wt-cli-checkbox-<?php echo esc_attr($plugin); ?>" class="cli-slider"></label>
                                    </div>
                                </td>
                                <td><?php echo  $plugin_data['description']; ?></td>
                                <td>
                                    <select name="cliscript_category" id="cliscript_category">
                                        <option value="0">--Select Category--</option>
                                        <?php foreach ($terms as $key => $term) : ?>
                                            <option value="<?php echo $term->slug; ?>" <?php echo selected($plugin_data['category'], $term->slug, false) ?>><?php echo $term->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?php echo $plugin_data['description']; ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    function submitform() {
        document.script_blocker_form.submit();
    }
</script>
<?php
