<?php
ob_start();

$cli_always_enable_text = __('Always Enabled', 'webtoffee-gdpr-cookie-consent');
$cli_enable_text = __('Enabled', 'webtoffee-gdpr-cookie-consent');
$cli_disable_text = __('Disabled', 'webtoffee-gdpr-cookie-consent');
$cli_privacy_readmore = '<a id="wt-cli-privacy-readmore"  tabindex="0" role="button" class="cli-privacy-readmore" data-readmore-text="' . __('Show more', 'webtoffee-gdpr-cookie-consent') . '" data-readless-text="' . __('Show less', 'webtoffee-gdpr-cookie-consent') . '"></a>';

$necessary_categories = Cookie_Law_Info::get_strictly_necessory_categories();

$privacy_overview_content = nl2br($privacy_overview_content);
$privacy_overview_content = do_shortcode(stripslashes($privacy_overview_content));
$content_length = strlen(strip_tags($privacy_overview_content));
$privacy_overview_title = trim($privacy_overview_title);

?>
<div class="cli-modal-body">

    <div class="wt-cli-element cli-container-fluid cli-tab-container">
        <div class="cli-row">
            <?php if ($the_options['cookie_setting_popup'] === true) : ?>
                <div class="cli-col-12 cli-align-items-stretch cli-px-0">
                    <div class="cli-privacy-overview">
                        <?php
                        if (isset($privacy_overview_title) === true && $privacy_overview_title !== '') {
                            if ( has_filter( 'wt_cli_change_privacy_overview_title_tag' ) ) {
                                echo apply_filters( 'wt_cli_change_privacy_overview_title_tag', $privacy_overview_title, '<h4 id="wt-cli-privacy-title">', '</h4>' );
                            } else {
                                echo "<h4 id='wt-cli-privacy-title'>" . esc_html( $privacy_overview_title ) . "</h4>";
                            }
                        }
                        ?>
                        <div class="cli-privacy-content">
                            <div class="cli-privacy-content-text"><?php echo $privacy_overview_content; ?></div>
                        </div>
                        <?php
                        echo $cli_privacy_readmore;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="cli-col-12 cli-align-items-stretch cli-px-0 cli-tab-section-container" role="tablist">

                <?php if ($the_options['accept_all'] != true && $the_options['cookie_setting_popup'] != true) : ?>
                    <div class="cli-tab-section cli-privacy-tab">
                        <div class="cli-tab-header">
                            <a id="wt-cli-tab-link-privacy-overview" class="cli-nav-link cli-settings-mobile" tabindex="0" role="tab" aria-expanded="false" aria-describedby="wt-cli-tab-privacy-overview" aria-controls="wt-cli-tab-privacy-overview">
                                <?php echo $privacy_overview_title; ?>
                            </a>
                        </div>
                        <div class="cli-tab-content">
                            <div id="wt-cli-tab-privacy-overview" class="cli-tab-pane cli-fade" tabindex="0" role="tabpanel" aria-labelledby="wt-cli-tab-link-privacy-overview">
                                <p><?php echo $privacy_overview_content;  ?></p>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

                <?php
                foreach ($cookie_list as $key => $cookie) {

                    $ccpa_applicable = $cookie['ccpa_optout'];
                    $category_description   = ( isset( $cookie['description'] ) ? $cookie['description'] : '' );
                    $aria_described_by = !empty( $category_description ) ? 'aria-describedby="wt-cli-tab-' . $key . '"' : '';
                    $ccpa_category_identity = ( $ccpa_applicable === true ) ? 'data-cli-ccpa-optout' : '';
                   
                ?>
                    <div class="cli-tab-section">
                        <div class="cli-tab-header">
                            <a id="wt-cli-tab-link-<?php echo $key; ?>" tabindex="0" role="tab" aria-expanded="false" <?php echo $aria_described_by; ?> aria-controls="wt-cli-tab-<?php echo $key; ?>" class="cli-nav-link cli-settings-mobile" data-target="<?php echo $key; ?>" data-toggle="cli-toggle-tab">
                                <?php echo $cookie['title']; ?>
                            </a>
                            <?php
                            $checked = false;
                            if ( true === $cookie['default_state']) {
                                $checked = true;
                            }
                            ?>
                            <?php if ( true === $cookie['strict'] ) {
                            ?>
                                <div class="wt-cli-necessary-checkbox">
                                    <input type="checkbox" class="cli-user-preference-checkbox" id="wt-cli-checkbox-<?php echo $key; ?>" aria-label="<?php echo $cookie['title']; ?>" data-id="checkbox-<?php echo $key; ?>" checked="checked" />
                                    <label class="form-check-label" for="wt-cli-checkbox-<?php echo $key; ?>"> <?php echo $cookie['title']; ?> </label>
                                </div>
                                <span class="cli-necessary-caption">
                                    <?php echo $cli_always_enable_text; ?>
                                </span>
                            <?php } else {
                            ?>
                                <div class="cli-switch">
                                    <input type="checkbox" class="cli-user-preference-checkbox" <?php echo $ccpa_category_identity; ?> id="wt-cli-checkbox-<?php echo $key; ?>" aria-label="<?php echo $key; ?>" data-id="checkbox-<?php echo $key; ?>" role="switch" aria-controls="wt-cli-tab-link-<?php echo $key; ?>" aria-labelledby="wt-cli-tab-link-<?php echo $key; ?>" <?php checked($checked, true); ?> />
                                    <label for="wt-cli-checkbox-<?php echo $key; ?>" class="cli-slider" data-cli-enable="<?php echo $cli_enable_text; ?>" data-cli-disable="<?php echo $cli_disable_text; ?>"><span class="wt-cli-sr-only"><?php echo $key; ?></span></label>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="cli-tab-content">
                            <div id="wt-cli-tab-<?php echo $key; ?>" tabindex="0" role="tabpanel" aria-labelledby="wt-cli-tab-link-<?php echo $key; ?>" class="cli-tab-pane cli-fade" data-id="<?php echo $key; ?>">
                            <div class="wt-cli-cookie-description"><?php echo do_shortcode( $category_description, 'cookielawinfo-category' ); ?></div>
                            </div>
                        </div>
                    </div>
                <?php  } ?>

            </div>
        </div>
    </div>
</div>
<div class="cli-modal-footer">
    <div class="wt-cli-element cli-container-fluid cli-tab-container">
        <div class="cli-row">
            <div class="cli-col-12 cli-align-items-stretch cli-px-0">
                <div class="cli-tab-footer wt-cli-privacy-overview-actions">
                    <?php if ($the_options['cookie_setting_popup'] === true && $the_options['accept_all'] == true) : ?>

                        <?php if (apply_filters('wt_cli_enable_settings_accept_all_btn', $accept_all_btn_enable) === true) : ?>
                            <a id="wt-cli-privacy-accept-all-btn" role="button" tabindex="0" data-cli-action="accept_all" class="wt-cli-privacy-btn wt-cli-privacy-accept-all-btn cli-btn"><?php echo $accept_all_btn_text; ?></a>
                        <?php endif; ?>

                        <?php if ($accept_btn_enable === true) : ?>
                            <a id="wt-cli-privacy-save-btn" role="button" tabindex="0" data-cli-action="accept" class="wt-cli-privacy-btn cli_setting_save_button wt-cli-privacy-accept-btn cli-btn"><?php echo $accept_btn_text; ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ( apply_filters( 'wt_cli_enable_settings_accept_btn', false ) === true && $the_options['accept_all'] === false ) : ?>
								<a id="wt-cli-privacy-save-btn" role="button" tabindex="0" data-cli-action="accept" class="wt-cli-privacy-btn cli_setting_save_button wt-cli-privacy-accept-btn cli-btn"><?php echo __( 'SAVE & ACCEPT', 'cookie-law-info' ); ?></a>
					<?php endif; ?>    
                </div>
                <?php if ( apply_filters( 'wt_cli_enable_ckyes_branding', true ) === true ) : ?>
                    <div class="wt-cli-ckyes-footer-section">
                        <div class="wt-cli-ckyes-brand-logo"><?php echo __( 'Powered by', 'webtoffee-gdpr-cookie-consent' ); ?> <a target="_blank" href="https://www.cookieyes.com/"><img src="<?php echo CLI_PLUGIN_URL . 'public/images/logo-cookieyes.svg'; ?>" alt="CookieYes Logo"></a></div>
                    </div>
                 <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $pop_out = ob_get_contents();
ob_end_clean();
?>