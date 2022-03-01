<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.3
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.1.3
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Cookie_Law_Info {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.1.3
	 * @access   protected
	 * @var      Cookie_Law_Info_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.1.3
	 * @access   public
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.1.3
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public static $stored_options     = array();
	public static $db_initial_version = '2.3.2';
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.1.3
	 */
	public function __construct() {
		if ( defined( 'CLI_VERSION' ) ) {
			$this->version = CLI_VERSION;
		} else {
			$this->version = '2.3.7';
		}
		$this->plugin_name = 'cookie-law-info';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_thrid_party_hooks();
		$this->cli_get_client_ip( true );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cookie_Law_Info_Loader. Orchestrates the hooks of the plugin.
	 * - Cookie_Law_Info_i18n. Defines internationalization functionality.
	 * - Cookie_Law_Info_Admin. Defines all hooks for the admin area.
	 * - Cookie_Law_Info_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.1.3
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cookie-law-info-loader.php';
		/**
		 * Webtoffee Security Library
		 * Includes Data sanitization, Access checking
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt-security-helper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cookie-law-info-remote-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/license_manager/class-cookie-law-info-license-handler.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/license_manager/class-cookie-law-info-package-handler.php';

		/**
		 * The class which integrates Cookieyes services to the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cookie-law-info-cookieyes.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cookie-law-info-i18n.php';
		/**
				 * The class responsible for handling multi language features
				 * of the plugin.
				 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cookie-law-info-languages.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cookie-law-info-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cookie-law-info-public.php';

		/**
		 * The class responsible for adding compatibility to third party plugins
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'third-party/class-cookie-law-info-third-party.php';

		$this->loader = new Cookie_Law_Info_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cookie_Law_Info_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.1.3
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cookie_Law_Info_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Cookie_Law_Info_Admin( $this->get_plugin_name(), $this->get_version(), $this );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'debug_save' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu', 11 ); /* Adding admin menu */
		$this->loader->add_action( 'admin_init', $plugin_admin, 'redirect_to_settings_page' );

		/*
		.
		* loading admin modules
		*/
		$plugin_admin->admin_modules();

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'remove_cli_addnew_link' );

		// Add plugin settings link:
		add_filter( 'plugin_action_links_' . plugin_basename( CLI_PLUGIN_FILENAME ), array( $plugin_admin, 'plugin_action_links' ) );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.1.3
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Cookie_Law_Info_Public( $this->get_plugin_name(), $this->get_version(), $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$plugin_public->common_modules();
		// below hook's functions needs update
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'init' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'include_user_accepted_cookielawinfo' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'include_user_accepted_cookielawinfo_in_body' );

		// get json settings
		$this->loader->add_action( 'wp_ajax_cli_get_settings_json', $plugin_public, 'cli_get_settings_json' );
		$this->loader->add_action( 'wp_ajax_nopriv_cli_get_settings_json', $plugin_public, 'cli_get_settings_json' );
	}


	/**
	 * Register all of the hooks related to the Third party plugin compatibility
	 * of the plugin.
	 *
	 * @since    2.1.6
	 * @access   public
	 */
	public function define_thrid_party_hooks() {
		$plugin_third_party = new Cookie_Law_Info_Third_Party();
		$plugin_third_party->register_scripts();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.1.3
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.1.3
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.1.3
	 * @return    Cookie_Law_Info_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.1.3
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	/**
	 *
	 * @since     2.2.2
	 * @return    boolean
	 */
	// Add Editor Support
	public static function wt_cli_is_disable_blocking() {
		$disabled = false;
		if ( isset( $_GET['elementor-preview'] ) || isset( $_POST['cs_preview_state'] ) ) {
			$disabled = true;

		}
		if ( defined( 'ET_FB_ENABLED' ) ) {
			if ( ET_FB_ENABLED ) {
				$disabled = true;
			}
		}
		return apply_filters( 'wt_cli_disable_script_blocker', $disabled );
	}
	/**
	 * Get current settings.
	 */
	public static function get_settings() {
		 $settings = self::get_default_settings();
		// self::$stored_options=self::$stored_options && count(self::$stored_options)>0 ? self::$stored_options : get_option(CLI_SETTINGS_FIELD);
		self::$stored_options = get_option( CLI_SETTINGS_FIELD );
		if ( ! empty( self::$stored_options ) ) {
			foreach ( self::$stored_options as $key => $option ) {
				if ( isset( $_GET['cli_bypass'] ) ) {
					if ( get_option( 'CLI_BYPASS' ) == 1 ) {
						if ( $key == 'is_on' ) {
							$option = 'true';
						}
						if ( $key == 'is_eu_on' ) {
							$option = 'false';
						}
					}
				}
				$settings[ $key ] = self::sanitise_settings( $key, $option );
			}
		}
		return $settings;
	}

	/**
	 * Generate tab head for settings page.
	 * method will translate the string to current language
	 */
	public static function generate_settings_tabhead( $title_arr ) {
		$out_arr = array();
		foreach ( $title_arr as $k => $v ) {
			if ( $k == 'cookie-law-info-buttons' ) {
				$out_arr[ $k ] = $v;
				// tab head for modules
				$out_arr = apply_filters( 'cli_module_settings_tabhead', $out_arr );
			} else {
				$out_arr[ $k ] = $v;
			}
		}
		foreach ( $out_arr as $k => $v ) {
			if ( is_array( $v ) ) {
				$v = ( isset( $v[2] ) ? $v[2] : '' ) . $v[0] . ' ' . ( isset( $v[1] ) ? $v[1] : '' );
			}
			?>
			<a class="nav-tab" href="#<?php echo $k; ?>"><?php echo $v; ?></a>
			<?php
		}
	}

	/**
	 * Envelope settings tab content with tab div.
	 * relative path is not acceptable in view file
	 */
	public static function envelope_settings_tabcontent( $target_id, $view_file = '', $html = '', $variables = array(), $need_submit_btn = 0 ) {
		$templates = $variables;
		?>
		<div class="cookie-law-info-tab-content" data-id="<?php echo $target_id; ?>">
			<?php
			if ( $view_file != '' && file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo $html;
			}
			?>
			<?php
			if ( $need_submit_btn == 1 ) {
				include plugin_dir_path( CLI_PLUGIN_FILENAME ) . 'admin/views/admin-settings-save-button.php';
			}
			?>
		</div>
		<?php
	}

	/**
	 Returns default settings
	 If you override the settings here, be ultra careful to use escape characters!
	 */
	public static function get_default_settings( $key = '' ) {
		$settings_v0_9 = array(
			'animate_speed_hide'           => '500',
			'animate_speed_show'           => '500',
			'background'                   => '#fffffff7',
			'background_url'               => '',
			'border'                       => '#b1a6a6c2',
			'border_on'                    => true,
			'bar_style'                    => array( array( 'text-align', 'left' ), array( 'padding', '15px 30px' ) ),
			'bar_hd_style'                 => array(),
			'button_1_text'                => 'ACCEPT',
			'button_1_url'                 => '#',
			'button_1_action'              => '#cookie_action_close_header',
			'button_1_link_colour'         => '#fff',
			'button_1_new_win'             => false,
			'button_1_as_button'           => true,
			'button_1_button_colour'       => '#61a229',
			'button_1_button_size'         => 'medium',
			'button_1_style'               => array( array( 'margin', '5px 5px 5px 30px' ), array( 'border-radius', '0' ), array( 'padding', '8px 25px 8px 25px' ) ),

			'button_2_text'                => 'Read More',
			'button_2_url'                 => get_site_url(),
			'button_2_action'              => 'CONSTANT_OPEN_URL',
			'button_2_link_colour'         => '#898888',
			'button_2_new_win'             => true,
			'button_2_as_button'           => false,
			'button_2_button_colour'       => '#ffffff',
			'button_2_button_size'         => 'medium',
			'button_2_url_type'            => 'url',
			'button_2_page'                => get_option( 'wp_page_for_privacy_policy' ) ? get_option( 'wp_page_for_privacy_policy' ) : 0,
			'button_2_hidebar'             => false,
			'button_2_nofollow'            => false,
			'button_2_style'               => array(),

			'button_3_text'                => 'REJECT',
			'button_3_url'                 => '#',
			'button_3_action'              => '#cookie_action_close_header_reject',
			'button_3_link_colour'         => '#fff',
			'button_3_new_win'             => false,
			'button_3_as_button'           => true,
			'button_3_button_colour'       => '#61a229',
			'button_3_button_size'         => 'medium',
			'button_3_style'               => array( array( 'margin', '5px 5px 5px 5px' ), array( 'border-radius', '0' ), array( 'padding', '8px 25px 8px 25px' ) ),

			'button_4_text'                => 'Cookie settings',
			'button_4_url'                 => '#',
			'button_4_action'              => '#cookie_action_settings',
			'button_4_link_colour'         => '#898888',
			'button_4_new_win'             => false,
			'button_4_as_button'           => false,
			'button_4_button_colour'       => '#ffffff',
			'button_4_button_size'         => 'medium',
			'button_4_style'               => array( array( 'border-bottom', '1px solid' ), array( 'text-decoration', 'none' ), array( 'text-decoration', 'none' ) ),

			'button_5_style'               => array( array( 'float', 'right' ), array( 'text-decoration', 'none' ), array( 'color', '#333' ) ),
			'button_7_text'                => 'ACCEPT ALL',
			'button_7_url'                 => '#',
			'button_7_action'              => '#cookie_action_close_header',
			'button_7_link_colour'         => '#fff',
			'button_7_new_win'             => false,
			'button_7_as_button'           => true,
			'button_7_button_colour'       => '#61a229',
			'button_7_button_size'         => 'medium',
			'button_7_style'               => array( array( 'margin', '5px 5px 5px 30px' ), array( 'border-radius', '0' ), array( 'padding', '8px 25px 8px 25px' ) ),

			'font_family'                  => 'inherit', // Pick the family, not the easy name (see helper function below)
			'header_fix'                   => false,
			'is_on'                        => true,
			'is_eu_on'                     => false,
			'logging_on'                   => false,
			'notify_animate_hide'          => true,
			'notify_animate_show'          => false,
			'notify_div_id'                => '#cookie-law-info-bar',
			'notify_position_horizontal'   => 'right', // left | right
			'notify_position_vertical'     => 'bottom', // 'top' = header | 'bottom' = footer
			'notify_message'               => addslashes( '<div class="cli-bar-container cli-style-v2"><div class="cli-bar-message">We use cookies on our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking “Accept”, you consent to the use of ALL the cookies. However you may visit Cookie Settings to provide a controlled consent.</div><div class="cli-bar-btn_container">[cookie_settings][cookie_button]</div></div>' ),
			'scroll_close'                 => false,
			'scroll_close_reload'          => false,
			'accept_close_reload'          => false,
			'reject_close_reload'          => false,
			'showagain_background'         => '#fff',
			'showagain_border'             => '#000',
			'showagain_text'               => addslashes( 'Manage consent' ),
			'showagain_div_id'             => '#cookie-law-info-again',
			'showagain_tab'                => false,
			'showagain_x_position'         => '100px',
			'text'                         => '#333',
			'use_colour_picker'            => true,
			'show_once_yn'                 => false,   // this is a new feature so default = switched off
			'show_once'                    => '10000', // 8 seconds
			'is_GMT_on'                    => true,
			'as_popup'                     => false, // version 2.1.6 onwards this option is merged with `cookie_bar_as`
			'popup_overlay'                => true,
			'bar_heading_text'             => '',
			'cookie_bar_as'                => 'banner',
			'cookie_setting_popup'         => true,
			'accept_all'                   => true,
			'js_script_blocker'            => false,
			'popup_showagain_position'     => 'bottom-right', // bottom-right | bottom-left | top-right | top-left
			'widget_position'              => 'left', // left | right
			'accept_close_page_navigation' => false,

		);
		$settings_v0_9 = apply_filters( 'wt_cli_plugin_settings', $settings_v0_9 );
		return $key != '' ? $settings_v0_9[ $key ] : $settings_v0_9;
	}

	/**
	 Returns JSON object containing the settings for the main script
	 REFACTOR / DEBUG: may need to use addslashes( ... ) else breaks JSON
	 */
	public static function get_json_settings() {
		$settings = self::get_settings();

		// DEBUG hex:
		// preg_match('/^#[a-f0-9]{6}|#[a-f0-9]{3}$/i', $hex)
		// DEBUG json_encode - issues across different versions of PHP!
		// $str = json_encode( $slim_settings, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
		$button_1_style = array();
		$button_2_style = array();
		$button_3_style = array();
		$button_4_style = array();
		$button_5_style = array();
		$button_7_style = array();
		if ( Cookie_Law_Info_Admin::module_exists( 'cli-themes' ) ) {
			$button_1_style = isset( $settings['button_1_style'] ) ? $settings['button_1_style'] : array();
			$button_2_style = isset( $settings['button_2_style'] ) ? $settings['button_2_style'] : array();
			$button_3_style = isset( $settings['button_3_style'] ) ? $settings['button_3_style'] : array();
			$button_4_style = isset( $settings['button_4_style'] ) ? $settings['button_4_style'] : array();
			$button_5_style = isset( $settings['button_5_style'] ) ? $settings['button_5_style'] : array();
			$button_7_style = isset( $settings['button_7_style'] ) ? $settings['button_7_style'] : array();
		}

		// Slim down JSON objects to the bare bones:
		$slim_settings = array(
			'animate_speed_hide'           => $settings['animate_speed_hide'],
			'animate_speed_show'           => $settings['animate_speed_show'],
			'background'                   => $settings['background'],
			'border'                       => $settings['border'],
			'border_on'                    => false, // $settings['border_on'],
			'button_1_button_colour'       => $settings['button_1_button_colour'],
			'button_1_button_hover'        => ( self::su_hex_shift( $settings['button_1_button_colour'], 'down', 20 ) ),
			'button_1_link_colour'         => $settings['button_1_link_colour'],
			'button_1_as_button'           => $settings['button_1_as_button'],
			'button_1_new_win'             => $settings['button_1_new_win'],

			'button_2_button_colour'       => $settings['button_2_button_colour'],
			'button_2_button_hover'        => ( self::su_hex_shift( $settings['button_2_button_colour'], 'down', 20 ) ),
			'button_2_link_colour'         => $settings['button_2_link_colour'],
			'button_2_as_button'           => $settings['button_2_as_button'],
			'button_2_hidebar'             => $settings['button_2_hidebar'],
			'button_2_nofollow'            => $settings['button_2_nofollow'],

			'button_3_button_colour'       => $settings['button_3_button_colour'],
			'button_3_button_hover'        => ( self::su_hex_shift( $settings['button_3_button_colour'], 'down', 20 ) ),
			'button_3_link_colour'         => $settings['button_3_link_colour'],
			'button_3_as_button'           => $settings['button_3_as_button'],
			'button_3_new_win'             => $settings['button_3_new_win'],

			'button_4_button_colour'       => $settings['button_4_button_colour'],
			'button_4_button_hover'        => ( self::su_hex_shift( $settings['button_4_button_colour'], 'down', 20 ) ),
			'button_4_link_colour'         => $settings['button_4_link_colour'],
			'button_4_as_button'           => $settings['button_4_as_button'],
			'button_7_button_colour'       => $settings['button_7_button_colour'],
			'button_7_button_hover'        => ( self::su_hex_shift( $settings['button_7_button_colour'], 'down', 20 ) ),
			'button_7_link_colour'         => $settings['button_7_link_colour'],
			'button_7_as_button'           => $settings['button_7_as_button'],
			'button_7_new_win'             => $settings['button_7_new_win'],

			'font_family'                  => $settings['font_family'],
			'header_fix'                   => $settings['header_fix'],
			'notify_animate_hide'          => $settings['notify_animate_hide'],
			'notify_animate_show'          => $settings['notify_animate_show'],
			'notify_div_id'                => $settings['notify_div_id'],
			'notify_position_horizontal'   => $settings['notify_position_horizontal'],
			'notify_position_vertical'     => $settings['notify_position_vertical'],
			'scroll_close'                 => $settings['scroll_close'],
			'scroll_close_reload'          => $settings['scroll_close_reload'],
			'accept_close_reload'          => $settings['accept_close_reload'],
			'reject_close_reload'          => $settings['reject_close_reload'],
			'showagain_tab'                => $settings['showagain_tab'],
			'showagain_background'         => $settings['showagain_background'],
			'showagain_border'             => $settings['showagain_border'],
			'showagain_div_id'             => $settings['showagain_div_id'],
			'showagain_x_position'         => $settings['showagain_x_position'],
			'text'                         => $settings['text'],
			'show_once_yn'                 => $settings['show_once_yn'],
			'show_once'                    => $settings['show_once'],
			'logging_on'                   => $settings['logging_on'],
			'as_popup'                     => $settings['as_popup'],
			'popup_overlay'                => $settings['popup_overlay'],
			'bar_heading_text'             => $settings['bar_heading_text'],
			'cookie_bar_as'                => $settings['cookie_bar_as'],
			'cookie_setting_popup'         => $settings['cookie_setting_popup'],
			'accept_all'                   => $settings['accept_all'],
			'js_script_blocker'            => $settings['js_script_blocker'],
			'popup_showagain_position'     => $settings['popup_showagain_position'],
			'widget_position'              => $settings['widget_position'],
			'button_1_style'               => $button_1_style,
			'button_2_style'               => $button_2_style,
			'button_3_style'               => $button_3_style,
			'button_4_style'               => $button_4_style,
			'button_5_style'               => $button_5_style,
			'button_7_style'               => $button_7_style,
			'accept_close_page_navigation' => $settings['accept_close_page_navigation'],
		);
		$str           = json_encode( $slim_settings );
		return $str;
	}

	/**
	Returns sanitised content based on field-specific rules defined here
	 Used for both read AND write operations
	 */
	public static function sanitise_settings( $key, $value ) {
		$ret = null;
		switch ( $key ) {
			// Convert all boolean values from text to bool:
			case 'is_on':
			case 'is_eu_on':
			case 'logging_on':
			case 'border_on':
			case 'notify_animate_show':
			case 'notify_animate_hide':
			case 'showagain_tab':
			case 'use_colour_picker':
			case 'button_1_new_win':
			case 'button_1_as_button':
			case 'button_2_new_win':
			case 'button_2_as_button':
			case 'button_2_hidebar':
			case 'button_2_nofollow':
			case 'button_3_new_win':
			case 'button_3_as_button':
			case 'button_4_new_win':
			case 'button_4_as_button':
			case 'button_7_new_win':
			case 'button_7_as_button':
			case 'scroll_close':
			case 'scroll_close_reload':
			case 'accept_close_reload':
			case 'reject_close_reload':
			case 'show_once_yn':
			case 'header_fix':
			case 'is_GMT_on':
			case 'as_popup':
			case 'popup_overlay':
			case 'cookie_setting_popup':
			case 'accept_all':
			case 'js_script_blocker':
			case 'ccpa_enabled':
			case 'button_6_as_link':
			case 'ccpa_region_based':
			case 'ccpa_enable_bar':
			case 'accept_btn_enable':
			case 'accept_all_btn_enable':
			case 'accept_close_page_navigation':
				if ( $value == 'true' || $value === true ) {
					$ret = true;
				} elseif ( $value == 'false' || $value === false ) {
					$ret = false;
				} else {
					// Unexpected value returned from radio button, go fix the HTML.
					// Failover = assign null.
					$ret = 'fffffff';
				}
				break;
			// Any hex colour e.g. '#f00', '#FE01ab' '#ff0000' but not 'f00' or 'ff0000':
			case 'background':
			case 'text':
			case 'border':
			case 'showagain_background':
			case 'showagain_border':
			case 'button_1_link_colour':
			case 'button_1_button_colour':
			case 'button_2_link_colour':
			case 'button_2_button_colour':
			case 'button_3_link_colour':
			case 'button_3_button_colour':
			case 'button_4_link_colour':
			case 'button_4_button_colour':
			case 'button_6_link_colour':
			case 'button_7_link_colour':
			case 'button_7_button_colour':
			case 'privacy_overview_bg_color':
			case 'privacy_overview_text_color':
			case 'accordion_bg_color':
			case 'accordion_text_color':
			case 'switch_bg_color':
			case 'switch_bg_color_inactive':
			case 'switch_bullet_color':
			case 'switch_bullet_color_inactive':
			case 'accept_btn_bg_color':
			case 'accept_btn_text_color':
			case 'accept_all_btn_bg_color':
			case 'accept_all_btn_text_color':
				$ret = $value;

				if ( preg_match( '/^#[a-f0-9]{6}|#[a-f0-9]{3}$/i', $value ) ) {
					// Was: '/^#([0-9a-fA-F]{1,2}){3}$/i' which allowed e.g. '#00dd' (error)
					$ret = $value;
				} elseif ( preg_match( '/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d\.]+)?\)$/i', $value ) ) {
					$ret = $value;
				} else {
					// Failover = transparent
					$ret = 'rgba(0,0,0,0)';
				}
				break;
			// Allow some HTML, but no JavaScript. Note that deliberately NOT stripping out line breaks here, that's done when sending JavaScript parameter elsewhere:
			case 'notify_message':
			case 'ccpa_content':
			case 'ccpa_gdpr_content':
			case 'bar_heading_text':
			case 'gdpr_content':
				$ret = wp_kses( $value, self::allowed_html(), self::allowed_protocols() );
				break;
			// URLs only:
			case 'button_1_url':
			case 'button_2_url':
			case 'button_3_url':
			case 'button_4_url':
			case 'button_7_url':
				$ret = esc_url( $value );
				break;
			// Basic sanitisation for all the rest:
			default:
				$ret = is_array( $value ) ? self::sanitize_array( $value ) : sanitize_text_field( $value );
				break;
		}
		if ( ( 'is_eu_on' === $key || 'logging_on' == $key ) && 'fffffff' === $ret ) {
			$ret = false;
		}
		return $ret;
	}
	public static function sanitize_array( $arr ) {
		if ( is_array( $arr ) ) {
			return array_map( array( __CLASS__, 'sanitize_array' ), $arr );
		} else {
			return sanitize_text_field( $arr );
		}
	}
	public static function get_cookies_by_category() {
		$cookie_categories = apply_filters( 'wt_cli_cookie_categories', array() );

		$categories = array();

		if ( ! empty( $cookie_categories ) ) {

			foreach ( $cookie_categories as $slug => $data ) {

				if ( isset( $data['status'] ) && $data['status'] === true ) {

					$cookies     = ( isset( $data['cookies'] ) && is_array( $data['cookies'] ) ) ? $data['cookies'] : array();
					$cookie_list = array();
					$strict      = isset( $data['strict'] ) ? $data['strict'] : false;

					if ( ! empty( $cookies ) ) {
						foreach ( $cookies as $key => $cookie ) {

							$sensitivity  = $cookie['sensitivity'];
							$cookie_title = $cookie['slug_id'];
							$cookie_id    = ( '' !== $cookie_title ) ? $cookie_title : $cookie['title'];

							if ( 'non-necessary' === $sensitivity || false === $strict ) {
								$cookie_list[] = $cookie_id;
							}
						}
					}
				}
				if ( ! empty( $cookie_list ) ) {
					$categories[ $slug ] = $cookie_list;
				}
			}
		}
		return $categories;
	}
	public static function get_non_necessary_cookie_ids() {
		global $wpdb;

		$args = array(
			'post_type'        => CLI_POST_TYPE,
			'posts_per_page'   => -1,
			'suppress_filters' => false,
			'meta_query'       => array(
				array(
					'key'   => '_cli_cookie_sensitivity',
					'value' => 'non-necessary',
				),
			),

		);

		add_filter( 'posts_where', array( 'Cookie_Law_Info', 'remove_category_cookies' ) );
		$posts = get_posts( $args );
		remove_filter( 'posts_where', array( 'Cookie_Law_Info', 'remove_category_cookies' ) );
		if ( ! $posts ) {
			return;
		}
			$cookie_slugs = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {
					$cookie_slugs[] = get_post_meta( $post->ID, '_cli_cookie_slugid', true );
			}
		}
			return $cookie_slugs;
	}
	public static function remove_category_cookies( $where = '' ) {
		 global $wpdb;
		$key    = 'cookielawinfo-checkbox-%';
		$where .= $wpdb->prepare( ' AND post_title NOT LIKE %s', $key );
		return $where;
	}
	/**
	 * Color shift a hex value by a specific percentage factor
	 * By http://www.phpkode.com/source/s/shortcodes-ultimate/shortcodes-ultimate/lib/color.php
	 * Adapted by Richard Ashby; amended error handling to use failovers not messages, so app continues
	 *
	 * @param string  $supplied_hex Any valid hex value. Short forms e.g. #333 accepted.
	 * @param string  $shift_method How to shift the value e.g( +,up,lighter,>)
	 * @param integer $percentage Percentage in range of [0-100] to shift provided hex value by
	 * @return string shifted hex value
	 * @version 1.0 2008-03-28
	 */
	public static function su_hex_shift( $supplied_hex, $shift_method, $percentage = 50 ) {
		$shifted_hex_value     = null;
		$valid_shift_option    = false;
		$current_set           = 1;
		$RGB_values            = array();
		$valid_shift_up_args   = array( 'up', '+', 'lighter', '>' );
		$valid_shift_down_args = array( 'down', '-', 'darker', '<' );
		$shift_method          = strtolower( trim( $shift_method ) );

		// Check Factor
		if ( ! is_numeric( $percentage ) || ( $percentage = (int) $percentage ) < 0 || $percentage > 100 ) {
			// trigger_error( "Invalid factor", E_USER_ERROR );
			return $supplied_hex;
		}

		// Check shift method
		foreach ( array( $valid_shift_down_args, $valid_shift_up_args ) as $options ) {
			foreach ( $options as $method ) {
				if ( $method == $shift_method ) {
					$valid_shift_option = ! $valid_shift_option;
					$shift_method       = ( $current_set === 1 ) ? '+' : '-';
					break 2;
				}
			}
			++$current_set;
		}

		if ( ! $valid_shift_option ) {
			// trigger_error( "Invalid shift method", E_USER_ERROR );
			return $supplied_hex;
		}

		// Check Hex string
		switch ( strlen( $supplied_hex = ( str_replace( '#', '', trim( $supplied_hex ) ) ) ) ) {
			case 3:
				if ( preg_match( '/^([0-9a-f])([0-9a-f])([0-9a-f])/i', $supplied_hex ) ) {
					$supplied_hex = preg_replace( '/^([0-9a-f])([0-9a-f])([0-9a-f])/i', '\\1\\1\\2\\2\\3\\3', $supplied_hex );
				} else {
					// trigger_error( "Invalid hex color value", E_USER_ERROR );
					return $supplied_hex;
				}
				break;
			case 6:
				if ( ! preg_match( '/^[0-9a-f]{2}[0-9a-f]{2}[0-9a-f]{2}$/i', $supplied_hex ) ) {
					// trigger_error( "Invalid hex color value", E_USER_ERROR );
					return $supplied_hex;
				}
				break;
			default:
				// trigger_error( "Invalid hex color length", E_USER_ERROR );
				return $supplied_hex;
		}

		// Start shifting
		$RGB_values['R'] = hexdec( $supplied_hex[0] . $supplied_hex[1] );
		$RGB_values['G'] = hexdec( $supplied_hex[2] . $supplied_hex[3] );
		$RGB_values['B'] = hexdec( $supplied_hex[4] . $supplied_hex[5] );

		foreach ( $RGB_values as $c => $v ) {
			switch ( $shift_method ) {
				case '-':
					$amount = round( ( ( 255 - $v ) / 100 ) * $percentage ) + $v;
					break;
				case '+':
					$amount = $v - round( ( $v / 100 ) * $percentage );
					break;
				default:
					// trigger_error( "Oops. Unexpected shift method", E_USER_ERROR );
					return $supplied_hex;
			}

			$shifted_hex_value .= $current_value = (
			strlen( $decimal_to_hex = dechex( $amount ) ) < 2
			) ? '0' . $decimal_to_hex : $decimal_to_hex;
		}

		return '#' . $shifted_hex_value;
	}

	/**
	 Returns list of HTML tags allowed in HTML fields for use in declaration of wp_kset field validation.

	 Deliberately allows class and ID declarations to assist with custom CSS styling.
	 To customise further, see the excellent article at: http://ottopress.com/2010/wp-quickie-kses/
	 */
	public static function allowed_html() {
		$allowed_html = array(
			// Allowed:		<a href="" id="" class="" title="" target="">...</a>
			// Not allowed:	<a href="javascript(...);">...</a>
			'a'      => array(
				'href'   => array(),
				'id'     => array(),
				'class'  => array(),
				'title'  => array(),
				'target' => array(),
				'rel'    => array(),
				'style'  => array(),
			),
			'b'      => array(),
			'br'     => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'div'    => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'em'     => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'i'      => array(),
			'img'    => array(
				'src'   => array(),
				'id'    => array(),
				'class' => array(),
				'alt'   => array(),
				'style' => array(),
			),
			'p'      => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'span'   => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'strong' => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'label'  => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
		);
		$html5_tags   = array( 'article', 'section', 'aside', 'details', 'figcaption', 'figure', 'footer', 'header', 'main', 'mark', 'nav', 'summary', 'time' );
		foreach ( $html5_tags as $html5_tag ) {
			$allowed_html[ $html5_tag ] = array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			);
		}
		return $allowed_html;
	}


	/**
	 Returns list of allowed protocols, for use in declaration of wp_kset field validation.
	 N.B. JavaScript is specifically disallowed for security reasons.
	 Don't even trust your own database, as you don't know if another plugin has written to your settings.
	 */
	public static function allowed_protocols() {
		// Additional options: 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet'
		return array( 'http', 'https' );
	}


	/**
	 * Check if GTM is active
	 **/
	public static function cli_is_active_GTM() {
		if ( in_array( 'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		}
	}

	public static function cli_get_client_ip( $mask = false ) {

		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = 'UNKNOWN';
		}
		if ( $ipaddress === '::1' ) {
			$ipaddress = '127.0.0.1';
		}
		if ( $mask ) {
			$ip_arr = explode( '.', $ipaddress );
			if ( count( $ip_arr ) < 2 ) {
				$ip_arr = explode( ':', $ipaddress );
			}
			$last_index            = count( $ip_arr ) - 1;
			$last_key              = $ip_arr[ $last_index ];
			$last_key              = self::wt_cli_encrypt( $last_key );
			$ip_arr[ $last_index ] = $last_key;

			if ( count( $ip_arr ) < 1 ) {
				$ipaddress = implode( ':', $ip_arr );
			} else {
				$ipaddress = implode( '.', $ip_arr );
			}
		}
		return $ipaddress;
	}
	public static function wt_cli_get_encryption_key() {
		$encryption_key = apply_filters( 'wt_cli_encryption_key', 512814 );
		return $encryption_key;
	}
	public static function wt_cli_encrypt( $string ) {
		$key    = self::wt_cli_get_encryption_key();
		$result = '';
		for ( $i = 0, $k = strlen( $string ); $i < $k; $i++ ) {
			$char    = substr( $string, $i, 1 );
			$keychar = substr( $key, ( $i % strlen( $key ) ) - 1, 1 );
			$char    = chr( ord( $char ) + ord( $keychar ) );
			$result .= $char;
		}
		return base64_encode( $result );
	}
	public static function wt_cli_decrypt( $string ) {

		$key    = self::wt_cli_get_encryption_key();
		$result = '';
		$string = base64_decode( $string );
		for ( $i = 0,$k = strlen( $string ); $i < $k; $i++ ) {
			$char    = substr( $string, $i, 1 );
			$keychar = substr( $key, ( $i % strlen( $key ) ) - 1, 1 );
			$char    = chr( ord( $char ) - ord( $keychar ) );
			$result .= $char;
		}
		return $result;
	}
	public static function get_user_preference_cookie( $cookies ) {
		if ( ! in_array( 'cli_user_preference', $cookies ) ) {
			$cookies[] = 'cli_user_preference';
		}
		return $cookies;
	}

	/*
	* @since 2.1.9
	* Generate pagination HTML
	*/
	public static function gen_pagination_html( $ttnum, $mxdata, $crpage, $url, $is_js = false, $url_params = array(), $mxnav = 6 ) {
		if ( $ttnum <= 0 ) {
			return '';
		}
		$mxdata = $mxdata <= 0 ? 1 : $mxdata;
		$ttpg   = ceil( $ttnum / $mxdata );
		if ( $ttpg < $crpage ) {
			return '';
		}
		// calculations
		$mxnav = $ttpg < $mxnav ? $ttpg : $mxnav;

		$mxnav_mid  = floor( $mxnav / 2 );
		$pgstart    = $mxnav_mid >= $crpage ? 1 : $crpage - $mxnav_mid;
		$mxnav_mid += $mxnav_mid >= $crpage ? ( $mxnav_mid - $crpage ) : 0;  // adjusting other half with first half balance
		$pgend      = $crpage + $mxnav_mid;
		if ( $pgend > $ttpg ) {
			$pgend = $ttpg;
		}
		$html              = '';
		$url_params_string = http_build_query( $url_params );
		$url_params_string = $url_params_string != '' ? '&' . $url_params_string : '';
		$prev_onclick      = '';
		if ( $crpage > 1 ) {
			$offset = ( ( $crpage - 2 ) * $mxdata );
			if ( $is_js === false ) {
				$prev_onclick = ' href="?offset=' . $offset . $url_params_string . '"';
			} else {
				$prev_onclick = ' data-cli-offset="' . $offset . '"';
			}
		}
		$html .= '<a class="' . ( $crpage > 1 ? 'cli_page' : 'cli_pagedisabled' ) . '"' . $prev_onclick . '>‹</a>';
		for ( $i = $pgstart; $i <= $pgend; $i++ ) {
			$page_offset = '';
			$onclick     = '';
			$offset      = ( $i * $mxdata ) - $mxdata;
			if ( $i != $crpage ) {
				if ( $is_js === false ) {
					$onclick = ' href="?offset=' . $offset . $url_params_string . '"';
				} else {
					$onclick = ' data-cli-offset="' . $offset . '"';
				}
			}
			$html .= '<a class="' . ( $i == $crpage ? 'cli_pageactive' : 'cli_page' ) . '" ' . $onclick . '>' . $i . '</a>';
		}
		$next_onclick = '';
		if ( $crpage < $ttpg ) {
			$offset = ( $crpage * $mxdata );
			if ( $is_js === false ) {
				$next_onclick = ' href="?offset=' . $offset . $url_params_string . '"';
			} else {
				$next_onclick = ' data-cli-offset="' . $offset . '"';
			}
		}
		$html .= '<a class="' . ( $crpage < $ttpg ? 'cli_page' : 'cli_pagedisabled' ) . '"' . $next_onclick . '>›</a>';
		$html .= '  &nbsp; ' . __( 'Page number', 'webtoffee-gdpr-cookie-consent' ) . ': <input type="number" name="cli_scan_exurl_pageno" min="1" max="' . $ttpg . '" value="' . $crpage . '" style="width:50px;" /> <input type="button" class="button-primary cli_scan_exurl_goto" style="height:26px;" data-mxdata="' . $mxdata . '" name="" value="Go">';
		return '<div class="cli_pagination">' . $html . '</div>';
	}

	/*
	*
	* Checking any cache plugin installed
	*/
	public static function is_cache_plugin_installed() {
		$out = false;
		// Clear Litespeed cache
		if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
			$out = true;
		} elseif ( class_exists( 'SG_CachePress_Supercacher' ) && method_exists( 'SG_CachePress_Supercacher', 'purge_cache' ) ) {
			$out = true;
		} elseif ( class_exists( 'Endurance_Page_Cache' ) && method_exists( 'Endurance_Page_Cache', 'purge_all' ) ) {
			$out = true;
		} elseif ( isset( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
			$out = true;
		}
		return $out;
	}

	/**
	 * Initilaize consent version for renewing consent
	 *
	 * @since    2.2.2
	 * @access   public
	 */
	public static function wt_cli_init_consent_version() {
		$consent_version = get_option( 'wt_cli_consent_version' );
		if ( empty( $consent_version ) ) {
			update_option( 'wt_cli_consent_version', 1 );
		}
	}
	/**
	 * Get consent version for renewing consent
	 *
	 * @since    2.2.2
	 * @access   public
	 */
	public static function wt_cli_get_consent_version() {
		$current_consent_version = 1;
		$consent_version         = get_option( 'wt_cli_consent_version' );
		if ( ! empty( $consent_version ) ) {
			$current_consent_version = (int) $consent_version;
		}
		return $current_consent_version;

	}
	/*
	*
	* Patch script while updating versions
	*/
	public static function cli_patches() {
		$options = self::get_settings();

		// ========bar as widget=========@since 2.1.6
		if ( $options['cookie_bar_as'] == 'banner' && $options['as_popup'] == true ) {
			$options['cookie_bar_as']            = 'popup';
			$options['as_popup']                 = false;
			$options['popup_showagain_position'] = $options['notify_position_vertical'] . '-' . $options['notify_position_horizontal'];
			update_option( CLI_SETTINGS_FIELD, $options );
		}

		// bar heading text issue @since 2.1.4
		$bar_version         = '2.1.3';
		$bar_heading_version = get_option( 'cli_heading_version' );
		if ( $bar_heading_version != $bar_version ) {
			if ( isset( $options['bar_heading_text'] ) && $options['bar_heading_text'] == 'This website uses cookies' ) {
				$options['bar_heading_text'] = '';
				update_option( CLI_SETTINGS_FIELD, $options );
				update_option( 'cli_heading_version', $bar_version );
			}
		}
	}
	public static function wt_cli_category_widget_exist( $bar_message ) {
		if ( isset( $bar_message ) ) {
			$is_exist = strpos( $bar_message, 'wt_cli_category_widget' );
			if ( $is_exist ) {

				return true;
			}
		}
		return false;
	}
	public static function get_strictly_necessory_categories() {

		$strictly_necessary_categories = array( 'necessary', 'obligatoire' );
		$terms                         = get_terms(
			array(
				'taxonomy'   => 'cookielawinfo-category',
				'hide_empty' => false,
				'meta_key'   => 'CLIstrictlynecessary',
				'meta_value' => '1',

			)
		);
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$strictly_necessary_categories[] = $term->slug;
			}
		}
		return apply_filters( 'gdpr_strictly_enabled_category', $strictly_necessary_categories );
	}
	/**
	 * Check if plugin is installed first time
	 *
	 * @since  2.3.1
	 * @return bool
	 */
	public static function check_for_upgrade() {
		$plugin_settings = get_option( CLI_SETTINGS_FIELD );
		if ( $plugin_settings === false ) {

			$transient_value = get_transient( '_wt_cli_first_time_activation' );
			if ( $transient_value === false ) {
				set_transient( '_wt_cli_first_time_activation', true, 30 );
			}
			return true;
		}
		return false;
	}
	/**
	 * Check if plugin is installed first time
	 *
	 * @since  2.3.1
	 * @return bool
	 */
	public static function maybe_first_time_install() {
		$maybe_first_time     = false;
		$activation_transient = wp_validate_boolean( get_transient( '_wt_cli_first_time_activation' ) );
		if ( $activation_transient === true ) {
			$maybe_first_time = true;
		}
		return $maybe_first_time;
	}
}
