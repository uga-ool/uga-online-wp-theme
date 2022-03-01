<?php
/**
 * Template options
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cookie_Law_Info_Cli_Themes {


	public static $current_preview_id     = 'banner_1';
	public static $current_preview_themes = array();

	/*
	* button names and corresponding shortcodes
	*/
	public $btn_shortcodes = array(
		'button_1' => '[cookie_button]', // Accept
		'button_2' => '[cookie_link]', // Read More
		'button_3' => '[cookie_reject]', // Reject
		'button_4' => '[cookie_settings]', // Settings
		'button_5' => '[cookie_close]', // Close
		'button_7' => '[cookie_accept_all]', // Close
	);

	public static $short_code_arr = array( '[cookie_button]', '[cookie_link]', '[cookie_reject]', '[cookie_settings]', '[cookie_close]', '[cookie_accept_all]', '[wt_cli_category_widget]', '[wt_cli_ccpa_optout]' );
	// do not change the order of values in the array.
	public static $title_arr = array( 'Accept', 'Read more', 'Reject', 'Settings', 'Close', '', 'Accept All' );

	/**
	 * Initialization
	 *
	 * @since 2.1.5
	 **/
	public function __construct() {
		 // =====Plugin settings page Hooks=====
		add_action( 'admin_init', array( __CLASS__, 'save_settings' ) );
		add_filter( 'cli_module_settings_tabhead', array( __CLASS__, 'settings_tabhead' ) );
		add_action( 'cli_module_out_settings_form', array( $this, 'out_settings_form' ) );
		$this->htmlscanner();
		add_action( 'wp_footer', array( __CLASS__, 'live_preview' ) );
		add_action( 'wp_ajax_cli_theme', array( $this, 'ajax_cli_theme' ) );
		register_activation_hook( CLI_PLUGIN_FILENAME, array( $this, 'activator' ) );
		add_action( 'cli_module_save_settings', array( $this, 'update_style_attributes' ) );

	}
	public function activator() {
		$this->update_style_attributes();
	}

	public function htmlscanner() {
		 include_once plugin_dir_path( __FILE__ ) . 'classes/class-htmlscanner.php';
	}
	/*
	* Update style from bar message to options table
	* @since 2.1.9
	*/
	public function update_style_attributes() {
		$arr_out     = array();
		$margin      = '';
		$the_options = Cookie_Law_Info::get_settings();
		if ( isset( $_POST['notify_message_field'] ) ) {
			$notify_message = stripslashes( $_POST['notify_message_field'] );

		} else {

			$notify_message = stripslashes( $the_options['notify_message'] );
		}
		$notify_message  = Cookie_Law_Info::sanitise_settings( 'notify_message', $notify_message );
		$style_arr       = array();
		$settings_params = array(
			'bar'      => array(
				'background'  => 'background-color',
				'text'        => 'color',
				'font_family' => 'font-family',
			),
			'button_1' => array(
				'button_1_button_colour' => 'background-color',
				'button_1_link_colour'   => 'color',
			),
			'button_2' => array(
				'button_2_button_colour' => 'background-color',
				'button_2_link_colour'   => 'color',
			),
			'button_3' => array(
				'button_3_button_colour' => 'background-color',
				'button_3_link_colour'   => 'color',
			),
			'button_4' => array(
				'button_4_button_colour' => 'background-color',
				'button_4_link_colour'   => 'color',
			),
			'button_5' => array( // we are skipping `close_button` (button_5) because it has no tab in settings

			),
			'button_7' => array(
				'button_7_button_colour' => 'background-color',
				'button_7_link_colour'   => 'color',
			),
		);
		for ( $e = 1; $e <= 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$btn_name        = 'button_' . $e;
			$cli_current_btn = $this->btn_shortcodes[ $btn_name ];
			$cli_current_btn = str_replace( array( '[', ']' ), '', $cli_current_btn );
			preg_match( '/\[' . $cli_current_btn . '( )(.*?)\]/', $notify_message, $arr_out );

			if ( $arr_out ) {
				preg_match( '/(margin).*?(=).*?(").*?(")/', $arr_out[0], $margin );
				if ( $margin ) {
					$reg_pattern[0]     = '/=/';
					$reg_pattern[1]     = '/"/';
					$reg_replacement[0] = ':';
					$reg_replacement[1] = '';
					$margin             = preg_replace( $reg_pattern, $reg_replacement, $margin[0] );
					$this->update_common_settings_vars( $this->process_css( $margin ), $the_options, $btn_name, $settings_params );

				}
			}
		}
		$filtered_shortcodes = str_replace( array( '[', ']' ), '', $this->btn_shortcodes );
			// Replace cookie bar button shortcode with HTML
		foreach ( $filtered_shortcodes as $row ) {
			$pattern        = '/\[' . $row . ' (.*?)\]/';
			$replacement    = '[' . $row . '] ';
			$notify_message = preg_replace( $pattern, $replacement, $notify_message );
		}
		if ( isset( $_POST['notify_message_field'] ) ) {
			$_POST['notify_message_field'] = $notify_message;
		} else {
			$the_options['notify_message'] = Cookie_Law_Info::sanitise_settings( 'notify_message', $notify_message );

		}
		update_option( CLI_SETTINGS_FIELD, $the_options );
		Cookie_Law_Info::$stored_options = $the_options;
	}
	public static function cli_themeblock( $data ) {
		$config          = $data['config'];
		$plugin_options  = Cookie_Law_Info::get_settings();
		$ccpa_colour     = Cookie_Law_Info::sanitise_settings( 'button_6_link_colour', ( isset( $plugin_options['button_6_link_colour'] ) ? $plugin_options['button_6_link_colour'] : '#000000' ) );
		$cookie_button   = '';
		$cookie_link     = '';
		$cookie_settings = '';
		$cookie_close    = '';
		$cookie_reject   = '';
		$bar_hd          = '';
		$short_code_arr  = self::$short_code_arr; // do not change the order of values in the array. It is depend on the below loop
		$title_arr       = self::$title_arr;
		$short_code_html = array();
		$btn_html_arr    = array();
		$wt_cli_widget   = '';

		for ( $e = 1; $e <= 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$btn_name          = 'button_' . $e;
			$btnconf           = $config[ $btn_name ];
			$btn_html          = '<a class="cli_theme_' . $btn_name . '" data-cli-id="cli_theme_' . $btn_name . '" data-cli-bartype="' . $config['bar']['type'] . '" style="' . $config[ $btn_name ]['style'] . '" title="' . $title_arr[ $e - 1 ] . '">' . $config[ $btn_name ]['text'] . '</a>';
			$short_code_html[] = $btnconf['status'] == 1 ? $btn_html : '';
			$btn_html_arr[]    = $btn_html;
		}
		$bar_hd_html = '<h5 style="' . $config['heading']['style'] . '" data-cli-id="cli_theme_hd" class="cli_theme_hd" data-cli-bartype="' . $config['bar']['type'] . '" title="Cookie Bar heading">' . $config['heading']['text'] . '</h5>';
		$bar_hd      = $config['heading']['status'] == 1 ? $bar_hd_html : '';
		$bar_content = $bar_hd . $config['bar']['text'];

		$the_cookie_list = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
		$wt_cli_widget  .= '<span class="wt-cli-category-widget">';
		foreach ( $the_cookie_list as $key => $cookie ) {
			$checked = '';
			if ( true === $cookie['default_state'] ) {
				$checked = 'checked';
			}
			if ( true === $cookie['strict'] ) {
				$checked = 'checked' . ' ' . 'disabled';
			}
			$wt_cli_widget .= '<span class="wt-cli-form-group wt-cli-custom-checkbox"><input type="checkbox" class="cli-user-preference-checkbox" aria-label="' . $cookie['title'] . '" data-id="checkbox-' . $key . '" id="checkbox-' . $key . '" ' . $checked . ' ><label for="checkbox-' . $key . '">' . $cookie['title'] . '</label></span>';
		}
		$wt_cli_widget .= '</span>';

		// CCPA Widget
		$wt_cli_ccpa_widget = '';
		$ccpa_enabled       = wp_validate_boolean( ( isset( $plugin_options['ccpa_enabled'] ) ? $plugin_options['ccpa_enabled'] : false ) );

		if ( $ccpa_enabled !== false ) {
			$ccpa_as_link = wp_validate_boolean( ( isset( $plugin_options['button_6_as_link'] ) ? $plugin_options['button_6_as_link'] : false ) );
			$ccpa_text    = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( ( isset( $plugin_options['button_6_text'] ) ? $plugin_options['button_6_text'] : '' ) );
			if ( $ccpa_as_link === false ) {

				$wt_cli_ccpa_widget = '<span class="wt-cli-form-group wt-cli-custom-checkbox wt-cli-ccpa-checkbox"><input type="checkbox" id="wt-cli-ccpa-opt-out" class="wt-cli-ccpa-opt-out wt-cli-ccpa-opt-out-checkbox" ><label for="wt-cli-ccpa-opt-out" style="color:' . $ccpa_colour . ';" >' . $ccpa_text . '</label></span>';

			} else {
				$wt_cli_ccpa_widget = '<a href="javascript:void(0)" class="wt-cli-ccpa-opt-out" style="color:' . $ccpa_colour . ';">' . $ccpa_text . '</a>';
			}
		}
		if ( strpos( $bar_content, '[wt_cli_ccpa_optout]' ) !== false ) {
			$bar_content = str_replace( '[wt_cli_ccpa_optout]', $wt_cli_ccpa_widget, $bar_content );
		}
		// End CCPA
		if ( strpos( $bar_content, '[wt_cli_category_widget]' ) !== false ) {
			$bar_content = str_replace( '[wt_cli_category_widget]', $wt_cli_widget, $bar_content );
		}

		// we need to add the [cookie_close] shortcode before heading tag
		if ( strpos( $bar_content, '[cookie_close]' ) !== false ) {
			$bar_content = str_replace( '[cookie_close]', '', $bar_content );
			$bar_content = '[cookie_close]' . $bar_content;
		}
		$bar_content = str_replace( $short_code_arr, $short_code_html, stripslashes( $bar_content ) );
		?>
		<div style="<?php echo $config['bar']['style']; ?>" title="Cookie Bar" data-cli-id="cli_theme_bar" class="cli_theme_bar wt-cli-cookie-bar" data-cli-type="<?php echo $config['bar']['type']; ?>" data-cli-bartype="<?php echo $config['bar']['type']; ?>">
			<?php echo nl2br( $bar_content ); ?>
		</div>
		<div style="display:none;" class="cli_theme_buttons">
			<?php
			echo $bar_hd;
			echo implode( ' ', $btn_html_arr );
			?>
		</div>
		<div style="display:none;" class="cli_theme_bar_txt"><?php echo $config['bar']['text']; ?></div>
		<?php
	}

	public static function add_admin_menu( $wp_admin_bar ) {
		$args = array(
			'id'    => 'cli-preview-change-template',
			'title' => __( 'Change GDPR cookie preview template', 'webtoffee-gdpr-cookie-consent' ),
			'href'  => '',
			'meta'  => array(
				'class' => 'cli-preview-change-template',
			),
		);
		$wp_admin_bar->add_node( $args );

		$theme_arr = array();
		foreach ( self::$current_preview_themes as $themek => $theme ) {
			$themearr                      = explode( '_', $themek );
			$theme_arr[ end( $themearr ) ] = $theme;
		}
		asort( $theme_arr );
		foreach ( $theme_arr as $k => $v ) {
			$theme_id        = $v['config']['bar']['type'] . '_' . $k;
			$theme_sub_class = self::$current_preview_id == $theme_id ? 'cli_preview_template_sub_active' : 'cli_preview_template_sub';
			$args            = array(
				'id'     => 'cli-preview-change-template-' . $k,
				'title'  => $v['title'],
				'href'   => home_url() . '?cli_live_theme_preview=' . $theme_id,
				'parent' => 'cli-preview-change-template',
				'meta'   => array(
					'class' => $theme_sub_class,
				),
			);
			$wp_admin_bar->add_node( $args );
		}

	}

	/**
	 *  Updating theme text with user text in live preview bar
	 **/
	public static function update_preview_text( $the_options, $theme_arr ) {
		// bar text
		$usr_txt   = self::get_plain_text( stripslashes( $the_options['notify_message'] ) );
		$theme_txt = self::get_plain_text( stripslashes( $theme_arr['config']['bar']['text'] ) );

		$theme_arr['config']['bar']['text'] = str_replace( $theme_txt, $usr_txt, $theme_arr['config']['bar']['text'] );
		// update button and heading text with current text
		for ( $e = 1; $e < 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$theme_arr['config'][ 'button_' . $e ]['text'] = isset( $the_options[ 'button_' . $e . '_text' ] ) ? $the_options[ 'button_' . $e . '_text' ] : '';
		}

		$theme_arr['config']['heading']['text'] = $the_options['bar_heading_text'];
		return $theme_arr;
	}

	/**
	 *  Removing known shortcodes from text
	 **/
	public static function get_plain_text( $txt ) {
		return trim( str_replace( self::$short_code_arr, '', $txt ) );
	}

	/**
	 *  Showing live preview
	 **/
	public static function live_preview() {
		if ( isset( $_GET['cli_live_theme_preview'] ) && trim( $_GET['cli_live_theme_preview'] ) != '' && current_user_can( 'administrator' ) ) {
			$allowed_types              = array( 'banner', 'popup', 'widget' );
			$cli_live_theme_preview     = Wt_Cookie_Law_Info_Security_Helper::sanitize_item( $_GET['cli_live_theme_preview'] );
			$cli_live_theme_preview_arr = explode( '_', $cli_live_theme_preview );
			if ( count( $cli_live_theme_preview_arr ) == 2 && in_array( $cli_live_theme_preview_arr[0], $allowed_types ) ) {
				include plugin_dir_path( __FILE__ ) . 'data/data.themes_' . $cli_live_theme_preview_arr[0] . '.php';
				$theme_var_name = 'cli_theme_' . implode( '', $cli_live_theme_preview_arr );
				if ( isset( $$theme_var_name ) ) {
					$the_options = Cookie_Law_Info::get_settings();
					$theme_arr   = 'cli_themes_' . $cli_live_theme_preview_arr[0];

					// adding admin bar menu to change the template
					self::$current_preview_id     = $cli_live_theme_preview;
					self::$current_preview_themes = $$theme_arr;
					add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_menu' ), 100 );

					// updating text with user text and generate bar html
					self::cli_themeblock( self::update_preview_text( $the_options, $$theme_var_name ) );
					?>
					<style type="text/css">
						.cli-preview-change-template .ab-empty-item{font-weight:bold !important; color:red !important; }
						.cli_preview_template_sub_active .ab-item{font-weight:bold !important; color:white !important;}
					</style>
					<script type="text/javascript">
						jQuery(document).ready(function(){							
							var cli_theme_preview_elm=jQuery('.cli_theme_bar');
							var cli_again_elm=jQuery('#cookie-law-info-again');
							var cli_bar_elm=jQuery('#cookie-law-info-bar');
							cli_theme_preview_elm.css({'display':'block','position':'fixed','z-index':'10000','box-shadow':'rgba(0,0,0,.5) 0px 5px 10px'});
							<?php
							if ( $cli_live_theme_preview_arr[0] == 'widget' ) {
								?>
								cli_theme_preview_elm.css({'bottom':'20px','<?php echo $the_options['widget_position']; ?>':'20px'});
								setTimeout(function(){
									jQuery('body').removeClass("cli-barmodal-open");
									jQuery(".cli-popupbar-overlay").removeClass("cli-show");
								},100);
								<?php
							} elseif ( $cli_live_theme_preview_arr[0] == 'banner' ) {
								$tp_vl  = '0px';
								$pos_vl = 'fixed';
								if ( $the_options['notify_position_vertical'] == 'top' ) {
									$tp_vl  = '25px'; // to adjust the admin bar
									$pos_vl = $the_options['header_fix'] === false ? 'absolute' : $pos_vl;
								}
								?>
								cli_theme_preview_elm.css({'<?php echo $the_options['notify_position_vertical']; ?>':'<?php echo $tp_vl; ?>','left':'0px','position':'<?php echo $pos_vl; ?>'});
								setTimeout(function(){
									jQuery('body').removeClass("cli-barmodal-open");
									jQuery(".cli-popupbar-overlay").removeClass("cli-show");
								},100);

								<?php
							} else {
								?>
								var cli_win=jQuery(window);
								var cli_winh=cli_win.height()-40;
								var cli_winw=cli_win.width();
								var cli_defw=cli_winw>700 ? 500 : cli_winw-20;
								cli_theme_preview_elm.css({
									'width':cli_defw,'height':'auto','max-height':cli_winh,'bottom':'','top':'50%','left':'50%','margin-left':(cli_defw/2)*-1,'margin-top':'-100px','overflow':'auto'
								});
								
								var cli_h=cli_theme_preview_elm.height();
								var li_h=(cli_h<200 ? 200 : cli_h);
								cli_theme_preview_elm.css({'top':'50%','margin-top':((cli_h/2)+30)*-1});	

								cli_h=cli_theme_preview_elm.height();
								li_h=cli_h<200 ? 200 : cli_h;
								cli_theme_preview_elm.css({'top':'50%','margin-top':((cli_h/2)+30)*-1});
								<?php
								if ( $the_options['popup_overlay'] ) {
									?>
									jQuery('body').addClass("cli-barmodal-open");
									jQuery(".cli-popupbar-overlay").addClass("cli-show");
									<?php
								}
							}
							?>
							if(jQuery.trim(jQuery('.cli_theme_bar').find('.cli_theme_hd').html())=="")
							{
								jQuery('.cli_theme_bar').find('.cli_theme_hd').css({'display':'none'});
							}
							cli_again_elm.remove();
							cli_bar_elm.remove();
						});
					</script>
					<?php
				}
			}
		}
	}

	/**
	 *  =====Plugin settings page Hook=====
	 *  Tab head for settings page
	 **/
	public static function settings_tabhead( $arr ) {
		$arr['cookie-law-info-themes'] = 'Themes';
		return $arr;
	}

	/**
	 *  =====Plugin settings page Hook=====
	 * save settings hook
	 **/
	public static function save_settings() {
	}

	/**
	 *  =====Plugin settings page Hook=====
	 * settings form
	 * Do not include a form, its already inside a form
	 **/
	public function settings_form() {
	}

	/**
	 *
	 * Ajax hook to save current theme
	 **/
	public function ajax_cli_theme() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permission to perform this operation', 'webtoffee-gdpr-cookie-consent' ) );
		}
		// Check nonce:
		check_admin_referer( 'cli_theme', 'security' );
		// Get options:
		$the_options  = Cookie_Law_Info::get_settings();
		$consent_type = Cookie_Law_Info::sanitise_settings( 'consent_type', ( isset( $the_options['consent_type'] ) ? $the_options['consent_type'] : 'gdpr' ) );
		// not allowed params
		$param_to_trim = array(
			'banner' => array(
				'width', // our bar is responsive so width is not allowed
				'height', // our bar is responsive so height is not allowed
			),
			'widget' => array(
				'width', // our bar is responsive so width is not allowed
				'height', // our bar is responsive so height is not allowed
			),
			'popup'  => array(
				'width', // our bar is responsive so width is not allowed
				'height', // our bar is responsive so height is not allowed
				'display',
			),
		);

		// params that are already available in settings
		// array format: [css value=> settings json value]
		$settings_params = array(
			'bar'      => array(
				// 'background'=>'background',
				'background-color' => 'background',
				'color'            => 'text',
				'font-family'      => 'font_family',
			),
			'button_1' => array(
				// 'background'=>'button_1_button_colour',
				'background-color' => 'button_1_button_colour',
				'color'            => 'button_1_link_colour',
			),
			'button_2' => array(
				// 'background'=>'button_2_button_colour',
				'background-color' => 'button_2_button_colour',
				'color'            => 'button_2_link_colour',
			),
			'button_3' => array(
				// 'background'=>'button_3_button_colour',
				'background-color' => 'button_3_button_colour',
				'color'            => 'button_3_link_colour',
			),
			'button_4' => array(
				// 'background'=>'button_4_button_colour',
				'background-color' => 'button_4_button_colour',
				'color'            => 'button_4_link_colour',
			),
			'button_5' => array( // we are skipping `close_button` (button_5) because it has no tab in settings

			),
			'button_7' => array(
				// 'background'=>'button_4_button_colour',
				'background-color' => 'button_7_button_colour',
				'color'            => 'button_7_link_colour',
			),
		);
		$bar_style    = isset( $_POST['bar_style'] ) ? sanitize_text_field( $_POST['bar_style'] ) : '';
		$bar_type     = isset( $_POST['bar_type'] ) ? sanitize_text_field( $_POST['bar_type'] ) : '';
		$bar_hd_style = isset( $_POST['bar_hd_style'] ) ? sanitize_text_field( $_POST['bar_hd_style'] ) : '';
		$bar_hd_txt   = isset( $_POST['bar_hd_txt'] ) ? sanitize_text_field( $_POST['bar_hd_txt'] ) : '';
		$bar_txt      = isset( $_POST['bar_txt'] ) ? Cookie_Law_Info::sanitise_settings( 'notify_message', $_POST['bar_txt'] ) : '';

		// validate button style if exists/not
		if ( '' === $bar_style ) {
			echo json_encode(
				array(
					'response' => false,
				)
			);
			exit();
		}

		// processing bar style
		$bar_style_arr = $this->process_css( $bar_style );
		foreach ( $bar_style_arr as $bar_st_k => $bar_st_v ) {
			if ( in_array( $bar_st_v[0], $param_to_trim[ $bar_type ] ) ) {
				unset( $bar_style_arr[ $bar_st_k ] );
			}
		}

		$this->update_common_settings_vars( $bar_style_arr, $the_options, 'bar', $settings_params );
		$the_options['cookie_bar_as']  = $bar_type;
		$cookie_message                = stripslashes( $bar_txt );
		$the_options['notify_message'] = $cookie_message;

		// Update message on correspondant text field
		if ( $consent_type === 'ccpa' ) {
			$the_options['ccpa_content'] = $cookie_message;
		} elseif ( $consent_type === 'ccpa_gdpr' ) {
			$the_options['ccpa_gdpr_content'] = $cookie_message;
		} else {
			$the_options['gdpr_content'] = $cookie_message;
		}

		// processing bar hd(heading) style
		$bar_hd_style_arr                = $this->process_css( $bar_hd_style );
		$the_options['bar_hd_style']     = $bar_hd_style_arr;
		$the_options['bar_heading_text'] = $bar_hd_txt;

		// processing button styles
		for ( $e = 1; $e <= 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$btn_name                 = 'button_' . $e;
			$style_name               = $btn_name . '_style';
			$txt_name                 = $btn_name . '_txt';
			$the_options[ $txt_name ] = isset( $_POST[ $txt_name ] ) ? Cookie_Law_Info::sanitise_settings( $txt_name, $_POST[ $txt_name ] ) : '';
			$btn_style                = isset( $_POST[ $style_name ] ) ? sanitize_text_field( $_POST[ $style_name ] ) : '';
			$this->update_common_settings_vars( $this->process_css( $btn_style ), $the_options, $btn_name, $settings_params );
		}
		if ( Cookie_Law_Info::wt_cli_category_widget_exist( $the_options['notify_message'] ) ) {
			$the_options['accept_all'] = false;
		}

		update_option( CLI_SETTINGS_FIELD, $the_options );
		echo json_encode(
			array(
				'response' => true,
			)
		);
		exit();
	}

	private function update_common_settings_vars( $style_arr, &$the_options, $item, $settings_params ) {
		foreach ( $style_arr as $st_k => $st_v ) {
			if ( isset( $settings_params[ $item ][ $st_v[0] ] ) ) {
				unset( $style_arr[ $st_k ] ); // no need it on both places so remove it from custom css
				$the_options[ $settings_params[ $item ][ $st_v[0] ] ] = $st_v[1];

				// toggle the as button feature according to background
				if ( $item != 'bar' && ( $st_v[0] == 'background' || $st_v[0] == 'background-color' ) ) {
					$the_options[ $item . '_as_button' ] = $this->is_nocolor( $st_v[1] ) ? false : true;
				}
			}
		}
		$the_options[ $item . '_style' ] = $style_arr;
	}

	private function is_nocolor( $bg ) {
		if ( $bg == 'transparent' || $bg == 'none' ) {
			return true;
		} else {
			if ( strpos( $bg, 'rgba' ) !== false ) {
				$bg = substr( $bg, strpos( $bg, 'rgba' ) );
				preg_match_all( '/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)(?s).*$/', $bg, $rgba );
				if ( $rgba[4][0] == 0 ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 *
	 * CSS property from style attribute
	 **/
	private function process_css( $style ) {
		$style_arr = explode( ';', trim( $style ) );
		$style_arr = array_unique( array_filter( $style_arr ) );
		return array_map( array( $this, 'process_css_params' ), $style_arr );
	}

	/**
	 *
	 * callback for CSS property generator (process_css)
	 **/
	private function process_css_params( $val ) {
		$val     = trim( $val );
		$val_arr = explode( ':', $val, 2 );
		return array_map( 'trim', $val_arr );
	}

	/**
	 *
	 * style attribute generator
	 **/
	public static function create_style_attr( $arr ) {
		if ( is_array( $arr ) && ! empty( $arr ) ) {
			$arr = array_map( array( __CLASS__, 'create_style_attr_sub' ), $arr );
			return str_replace( '"', "'", implode( '; ', $arr ) . ';' );
		} else {
			return '';
		}
	}


	/**
	 *
	 * callback function for style attribute generator (create_style_attr)
	 **/
	public static function create_style_attr_sub( $vl ) {
		if ( is_array( $vl ) ) {
			return implode( ':', $vl );
		} else {
			return $vl;
		}
	}

	/**
	 *
	 * generate current template config
	 **/
	private function get_current_theme_vars( $the_options, $default_theme ) {
		// params that are already available in settings
		// array format: [settings json value => css value ]
		$settings_params = array(
			'bar'      => array(
				'background'  => 'background-color',
				'text'        => 'color',
				'font_family' => 'font-family',
			),
			'button_1' => array(
				'button_1_button_colour' => 'background-color',
				'button_1_link_colour'   => 'color',
			),
			'button_2' => array(
				'button_2_button_colour' => 'background-color',
				'button_2_link_colour'   => 'color',
			),
			'button_3' => array(
				'button_3_button_colour' => 'background-color',
				'button_3_link_colour'   => 'color',
			),
			'button_4' => array(
				'button_4_button_colour' => 'background-color',
				'button_4_link_colour'   => 'color',
			),
			'button_5' => array( // we are skipping `close_button` (button_5) because it has no tab in settings

			),
			'button_7' => array(
				'button_7_button_colour' => 'background-color',
				'button_7_link_colour'   => 'color',
			),
		);

		// this params will not append while saving the theme. so we need to add it manually on preview section
		$param_to_add = array(
			'banner' => array(
				array( 'width', '100%' ),
				array( 'height', 'auto' ),
			),
			'widget' => array(
				array( 'width', '445px' ),
				array( 'height', 'auto' ),
			),
			'popup'  => array(
				array( 'width', '500px' ),
				array( 'height', 'auto' ),
				array( 'display', 'inline-block' ),
				// array('border','solid 20px rgba(0,0,0,.5)'),
				// array('box-shadow','rgba(0,0,0,.5) 0px 5px 50px'),
			),
		);

		// setting bar style
		$bar_style      = array();
		$bar_style_attr = '';
		foreach ( $settings_params['bar'] as $k => $v ) {
			if ( isset( $the_options[ $k ] ) ) {
				$bar_style[] = array( $v, $the_options[ $k ] );
			}
		}
		if ( isset( $the_options['bar_style'] ) && is_array( $the_options['bar_style'] ) ) {
			// add bar style params as last element to override previous
			$bar_style_arr  = array_merge( $the_options['bar_style'], $param_to_add[ $the_options['cookie_bar_as'] ], $bar_style );
			$bar_style_attr = $this->create_style_attr( $bar_style_arr );
		} else {
			$bar_style_attr = $this->create_style_attr( $bar_style );
			// no extra custom CSS so we need to prepend the default
			$bar_style_attr = $default_theme['config']['bar']['style'] . ' ' . $bar_style_attr;
		}
		$default_theme['config']['bar']['style'] = stripslashes( $bar_style_attr );
		// setting bar content
		$default_theme['config']['bar']['text'] = stripslashes( $the_options['notify_message'] );

		// bar heading style
		$bar_hd_style_attr = '';
		if ( isset( $the_options['bar_hd_style'] ) && is_array( $the_options['bar_hd_style'] ) ) {
			$bar_hd_style_attr = $this->create_style_attr( $the_options['bar_hd_style'] );
		}
		$default_theme['config']['heading']['style'] = $bar_hd_style_attr;
		$default_theme['config']['heading']['text']  = stripslashes( $the_options['bar_heading_text'] );

		// setting buttons style
		for ( $e = 1; $e <= 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$btn_name       = 'button_' . $e;
			$btn_style      = array();
			$btn_style_attr = '';
			foreach ( $settings_params[ $btn_name ] as $k => $v ) {
				if ( isset( $the_options[ $k ] ) ) {
					$btn_style[] = array( $v, $the_options[ $k ] );
				}
			}
			if ( isset( $the_options[ $btn_name . '_style' ] ) && is_array( $the_options[ $btn_name . '_style' ] ) ) {
				$btn_style_arr  = array_merge( $the_options[ $btn_name . '_style' ], $btn_style );
				$btn_style_attr = $this->create_style_attr( $btn_style_arr );
			} else {
				$btn_style_attr = $this->create_style_attr( $btn_style );
				// no extra custom CSS so we need to prepend the default
				$btn_style_attr = $default_theme['config'][ $btn_name ]['style'] . ' ' . $btn_style_attr;
			}
			$default_theme['config'][ $btn_name ]['style'] = stripslashes( $btn_style_attr );

			// setting button content
			$default_theme['config'][ $btn_name ]['text'] = isset( $the_options[ $btn_name . '_text' ] ) ? $the_options[ $btn_name . '_text' ] : $default_theme['config'][ $btn_name ]['text'];

			// setting button status
			$default_theme['config'][ $btn_name ]['status'] = strpos( $default_theme['config']['bar']['text'], $this->btn_shortcodes[ $btn_name ] ) !== false ? 1 : 0;
		}

		return $default_theme;
	}

	private function get_btn_and_hd_text( $current_theme ) {
		$out = array();
		for ( $e = 1; $e <= 7; $e++ ) {
			if ( $e == 6 ) {
				continue;
			}
			$btn_name         = 'button_' . $e;
			$out[ $btn_name ] = $current_theme['config'][ $btn_name ]['text'];
		}
		$default_theme['config']['heading']['text'];
	}


	/**
	 *  =====Plugin settings page Hook=====
	 * settings form
	 * You can include a form, its outside settings form
	 **/
	public function out_settings_form() {
		include plugin_dir_path( __FILE__ ) . 'data/data.themes_banner.php';
		include plugin_dir_path( __FILE__ ) . 'data/data.themes_widget.php';
		include plugin_dir_path( __FILE__ ) . 'data/data.themes_popup.php';

		$the_options            = Cookie_Law_Info::get_settings();
		$current_theme_def_name = 'cli_theme_' . $the_options['cookie_bar_as'] . '_default';
		$current_theme          = $this->get_current_theme_vars( $the_options, $$current_theme_def_name );

		$view_file = plugin_dir_path( __FILE__ ) . 'views/themes.php';

		// $the_options = Cookie_Law_Info::get_settings();
		if ( isset( $this->themes['current-theme'] ) ) {
			foreach ( $this->themes['current-theme']['config'] as $key => $value ) {
				if ( isset( $the_options[ $key ] ) ) {
					$this->themes['current-theme']['config'][ $key ] = $the_options[ $key ];
				}
			}
		}
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == CLI_POST_TYPE && isset( $_GET['page'] ) && $_GET['page'] == 'cookie-law-info' ) {
			wp_enqueue_script( 'cli-themes', plugin_dir_url( __FILE__ ) . 'assets/js/cli-themes-admin.js', array( 'jquery' ), CLI_VERSION, false );

			wp_enqueue_style( 'cli-themes-css', plugin_dir_url( __FILE__ ) . 'assets/css/cli-theme.css', array(), CLI_VERSION, false );
			$params = array(
				'nonces'   => array(
					'cli_theme' => wp_create_nonce( 'cli_theme' ),
				),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'home_url' => home_url(),
				'labels'   => array(
					'error'   => __( 'Error', 'webtoffee-gdpr-cookie-consent' ),
					'success' => __( 'Success', 'webtoffee-gdpr-cookie-consent' ),
				),
			);
			wp_localize_script( 'cli-themes', 'cli_theme_vars', $params );
		}

		$params = array(
			'themes_banner'      => $cli_themes_banner,
			'themes_widget'      => $cli_themes_widget,
			'themes_popup'       => $cli_themes_popup,
			'current_theme'      => $current_theme,
			'current_theme_type' => $the_options['cookie_bar_as'],
		);
		Cookie_Law_Info::envelope_settings_tabcontent( 'cookie-law-info-themes', $view_file, '', $params, 0 );
	}
}
$Cli_Themes = new Cookie_Law_Info_Cli_Themes();
