<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cli_theme_customizebutton" style=" float:right; width:50%; display:none; padding:10px 0px;">
	<button type="button" name="cli_activate_theme" class="button-primary" style="height: 28px; float: right;">
	<span class="dashicons dashicons-yes" style="line-height: 28px;"></span> <?php _e('Save & Publish','webtoffee-gdpr-cookie-consent');?></button>
<!--
	<button name="" class="button-secondary" style="height: 28px; float: right;  margin-right: 5px;">
	<span class="dashicons dashicons-download" style="line-height: 28px;"></span> Export</button>

	<button name="" class="button-secondary" style="height: 28px; float: right;  margin-right: 5px;">
	<span class="dashicons dashicons-upload" style="line-height: 28px;"></span> Import</button> -->

	<button type="button" name="" class="button-secondary cli_theme_show_themes" style="float: right;  margin-right: 5px;">
	<span class="dashicons dashicons-admin-appearance" style="line-height: 28px;"></span>
	 <?php _e('Change Template','webtoffee-gdpr-cookie-consent');?>
	</button>

	<button type="button" name="" class="button-secondary" style="float: right;  margin-right: 5px;" onclick="window.location.reload(true);">
	<span class="dashicons dashicons-no-alt" style="line-height: 28px;"></span> 
	<?php _e('Cancel','webtoffee-gdpr-cookie-consent');?>
	</button>
	<span class="spinner" style="margin-top:5px; float:right;"></span>
</div>