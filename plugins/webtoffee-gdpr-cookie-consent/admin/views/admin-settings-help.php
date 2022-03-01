<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="cookie-law-info-tab-content" data-id="<?php echo $target_id;?>">
    <ul class="cli_sub_tab">
        <li style="border-left:none; padding-left: 0px;" data-target="shortcodes"><a><?php _e('Shortcodes', 'webtoffee-gdpr-cookie-consent'); ?></a></li>
        <li data-target="help-links"><a><?php _e('Help Links', 'webtoffee-gdpr-cookie-consent'); ?></a></li>
    </ul>
    <div class="cli_sub_tab_container">
        <div class="cli_sub_tab_content" data-id="shortcodes" style="display:block;">
            
            <div style="font-size: 14px;">
            <h3><?php _e('Cookie bar shortcodes', 'webtoffee-gdpr-cookie-consent'); ?></h3>
            <?php _e('You can enter the shortcodes in the "message" field of the Cookie Law Info bar. They add nicely formatted buttons and/or links into the cookie bar, without you having to add any HTML.', 'webtoffee-gdpr-cookie-consent'); ?>
            <?php _e('You can use `do_shortcode` function to add shortcodes inside the template file.', 'webtoffee-gdpr-cookie-consent'); ?>
            </div>
    <ul class="cli-shortcodes">
        <li>
            <div style="font-weight: bold;">[cookie_accept]</div>
            <span><?php _e('If you just want a standard green "Accept" button that closes the header and nothing more, use this shortcode. It is already styled, you don\'t need to customise it.', 'webtoffee-gdpr-cookie-consent'); ?></span>
        </li>
        <li>
            <div style="font-weight: bold;">[cookie_accept colour="red"]</div>
            <?php _e('Alternatively you can add a colour value. Choose from: red, blue, orange, yellow, green or pink.', 'webtoffee-gdpr-cookie-consent'); ?><br /><em><?php _e('Careful to use the British spelling of "colour" for the attribute.', 'webtoffee-gdpr-cookie-consent'); ?></em>
        </li>
        <li>
        <div style="font-weight: bold;">[cookie_button]</div>
        <?php _e('This is the "main button" you can customise.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>

        <li>
        <div style="font-weight: bold;">[cookie_settings]</div>
        <?php _e('This is the cookie settings button rendering shortcode.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>

        <li><div style="font-weight: bold;">[cookie_reject]</div>
            <?php _e('This is the cookie reject button shortcode.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>

        <li><div style="font-weight: bold;">[cookie_link]</div>
            <?php _e('This is the "read more" link you can customise.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>
        </ul>
        <div style="font-size: 14px;">
            <h3 style="margin-bottom:5px; margin-top:25px;"><?php _e('Other shortcodes', 'webtoffee-gdpr-cookie-consent'); ?></h3>
            <?php _e('These shortcodes can be used in pages and posts on your website. It is not recommended to use these inside the cookie bar itself.', 'webtoffee-gdpr-cookie-consent'); ?>
            <?php _e('You can use `do_shortcode` function to add shortcodes inside the template file.', 'webtoffee-gdpr-cookie-consent'); ?>
        </div>

        <ul class="cli-shortcodes">
        <li>
            <div style="font-weight: bold;">[cookie_audit]</div>
                <?php _e('This prints out a nice table of cookies, in line with the guidance given by the ICO.', 'webtoffee-gdpr-cookie-consent'); ?> <em><?php _e('You need to enter the cookies your website uses via the Cookie Law Info menu in your WordPress dashboard.', 'webtoffee-gdpr-cookie-consent'); ?></em>
            <div style="font-weight: bold;">
                [cookie_audit category="category-slug"] <br />
                [cookie_audit style="winter"] <br />
                [cookie_audit not_shown_message="No records found"] <br />
                [cookie_audit category="category-slug" style="winter" not_shown_message="Not found"]
            </div>
            Styles included:    simple, classic, modern, rounded, elegant, winter. Default is classic.
        </li>
        <li>
            <div style="font-weight: bold;">
            [cookie_audit_category]</div>
            <?php _e('This prints out a nice table of cookies by category.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>
        <li>
            <div style="font-weight: bold;">[cookie_popup_content]</div>
            <?php _e('This prints the settings popup of cookie category.', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>
        <li>
            <div style="font-weight: bold;">[delete_cookies]</div>
            <?php _e('This shortcode will display a normal HTML link which when clicked, will delete the cookie set by Cookie Law Info (this cookie is used to remember that the cookie bar is closed).', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>
        <li>
            <div style="font-weight: bold;">[delete_cookies text="Click here to delete"]</div>
            <?php _e('Add any text you like useful if you want to add another language', 'webtoffee-gdpr-cookie-consent'); ?>
        </li>
        <li>
            <div style="font-weight: bold;">[cookie_after_accept] Your content goes here... [/cookie_after_accept]</div>
            <?php _e('Add content after accepting the cookie notice. Category wise checking allowed', 'webtoffee-gdpr-cookie-consent'); ?>
            <div style="font-weight: bold;">
                [cookie_after_accept category="category1-slug"] ...Your content goes here...  [/cookie_after_accept] <br />
                <span style="font-weight: normal;"><?php _e('Add content only if the consent has been obtained for the specified category.', 'webtoffee-gdpr-cookie-consent'); ?></span><br />

                [cookie_after_accept category="category1-slug, category2-slug" condition="or"] ...Your content goes here...  [/cookie_after_accept] <br />
                <span style="font-weight: normal;"><?php _e('Add content if consent has been obtained for all/or any of the specified categories.', 'webtoffee-gdpr-cookie-consent'); ?></span><br />
            </div>
        </li>
        </ul>
        </div>
        
        <div class="cli_sub_tab_content" data-id="help-links" style="float: left; height:auto;">
            <?php
            $admin_img_path=plugin_dir_url(CLI_PLUGIN_FILENAME).'admin/images/';
            ?>
            <h3><?php _e('Help Links', 'webtoffee-gdpr-cookie-consent'); ?></h3>
            <ul class="cli-help-links">
                <li>
                    <img src="<?php echo $admin_img_path;?>documentation.png">
                    <h3><?php _e('Documentation', 'webtoffee-gdpr-cookie-consent'); ?></h3>
                    <p><?php _e('Refer to our documentation to set and get started', 'webtoffee-gdpr-cookie-consent'); ?></p>
                    <a target="_blank" href="https://www.webtoffee.com/category/documentation/" class="button button-primary">
                        <?php _e('Documentation', 'webtoffee-gdpr-cookie-consent'); ?>        
                    </a>
                </li>
                <li>
                    <img src="<?php echo $admin_img_path;?>support.png">
                    <h3><?php _e('Help and Support', 'webtoffee-gdpr-cookie-consent'); ?></h3>
                    <p><?php _e('We would love to help you on any queries or issues.', 'webtoffee-gdpr-cookie-consent'); ?></p>
                    <a target="_blank" href="https://www.webtoffee.com/support/" class="button button-primary">
                        <?php _e('Contact Us', 'webtoffee-gdpr-cookie-consent'); ?>
                    </a>
                </li>               
            </ul>
        </div>
    </div>
</div>