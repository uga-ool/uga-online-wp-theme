<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.cli_pg_main{ float:left; width:100%; height:auto; min-height:600px; background:#fff; box-shadow:0px 2px 2px #ccc; border:solid 1px #ddd; margin-top:15px;}
.cli_pg_main *{box-sizing:border-box;}
.cli_pg_left{ float:left; width:25%; height:100%; background:#f3f3f3; border-right:solid 1px #cccccc;}
.cli_pg_left_menu{ float:left; width:100%; padding:10px; border-top:solid 1px #fff; border-bottom:solid 1px #ccc; cursor:pointer;}
.cli_pg_addnew_hd_btn{background:#fff !important; margin-top:5px !important; margin-right:5px !important; float:right; }
.cli_pg_right{ float:left; width:75%; padding:15px; min-height:540px; overflow:auto; font-size:12px; border-bottom:solid 1px #cccccc; }
.cli_pg_formrow{ float:left; width:100%; height:auto;}
.cli_pg_formrow label{ float:left; width:100%; height:auto; font-weight:bold; font-size:14px; margin-top:20px;}
.cli_pg_formrow .cli_pg_txt_field{ float:left; width:100%; border:1px solid #ced4da; border-radius:0.25rem; padding:0.375rem 0.75rem; font-size:1rem; line-height:1.5; margin-top:10px; }
.cli_pg_footer{ float:left; width:100%; background-color:#f3f3f3; min-height:60px; margin-top:0px; padding-bottom:10px;}
.cli_pg_main_buttons{height:28px; float:right; margin-right:15px !important; margin-top:15px !important; }
.cli_pg_content_hid, .cli_pg_samplehid_block{ display:none; }
</style>
<div class="wrap">
	<h1><?php _e('Cookie Policy generator', 'webtoffee-gdpr-cookie-consent'); ?></h1>
	<div class="cli_pg_samplehid_block">
		<div class="cli_pg_left_menu">
			<input type="checkbox" name="cli_pg_enabled_block_checkbox" checked="checked">
			<span class="cli_pg_content_hd"><?php _e('Sample heading','webtoffee-gdpr-cookie-consent'); ?></span>
			<div class="cli_pg_content_hid"><?php _e('Sample content','webtoffee-gdpr-cookie-consent'); ?></div>
			<span class="dashicons dashicons-trash cli_pg_content_delete" style="float:right;" title="<?php _e('Delete','webtoffee-gdpr-cookie-consent');?>"></span>
		</div>
	</div>
	<div class="cli_pg_main">
		<div class="cli_pg_left">
			<?php
			if(isset($default_data))
			{
				foreach($default_data as $data)
				{
					?>
					<div class="cli_pg_left_menu">
						<input type="checkbox" name="cli_pg_enabled_block_checkbox" checked="checked">
						<span class="cli_pg_content_hd"><?php echo $data['head'];?></span>
						<div class="cli_pg_content_hid"><?php echo $data['body'];?></div>
					</div>
					<?php
				}
			}
			?>
			<button class="cli_pg_addnew_hd_btn button-secondary">
				<span class="dashicons dashicons-plus" style="line-height:28px;"></span>
				<?php _e('Add new','webtoffee-gdpr-cookie-consent');?></button>
		</div>	
		<div class="cli_pg_right">
			<div class="cli_pg_formrow">
				<label><?php _e('Heading','webtoffee-gdpr-cookie-consent');?></label>
				<input type="text" class="cli_pg_txt_field" name="cli_pg_hd_field">
			</div>
			<div class="cli_pg_formrow">
				<label><?php _e('Description','webtoffee-gdpr-cookie-consent');?></label>				
				<?php
				wp_editor('','cli_pg_content',array('textarea_name'=>'cli_pg_content','editor_class'=>'cli_pg_txt_field','editor_height'=>250));
				?>
			</div>
		</div>
		<div class="cli_pg_footer">
			<?php
			$policy_pageid=$this->get_cookie_policy_pageid();
			$update_exists_page_visibility='display:none;';
			if($policy_pageid)
			{
				$policy_page_status=get_post_status($policy_pageid);
				if($policy_page_status && $policy_page_status!='trash')
				{
					$update_exists_page_visibility='';
				}
			}
			?>
			<a name="cli_pg_save_currentpage" style="<?php echo $update_exists_page_visibility;?>" class="button-primary cli_pg_main_buttons">
				<span class="dashicons dashicons-yes" style="line-height: 28px;"></span>
				<?php _e('Update existing Cookie Policy page','webtoffee-gdpr-cookie-consent');?>
			</a>
			
			<input type="hidden" name="cli_pg_policy_pageid" value="<?php echo $policy_pageid ? $policy_pageid : 0 ?>">
			<a name="cli_pg_save_newpage" class="button-primary cli_pg_main_buttons">
				<span class="dashicons dashicons-welcome-add-page" style="line-height: 28px;"></span>
				<?php _e('Create Cookie Policy page','webtoffee-gdpr-cookie-consent');?>
			</a>
			<a name="cli_pg_live_preview" class="button-secondary cli_pg_main_buttons" href="<?php echo $preview_url;?>" target="_blank">
				<span class="dashicons dashicons-external" style="line-height: 28px;"></span>
				<?php _e('Live preview','webtoffee-gdpr-cookie-consent');?>
			</a>		
		</div>	
	</div>
</div>