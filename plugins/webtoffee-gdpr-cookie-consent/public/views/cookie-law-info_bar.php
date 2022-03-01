<?php 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Disable indexing of CookieLawInfo Cookie data

echo '<div class="wt-cli-cookie-bar-container" data-nosnippet="true">';
echo "<!--googleoff: all-->";
echo $notify_html;
?>
<?php if($the_options['cookie_setting_popup'] == true ) { ?>
<div class="cli-modal" id="cliSettingsPopup" role="dialog" aria-labelledby="wt-cli-privacy-title" tabindex="-1" aria-hidden="true">
  <div class="cli-modal-dialog" role="document">
    <div class="cli-modal-content cli-bar-popup">
      <button aria-label="<?php _e('Close', 'webtoffee-gdpr-cookie-consent'); ?>" type="button" class="cli-modal-close" id="cliModalClose">
      <svg class="" viewBox="0 0 24 24"><path d="M19 6.41l-1.41-1.41-5.59 5.59-5.59-5.59-1.41 1.41 5.59 5.59-5.59 5.59 1.41 1.41 5.59-5.59 5.59 5.59 1.41-1.41-5.59-5.59z"></path><path d="M0 0h24v24h-24z" fill="none"></path></svg>
      <span class="wt-cli-sr-only"><?php _e('Close', 'webtoffee-gdpr-cookie-consent'); ?></span>
      </button>
        <?php 
          do_action('wt_cli_settings_popup');
        ?>
    </div>
  </div>
</div>
<?php } ?>
<div class="cli-modal-backdrop cli-fade cli-settings-overlay"></div>
<div class="cli-modal-backdrop cli-fade cli-popupbar-overlay"></div>
<?php 
// Re-enable indexing
echo "<!--googleon: all-->";?>
</div>
<?php 
