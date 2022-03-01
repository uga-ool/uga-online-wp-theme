<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}
?>
<div class="cookie-law-info-tab-content" data-id="<?php echo $target_id; ?>">
	<ul class="cli_sub_tab">
		<li style="border-left:none; padding-left: 0px;" data-target="cookie-bar"><a><?php _e('General', 'webtoffee-gdpr-cookie-consent'); ?></a></li>
		<li data-target="other"><a><?php _e('Other', 'webtoffee-gdpr-cookie-consent'); ?></a></li>
	</ul>
	<div class="cli_sub_tab_container">
		<div class="cli_sub_tab_content" data-id="cookie-bar" style="display:block;">
			<div class="wt-cli-section wt-cli-section-general-settings">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="is_on_field"><?php _e('Enable cookie bar', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
						<td>
							<input type="radio" id="is_on_field_yes" name="is_on_field" class="styled cli_bar_on" value="true" <?php echo ($the_options['is_on'] == true) ? ' checked="checked"' : ''; ?> /><?php _e('On', 'webtoffee-gdpr-cookie-consent'); ?>
							<input type="radio" id="is_on_field_no" name="is_on_field" class="styled" value="false" <?php echo ($the_options['is_on'] == false) ? ' checked="checked" ' : ''; ?> /><?php _e('Off', 'webtoffee-gdpr-cookie-consent'); ?>
						</td>
					</tr>
					<?php do_action('wt_cli_before_cookie_message'); ?>
				</table>
			</div>
			<div class="wt-cli-section wt-cli-section-gdpr-ccpa">
					<div class="wt-cli-section-inner wt-cli-section-inner-gdpr">
						<h3><?php _e('GDPR Settings', 'webtoffee-gdpr-cookie-consent'); ?><span class="wt-cli-tootip" data-wt-cli-tooltip="<?php _e('GDPR regulation lays down rules relating to the protection of natural persons with regard to the processing of personal data and rules relating to the free movement of personal data.', 'webtoffee-gdpr-cookie-consent'); ?>"><span class="wt-cli-tootip-icon"></span></span></h3>
						<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="is_eu_on_field"><?php _e('Show only for EU Countries ( GeoIP )', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
							<td>
								<input type="radio" id="is_eu_on_field_yes" name="is_eu_on_field" class="styled" value="true" <?php echo ($the_options['is_eu_on'] == true) ? ' checked="checked"' : ''; ?> /> <?php _e('On', 'webtoffee-gdpr-cookie-consent'); ?>
								<input type="radio" id="is_eu_on_field_no" name="is_eu_on_field" class="styled" value="false" <?php echo ($the_options['is_eu_on'] == false) ? ' checked="checked"' : ''; ?> /> <?php _e('Off', 'webtoffee-gdpr-cookie-consent'); ?>
							</td>
						</tr>
						</table>
					</div>
					<div class="wt-cli-section-inner wt-cli-section-inner-ccpa">
						<?php do_action('wt_cli_ccpa_settings'); ?>
					</div>
				</div>
			<div class="wt-cli-section-advanced-settings">
				<div class="wt-cli-accordion-tab wt-cli-accordion-advanced-settings active" >
					<a  href="#" class="active"><?php echo __('More', 'webtoffee-gdpr-cookie-consent'); ?></a>
					<div class="wt-cli-accordion-content active" style="display:block;">
						<table class="form-table">
							
							<!-- SHOW ONCE / TIMER -->
							<tr valign="top">
								<th scope="row"><label for="show_once_yn_field"><?php _e('Auto-hide(Accept) cookie bar after delay?', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
								<td>
									<input type="radio" id="show_once_yn_yes" name="show_once_yn_field" class="styled cli_form_toggle" cli_frm_tgl-target="cli_bar_autohide" value="true" <?php echo ($the_options['show_once_yn'] == true) ? ' checked="checked"' : ''; ?> /> <?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
									<input type="radio" id="show_once_yn_no" name="show_once_yn_field" class="styled cli_form_toggle" cli_frm_tgl-target="cli_bar_autohide" value="false" <?php echo ($the_options['show_once_yn'] == false) ? ' checked="checked"' : ''; ?> /> <?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>
								</td>
							</tr>
							<tr valign="top" cli_frm_tgl-id="cli_bar_autohide" cli_frm_tgl-val="true">
								<th scope="row"><label for="show_once_field"><?php _e('Milliseconds until hidden', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
								<td>
									<input type="text" name="show_once_field" value="<?php echo $the_options['show_once'] ?>" />
									<span class="cli_form_help"><?php _e('Specify milliseconds (not seconds)', 'webtoffee-gdpr-cookie-consent'); ?> e.g. 8000 = 8 <?php _e('seconds', 'webtoffee-gdpr-cookie-consent'); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="scroll_close_field"><?php _e('Auto-hide cookie bar if the user scrolls ( Accept on Scroll )?', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
								<td>
									<input type="radio" id="scroll_close_yes" name="scroll_close_field" class="styled" value="true" <?php echo ($the_options['scroll_close'] == true) ? ' checked="checked"' : ''; ?> /> <?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
									<input type="radio" id="scroll_close_no" name="scroll_close_field" class="styled" value="false" <?php echo ($the_options['scroll_close'] == false) ? ' checked="checked"' : ''; ?> /> <?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>
									<span class="cli_form_help" style="margin-top:8px;"><?php _e('As per latest GDPR policies it is required to take an explicit consent for the cookies. Use this option with discretion especially if you serve EU', 'webtoffee-gdpr-cookie-consent'); ?></span>
									<span class="cli_form_er cli_scroll_accept_er"><?php _e('This option will not work along with `Popup overlay`.', 'webtoffee-gdpr-cookie-consent'); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="accept_close_page_navigation_field"><?php _e('Auto-hide(Accept) cookie bar on-page navigation.', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
								<td>
									<input type="radio" id="accept_close_page_navigation_yes" name="accept_close_page_navigation_field" class="styled" value="true" <?php echo ($the_options['accept_close_page_navigation'] == true) ? ' checked="checked"' : ''; ?> /> <?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
									<input type="radio" id="accept_close_page_navigation_no" name="accept_close_page_navigation_field" class="styled" value="false" <?php echo ($the_options['accept_close_page_navigation'] == false) ? ' checked="checked"' : ''; ?> /> <?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>
									<span class="cli_form_help" style="margin-top:8px;"><?php _e('Auto accept the cookie consent if the user navigates to other pages', 'webtoffee-gdpr-cookie-consent'); ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>

			</div>
		</div>
		<div class="cli_sub_tab_content" data-id="other">
			<h3><?php _e('Other', 'webtoffee-gdpr-cookie-consent'); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="logging_on_field"><?php _e('Enable consent logging', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
					<td>
						<input type="radio" id="logging_on_field_yes" name="logging_on_field" class="styled" value="true" <?php echo ($the_options['logging_on'] == true) ? ' checked="checked" />' : ' />'; ?> <?php _e('On', 'webtoffee-gdpr-cookie-consent'); ?> <input type="radio" id="logging_on_field_no" name="logging_on_field" class="styled" value="false" <?php echo ($the_options['logging_on'] == false) ? ' checked="checked" ' : ' '; ?> /><?php _e('Off', 'webtoffee-gdpr-cookie-consent'); ?>
					</td>
				</tr>
				<tr valign="top" class="">
					<th scope="row"><label for="scroll_close_reload_field"><?php _e('Reload after "scroll accept" event?', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
					<td>
						<input type="radio" id="scroll_close_reload_yes" name="scroll_close_reload_field" class="styled" value="true" <?php echo ($the_options['scroll_close_reload'] == true) ? ' checked="checked" ' : ' '; ?> /> <?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
						<input type="radio" id="scroll_close_reload_no" name="scroll_close_reload_field" class="styled" value="false" <?php echo ($the_options['scroll_close_reload'] == false) ? ' checked="checked" ' : ''; ?> /> <?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>

					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="accept_close_reload_field"><?php _e('Reload after Accept button click', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
					<td>
						<input type="radio" id="accept_close_reload_yes" name="accept_close_reload_field" class="styled" value="true" <?php echo ($the_options['accept_close_reload'] == true) ? ' checked="checked" ' : ''; ?> /><?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
						<input type="radio" id="accept_close_reload_no" name="accept_close_reload_field" class="styled" value="false" <?php echo ($the_options['accept_close_reload'] == false) ? ' checked="checked" ' : ''; ?> /><?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="reject_close_reload_field"><?php _e('Reload after Reject button click', 'webtoffee-gdpr-cookie-consent'); ?></label></th>
					<td>
						<input type="radio" id="reject_close_reload_yes" name="reject_close_reload_field" class="styled" value="true" <?php echo ($the_options['reject_close_reload'] == true) ? ' checked="checked" ' : ''; ?> /><?php _e('Yes', 'webtoffee-gdpr-cookie-consent'); ?>
						<input type="radio" id="reject_close_reload_no" name="reject_close_reload_field" class="styled" value="false" <?php echo ($the_options['reject_close_reload'] == false) ? ' checked="checked" ' : ''; ?> /><?php _e('No', 'webtoffee-gdpr-cookie-consent'); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php
	include "admin-settings-save-button.php";
	?>
</div>