<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cli_themesidebox" id="cli_themesidebox">
	<div class="cli_themesidebox_head cli_noselect" id="cli_themesideboxheader">
		<?php _e('Properties','webtoffee-gdpr-cookie-consent'); ?> <span class="cli_theme_property_namehd"></span>
		<div class="cli_themesidebox_head_close">X</div>
		<div class="cli_themesidebox_head_resize">-</div>			
	</div>
	<div class="cli_themesidebox_content">
		<div style="width:100%; float: left; height: auto;" class="cli_theme_item_text">
			<div class="cli_theme_form_group" style="width: 67%">
				<b><?php _e('Text','webtoffee-gdpr-cookie-consent'); ?></b>
				<input type="text" class="cli_theme_input cli_theme_txt_input" name="cli_theme_text" 
				data-type="text" data-unit="" value="">
			</div>
		</div>
		<div class="cli_theme_form_group">
			<b><?php _e('Text size','webtoffee-gdpr-cookie-consent'); ?> (px)</b>
			<input type="number" class="cli_theme_input cli_theme_num_input" name="cli_theme_fontsize" 
			data-type="font-size" data-unit="px" value="" step="1" min="5" max="100">
		</div>			
		<div class="cli_theme_form_group" style="width: 67%">
			<b><?php _e('Text color','webtoffee-gdpr-cookie-consent'); ?></b>
			<input type="text" class="cli_theme_input cli_theme-color-field" data-type="color" data-unit="" name="cli_theme_tc" value="">
		</div>
		<div class="cli_theme_form_group">
			<b><?php _e('Font weight','webtoffee-gdpr-cookie-consent'); ?></b>
			<select class="cli_theme_input cli_theme_num_input" name="cli_theme_fw" data-type="font-weight" data-unit="">
				<option value="normal">Normal</option>
				<option value="bold">Bold</option>
				<option value="bolder">Bolder</option>
				<option value="lighter">Lighter</option>
				<?php
				for($i=100; $i<=900; $i=$i+100)
				{
					?>
					<option value="<?php echo $i;?>"><?php echo $i;?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="cli_theme_form_group" style="width: 67%">
			<b><?php _e('Background','webtoffee-gdpr-cookie-consent'); ?></b>
			<input type="text" class="cli_theme_input cli_theme-color-field" data-type="background-color" data-unit="" name="cli_theme_bgc" value="">
		</div>
		<div class="cli_theme_form_group">
			<b><?php _e('Border width','webtoffee-gdpr-cookie-consent'); ?></b>
			<input type="number" class="cli_theme_input cli_theme_num_input" name="cli_theme_bw" data-type="border-width" data-unit="px" value="" step="1" min="0" max="100">
		</div>
		<div class="cli_theme_form_group" style="width:67%">
			<b><?php _e('Border Color','webtoffee-gdpr-cookie-consent'); ?></b>
			<input type="text" class="cli_theme_input cli_theme-color-field" data-type="border-color" data-unit="" name="cli_theme_bc" value="">
		</div>
		<div class="cli_theme_form_group">
			<b><?php _e('Corner radius','webtoffee-gdpr-cookie-consent'); ?></b>
			<input type="number" class="cli_theme_input cli_theme_num_input" name="cli_theme_cr" data-type="border-radius" data-unit="px" value="" step="1" min="0" max="100">
		</div>
		<div class="cli_theme_form_group" style="width:67%;">
			<b><?php _e('Text style','webtoffee-gdpr-cookie-consent'); ?></b> <br />
			<div style="float: left;width: 100%; padding-top: 10px;">
				<input type="checkbox" name="" value="1" id="cli_theme_italic_chk" data-type="font-style" data-unit="" data-on="italic" data-off="normal" class="cli_theme_check_input"> 
				<label for="cli_theme_italic_chk"><?php _e('Italic','webtoffee-gdpr-cookie-consent'); ?></label> &nbsp; &nbsp;
				<input type="checkbox" name="" value="1" id="cli_theme_underline_chk" data-type="text-decoration" data-unit="" data-on="underline" data-off="none" class="cli_theme_check_input"> 
				<label for="cli_theme_underline_chk"><?php _e('Underline','webtoffee-gdpr-cookie-consent'); ?></label>
			</div>
		</div>			
		
		
		<div class="cli_theme_form_group" style="width: 100%; padding:0px 2px; height: auto;">
			<b>CSS</b>
			<div class="cli_theme_css_box">
				<b>.cli_style{</b>
				<textarea class="cli_theme_css_txt" name="cli_theme_custm_css"></textarea>
				<b>}</b>
			</div>
		</div>
		<div class="cli_theme_form_group" style="width:100%; height: auto; padding-top:10px;">
			<input type="button" name="cli_theme_prop_apply" value="<?php _e('Apply','webtoffee-gdpr-cookie-consent'); ?>" class="button-primary" style="height: 28px; float: right;">
			<!-- <input type="button" name="" value="Cancel" class="button-secondary" style=" float: right; margin-right: 5px;">	-->			
		</div>
	</div>
</div>