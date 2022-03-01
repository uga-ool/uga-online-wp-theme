<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Cookie_Law_Info_Script_Blocker_Frontend extends Cookie_Law_Info_Script_Blocker {

	public $version;

	public $parent_obj; // frontend class of the plugin

	public $plugin_obj; // main class file

	public $module_obj; // script blocker module class object file

	public $buffer_type = 1;

	private $replace_pairs;
	private function cli_check_script_blocker_status() {
		$the_options = Cookie_Law_Info::get_settings();
		if ( $the_options['is_on'] == true ) {
			$cli_sb_status = get_option( 'cli_script_blocker_status' );
			if ( $cli_sb_status === 'disabled' ) {
				return false;
			}
			return true;
		} else {
			return false;
		}

	}
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		$this->replace_pairs = array(
			"\r\n" => '_RNL_',
			"\n"   => '_NL_',
			'<'    => '_LT_',
		);

	}
	public function init() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active( CLI_PLUGIN_BASENAME ) ) {
			$is_script_blocker_enabled = $this->cli_check_script_blocker_status();
			$is_ok                     = false;
			if ( $is_script_blocker_enabled ) {
				$is_ok = true;
			}
			if ( is_admin() || Cookie_Law_Info::wt_cli_is_disable_blocking() ) {
				$is_ok = false;
			}

			if ( $is_ok ) {

				// checking buffer type
				$this->buffer_type = Cookie_Law_Info_Script_Blocker::get_buffer_type();

				add_action( 'template_redirect', array( $this, 'wt_start_custom_buffer' ), $this->cli_get_priority() );
				if ( $this->buffer_type == 2 ) {
					remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
					add_action( 'shutdown', array( $this, 'wt_end_custom_buffer' ), 1 );
				}
			} else {
				return;
			}
		}
	}
	public function wt_start_custom_buffer() {

		ob_start();
		if ( $this->buffer_type == 1 ) {
			ob_start( array( $this, 'wt_end_custom_buffer' ) );
		}
	}
	public function takeBuffer() {
		$buffer_option = get_option( 'cli_sb_buffer_option' );
		if ( $buffer_option ) {
			 $buffer_option = get_option( 'cli_sb_buffer_option' );
		} else {
			$buffer_option = Cookie_Law_Info_Script_Blocker::decideBuffer();
			update_option( 'cli_sb_buffer_option', $buffer_option );
		}
		$buffer = '';
		if ( $buffer_option == 1 ) {
			$level = @ob_get_level();
			for ( $i = 0; $i < $level; $i++ ) {
				$buffer .= @ob_get_clean();
			}
		} else {
			$buffer = @ob_get_contents();
			@ob_end_clean();
		}
		return $buffer;
	}
	public function wt_end_custom_buffer( $buffer = '' ) {
		if ( $this->buffer_type == 2 ) {
			$buffer = $this->takeBuffer();
		}
		try {

			$script_list = $this->get_script_data();

			$third_party_script = $this->cli_getScriptPatterns();

			$viewed_cookie = 'viewed_cookie_policy';

			$scripts = '';

			$wt_cli_placeholder = '';
			$scripts            = apply_filters( 'cli_extend_script_blocker', $scripts );

			if ( $scripts && is_array( $scripts ) ) {
				foreach ( $scripts as $key => $script ) {
					$is_html_element = $html_element = false;
					$script_data     = array();
					if ( isset( $script['html_elem'] ) ) {
						$is_html_element = true;
						$html_element    = $script['html_elem'];
					}
					if ( isset( $script['placeholder'] ) ) {
						$wt_cli_placeholder = $script['placeholder'];
					}
					$script_id = isset( $script['id'] ) ? $script['id'] : '';

					if ( '' !== $script_id ) {
						$label           = isset( $script['label'] ) ? $script['label'] : '';
						$script_key      = isset( $script['key'] ) ? $script['key'] : '';
						$script_category = isset( $script['category'] ) ? $script['category'] : '';
						$status          = isset( $script['status'] ) && 'yes' === $script['status'] ? true : false;
						$load_on_start   = false;
						$ccpa_optout     = false;
						$term            = get_term_by( 'slug', $script_category, 'cookielawinfo-category' );
						if ( $term !== false ) {
							$load_on_start = get_term_meta( $term->term_id, 'CLIloadonstart', true );
							$load_on_start = isset( $load_on_start ) && intval( $load_on_start ) === 1 ? true : false;
							$ccpa_optout   = get_term_meta( $term->term_id, 'CLIccpaoptout', true );
							$ccpa_optout   = isset( $ccpa_optout ) && intval( $ccpa_optout ) === 1 ? true : false;
						}
						$third_party_script[ $script_id ]    = array(
							'label'         => $label,
							'js_needle'     => $script_key,
							'js'            => $script_key,
							'cc'            => $script_category,
							'has_s'         => false,
							'has_js'        => true,
							'has_js_needle' => true,
							'has_uri'       => false,
							'has_cc'        => false,
							'has_html_elem' => $is_html_element,
							'internal_cb'   => true,
							's'             => false,
							'uri'           => false,
							'html_elem'     => $html_element,
							'callback'      => 'cli_automateDefault',
							'placeholder'   => $wt_cli_placeholder,
						);
						$script_data                         = array(
							'id'          => $script_id,
							'label'       => $label,
							'category'    => $script_category,
							'status'      => $status,
							'type'        => 2,
							'loadonstart' => $load_on_start,
							'ccpa_optout' => $ccpa_optout,
						);
						$script_list['custom'][ $script_id ] = $script_data;
					}
				}
			}
			if ( ! empty( $script_list ) ) {

				$scripts         = array();
				$scripts         = apply_filters( 'wt_cli_add_placeholder', $scripts );
				$default_scripts = ( isset( $script_list['default'] ) && is_array( $script_list['default'] ) ) ? $script_list['default'] : array();
				$plugin_scripts  = ( isset( $script_list['plugins'] ) && is_array( $script_list['plugins'] ) ) ? $script_list['plugins'] : array();
				$custom_scripts  = ( isset( $script_list['custom'] ) && is_array( $script_list['custom'] ) ) ? $script_list['custom'] : array();

				$script_list = $default_scripts + $plugin_scripts + $custom_scripts;
				foreach ( $script_list as $key => $script ) {
					$placeholder_text = __( 'Accept consent to view this', 'webtoffee-gdpr-cookie-consent' );
					$script_key       = $key;
					if ( isset( $third_party_script[ $script_key ] ) ) {
						if ( true === $script['status'] ) {

							$third_party_script[ $script_key ]['block_script']  = 'true';
							$third_party_script[ $script_key ]['ccpa_optout']   = 'false';
							$third_party_script[ $script_key ]['category']      = '';
							$third_party_script[ $script_key ]['category_name'] = '';

							if ( '' !== $script['category'] ) { // a category assigned
								$category = get_term_by( 'slug', $script['category'], 'cookielawinfo-category' );

								if ( $category !== false ) {

									if ( true === $script['loadonstart'] ) {
										$third_party_script[ $script_key ]['block_script'] = 'false';
									}

									if ( true === $script['ccpa_optout'] ) {
										$third_party_script[ $script_key ]['ccpa_optout'] = 'true';
									}
									$third_party_script[ $script_key ]['category']      = $script['category'];
									$third_party_script[ $script_key ]['category_name'] = $category->name;
									$placeholder_text                                   = sprintf( __( "Accept <a class='cli_manage_current_consent'>%s</a> cookies to view the content.", 'webtoffee-gdpr-cookie-consent' ), $category->name );
								}
							}
							if ( $scripts && is_array( $scripts ) ) {
								if ( isset( $scripts[ $script_key ] ) ) {
									$wt_cli_custom_script = $scripts[ $script_key ];
									if ( isset( $wt_cli_custom_script['placeholder'] ) ) {
										$placeholder_text = $wt_cli_custom_script['placeholder'];
									}
								}
							}
							$third_party_script[ $script_key ]['placeholder'] = $placeholder_text;
						} else // only codes that was enabled by admin. Unset other items
						{
							unset( $third_party_script[ $script_key ] );
						}
					}
				}
			} else // unable to load cookie table data - May DB error.
			{
				if ( $this->buffer_type == 2 ) {
					echo $buffer;
					exit();
				} else {
					return $buffer;
				}
			}

			// $third_party_script[$key]['check'] = true; - if true  - it will replce the code - it means the code will not render
			foreach ( $third_party_script as $key => $script ) {
				if ( isset( $script['category'] ) ) {
					$category_cookie   = 'cookielawinfo-checkbox-' . $script['category'];
					$preference_cookie = 'CookieLawInfoConsent';
					$ccpa_optout       = false;

					if ( isset( $_COOKIE[ $preference_cookie ] ) ) {
						$json_cookie = json_decode( base64_decode( $_COOKIE[ $preference_cookie ] ) );
						$ccpa_optout = ( isset( $json_cookie->ccpaOptout ) ? $json_cookie->ccpaOptout : false );
					}
					if ( isset( $_COOKIE[ $category_cookie ] ) && isset( $_COOKIE[ $viewed_cookie ] ) ) {
						if ( $_COOKIE[ $category_cookie ] == 'yes' && $_COOKIE[ $viewed_cookie ] == 'yes' ) {
							$third_party_script[ $key ]['check'] = false;
							if ( isset( $third_party_script[ $key ]['ccpa_optout'] ) && $third_party_script[ $key ]['ccpa_optout'] === 'true' && $ccpa_optout === true ) {
								$third_party_script[ $key ]['check'] = true;
							}
							// allowed by user then false
						} else {
							$third_party_script[ $key ]['check'] = true; // not allowed by user then true
						}
					} else {
						$third_party_script[ $key ]['check'] = true; // default it is true so blocks the code
					}
				} else {
					$third_party_script[ $key ]['check'] = false; // not configured from admin then false;
				}
			}
			$buffer = $this->cli_beforeAutomate( $buffer );
			$parts  = $this->cli_getHeadBodyParts( $buffer );
			if ( $parts ) {

				foreach ( $third_party_script as $type => $autoData ) {
					if ( ! isset( $autoData['callback'] ) ) {
						$autoData['callback'] = '_automateDefault';
					}
					$bypass_cache_fallback = apply_filters( 'wt_cli_script_blocker_cache_fallback', true );
					if ( 0 === (int) $autoData['check'] && $bypass_cache_fallback === true ) {
						continue;
					} else {
						$callback = $autoData['callback'];

						if ( $autoData['internal_cb'] ) {
							$callback = array( $this, $callback );
						}
					}
					// set parameters for preg_replace_callback() callback
					$parts = call_user_func_array( $callback, array( $type, $autoData, $parts ) );
				}
				$buffer = $parts['head'] . $parts['split'] . $parts['body'];

			}
			$buffer = $this->cli_afterAutomate( $buffer );
			if ( $this->buffer_type == 2 ) {
				echo $buffer;
				exit();
			} else {
				return $buffer;
			}
		} catch ( Exception $e ) {

			$message = $e->getMessage();
			if ( '' !== $message && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Error: ' . $message . ' in ' . $e->getFile() . ':' . $e->getLine() );
			}
			if ( $this->buffer_type == 2 ) {
				echo $buffer;
				exit();
			} else {
				return $buffer;
			}
		}
		if ( $this->buffer_type == 2 ) {
			echo $buffer;
			exit();
		} else {
			return $buffer;
		}
	}

	public function cli_defineRegex() {
		$regex_array = array(
			'_regexParts'                        => array(
				'-lookbehind_img'        => '(?<!src=")',
				'-lookbehind_link'       => '(?<!href=")',
				'-lookbehind_link_img'   => '(?<!href=")(?<!src=")',
				'-lookbehind_shortcode'  => '(?<!])',
				'-lookbehind_after_body' => '(?<=\<body\>)',
				'-lookahead_body_end'    => '(?=.*\</body\>)',
				'-lookahead_head_end'    => '(?=.*\</head\>)',
				'random_chars'           => '[^\s\["\']+',
				'src_scheme_www'         => '(?:https?://|//)?(?:[www\.]{4})?',
			),
			'_regexPatternScriptBasic'           => '\<script' .
			'.+?' .
			'\</script\>',
			'_regexPatternScriptTagOpen'         => '\<script[^\>]*?\>',
			'_regexPatternScriptTagClose'        => '\</script\>',
			'_regexPatternScriptAllAdvanced'     => '\<script' .
			'[^>]*?' .
			'\>' .
			'(' .
			'(?!\</script\>)' .
			'.*?' .
			')' .
			'?' .
			'\</script\>',
			'_regexPatternScriptHasNeedle'       => '\<script' .
			'[^>]*?' .
			'\>' .
			'(?!\</script>)' .
			'[^<]*' .
			'%s' .
			'[^<](?s).*?' .
			'\</script\>',
			'_regexPatternScriptSrc'             => '\<script' .
			'[^>]+?' .
			'src=' .
			'("|\')' .
			'(' .
			'(https?:)?' .
			'//(?:[www\.]{4})?' .
			'%s' .
			'%s' .
			'[^\s"\']*?' .
			')' .
			'("|\')' .
			'[^>]*' .
			'\>' .
			'[^<]*' .
			'\</script\>',
			'_regexPatternIframeBasic'           => '\<iframe' .
			'.+?' .
			'\</iframe\>',
			'_regexPatternIframe'                => '\<iframe' .
			'[^>]+?' .
			'src=' .
			'("|\')' .
			'(' .
			'(https?://|//)?' .
			'(?:[www\.]{4})?' .
			'%s' .
			'%s' .
			'[^"\']*?' .
			')' .
			'("|\')' .
			'[^>]*' .
			'\>' .
			'(?:' .
			'(?!\<iframe).*?' .
			')' .
			'\</iframe\>',
			'_regexPatternHtmlElemWithAttr'      => '\<%s' .
			'[^>]+?' .
			'%s\s*=\s*' .
			'(?:"|\')' .
			'(?:' .
			'%s' .
			'%s' .
			'[^"\']*?' .
			')' .
			'(?:"|\')' .
			'[^>]*' .
			'(?:' .
			'\>' .
			'\s*'.
			'(' .
			'(?!\<%s).*?' .
			')' .
			'\</%s\>' .
			'|' .
			'/\>' .
			')',
			'_regexPatternHtmlElemWithAttrTypeA' => '\<%s' .
			'[^>]+?' .
			'%s=' .
			'(?:"|\')' .
			'(?:' .
			'%s' .
			'%s' .
			'[^"\']*?' .
			')' .
			'(?:"|\')' .
			'[^>]*' .
			'(?:' .
			'\>' .
			')',

		);
		return apply_filters( 'wt_cli_script_blocker_regex', $regex_array );
	}

	public function cli_beforeAutomate( $content ) {

		$textarr                     = wp_html_split( $content );
		$regex_patterns              = $this->cli_defineRegex();
		$_regexPatternScriptTagOpen  = $regex_patterns['_regexPatternScriptTagOpen'];
		$_regexPatternScriptTagClose = $regex_patterns['_regexPatternScriptTagClose'];
		$changed                     = false;
		$replacePairs                = apply_filters( 'wt_cli_script_blocker_replace_keys', $this->replace_pairs );
		$c                           = count( $textarr );
		$is_script                   = false;
		foreach ( $replacePairs as $needle => $replace ) {
			foreach ( $textarr as $i => $html ) {
				if ( preg_match( "#^$_regexPatternScriptTagOpen#", $textarr[ $i ], $m ) ) {
					if ( false !== strpos( $textarr[ $i + 1 ], $needle ) ) {
						$textarr[ $i + 1 ] = str_replace( $needle, $replace, $textarr[ $i + 1 ] );
						$changed           = true;
					}

					if ( '<' === $needle && $needle === $textarr[ $i + 2 ][0] && '</script>' !== $textarr[ $i + 2 ] ) {
						$textarr[ $i + 2 ] = preg_replace( '#\<(?!/script\>)#', $replace, $textarr[ $i + 2 ] );
					}
				}
			}
		}
		if ( $changed ) {
			$content = implode( $textarr );
		}
		unset( $textarr );

		return $content;
	}

	public function cli_getHeadBodyParts( $buffer ) {

		$parts   = array(
			'head'  => '',
			'body'  => '',
			'split' => '',
		);
		$pattern = '#\</head\>[^<]*\<body[^\>]*?\>#';

		if ( preg_match( $pattern, $buffer, $m ) ) {

			$splitted = preg_split( $pattern, $buffer );
			if ( 2 !== count( $splitted ) ) {
				throw new RuntimeException( 'Could not split content in <head> and <body> parts.' );
			}
			$parts['head']  = $splitted[0];
			$parts['body']  = $splitted[1];
			$parts['split'] = $m[0];
			unset( $splitted );
			return $parts;

		}

		return false;
	}
	public function cli_afterAutomate( $content ) {

		$replace_pairs  = apply_filters( 'wt_cli_script_blocker_replace_keys', $this->replace_pairs );
		$replace_keys   = array_keys( $replace_pairs );
		$replace_values = array_values( $replace_pairs );
		return str_replace( $replace_values, $replace_keys, $content );
	}
	public function cli_getScriptPatterns() {

		$third_party_script = array();

		$third_party_script['googleanalytics'] = array(
			'label'     => __( 'Google Analytics', 'webtoffee-gdpr-cookie-consent' ),
			's'         => 'google-analytics.com',
			'js'        => 'www.google-analytics.com/analytics.js',
			'js_needle' => array(
				'www.google-analytics.com/analytics.js',
				'google-analytics.com/ga.js',
				'stats.g.doubleclick.net/dc.js',
				'window.ga=window.ga',
				'_getTracker',
				'__gaTracker',
				'GoogleAnalyticsObject',
			),
			'cc'        => 'analytical',
		);

		$third_party_script['facebook_pixel'] = array(
			'label'     => __( 'Facebook Pixel Code', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'connect.facebook.net/en_US/fbevents.js',
			'js_needle' => array(
				'connect.facebook.net/en_US/fbevents.js',
				'fbq',
				'facebook-jssdk',
			),
			'cc'        => 'analytical',
			'html_elem' => array(
				'name' => 'img',
				'attr' => 'src:facebook.com/tr',
			),
		);

		$third_party_script['google_tag_manager'] = array(
			'label'     => __( 'Google Tag Manager', 'webtoffee-gdpr-cookie-consent' ),
			's'         => 'www.googletagmanager.com/ns.html?id=GTM-',
			'js'        => 'googletagmanager.com/gtag/js',
			'js_needle' => array( 'www.googletagmanager.com/gtm' ),
			'cc'        => 'analytical',
		);

		$third_party_script['hotjar'] = array(
			'label'     => __( 'Hotjar', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => false,
			'js_needle' => array( 'static.hotjar.com/c/hotjar-' ),
			'cc'        => 'analytical',
		);

		$third_party_script['google_publisher_tag'] = array(
			'label'     => __( 'Google Publisher Tag', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => array( 'www.googletagservices.com/tag/js/gpt.js', 'www.googleadservices.com/pagead/conversion.js' ),
			'js_needle' => array( 'googletag.pubads', 'googletag.enableServices', 'googletag.display', 'www.googletagservices.com/tag/js/gpt.js', 'www.googleadservices.com/pagead/conversion.js' ),
			'cc'        => 'advertising',
			'html_elem' => array(
				array(
					'name' => 'img',
					'attr' => 'src:pubads.g.doubleclick.net/gampad',
				),
				array(
					'name' => 'img',
					'attr' => 'src:googleads.g.doubleclick.net/pagead',
				),
			),
		);

		$third_party_script['youtube_embed'] = array(
			'label'     => __( 'Youtube embed', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'www.youtube.com/player_api',
			'js_needle' => array( 'www.youtube.com/player_api', 'onYouTubePlayerAPIReady', 'YT.Player', 'onYouTubeIframeAPIReady', 'www.youtube.com/iframe_api' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:www.youtube.com/embed',
				),
				array(
					'name' => 'iframe',
					'attr' => 'src:youtu.be',
				),
				array(
					'name' => 'object',
					'attr' => 'data:www.youtube.com/embed',
				),
				array(
					'name' => 'embed',
					'attr' => 'src:www.youtube.com/embed',
				),
				array(
					'name' => 'img',
					'attr' => 'src:www.youtube.com/embed',
				),
			),
		);

		$third_party_script['vimeo_embed']  = array(
			'label'     => __( 'Vimeo embed', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'player.vimeo.com/api/player.js',
			'js_needle' => array( 'www.vimeo.com/api/oembed', 'player.vimeo.com/api/player.js', 'Vimeo.Player', 'new Player' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:player.vimeo.com/video',
				),
			),
		);
		$third_party_script['libsyn_embed'] = array(
			'label'     => __( 'Libsyn', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'cdn.embed.ly/player-0.0.12.min.js',
			'js_needle' => array( 'html5-player.libsyn.com/embed' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:html5-player.libsyn.com',
				),
			),
		);
		$third_party_script['google_maps']  = array(
			'label'     => __( 'Google maps', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'maps.googleapis.com/maps/api',
			'js_needle' => array( 'maps.googleapis.com/maps/api', 'google.map', 'initMap' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:www.google.com/maps/embed',
				),
				array(
					'name' => 'iframe',
					'attr' => 'src:maps.google.com/maps',
				),
			),
		);

		$third_party_script['addthis_widget'] = array(
			'label'     => __( 'Addthis widget', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 's7.addthis.com/js',
			'js_needle' => array( 'addthis_widget' ),
			'cc'        => 'social-media',
		);

		$third_party_script['sharethis_widget'] = array(
			'label'     => __( 'Sharethis widget', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'platform-api.sharethis.com/js/sharethis.js',
			'js_needle' => array( 'sharethis.js' ),
			'cc'        => 'social-media',
		);

		$third_party_script['twitter_widget'] = array(
			'label'     => __( 'Twitter widget', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'platform.twitter.com/widgets.js',
			'js_needle' => array( 'platform.twitter.com/widgets.js', 'twitter-wjs', 'twttr.widgets', 'twttr.events', 'twttr.ready', 'window.twttr' ),
			'cc'        => 'social-media',
		);

		$third_party_script['soundcloud_embed'] = array(
			'label'     => __( 'Soundcloud embed', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'connect.soundcloud.com',
			'js_needle' => array( 'SC.initialize', 'SC.get', 'SC.connectCallback', 'SC.connect', 'SC.put', 'SC.stream', 'SC.Recorder', 'SC.upload', 'SC.oEmbed', 'soundcloud.com' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:w.soundcloud.com/player',
				),
				array(
					'name' => 'iframe',
					'attr' => 'src:api.soundcloud.com',
				),
			),
		);

		$third_party_script['slideshare_embed'] = array(
			'label'     => __( 'Slideshare embed', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'www.slideshare.net/api/oembed',
			'js_needle' => array( 'www.slideshare.net/api/oembed' ),
			'cc'        => 'other',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:www.slideshare.net/slideshow',
				),
			),
		);

		$third_party_script['linkedin_widget'] = array(
			'label'     => __( 'Linkedin widget/Analytics', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'platform.linkedin.com/in.js',
			'js_needle' => array( 'platform.linkedin.com/in.js', 'snap.licdn.com/li.lms-analytics/insight.min.js', '_linkedin_partner_id' ),
			'cc'        => 'social-media',
			'html_elem' => array(
				array(
					'name' => 'img',
					'attr' => 'src:dc.ads.linkedin.com/collect/',
				),
			),
		);

		$third_party_script['instagram_embed'] = array(
			'label'     => __( 'Instagram embed', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'www.instagram.com/embed.js',
			'js_needle' => array( 'www.instagram.com/embed.js', 'api.instagram.com/oembed' ),
			'cc'        => 'social-media',
			'html_elem' => array(
				array(
					'name' => 'iframe',
					'attr' => 'src:www.instagram.com/p',
				),
			),
		);

		$third_party_script['pinterest']          = array(
			'label'     => __( 'Pinterest widget', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'assets.pinterest.com/js/pinit.js',
			'js_needle' => array( 'assets.pinterest.com/js/pinit.js' ),
			'cc'        => 'social-media',
		);
		$third_party_script['google_adsense_new'] = array(
			'label'     => __( 'Google Adsense', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
			'js_needle' => array( 'adsbygoogle.js' ),
			'cc'        => 'analytical',
		);
		$third_party_script['hubspot_analytics']  = array(
			'label'     => __( 'Hubspot Analytics', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'js.hs-scripts.com',
			'js_needle' => array( 'js.hs-scripts.com' ),
			'cc'        => 'analytical',
		);

		$third_party_script['matomo_analytics'] = array(
			'label'     => __( 'Matomo Analytics', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => 'matomo.js',
			'js_needle' => array( '_paq.push', '_mtm.push' ),
			'cc'        => 'analytical',
		);
		$third_party_script['google_recaptcha'] = array(
			'label'     => __( 'Google Recaptcha', 'webtoffee-gdpr-cookie-consent' ),
			'js'        => array(
				'www.google.com/recaptcha/api.js',
				'recaptcha.js',
				'recaptcha/api',
			),
			'cc'        => 'functional',
		);

		$third_party_script = apply_filters( 'wt_cli_third_party_scripts', $third_party_script );

		foreach ( $third_party_script as $key => $data ) {

			if ( ! is_string( $key ) ) {
				throw new Exception( sprintf( __( "Invalid index found in the thirdparties array. Index should be of type 'string'. Index found: %d.", 'webtoffee-gdpr-cookie-consent' ), $key ) );
				break;
			}

			$s     = $label = $js = $jsNeedle = $uri = $cb = $htmlElem = null;
			$hasJs = $hasJsNeedle = $hasUri = false;

			$defaultCallback = '_automate' . ucfirst( $key );

			$defaultCallbackExist = function_exists( $defaultCallback );

			$third_party_script[ $key ]['has_s']         = false;
			$third_party_script[ $key ]['has_js']        = false;
			$third_party_script[ $key ]['has_js_needle'] = false;
			$third_party_script[ $key ]['has_uri']       = false;
			$third_party_script[ $key ]['has_cc']        = false;
			$third_party_script[ $key ]['has_html_elem'] = false;
			$third_party_script[ $key ]['internal_cb']   = false;

			if ( ! isset( $data['label'] ) ) {
				$label                               = ucfirst( $key );
				$third_party_script[ $key ]['label'] = $label;
			} elseif ( is_string( $data['label'] ) ) {
				$label                               = sanitize_text_field( $data['label'] );
				$third_party_script[ $key ]['label'] = $label;
			}

			if ( ! isset( $data['s'] ) ) {
				$third_party_script[ $key ]['s'] = $s;
			} elseif ( is_string( $data['s'] ) ) {
				$s                                   = sanitize_text_field( $data['s'] );
				$third_party_script[ $key ]['s']     = $s;
				$third_party_script[ $key ]['has_s'] = true;
			} elseif ( is_array( $data['s'] ) ) {
				foreach ( $data['s'] as $k => $v ) {
					if ( is_string( $v ) ) {
						$third_party_script[ $key ]['s'][ $k ] = sanitize_text_field( $v );
						$has_s                                 = true;
					} else {
						$third_party_script[ $key ]['s'] = $s;
						$has_s                           = false;
						break;
					}
				}
				$third_party_script[ $key ]['has_s'] = $has_s;
			}

			if ( ! isset( $data['js'] ) ) {
				$third_party_script[ $key ]['js'] = $js;
			} elseif ( is_string( $data['js'] ) ) {
				$js                                   = sanitize_text_field( $data['js'] );
				$third_party_script[ $key ]['js']     = $js;
				$third_party_script[ $key ]['has_js'] = true;
			} elseif ( is_array( $data['js'] ) ) {
				foreach ( $data['js'] as $k => $v ) {
					if ( is_string( $v ) ) {
						$third_party_script[ $key ]['js'][ $k ] = sanitize_text_field( $v );
						$hasJs                                  = true;
					} else {
						$third_party_script[ $key ]['js'] = $js;
						$hasJs                            = false;
						break;
					}
				}
				$third_party_script[ $key ]['has_js'] = $hasJs;
			}

			if ( ! isset( $data['js_needle'] ) ) {
				$third_party_script[ $key ]['js_needle'] = $jsNeedle;
			} elseif ( is_string( $data['js_needle'] ) ) {
				$jsNeedle                                    = sanitize_text_field( $data['js_needle'] );
				$third_party_script[ $key ]['js_needle']     = $jsNeedle;
				$third_party_script[ $key ]['has_js_needle'] = true;
			} elseif ( is_array( $data['js_needle'] ) ) {
				foreach ( $data['js_needle'] as $k => $v ) {
					if ( is_string( $v ) ) {
						$third_party_script[ $key ]['js_needle'][ $k ] = sanitize_text_field( $v );
						$hasJsNeedle                                   = true;
					} else {
						$third_party_script[ $key ]['js_needle'] = $jsNeedle;
						$hasJsNeedle                             = false;
						break;
					}
				}
				$third_party_script[ $key ]['has_js_needle'] = $hasJsNeedle;
			}

			if ( ! isset( $data['uri'] ) ) {
				$third_party_script[ $key ]['uri'] = $uri;
			} elseif ( is_string( $data['uri'] ) ) {
				$uri                                   = esc_url_raw( $data['uri'], array( 'http', 'https' ) );
				$third_party_script[ $key ]['uri']     = $uri;
				$third_party_script[ $key ]['has_uri'] = true;
			} elseif ( is_array( $data['uri'] ) ) {
				foreach ( $data['uri'] as $k => $v ) {
					if ( is_string( $v ) ) {
						$third_party_script[ $key ]['uri'][ $k ] = esc_url_raw( $v, array( 'http', 'https' ) );
						$hasUri                                  = true;
					} else {
						$third_party_script[ $key ]['uri'] = $uri;
						$hasUri                            = false;
						break;
					}
				}
				$third_party_script[ $key ]['has_uri'] = $hasUri;
			}

			if ( isset( $data['callback'] ) && is_string( $data['callback'] ) && ! empty( $data['callback'] ) ) {
				$cb = trim( $data['callback'] );
			} elseif ( isset( $data['callback'] ) && is_array( $data['callback'] ) && 2 === count( $data['callback'] ) ) {
				$cbMethod            = trim( $data['callback'][1] );
				$data['callback'][1] = $cbMethod;
				$cb                  = & $data['callback'];
			} elseif ( ! isset( $data['callback'] ) && $defaultCallbackExist ) {
				$cb = $defaultCallback;
			} else {
				$cb = 'cli_automateDefault';
			}

			if ( ! isset( $data['cc'] ) ) {
				$third_party_script[ $key ]['cc'] = 'other';
			} elseif ( is_string( $data['cc'] ) ) {
				$third_party_script[ $key ]['has_cc'] = true;
				$cc                                   = sanitize_title( $data['cc'] );
				if ( ! $this->cli_isAllowedCookieCategory( $cc ) ) {
					$third_party_script[ $key ]['cc'] = 'other';
				} else {
					$third_party_script[ $key ]['cc'] = $cc;
				}
			}

			if ( isset( $data['html_elem'] ) ) {
				if ( is_array( $data['html_elem'] ) && isset( $data['html_elem'][0] ) ) {
					$third_party_script[ $key ]['html_elem'] = array();
					for ( $i = 0; $i < count( $data['html_elem'] ); $i++ ) {
						$this->processHTMLelm( $data['html_elem'][ $i ], $third_party_script[ $key ], $i ); // $data['html_elem'], $third_party_script[$key]
					}
				} else {
					$third_party_script[ $key ]['html_elem'] = array();
					$this->processHTMLelm( $data['html_elem'], $third_party_script[ $key ], 0 ); // $data['html_elem'], $third_party_script[$key]
				}
			} else {
				$third_party_script[ $key ]['html_elem'] = $htmlElem;
			}

			if ( method_exists( $this, $cb ) ) {
				$third_party_script[ $key ]['internal_cb'] = true;
			}
			$third_party_script[ $key ]['callback'] = $cb;
		}
		return $third_party_script;
	}
	public function processHTMLelm( &$data, &$third_party_script, $i ) {
		// $data['html_elem'], $third_party_script[$key]
		$third_party_script['html_elem'][ $i ] = array();
		if ( ! isset( $data['name'] ) ) {
			$third_party_script['html_elem'][ $i ]['name'] = null;
		} elseif ( isset( $data['name'] ) && ! is_string( $data['name'] ) ) {
			$third_party_script['html_elem'][ $i ]['name'] = null;
		} elseif ( ! isset( $data['attr'] ) ) {
			$third_party_script['html_elem'][ $i ]['attr'] = null;
		} elseif ( isset( $data['attr'] ) && ! is_string( $data['attr'] ) ) {
			$third_party_script['html_elem'][ $i ]['attr'] = null;
		} elseif ( isset( $data['attr'] ) ) {
			$pos = strpos( $data['attr'], ':' );
			if ( false === $pos || $pos < 1 ) {
				$third_party_script['html_elem'][ $i ]['attr'] = null;
			}
		}
		if ( null !== $data['name'] ) {
			$third_party_script['html_elem'][ $i ]['name'] = sanitize_key( $data['name'] );
		}
		if ( null !== $data['attr'] ) {
			$attr = trim( $data['attr'] );
			$third_party_script['html_elem'][ $i ]['attr'] = $attr;
			$attrArr                                       = explode( ':', $attr );
			$k = sanitize_key( $attrArr[0] );
			$v = sanitize_html_class( $attrArr[1] );
			// $third_party_script['html_elem'][$i]['attr'] = "$k:$v";
			$third_party_script['has_html_elem'] = true;
		}
	}
	public function cli_isAllowedCookieCategory() {

		return array(
			'functional',
			'analytical',
			'social-media',
			'advertising',
			'other',
		);
	}

	public function cli_automateDefault( $type = null, $autoData = array(), $parts = array() ) {

		$patterns    = array();
		$hasS        = $autoData['has_s'];
		$hasJs       = $autoData['has_js'];
		$hasJsNeedle = $autoData['has_js_needle'];
		$hasUri      = $autoData['has_uri'];
		$hasHtmlElem = $autoData['has_html_elem'];

		$regex = $this->cli_defineRegex();

		if ( $hasUri ) {
			$uri = $autoData['uri'];

			$uriPattTmpl = $regex['_regexParts']['-lookbehind_link_img'] . 'https?://(?:[www\.]{4})?%s';
			foreach ( (array) $uri as $u ) {
				$url        = $this->cli_getUriWithoutSchemaSubdomain( $u );
				$url        = str_replace( '*', $regex['_regexParts']['random_chars'], $url );
				$escapedUri = $this->cli_escapeRegexChars( $url );
				$patt       = sprintf( $uriPattTmpl, $escapedUri );
				$patterns[] = $patt;
			}
		}

		if ( $hasS ) {
			$s = $autoData['s'];
			foreach ( (array) $s as $term ) {
				$cleanUri   = $this->cli_getCleanUri( $term, true );
				$subdmain   = ( '' !== $cleanUri && '.' === $cleanUri[0] ) ? '[^.]+?' : '';
				$escapedUri = $this->cli_escapeRegexChars( $cleanUri );
				$patt       = sprintf( $regex['_regexPatternIframe'], $subdmain, $escapedUri );
				$patterns[] = $patt;
			}
		}

		if ( $hasJs ) {
			$js = $autoData['js'];
			foreach ( (array) $js as $script ) {
				$hasPluginUri     = false;
				$cleanUri         = $this->cli_getCleanUri( $script, true );
				$allowedLocations = array(
					'plugin' => 'wp-content/plugins',
					'theme'  => 'wp-content/themes',
				);
				if ( '' !== $cleanUri && ! empty( $allowedLocations ) && preg_match( '#^' . join( '|', $allowedLocations ) . '#', $cleanUri ) ) {
					$home_url   = get_site_url();
					$parsed_url = parse_url( $home_url );
					$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
					if ( empty( $path ) ) {
						$hasPluginUri = true;
						$uriBegin     = trailingslashit( $this->cli_getCleanUri( $home_url ) );
					} else {
						$uriBegin = '[^.]+?.';
					}
				} elseif ( '' !== $cleanUri && '.' === $cleanUri[0] ) {
					$uriBegin = '[^.]+?';
				} else {
					$uriBegin = '';
				}
				$escapedUri = $this->cli_escapeRegexChars( $cleanUri );
				if ( $hasPluginUri ) {
					$uriBegin = $this->cli_escapeRegexChars( $uriBegin );
				}

				$patt       = sprintf( $regex['_regexPatternScriptSrc'], $uriBegin, $escapedUri );
				$patterns[] = $patt;
			}
		}

		if ( $hasJsNeedle ) {
			$jsNeedle = $autoData['js_needle'];
			foreach ( (array) $jsNeedle as $needle ) {
				$escaped    = $this->cli_escapeRegexChars( $needle );
				$patt       = sprintf( $regex['_regexPatternScriptHasNeedle'], $escaped );
				$patterns[] = $patt;
			}
		}

		if ( $hasHtmlElem ) {

			for ( $j = 0; $j < count( $autoData['html_elem'] ); $j++ ) {
				$htmlElemAttr      = explode( ':', $autoData['html_elem'][ $j ]['attr'] );
				$htmlElemName      = $this->cli_escapeRegexChars( $autoData['html_elem'][ $j ]['name'] );
				$htmlElemAttrName  = $this->cli_escapeRegexChars( $htmlElemAttr[0] );
				$htmlElemAttrValue = $this->cli_escapeRegexChars( $htmlElemAttr[1] );
				$prefix            = '';
				if ( ( $htmlElemAttrName == 'src' ) || ( $htmlElemAttrName == 'data' && $htmlElemName == 'object' ) ) {
					$prefix = $regex['_regexParts']['src_scheme_www'];
				}
				if ( ( $htmlElemName == 'img' ) || ( $htmlElemName == 'embed' ) ) {
					$patterns[] = sprintf( $regex['_regexPatternHtmlElemWithAttrTypeA'], $htmlElemName, $htmlElemAttrName, $prefix, $htmlElemAttrValue );
				} else {

					$patterns[] = sprintf( $regex['_regexPatternHtmlElemWithAttr'], $htmlElemName, $htmlElemAttrName, $prefix, $htmlElemAttrValue, $htmlElemName, $htmlElemName );
				}
			}
		}
		return $this->wt_cli_prepare_script( $patterns, '', $type, $parts, $autoData );
	}

	public function cli_getUriWithoutSchemaSubdomain( $uri = '', $subdomain = 'www' ) {

		$uri = preg_replace( "#(https?://|//|$subdomain\.)#", '', $uri );
		return ( null === $uri ) ? '' : $uri;
	}

	public function cli_automate( $patterns = '', $modifiers = '', $type = null, $autoData = array(), $parts = array() ) {

		$action = 'erase';
		switch ( $action ) {
			case 'erase':
			case 'erase-all':
				return $this->cli_erase( $patterns, $modifiers, $type, $parts, $autoData );
				break;
			default:
				throw new Exception( sprintf( __( 'Action is unknown.', 'webtoffee-gdpr-cookie-consent' ) ) );
				break;
		}
	}
	public function wt_cli_prepare_script( $patterns = '', $modifiers = '', $type = null, $parts = array(), $autoData = array() ) {

		$prefix         = '(?:\<!--\s+\[cli_skip]\s+--\>_NL_)?';
		$wrapperPattern = '#' . $prefix . '%s#' . $modifiers;
		$pattern        = $replacement = array();

		foreach ( $patterns as $pttrn ) {
			$pattern[] = sprintf( $wrapperPattern, $pttrn );
		}

		if ( ! isset( $parts['head'] ) || ! isset( $parts['body'] ) ) {
			throw new InvalidArgumentException( 'Parts array is not valid for ' . $type . ': head or body entry not found.' );
		}
		$parts['head'] = $this->script_replace_callback( $parts['head'], $pattern, $autoData, 'head' );
		if ( null === $parts['head'] ) {
			throw new RuntimeException( 'An error occured calling preg_replace_callback() context head.' );
		}

		$prefix         = '((?:\<!--\s+\[cli_skip]\s+--\>_NL_)?';
		$suffix         = ')';
		$wrapperPattern = '#' . $prefix . '%s' . $suffix . '#' . $modifiers;
		$pattern        = $replacement = array();
		foreach ( $patterns as $pttrn ) {
			$pattern[] = sprintf( $wrapperPattern, $pttrn );
		}
		$parts['body'] = $this->script_replace_callback( $parts['body'], $pattern, $autoData, 'body' );
		if ( null === $parts['body'] ) {
			throw new RuntimeException( 'An error occured calling preg_replace_callback() context body.' );
		}

		return $parts;
	}
	public function script_replace_callback( $html, $pattern, $autoData, $elm_position = 'head' ) {

		return preg_replace_callback(
			$pattern,
			function( $matches ) use ( $autoData, $elm_position ) {

				$placeholder_text     = '';
				$script_cat_slug      = ( isset( $autoData['category'] ) ? $autoData['category'] : '' );
				$script_label         = ( isset( $autoData['label'] ) ? $autoData['label'] : '' );
				$script_load_on_start = ( isset( $autoData['block_script'] ) ? $autoData['block_script'] : 'true' );
				$ccpa_optout          = ( isset( $autoData['ccpa_optout'] ) ? $autoData['ccpa_optout'] : 'false' );
				$script_type          = 'text/plain';
				$match                = $matches[0];

				if ( isset( $autoData['placeholder'] ) ) {
					$placeholder_text = $autoData['placeholder'];
				}
				$wt_cli_replace = 'data-cli-class="cli-blocker-script" data-cli-label="' . $script_label . '"  data-cli-script-type="' . $script_cat_slug . '" data-cli-block="' . $script_load_on_start . '" data-cli-block-if-ccpa-optout="' . $ccpa_optout . '" data-cli-element-position="' . $elm_position . '"';

				if ( strpos( $match, 'data-cli-class' ) === false ) {

					if ( ( preg_match( '/<iframe[^>]+?(data-src\s*=\s*(?:"|\')(.*)(?:"|\')).*>.*<\/iframe>/i', $match, $element_match ) )
						|| ( preg_match( '/<iframe[^>]+?(src\s*=\s*(?:"|\')(.*)(?:"|\'))[^>]*>\s*<\/iframe>/i', $match, $element_match ) )
						|| ( preg_match( '/<object.*(src=\"(.*)\").*>.*<\/object >/', $match, $element_match ) )
						|| ( preg_match( '/<embed.*(src=\"(.*)\").*>/', $match, $element_match ) )
						|| ( preg_match( '/<img.*(src=\"(.*)\").*>/', $match, $element_match ) ) ) {

							$element_src        = $element_match[1];
							$element_modded_src = preg_replace( '/(data-src=|src=)/', $wt_cli_replace . ' data-cli-placeholder="' . $placeholder_text . '" data-cli-src=', $element_src, 1 );
							$match              = str_replace( $element_src, $element_modded_src, $match );

					} else {
						$regex_patterns = array (
							'regexPatternScriptType' => '/<script(?s).*?(type=(?:"|\')(.*?)(?:"|\')).*?>/',
							'regexPatternScriptTypeValue' => '/<script(?s).*?(type=(?:"|\')text\/javascript(.*?)(?:"|\')).*?>/',
						);
						$regex_patterns = apply_filters( 'wt_cli_script_blocker_regex_patterns', $regex_patterns );

							
						if ( !empty( $regex_patterns['regexPatternScriptType'] ) && !empty( $regex_patterns['regexPatternScriptTypeValue'] ) && preg_match( $regex_patterns['regexPatternScriptType'], $match ) && preg_match( $regex_patterns['regexPatternScriptTypeValue'], $match ) ) {
							preg_match( $regex_patterns['regexPatternScriptTypeValue'], $match, $output_array );

							$re = preg_quote( $output_array[1], '/' );

							if ( ! empty( $output_array ) ) {

								$match = preg_replace( '/' . $re . '/', 'type="' . $script_type . '"' . ' ' . $wt_cli_replace, $match, 1 );

							}
						} else {

							$match = str_replace( '<script', '<script type="' . $script_type . '"' . ' ' . $wt_cli_replace, $match );

						}
					}
				}
				return $match;
			},
			$html
		);
	}
	public function cli_escapeRegexChars( $str = '' ) {

		$chars = array( '^', '$', '(', ')', '<', '>', '.', '*', '+', '?', '[', '{', '\\', '|' );

		foreach ( $chars as $k => $char ) {
			$chars[ $k ] = '\\' . $char;
		}

		$replaced = preg_replace( '#(' . join( '|', $chars ) . ')#', '\\\${1}', $str );

		return ( null !== $replaced ) ? $replaced : $str;
	}

	public function cli_getCleanUri( $uri = '', $stripSubDomain = false, $subdomain = 'www' ) {

		if ( ! is_string( $uri ) ) {
			return '';
		}

		$regexSubdomain = '';
		if ( $stripSubDomain && is_string( $subdomain ) ) {
			$subdomain = trim( $subdomain );
			if ( '' !== $subdomain ) {
				$regexSubdomain = $this->cli_escapeRegexChars( "$subdomain." );
			}
		}

		$regex = '^' .
				'https?://' .
				$regexSubdomain .
				'([^/?]+)' .
				'(.*)' .
				'$';

		$uri = preg_replace( "#$regex#", '${1}', $uri );
		return ( null === $uri ) ? '' : $uri;
	}

	/**
     * Get the priority value for buffer
     * @since  2.3.6
	 * @return int
     */
	public function cli_get_priority() {
		$cli_priority = 9999;
		if( $this->is_active_all_in_one_seo() ) {
			$cli_priority = 10;
		}

		return $cli_priority;
	}

	/**
     * Check whether "All in One SEO" plugin is active or not
     * @since  2.3.6
	 * @return bool
     */
	public function is_active_all_in_one_seo() {

		return defined( 'AIOSEO_FILE' );
	}
}
new Cookie_Law_Info_Script_Blocker_Frontend( $this );
