<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<?php if ( $scan_results ) : 
		?>
		<div class="wt-cli-cookie-scan-results-container">
			<div class="wt-cli-scan-result-header">
				<div class="wt-cli-row wt-cli-align-center">
					<div class="wt-cli-col-6">
						<h2>
							<?php
							echo __('Cookie scan result for your website','webtoffee-gdpr-cookie-consent');
							// echo sprintf(
							// 	wp_kses(
							// 		__( 'Scan result for <a href="%s" target="_blank">' . $this->get_website_url() . '</a>', 'cookie-law-info' ),
							// 		array(
							// 			'a' => array(
							// 				'href'   => array(),
							// 				'target' => array(),
							// 			),
							// 		)
							// 	),
							// 	esc_url( $this->get_website_url() )
							// );
							?>
						</h2>
					</div>
					<div class="wt-cli-col-6">
						<div class="wt-cli-scan-result-actions">
						<?php if ( $scan_results['total_cookies'] > 0 ) : ?>
							<a class="button-secondary cli_export" style="margin-left:5px;" href="<?php print wp_nonce_url($export_page_url.$scan_results['scan_id'], 'cli_cookie_scaner', 'cli_cookie_scaner');?>"><?php _e('Download cookies as CSV', 'webtoffee-gdpr-cookie-consent'); ?></a>
						<?php endif; ?>
						<?php echo $this->get_scan_btn( true ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="wt-cli-scan-results-body">
			<div class="wt-cli-scan-result-summary">
				<ul class="wt-cli-scan-result-summary-list">
					<li>
						<b><?php _e( 'Total URLs', 'webtoffee-gdpr-cookie-consent' ); ?></b>: <span class="wt-cli-cookie-scan-count"> <?php echo $scan_results['total_urls']; ?></span><br />
					</li>
					<li>
						<b><?php _e( 'Total cookies', 'webtoffee-gdpr-cookie-consent' ); ?></b>: <span class="wt-cli-cookie-scan-count"> <?php echo $scan_results['total_cookies']; ?></span><br />
					</li>
				</ul>
			</div>
			<?php if ( $scan_results['total_cookies'] > 0 ) : ?>
			<div class="wt-cli-scan-result-import-section">
				<p><?php
					
					echo sprintf(
						wp_kses(
							__( 'Clicking “Add to cookie list” will import the discovered cookies to the <a href="%s" target="_blank">Cookie List</a> and thus display them in the cookie declaration section of your consent banner.', 'webtoffee-gdpr-cookie-consent' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						),
						esc_url( $cookie_list_page )
					);
					?>
					</p>
				
				<a class="button-primary cli_import" data-scan-id="<?php echo $scan_results['scan_id']; ?>" style="margin-left:5px;"><?php _e( 'Add to cookie list', 'webtoffee-gdpr-cookie-consent' ); ?></a>
			</div>
			<?php endif; ?>
			<div class="wt-cli-scan-result-cookie-container">
				<div class="wt-cli-row">
					<div class="wt-cli-col-12">
						<div class="wt-cli-scan-result-cookies">
							<?php echo $this->create_cookies_table( $scan_results['cookies'] ); ?>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	<?php else : ?>
	<?php endif; ?>
