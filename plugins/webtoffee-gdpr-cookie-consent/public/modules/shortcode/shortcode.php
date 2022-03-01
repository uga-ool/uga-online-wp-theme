<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/*
	===============================================================================

	Copyright 2018 @ WebToffee

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


class Cookie_Law_Info_Shortcode {

	public $version;

	public $parent_obj; // instance of the class that includes this class

	public $plugin_obj;

	public $plugin_name;
	public $enable_shortcode;
	public $cookie_options;
	public function __construct( $parent_obj ) {
		$this->version     = $parent_obj->version;
		$this->parent_obj  = $parent_obj;
		$this->plugin_obj  = $parent_obj->plugin_obj;
		$this->plugin_name = $parent_obj->plugin_name;

		$this->enable_shortcode = true;
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( CLI_PLUGIN_BASENAME ) ) {
			// Shortcodes:
			add_shortcode( 'delete_cookies', array( $this, 'cookielawinfo_delete_cookies_shortcode' ) ); // a shortcode [delete_cookies (text="Delete Cookies")]
			add_shortcode( 'cookie_audit', array( $this, 'cookielawinfo_table_shortcode' ) );           // a shortcode [cookie_audit style="winter"]
			add_shortcode( 'cookie_audit_category', array( $this, 'cookielawinfo_category_table_shortcode' ) ); // a shortcode [cookie_audit_category style="winter"]
			add_shortcode( 'cookie_accept', array( $this, 'cookielawinfo_shortcode_accept_button' ) );      // a shortcode [cookie_accept (colour="red")]
			add_shortcode( 'cookie_reject', array( $this, 'cookielawinfo_shortcode_reject_button' ) );      // a shortcode [cookie_reject (colour="red")]
			add_shortcode( 'cookie_settings', array( $this, 'cookielawinfo_shortcode_settings_button' ) );      // a shortcode [cookie_reject (colour="red")]
			add_shortcode( 'cookie_link', array( $this, 'cookielawinfo_shortcode_more_link' ) );            // a shortcode [cookie_link]
			add_shortcode( 'cookie_button', array( $this, 'cookielawinfo_shortcode_main_button' ) );
			add_shortcode( 'cookie_accept_all', array( $this, 'cookielawinfo_accept_all_button' ) );      // a shortcode [cookie_button]
			add_shortcode( 'cookie_close', array( $this, 'cookielawinfo_shortcode_close_button' ) );        // a shortcode [close_button]
			add_shortcode( 'cookie_popup_content', array( $this, 'cookielawinfo_popup_content_shortcode' ) );
			add_shortcode( 'cookie_after_accept', array( $this, 'cookie_after_accept_shortcode' ) );
			add_shortcode( 'user_consent_state', array( $this, 'user_consent_state_shortcode' ) );
			add_shortcode( 'cookie_category', array( $this, 'cookie_category_shortcode' ) );
			add_shortcode( 'webtoffee_powered_by', array( $this, 'wf_powered_by' ) );
			add_shortcode( 'wt_cli_category_widget', array( $this, 'cookielawinfo_category_widget' ) );
			add_shortcode( 'wt_cli_manage_consent', array( $this, 'manage_consent' ) );
		}

	}
	/*
	*   Cookie category widget
	*   @since 2.2.3
	*/
	public function cookielawinfo_category_widget() {
		$strict_enabled     = Cookie_Law_Info::get_strictly_necessory_categories();
		$cookie_list        = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
		$wt_cli_categories  = '';
		$wt_cli_categories .= '<span class="wt-cli-category-widget">';
		foreach ( $cookie_list as $key => $cookie ) {
			$checked = '';
			if ( true === $cookie['default_state'] ) {
				$checked = 'checked';
			}
			if ( true === $cookie['strict'] ) {
				$checked = 'checked' . ' ' . 'disabled';

			}
			$wt_cli_categories .= '<span class="wt-cli-form-group wt-cli-custom-checkbox"><input type="checkbox" class="cli-user-preference-checkbox" aria-label="' . $cookie['title'] . '" data-id="checkbox-' . $key . '" id="checkbox-' . $key . '" ' . $checked . '><label for="checkbox-' . $key . '">' . $cookie['title'] . '</label></span>';

		}
		$wt_cli_categories .= '</span>';
		return $wt_cli_categories;
	}
	/*
	*   Powered by WebToffe
	*   @since 2.1.9
	*/
	public function wf_powered_by() {
		return '<p class="wt-cli-element" style="color:#333; clear:both; font-style:italic; font-size:12px; margin-top:15px;">Powered By <a href="https://www.webtoffee.com/" style="color:#333; font-weight:600; font-size:12px;">WebToffee</a></p>';
	}

	/*
	*   Prints cookie categories and description.
	*   @since 2.1.9
	*/
	public function cookie_category_shortcode() {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$html        = '<div class="wt-cli-element cli_cookie_category_single">';
		$cookie_list = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
		foreach ( $cookie_list as $key => $cookie ) {
            $category_description   = ( isset( $cookie['description'] ) ? $cookie['description'] : '' );
            $html.='<div class="cli_cookie_category_single"><h5 class="cli_cookie_category_single_hd">'.$cookie['title'].'</h5><div class="cli_cookie_category_single_description"><p>'.do_shortcode( $category_description, 'cookielawinfo-category' ).'</p></div></div>'; 
        }
		$html .= '</div>';
		return $html;
	}


	/*
	*   User can manage his current consent. This function is used in [user_consent_state] shortcode
	*   @since 2.1.9
	*/
	public function manage_user_consent_jsblock() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.cli_manage_current_consent').click(function(){
					jQuery('#cookie-law-info-again').click();
					setTimeout(function(){
						jQuery(window).scrollTop(jQuery('#cookie-law-info-bar').offset().top);
					},1000);
				});
			});
		</script>
		<?php
	}

	/*
	*   Show current user's consent state
	*   @since 2.1.9
	*/
	public function user_consent_state_shortcode( $atts = array() ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		add_action( 'wp_footer', array( $this, 'manage_user_consent_jsblock' ), 15 );
		$html = '<div class="wt-cli-element cli_user_consent_state">' . __( 'Your current state:', 'webtoffee-gdpr-cookie-consent' );
		if ( isset( $_COOKIE['viewed_cookie_policy'] ) ) {
			if ( $_COOKIE['viewed_cookie_policy'] == 'yes' ) {
				$cookie_list     = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
				$allowed_arr     = array();
				$not_allowed_arr = array();
				$not_allowed_txt = '';
				$allowed_txt     = '';
				foreach ( $cookie_list as $key => $cookie ) {
					$cookie_key  = "cookielawinfo-checkbox-$key";
					$cookie_name = $cookie['title'];
					if ( ! isset( $_COOKIE[ $cookie_key ] ) ) {
						$not_allowed_arr[] = $cookie_name;
					} else {
						if ( $_COOKIE[ $cookie_key ] == 'no' ) {
							$not_allowed_arr[] = $cookie_name;
						} else {
							$allowed_arr[] = $cookie_name;
						}
					}
				}
				if ( count( $not_allowed_arr ) > 0 ) {
					$not_allowed_txt = __( 'Not allowed cookies ', 'webtoffee-gdpr-cookie-consent' ) . '(' . implode( ', ', $not_allowed_arr ) . ').';
				}
				if ( count( $allowed_arr ) > 0 ) {
					$allowed_txt  = count( $not_allowed_arr ) > 0 ? __( 'Allowed cookies ', 'webtoffee-gdpr-cookie-consent' ) : __( 'Allow all cookies ', 'webtoffee-gdpr-cookie-consent' );
					$allowed_txt .= '(' . implode( ', ', $allowed_arr ) . ').';
				}
				$html .= ' ' . __( 'Consent Accepted. ', 'webtoffee-gdpr-cookie-consent' ) . $allowed_txt . ' ' . $not_allowed_txt;
			} else {
				$html .= ' ' . __( 'Consent rejected.', 'webtoffee-gdpr-cookie-consent' );
			}
		} else // no conset given
		{
			$html .= ' ' . __( 'No consent given.', 'webtoffee-gdpr-cookie-consent' );
		}
		$html .= ' <a class="cli_manage_current_consent" style="cursor:pointer;">' . __( 'Manage your consent.', 'webtoffee-gdpr-cookie-consent' ) . '</a> </div>';
		return $html;
	}

	/*
	*   Add content after accepting the cookie notice. Category wise checking allowed
	*   @params category: category slug (Main language)
	*   @params condition: and/or   In the case of multiple categories, default `and`
	*   Usage :
				Inside post editor
				[cookie_after_accept] ...Your content goes here...  [/cookie_after_accept]
				[cookie_after_accept category="non-necessary"] ...Your content goes here...  [/cookie_after_accept]
				[cookie_after_accept category="non-necessary, analytical" condition="or"] ...Your content goes here...  [/cookie_after_accept]

				Inside template
				<?php echo do_shortcode('...shortcode goes here...'); ?>
	*/
	public function cookie_after_accept_shortcode( $atts = array(), $content = '' ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$atts = shortcode_atts(
			array(
				'category'  => '',
				'condition' => 'and',
			),
			$atts
		);
		$ok   = 0;
		// accepted
		if ( isset( $_COOKIE['viewed_cookie_policy'] ) && $_COOKIE['viewed_cookie_policy'] == 'yes' ) {
			if ( trim( $atts['category'] ) == '' ) {
				$ok = 1;
			} else {
				$cat_arr = explode( ',', $atts['category'] );
				$check   = 0;
				foreach ( $cat_arr as $value ) {
					$value = trim( $value );
					if ( isset( $_COOKIE[ "cookielawinfo-checkbox-$value" ] ) && $_COOKIE[ "cookielawinfo-checkbox-$value" ] == 'yes' ) {
						$check++;
					}
				}
				// all accepted
				if ( $atts['condition'] == 'and' && $check == count( $cat_arr ) ) {
					$ok = 1;
				}

				// any one accepted
				if ( $atts['condition'] == 'or' && $check > 0 ) {
					$ok = 1;
				}
			}
		}
		if ( $ok == 0 ) {
			$content = '';
		}
		return $content;
	}

	/**
	 A shortcode that outputs a link which will delete the cookie used to track
	 whether or not a vistor has dismissed the header message (i.e. so it doesn't
	 keep on showing on all pages)

	 Usage: [delete_cookies]
			[delete_cookies linktext="delete cookies"]

	 N.B. This shortcut does not block cookies, or delete any other cookies!
	 */
	public function cookielawinfo_delete_cookies_shortcode( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$atts = shortcode_atts(
			array(
				'text' => __( 'Delete Cookies', 'cookie-law-info' ),
			),
			$atts,
			'delete_cookies'
		);
		return '<a href="" class="cookielawinfo-cookie-delete">' . esc_attr( $atts['text'] ) . '</a>';
	}


	/**
	 A nice shortcode to output a table of cookies you have saved, output in ascending
	 alphabetical order. If there are no cookie records found a single empty row is shown.
	 You can customise the 'not shown' message (see commented code below)

	 N.B. This only shows the information you entered on the "cookie" admin page, it
	 does not necessarily mean you comply with the cookie law. It is up to you, or
	 the website owner, to make sure you have conducted an appropriate cookie audit
	 and are informing website visitors of the actual cookies that are being stored.

	 Usage:                 [cookie_audit]
							[cookie_audit style="winter"]
							[cookie_audit not_shown_message="No records found"]
							[cookie_audit style="winter" not_shown_message="Not found"]

	 Styles included:       simple, classic, modern, rounded, elegant, winter.
							Default style applied: classic.

	 Additional styles:     You can customise the CSS by editing the CSS file itself,
							included with plugin.
	 */
	public function cookielawinfo_table_shortcode( $atts ) {
		/**
		*  cli_audit_table_on_off: contol the visibility of cookie audit table with/without EU option
		 *
		*  @since 2.1.7
		*/
		$enable_shortcode = apply_filters( 'cli_audit_table_on_off', $this->enable_shortcode, 'cookie_audit' );
		if ( $enable_shortcode === false ) {
			return '';
		}
		// table enabled by user so we need to include css file
		if ( $this->enable_shortcode === false && $enable_shortcode !== false ) {
			wp_register_style( $this->plugin_name . '-table', plugin_dir_url( CLI_PLUGIN_FILENAME ) . 'public/css/cookie-law-info-table.css', array(), $this->version, 'all' );
		}

		/** RICHARDASHBY EDIT: only add CSS if table is being used */
		wp_enqueue_style( $this->plugin_name . '-table' );
		/** END EDIT */

		$atts    = shortcode_atts(
			array(
				'style'             => 'classic',
				'not_shown_message' => '',
				'columns'           => 'cookie,type,duration,description',
				'heading'           => '',
				'category'          => '',
			),
			$atts,
			'cookie_audit'
		);
		$columns = array_filter( array_map( 'trim', explode( ',', $atts['columns'] ) ) );
		$posts   = false;
		$args    = array(
			'post_type'      => CLI_POST_TYPE,
			/** 28/05/2013: Changing from 10 to 50 to allow longer tables of cookie data */
			'posts_per_page' => 50,
			'tax_query'      => array(),
			'order'          => 'ASC',
			'orderby'        => 'title',
		);

		global $sitepress;
		$is_wpml_enabled = false;
		if ( function_exists( 'icl_object_id' ) && $sitepress ) {
			$is_wpml_enabled          = true;
			$args['suppress_filters'] = false;
		}
		$category = isset( $atts['category'] ) ? $atts['category'] : '';
		if ( isset( $category ) && $category != '' ) {
			$wpml_default_lang = 'en';
			$wpml_current_lang = 'en';
			$term              = false;
			if ( $is_wpml_enabled ) {
				$wpml_default_lang = $sitepress->get_default_language();
				$wpml_current_lang = ICL_LANGUAGE_CODE;
				if ( $wpml_default_lang != $wpml_current_lang ) {
					$sitepress->switch_lang( $wpml_default_lang ); // switching to default lang
					$term = get_term_by( 'slug', $category, 'cookielawinfo-category' ); // original term
					$sitepress->switch_lang( $wpml_current_lang ); // revert back to current lang
					if ( ! $term ) {
						$term = get_term_by( 'slug', $category, 'cookielawinfo-category' ); // current lang term
					}
				} else {
					$term = get_term_by( 'slug', $category, 'cookielawinfo-category' );
				}
			} else {
				$term = get_term_by( 'slug', $category, 'cookielawinfo-category' );
			}
			if ( $term ) {
				$args['tax_query'][] = array(
					'taxonomy'         => 'cookielawinfo-category',
					'terms'            => $term->term_id,
					'include_children' => false,
				);
				$posts               = get_posts( $args ); // only return posts if term available
			}
		} else {
			$posts = get_posts( $args );
		}

		$ret = '<table class="wt-cli-element cookielawinfo-row-cat-table cookielawinfo-' . esc_attr( $atts['style'] ) . '"><thead><tr>';
		if ( in_array( 'cookie', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-1">' . __( 'Cookie', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'type', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-2">' . __( 'Type', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'duration', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-3">' . __( 'Duration', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'description', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-4">' . __( 'Description', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		$ret  = apply_filters( 'cli_new_columns_to_audit_table', $ret );
		$ret .= '</tr>';
		$ret .= '</thead><tbody>';

		// Get custom fields:
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$custom          = get_post_custom( $post->ID );
				$cookie_type     = ( isset( $custom['_cli_cookie_type'][0] ) ) ? $custom['_cli_cookie_type'][0] : '';
				$cookie_duration = ( isset( $custom['_cli_cookie_duration'][0] ) ) ? $custom['_cli_cookie_duration'][0] : '';
				// Output HTML:
				$ret .= '<tr class="cookielawinfo-row">';
				if ( in_array( 'cookie', $columns ) ) {
					$ret .= '<td class="cookielawinfo-column-1">' . $post->post_title . '</td>';
				}
				if ( in_array( 'type', $columns ) ) {
					$ret .= '<td class="cookielawinfo-column-2">' . $cookie_type . '</td>';
				}
				if ( in_array( 'duration', $columns ) ) {
					$ret .= '<td class="cookielawinfo-column-3">' . $cookie_duration . '</td>';
				}
				if ( in_array( 'description', $columns ) ) {
					$ret .= '<td class="cookielawinfo-column-4">' . $post->post_content . '</td>';
				}
				$ret  = apply_filters( 'cli_new_column_values_to_audit_table', $ret, $custom );
				$ret .= '</tr>';
			}
		} else {
			$ret .= '<tr class="cookielawinfo-row"><td colspan="4" class="cookielawinfo-column-empty">' . esc_html( $atts['not_shown_message'] ) . '</td></tr>';
		}
		$ret .= '</tbody></table>';
		if ( '' === $atts['not_shown_message'] && empty( $posts ) ) {
			$ret = '';
		}
		return $ret;
	}




	/**
	 A nice shortcode to output a table of cookies you have saved, output in ascending
	 alphabetical order. If there are no cookie records found a single empty row is shown.
	 You can customise the 'not shown' message (see commented code below)

	 N.B. This only shows the information you entered on the "cookie" admin page, it
	 does not necessarily mean you comply with the cookie law. It is up to you, or
	 the website owner, to make sure you have conducted an appropriate cookie audit
	 and are informing website visitors of the actual cookies that are being stored.

	 Usage:                 [cookie_audit_category]

	 Styles included:       simple, classic, modern, rounded, elegant, winter.
							Default style applied: classic.

	 Additional styles:     You can customise the CSS by editing the CSS file itself,
							included with plugin.
	 */
	public function cookielawinfo_category_table_shortcode( $atts ) {
		/**
		*  cli_audit_table_on_off: contol the visibility of cookie audit table with/without EU option
		 *
		*  @since 2.1.7
		*/
		$enable_shortcode = apply_filters( 'cli_audit_table_on_off', $this->enable_shortcode, 'cookie_audit_category' );
		if ( $enable_shortcode === false ) {
			return '';
		}
		// table enabled by user so we need to include css file
		if ( $this->enable_shortcode === false && $enable_shortcode !== false ) {
			wp_register_style( $this->plugin_name . '-table', plugin_dir_url( CLI_PLUGIN_FILENAME ) . 'public/css/cookie-law-info-table.css', array(), $this->version, 'all' );
		}

		/** RICHARDASHBY EDIT: only add CSS if table is being used */
		wp_enqueue_style( $this->plugin_name . '-table' );
		/** END EDIT */

		$atts    = shortcode_atts(
			array(
				'style'             => 'classic',
				'not_shown_message' => '',
				'columns'           => 'cookie,type,duration,description',
			),
			$atts,
			'cookie_audit_category'
		);
		$columns = array_filter( array_map( 'trim', explode( ',', $atts['columns'] ) ) );

		$cookie_list = Cookie_Law_Info_Cookies::get_instance()->get_cookies();

		$ret = '<table class="wt-cli-element cookielawinfo-' . esc_attr( $atts['style'] ) . ' cookielawinfo-row-cat-table"><thead><tr>';
		if ( in_array( 'cookie', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-1">' . __( 'Cookie', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'type', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-2">' . __( 'Type', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'duration', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-3">' . __( 'Duration', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		if ( in_array( 'description', $columns ) ) {
			$ret .= '<th scope="col" class="cookielawinfo-column-4">' . __( 'Description', 'webtoffee-gdpr-cookie-consent' ) . '</th>';
		}
		$ret .= '</thead><tbody>';

		if ( empty( $cookie_list ) ) {
			$ret .= '<tr class="cookielawinfo-row"><td colspan="' . count( $columns ) . '" class="cookielawinfo-column-empty">' . esc_html( $atts['not_shown_message'] ) . '</td></tr>';
		}
		foreach ( $cookie_list as $key => $cookie ) {
			$cookies = ( isset( $cookie['cookies'] ) && is_array( $cookie['cookies'] ) ) ? $cookie['cookies'] : array();
			if ( count( $cookies ) > 0 ) {

				$ret .= '<tr class="cookielawinfo-row-cat-title"><th colspan="' . count( $columns ) . '" class="cookielawinfo-row-cat-title-head">' . esc_html( $cookie['title'] )  . '</th></tr>';
				foreach ( $cookies as $cookie_post ) {
					$re   = '';
					$ret .= $this->render_cookie_raw_table( $re, $columns, $cookie_post );
				}
			}
		}
		$ret .= '</tbody></table>';
		return $ret;
	}

	public function render_cookie_raw_table( $ret, $columns, $cookie_post = array() ) {

		// Get custom fields:
		$ret .= '<tr class="cookielawinfo-row">';
		if ( in_array( 'cookie', $columns ) ) {
			$ret .= '<td class="cookielawinfo-column-1">' . esc_html( $cookie_post['title'] ) . '</td>';
		}
		if ( in_array( 'type', $columns ) ) {
			$ret .= '<td class="cookielawinfo-column-2">' . esc_html( $cookie_post['type'] ) . '</td>';
		}
		if ( in_array( 'duration', $columns ) ) {
			$ret .= '<td class="cookielawinfo-column-3">' . esc_html( $cookie_post['duration'] ) . '</td>';
		}
		if ( in_array( 'description', $columns ) ) {
			$ret .= '<td class="cookielawinfo-column-4">' . wp_kses_post( $cookie_post['description'] )  . '</td>';
		}
		$ret .= '</tr>';
		return $ret;
	}




	/**
	 *   Returns HTML for a standard (green, medium sized) 'Accept' button
	 */
	public function cookielawinfo_shortcode_accept_button( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$atts          = shortcode_atts(
			array(
				'colour' => 'green',
			),
			$atts,
			'cookie_accept'
		);
		$defaults = Cookie_Law_Info::get_default_settings( 'button_1_text' );
		$settings = wp_parse_args( Cookie_Law_Info::get_settings(), $defaults );
		return '<a  role="button" tabindex="0" class="wt-cli-element cli_action_button cli-accept-button medium cli-plugin-button ' . esc_attr( $atts['colour'] ) . '" data-cli_action="accept" >' . esc_html( stripslashes( $settings['button_1_text'] ) ) . '</a>';
	}

	/** Returns HTML for a standard (green, medium sized) 'Reject' button */
	public function cookielawinfo_shortcode_reject_button( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$defaults = Cookie_Law_Info::get_default_settings();
		$settings = wp_parse_args( Cookie_Law_Info::get_settings(), $defaults );

		$classr = '';
		if ( $settings['button_3_as_button'] ) {
			$classr = ' class="wt-cli-element' . ' ' . esc_attr( $settings['button_3_button_size'] ) . ' cli-plugin-button cli-plugin-main-button cookie_action_close_header_reject cli_action_button"';
		} else {
			$classr = ' class="wt-cli-element cookie_action_close_header_reject cli_action_button" ';
		}

		// adding custom style
		$styles = $this->generateStyle( $settings, 'button_3_style' );
		$url_reject = ( $settings['button_3_action'] == 'CONSTANT_OPEN_URL' && $settings['button_3_url'] != '#' ) ? 'href="' . esc_url( $settings['button_3_url'] ) . '"' : "role='button'";
		$link_tag   = '';
		$link_tag  .= '<a id="wt-cli-reject-btn" tabindex="0" ' . $url_reject . ' style="' . esc_attr( $styles ) . '" ';
		$link_tag  .= ( $settings['button_3_new_win'] ) ? ' target="_blank" ' : '';
		$link_tag  .= $classr . '  data-cli_action="reject">' . esc_html( stripslashes( $settings['button_3_text'] ) ) . '</a>';
		return $link_tag;

	}

	public function cookielawinfo_shortcode_settings_button( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$defaults = Cookie_Law_Info::get_default_settings();
		$settings = wp_parse_args( Cookie_Law_Info::get_settings(), $defaults );

		// overriding custom setting @version: 2.1.2
		$settings['button_4_url']     = '#';
		$settings['button_4_action']  = '#cookie_action_settings';
		$settings['button_4_new_win'] = false;

		$classr = '';
		if ( $settings['button_4_as_button'] ) {
			$classr = ' class="wt-cli-element' . ' ' .esc_attr(  $settings['button_4_button_size'] ) . ' cli-plugin-button cli-plugin-main-button cli_settings_button"';
		} else {
			$classr = 'class="wt-cli-element cli_settings_button" ';
		}

		// adding custom style
		$styles    = $this->generateStyle( $settings, 'button_4_style' );
		$url_s     = ( $settings['button_4_action'] == 'CONSTANT_OPEN_URL' && $settings['button_4_url'] != '#' ) ? 'href="' . esc_url( $settings['button_4_url'] ). '"' : "role='button'";
		$link_tag  = '';
		$link_tag .= '<a id="wt-cli-settings-btn" tabindex="0" ' . $url_s . ' style="' . esc_attr( $styles ) . '"';
		$link_tag .= ( $settings['button_4_new_win'] ) ? ' target="_blank" ' : '';
		$link_tag .= $classr . ' >' . esc_html( stripslashes( $settings['button_4_text'] ) ) . '</a>';
		return $link_tag;
	}


	/** Returns HTML for a generic button */
	public function cookielawinfo_shortcode_more_link( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		return $this->cookielawinfo_shortcode_button_DRY_code( 'button_2' );
	}


	/** Returns HTML for a generic button */
	public function cookielawinfo_shortcode_main_button( $atts ) {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$defaults = Cookie_Law_Info::get_default_settings();
		$settings = wp_parse_args( Cookie_Law_Info::get_settings(), $defaults );
		$class    = '';
		if ( $settings['button_1_as_button'] ) {
			$class = ' class="wt-cli-element' . ' ' . esc_attr( $settings['button_1_button_size'] ) . ' cli-plugin-button cli-plugin-main-button cookie_action_close_header cli_action_button"';
		} else {
			$class = ' class="wt-cli-element cli-plugin-main-button cookie_action_close_header cli_action_button" ';
		}

		// If is action not URL then don't use URL!
		$url = ( $settings['button_1_action'] == 'CONSTANT_OPEN_URL' && $settings['button_1_url'] != '#' ) ? 'href="' . esc_url( $settings['button_1_url'] ). '"' : "role='button'";

		// adding custom style
		$styles    = $this->generateStyle( $settings, 'button_1_style' );
		$link_tag  = '<a id="wt-cli-accept-btn" tabindex="0" ' . $url . ' style="' . esc_attr( $styles ) . '" data-cli_action="accept" ';
		$link_tag .= ( $settings['button_1_new_win'] ) ? ' target="_blank" ' : '';
		$link_tag .= $class . ' >' . esc_html( stripslashes( $settings['button_1_text'] ) ) . '</a>';
		return $link_tag;
	}
	/**
	 * A separate buttons for accept all feature
	 *
	 * @since  2.3.1
	 * @return string
	 */
	public function cookielawinfo_accept_all_button() {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$defaults = Cookie_Law_Info::get_default_settings();
		$settings = wp_parse_args( Cookie_Law_Info::get_settings(), $defaults );
		$class    = '';
		if ( $settings['button_7_as_button'] ) {
			$class = ' class="wt-cli-element' . ' ' . esc_attr( $settings['button_7_button_size'] ) . ' cli-plugin-button wt-cli-accept-all-btn cookie_action_close_header cli_action_button"';
		} else {
			$class = ' class="wt-cli-element cli-plugin-main-button cookie_action_close_header cli_action_button" ';
		}

		// If is action not URL then don't use URL!
		$url = ( $settings['button_7_action'] == 'CONSTANT_OPEN_URL' && $settings['button_7_url'] != '#' ) ? 'href="' . esc_url( $settings['button_7_url'] ) . '"': "role='button'";

		// adding custom style
		$styles    = $this->generateStyle( $settings, 'button_7_style' );
		$link_tag  = '<a id="wt-cli-accept-all-btn" tabindex="0" ' . $url . ' style="' . esc_attr( $styles ) . '" data-cli_action="accept_all" ';
		$link_tag .= ( $settings['button_7_new_win'] ) ? ' target="_blank" ' : '';
		$link_tag .= $class . ' >' . esc_html( stripslashes( $settings['button_7_text'] ) ) . '</a>';
		return $link_tag;
	}
	public function cookielawinfo_shortcode_close_button() {
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$arr    = Cookie_Law_Info::get_settings();
		$styles = $this->generateStyle( $arr, 'button_5_style' );
		$txt    = '';
		return '<a style="' . esc_attr( $styles ) . '" data-cli_action="accept" class="wt-cli-element cli_cookie_close_button" title="' . __( 'Close and Accept', 'webtoffee-gdpr-cookie-consent' ) . '">X</a>';
	}
	private function generateStyle( $arr, $style_key ) {
		$styles = '';
		if ( Cookie_Law_Info_Admin::module_exists( 'cli-themes' ) && isset( $arr[ $style_key ] ) ) {
			$styles = Cookie_Law_Info_Cli_Themes::create_style_attr( $arr[ $style_key ] );
		}
		return stripslashes( $styles );
	}

	/** Returns HTML for a generic button */
	public function cookielawinfo_shortcode_button_DRY_code( $name ) {

		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$arr        = Cookie_Law_Info::get_settings();
		$settings   = array();
		$class_name = '';

		// adding custom style
		$styles = $this->generateStyle( $arr, $name . '_style' );

		if ( $name == 'button_1' ) {
			$settings    = array(
				'button_x_text'          => stripslashes( $arr['button_1_text'] ),
				'button_x_url'           => $arr['button_1_url'],
				'button_x_action'        => $arr['button_1_action'],

				'button_x_link_colour'   => $arr['button_1_link_colour'],
				'button_x_new_win'       => $arr['button_1_new_win'],
				'button_x_as_button'     => $arr['button_1_as_button'],
				'button_x_button_colour' => $arr['button_1_button_colour'],
				'button_x_button_size'   => $arr['button_1_button_size'],
			);
			$class_name .= 'wt-cli-element cli-plugin-main-button';
		} elseif ( $name == 'button_2' ) {
			$settings    = array(
				'button_x_text'          => stripslashes( $arr['button_2_text'] ),
				'button_x_action'        => $arr['button_2_action'],

				'button_x_link_colour'   => $arr['button_2_link_colour'],
				'button_x_new_win'       => $arr['button_2_new_win'],
				'button_x_as_button'     => $arr['button_2_as_button'],
				'button_x_button_colour' => $arr['button_2_button_colour'],
				'button_x_button_size'   => $arr['button_2_button_size'],
			);
			$class_name .= 'wt-cli-element cli-plugin-main-link';
			if ( $arr['button_2_url_type'] == 'url' ) {
				$settings['button_x_url'] = $arr['button_2_url'];

				/*
				* @since 2.1.9
				* Checks if user enabled minify bar in the current page
				*/
				if ( $arr['button_2_hidebar'] === true ) {
					global $wp;
					$current_url = home_url( add_query_arg( array(), $wp->request ) );
					$btn2_url    = $current_url[ strlen( $current_url ) - 1 ] == '/' ? substr( $current_url, 0, -1 ) : $current_url;
					$btn2_url    = $arr['button_2_url'][ strlen( $arr['button_2_url'] ) - 1 ] == '/' ? substr( $arr['button_2_url'], 0, -1 ) : $arr['button_2_url'];
					if ( strpos( $btn2_url, $current_url ) !== false ) {
						if ( $btn2_url != $current_url ) {
							$qry_var_arr  = explode( '?', $current_url );
							$hash_var_arr = explode( '#', $current_url );
							if ( $qry_var_arr[0] == $btn2_url || $hash_var_arr[0] == $btn2_url ) {
								$class_name .= ' cli-minimize-bar';
							}
						} else {
							 $class_name .= ' cli-minimize-bar';
						}
					}
				}
			} else {
				$privacy_page_exists = 0;
				if ( $arr['button_2_page'] > 0 ) {
					$privacy_policy_page = get_post( $arr['button_2_page'] );
					if ( $privacy_policy_page instanceof WP_Post ) {
						if ( $privacy_policy_page->post_status === 'publish' ) {
							$privacy_page_exists      = 1;
							$settings['button_x_url'] = get_page_link( $privacy_policy_page );

							/*
							* @since 2.1.9
							* Checks if user enabled minify bar in the current page
							*/
							if ( $arr['button_2_hidebar'] === true ) {
								if ( is_page( $arr['button_2_page'] ) ) {
									$class_name .= ' cli-minimize-bar';
								}
							}
						}
					}
				}
				if ( $privacy_page_exists == 0 ) {
					return '';
				}
			}
		}

		$settings = apply_filters( 'wt_readmore_link_settings', $settings );
		$class    = '';
		if ( $settings['button_x_as_button'] ) {
			$class .= ' class="wt-cli-element' . ' ' . esc_attr( $settings['button_x_button_size'] ) . ' cli-plugin-button ' . esc_attr( $class_name ) . '"';
		} else {
			$class .= ' class="wt-cli-element' . ' ' . esc_attr( $class_name ) . '" ';
		}
		// If no follow is set set rel="nofollow"

		$rel = ( $arr['button_2_nofollow'] ) ? 'rel=nofollow' : '';

		// If is action not URL then don't use URL!
		$url       = ( $settings['button_x_action'] == 'CONSTANT_OPEN_URL' && $settings['button_x_url'] != '#' ) ? 'href="' . esc_url( $settings['button_x_url'] ) . '"': "role='button'";
		$link_tag  = '<a id="wt-cli-policy-link" tabindex="0" ' . $url . '' . $rel . ' style="' . esc_attr( $styles ) . '"';
		$link_tag .= ( $settings['button_x_new_win'] ) ? ' target="_blank" ' : '';
		$link_tag .= $class . ' >' . esc_html( $settings['button_x_text'] ) . '</a>';
		return $link_tag;
	}

	public function cookielawinfo_popup_content_shortcode() {
		$settings_popup = '';
		if ( $this->enable_shortcode === false ) {
			return '';
		}
		$the_options = Cookie_Law_Info::get_settings();
		if ( $the_options['is_on'] == true ) {
			$cookie_list = Cookie_Law_Info_Cookies::get_instance()->get_cookies();
			ob_start();
			do_action( 'wt_cli_settings_popup' );
			$settings_popup = ob_get_contents();
			ob_end_clean();
			return $settings_popup;
		}
	}
	/**
	 * Add a link that allows the user the revisit their consent
	 *
	 * @since  2.3.1
	 * @access public
	 * @return string
	 */
	public function manage_consent() {
		if ( ! $this->cookie_options ) {
			$this->cookie_options = Cookie_Law_Info::get_settings();
		}
		$manage_consent_link = '';
		$manage_consent_text = ( isset( $this->cookie_options['showagain_text'] ) ? $this->cookie_options['showagain_text'] : '' );

		$manage_consent_link = '<a href="javascript:void(0)" class="wt-cli-manage-consent-link">' . esc_html( $manage_consent_text ) . '</a>';
		
		return $manage_consent_link;
	}
}
new Cookie_Law_Info_Shortcode( $this );
