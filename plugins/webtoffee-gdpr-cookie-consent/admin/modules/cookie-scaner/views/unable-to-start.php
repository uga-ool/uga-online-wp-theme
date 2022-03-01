<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h2><?php _e('Scan cookies', 'webtoffee-gdpr-cookie-consent'); ?></h2>
	<div class="error notice cli_notice">
		<h2><?php _e("Error",'webtoffee-gdpr-cookie-consent');?></h2>
	    <p><?php echo $error_message;?></p>
	</div>
</div>