<?php

/**
 * Cookies module to handle all the cookies related operations
 *
 * @version 2.3.4
 * @package CookieLawInfo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cookie_Law_Info_Cookies {



	protected $cookies;
	private static $instance;
	protected $necessary_categories;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ) );
		add_action( 'create_cookielawinfo-category', array( $this, 'add_category_meta' ) );
		add_action( 'edited_cookielawinfo-category', array( $this, 'edit_category_meta' ) );
		add_action( 'cookielawinfo-category_add_form_fields', array( $this, 'add_category_form_fields' ) );
		add_action( 'cookielawinfo-category_edit_form_fields', array( $this, 'edit_category_form_fields' ), 1 );
		add_action( 'save_post', array( $this, 'save_custom_metaboxes' ) );
		add_action( 'manage_edit-cookielawinfo_columns', array( $this, 'manage_edit_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'manage_edit-cookielawinfo-category_columns', array( $this, 'manage_edit_custom_column_header' ), 10 );
		add_action( 'manage_cookielawinfo-category_custom_column', array( $this, 'manage_custom_column_content' ), 10, 3 );

		add_action( 'wt_cli_initialize_plugin', array( $this, 'load_default_plugin_settings' ) );

	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Init hook, initialize all the settings.
	 *
	 * @return void
	 */
	public function init() {
		$this->register_custom_post_type();
		$this->create_taxonomy();
		add_filter( 'wt_cli_cookie_categories', array( $this, 'get_cookies' ) );
	}
	/**
	 * Load default cookie categories and cookies.
	 *
	 * @return void
	 */
	public function load_default_plugin_settings() {
		$this->load_default_terms();
		$this->load_default_cookies();
	}
	// The function update_term_meta() is only introduced in 4.4 so we have cloned this function locally
	public function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		if ( $this->wp_term_is_shared( $term_id ) ) {
			return new WP_Error( 'ambiguous_term_id', __( 'Term meta cannot be added to terms that are shared between taxonomies.', 'webtoffee-gdpr-cookie-consent' ), $term_id );
		}

		return update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
	}

	public function wp_term_is_shared( $term_id ) {
		 global $wpdb;

		if ( get_option( 'finished_splitting_shared_terms' ) ) {
			return false;
		}

		$tt_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE term_id = %d", $term_id ) );

		return $tt_count > 1;
	}

	public function get_term_meta( $term_id, $key = '', $single = false ) {
		return get_metadata( 'term', $term_id, $key, $single );
	}

	public function get_cookie_category_terms( $display_all = false ) {
		global $wp_version;

		$taxonomy = 'cookielawinfo-category';
		$terms    = array();
		if ( version_compare( $wp_version, '4.9', '>=' ) ) {
			$args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			);
			if ( true === $this->check_if_old_category_table() ) {
				$args['hide_empty'] = true;
			}
			$terms = get_terms( $args );
		} else {
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		}
		return $terms;
	}
	public function enqueue_scripts( $hook ) {
		global $wp_version;
		if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'cookielawinfo-category' && isset( $_GET['tag_ID'] ) ) {
			if ( version_compare( $wp_version, '4.9', '>=' ) ) {
				$code_editor_js = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

				if ( $code_editor_js !== false ) {
					wp_add_inline_script(
						'code-editor',
						sprintf(
							'jQuery( function() {
								if (jQuery(".wt-cli-code-editor").length) { jQuery(".wt-cli-code-editor").each(function () { wp.codeEditor.initialize(this.id, %s); }); } 
							} );',
							wp_json_encode( $code_editor_js )
						)
					);
				}
			}
		}
	}
	public function register_custom_post_type() {
		$labels = array(
			'name'               => __( 'GDPR Cookie Consent', 'webtoffee-gdpr-cookie-consent' ),
			'all_items'          => __( 'Cookie List', 'webtoffee-gdpr-cookie-consent' ),
			'singular_name'      => __( 'Cookie', 'webtoffee-gdpr-cookie-consent' ),
			'add_new'            => __( 'Add New', 'webtoffee-gdpr-cookie-consent' ),
			'add_new_item'       => __( 'Add New Cookie Type', 'webtoffee-gdpr-cookie-consent' ),
			'edit_item'          => __( 'Edit Cookie Type', 'webtoffee-gdpr-cookie-consent' ),
			'new_item'           => __( 'New Cookie Type', 'webtoffee-gdpr-cookie-consent' ),
			'view_item'          => __( 'View Cookie Type', 'webtoffee-gdpr-cookie-consent' ),
			'search_items'       => __( 'Search Cookies', 'webtoffee-gdpr-cookie-consent' ),
			'not_found'          => __( 'Nothing found', 'webtoffee-gdpr-cookie-consent' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'webtoffee-gdpr-cookie-consent' ),
			'parent_item_colon'  => '',
		);
		$args   = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'query_var'           => true,
			'rewrite'             => true,
			'capabilities'        => array(
				'publish_posts'       => 'manage_options',
				'edit_posts'          => 'manage_options',
				'edit_others_posts'   => 'manage_options',
				'delete_posts'        => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts'  => 'manage_options',
				'edit_post'           => 'manage_options',
				'delete_post'         => 'manage_options',
				'read_post'           => 'manage_options',
			),
			/** done editing */
			'menu_icon'           => plugin_dir_url( __FILE__ ) . 'images/cli_icon.png',
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor' ),
		);

		register_post_type( CLI_POST_TYPE, $args );
	}

	public function add_category_meta( $term_id ) {
		$this->save_defaultstate_meta( $term_id );
		$this->save_scripts_meta( $term_id );
		$this->save_priority_meta( $term_id );
		$this->save_loadonstart_meta( $term_id );
		$this->save_stricly_necessary_meta( $term_id );

	}
	public function edit_category_meta( $term_id ) {
		$this->save_defaultstate_meta( $term_id );
		$this->save_scripts_meta( $term_id );
		$this->save_priority_meta( $term_id );
		$this->save_loadonstart_meta( $term_id );
		$this->save_stricly_necessary_meta( $term_id );

	}
	public function add_category_form_fields( $term ) {
		$this->add_defaultstate_meta( $term );
		$this->add_stricly_necessary_meta( $term );
		$this->add_loadonstart_meta( $term );
		$this->add_priority_meta( $term );
		$this->add_scripts_meta( $term );

	}
	public function edit_category_form_fields( $term ) {
		$this->edit_defaultstate_meta( $term );
		$this->edit_stricly_necessary_meta( $term );
		$this->edit_loadonstart_meta( $term );
		$this->edit_priority_meta( $term );
		$this->edit_scripts_meta( $term );

	}
	public function add_meta_box() {

		add_meta_box( '_cli_cookie_slugid', __( 'Cookie ID', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_cookie_slugid' ), 'cookielawinfo', 'side', 'default' );
		add_meta_box( '_cli_cookie_type', __( 'Cookie Type', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_cookie_type' ), 'cookielawinfo', 'side', 'default' );
		add_meta_box( '_cli_cookie_duration', __( 'Cookie Duration', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_cookie_duration' ), 'cookielawinfo', 'side', 'default' );
		add_meta_box( '_cli_cookie_sensitivity', __( 'Cookie Sensitivity', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_cookie_sensitivity' ), 'cookielawinfo', 'side', 'default' );
		add_meta_box( '_cli_cookie_headscript_meta', __( 'Head Scripts', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_headscript' ), 'cookielawinfo', 'normal', 'low' );
		add_meta_box( '_cli_cookie_bodyscript_meta', __( 'Body Scripts', 'webtoffee-gdpr-cookie-consent' ), array( $this, 'metabox_bodyscript' ), 'cookielawinfo', 'normal', 'low' );
	}
	/** Display the custom meta box for cookie_slugid */
	public function metabox_cookie_slugid() {
		global $post;
		$custom        = get_post_custom( $post->ID );
		$cookie_slugid = ( isset( $custom['_cli_cookie_slugid'][0] ) ) ? $custom['_cli_cookie_slugid'][0] : '';
		?>
		<label><?php echo __( 'Cookie ID', 'webtoffee-gdpr-cookie-consent' ); ?></label>
		<input name="_cli_cookie_slugid" value="<?php echo sanitize_text_field( $cookie_slugid ); ?>" style="width:95%;" />
		<?php
	}

	/** Display the custom meta box for cookie_type */
	public function metabox_cookie_type() {
		 global $post;
		$custom      = get_post_custom( $post->ID );
		$cookie_type = ( isset( $custom['_cli_cookie_type'][0] ) ) ? $custom['_cli_cookie_type'][0] : '';
		?>
		<label><?php echo __( 'Cookie Type: (persistent, session, third party )', 'webtoffee-gdpr-cookie-consent' ); ?></label>
		<input name="_cli_cookie_type" value="<?php echo sanitize_text_field( $cookie_type ); ?>" style="width:95%;" />
		<?php
	}

	/** Display the custom meta box for cookie_duration */
	public function metabox_cookie_duration() {
		global $post;
		$custom          = get_post_custom( $post->ID );
		$cookie_duration = ( isset( $custom['_cli_cookie_duration'][0] ) ) ? $custom['_cli_cookie_duration'][0] : '';
		?>
		
		<label><?php echo __( 'Cookie Duration:', 'webtoffee-gdpr-cookie-consent' ); ?></label>
		<input name="_cli_cookie_duration" value="<?php echo sanitize_text_field( $cookie_duration ); ?>" style="width:95%;" />
		<?php
	}

	/** Display the custom meta box for cookie_sensitivity */
	public function metabox_cookie_sensitivity() {
		global $post;
		$custom             = get_post_custom( $post->ID );
		$cookie_sensitivity = ( isset( $custom['_cli_cookie_sensitivity'][0] ) ) ? $custom['_cli_cookie_sensitivity'][0] : '';
		?>
		<label><?php echo __( 'Cookie Sensitivity: ( necessary , non-necessary )', 'webtoffee-gdpr-cookie-consent' ); ?></label>
		<input name="_cli_cookie_sensitivity" value="<?php echo sanitize_text_field( $cookie_sensitivity ); ?>" style="width:95%;" />
		<?php
	}

	/** Saves all form data from custom post meta boxes, including saitisation of input */
	public function save_custom_metaboxes() {
		global $post;
		if ( isset( $_POST['_cli_cookie_type'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_type', sanitize_text_field( $_POST['_cli_cookie_type'] ) );
		}
		if ( isset( $_POST['_cli_cookie_type'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_duration', sanitize_text_field( $_POST['_cli_cookie_duration'] ) );
		}
		if ( isset( $_POST['_cli_cookie_sensitivity'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_sensitivity', sanitize_text_field( $_POST['_cli_cookie_sensitivity'] ) );
		}
		if ( isset( $_POST['_cli_cookie_slugid'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_slugid', sanitize_text_field( $_POST['_cli_cookie_slugid'] ) );
		}
		if ( isset( $_POST['_cli_cookie_headscript_meta'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_headscript_meta', wp_unslash( $_POST['_cli_cookie_headscript_meta'] ) );
		}
		if ( isset( $_POST['_cli_cookie_bodyscript_meta'] ) ) {
			update_post_meta( $post->ID, '_cli_cookie_bodyscript_meta', wp_unslash( $_POST['_cli_cookie_bodyscript_meta'] ) );
		}
	}
	public function manage_edit_columns( $columns ) {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Cookie Name', 'webtoffee-gdpr-cookie-consent' ),
			'type'        => __( 'Type', 'webtoffee-gdpr-cookie-consent' ),
			'category'    => __( 'Category', 'webtoffee-gdpr-cookie-consent' ),
			'duration'    => __( 'Duration', 'webtoffee-gdpr-cookie-consent' ),
			'sensitivity' => __( 'Sensitivity', 'webtoffee-gdpr-cookie-consent' ),
			'slugid'      => __( 'ID', 'webtoffee-gdpr-cookie-consent' ),
			'description' => __( 'Description', 'webtoffee-gdpr-cookie-consent' ),
		);
		return $columns;
	}

	/** Add column data to custom post type table columns */
	public function manage_posts_custom_columns( $column, $post_id = 0 ) {
		global $post;

		switch ( $column ) {
			case 'description':
					$content_post = get_post( $post_id );
				if ( $content_post ) {
					echo $content_post->post_content;
				} else {
					echo '---';
				}
				break;
			case 'type':
				$custom = get_post_custom();
				if ( isset( $custom['_cli_cookie_type'][0] ) ) {
					echo $custom['_cli_cookie_type'][0];
				}
				break;
			case 'category':
				$term_list = wp_get_post_terms( $post->ID, 'cookielawinfo-category', array( 'fields' => 'names' ) );
				if ( ! empty( $term_list ) ) {
					echo $term_list[0];
				} else {
					echo '<i>---</i>';
				}

				break;
			case 'duration':
				$custom = get_post_custom();
				if ( isset( $custom['_cli_cookie_duration'][0] ) ) {
					echo $custom['_cli_cookie_duration'][0];
				}
				break;
			case 'sensitivity':
				$custom = get_post_custom();
				if ( isset( $custom['_cli_cookie_sensitivity'][0] ) ) {
					echo $custom['_cli_cookie_sensitivity'][0];
				}
				break;
			case 'slugid':
				$custom = get_post_custom();
				if ( isset( $custom['_cli_cookie_slugid'][0] ) ) {
					echo $custom['_cli_cookie_slugid'][0];
				}
				break;
		}
	}
	public function manage_edit_custom_column_header( $columns ) {
		$columns['CLIpriority']    = __( 'Priority', 'webtoffee-gdpr-cookie-consent' );
		$columns['CLIloadonstart'] = __( 'Load on start', 'webtoffee-gdpr-cookie-consent' );
		return $columns;
	}
	public function manage_custom_column_content( $value, $column_name, $tax_id ) {
		if ( 'CLIpriority' === $column_name ) {
			$value = get_term_meta( $tax_id, 'CLIpriority', true );
		} elseif ( $column_name == 'CLIloadonstart' ) {
			$value = get_term_meta( $tax_id, 'CLIloadonstart', true );
			$value = $value == 1 ? __( 'Yes', 'webtoffee-gdpr-cookie-consent' ) : __( 'No', 'webtoffee-gdpr-cookie-consent' );
		}
		return $value;
	}
	public function add_priority_meta( $term ) {
		?>
		<div class="form-field">
			<label for="CLIpriority"><?php _e( 'Priority', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			<input type="number" name="CLIpriority" id="CLIpriority" value="" step="1">
			<p class="description"><?php _e( 'Numeric - Higher the value, higher the priority', 'webtoffee-gdpr-cookie-consent' ); ?></p>
		</div>
		<?php
	}
	public function edit_priority_meta( $term ) {
		// put the term ID into a variable
		$t_id             = $term->term_id;
		$term_CLIpriority = get_term_meta( $t_id, 'CLIpriority', true );
		?>
		<tr class="form-field">
			<th><label for="CLIpriority"><?php _e( 'Priority', 'webtoffee-gdpr-cookie-consent' ); ?></label></th>
			 
			<td>	 
				<input type="number" name="CLIpriority" id="CLIpriority" value="<?php echo esc_attr( $term_CLIpriority ) ? esc_attr( $term_CLIpriority ) : ''; ?>" step="1">
				<p class="description"><?php _e( 'Numeric - Higher the value, higher the priority', 'webtoffee-gdpr-cookie-consent' ); ?></p>
			</td>
		</tr>
		<?php
	}
	public function save_priority_meta( $term_id ) {

		if ( isset( $_POST['CLIpriority'] ) ) {
			$term_CLIpriority = (int) sanitize_text_field( $_POST['CLIpriority'] );
			if ( $term_CLIpriority ) {
				update_term_meta( $term_id, 'CLIpriority', $term_CLIpriority );
			} else {
				update_term_meta( $term_id, 'CLIpriority', 0 );
			}
		} else {
			update_term_meta( $term_id, 'CLIpriority', 0 );
		}
	}
	public function add_stricly_necessary_meta() {
		?>
		<div class="form-field">
			<label for="CLIstrictlynecessary"><?php _e( 'Make this as stricly necessary category', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			<input type="checkbox" name="CLIstrictlynecessary" id="CLIstrictlynecessary" value="1">
			<p class="description"><?php _e( 'The underlying scripts are assumed to be absolutely essential for the website to function properly. They will be rendered irrespective of user consent.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
		</div>
		<?php
	}
	/**
	 * Add option to edit strictly necessary category in post edit page
	 *
	 * @since  2.3.1
	 * @access public
	 */
	public function edit_stricly_necessary_meta( $term ) {
		// put the term ID into a variable
		$t_id                      = $term->term_id;
		$term_CLIstrictlynecessary = get_term_meta( $t_id, 'CLIstrictlynecessary', true );
		?>
		<tr class="form-field">
			<th><label for="CLIstrictlynecessary"><?php _e( 'Make this as stricly necessary category' ); ?></label></th>			 
			<td>	 
				<input type="checkbox" name="CLIstrictlynecessary" id="CLIstrictlynecessary" value="1" <?php echo $term_CLIstrictlynecessary == 1 ? 'checked="checked"' : ''; ?>>
				<p class="description"><?php _e( 'The underlying scripts are assumed to be absolutely essential for the website to function properly. They will be rendered irrespective of user consent.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
			</td>
		</tr>
		<?php
	}
	public function save_stricly_necessary_meta( $term_id ) {
		if ( isset( $_POST['CLIstrictlynecessary'] ) ) {
			$term_CLIstrictlynecessary = sanitize_text_field( $_POST['CLIstrictlynecessary'] );
			if ( $term_CLIstrictlynecessary ) {
				update_term_meta( $term_id, 'CLIstrictlynecessary', $term_CLIstrictlynecessary );
			}
		} else {
			update_term_meta( $term_id, 'CLIstrictlynecessary', 0 );
		}
	}
	public function add_loadonstart_meta( $term ) {
		?>
		<div class="form-field">
			<label for="CLIloadonstart"><?php _e( 'Load on start', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			<input type="checkbox" name="CLIloadonstart" id="CLIloadonstart" value="1">
			<p class="description"><?php _e( 'If you enable this option, scripts under this category will be rendered without waiting for user consent on first page visit. Use this option discreetly, only if you are sure that no user sensitive data is being obtained via these scripts.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
		</div>
		<?php
	}

	/*
	* Category load on start edit form
	* @since 2.1.8
	*/
	public function edit_loadonstart_meta( $term ) {
		// put the term ID into a variable
		$t_id                = $term->term_id;
		$term_CLIloadonstart = get_term_meta( $t_id, 'CLIloadonstart', true );
		?>
		<tr class="form-field">
			<th><label for="CLIloadonstart"><?php _e( 'Load on start' ); ?></label></th>			 
			<td>	 
				<input type="checkbox" name="CLIloadonstart" id="CLIloadonstart" value="1" <?php echo $term_CLIloadonstart == 1 ? 'checked="checked"' : ''; ?>>
				<p class="description"><?php _e( 'If you enable this option, scripts under this category will be rendered without waiting for user consent on first page visit. Use this option discreetly, only if you are sure that no user sensitive data is being obtained via these scripts.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
			</td>
		</tr>
		<?php
	}
	public function save_loadonstart_meta( $term_id ) {
		if ( isset( $_POST['CLIloadonstart'] ) ) {
			$term_CLIloadonstart = sanitize_text_field( $_POST['CLIloadonstart'] );
			if ( $term_CLIloadonstart ) {
				update_term_meta( $term_id, 'CLIloadonstart', $term_CLIloadonstart );
			}
		} else {
			update_term_meta( $term_id, 'CLIloadonstart', 0 );
		}
	}
	public function create_taxonomy() {
		register_taxonomy(
			'cookielawinfo-category',
			'cookielawinfo',
			array(
				'labels'              => array(
					'name'         => __( 'Cookie Category', 'webtoffee-gdpr-cookie-consent' ),
					'add_new_item' => __( 'Add cookie category', 'webtoffee-gdpr-cookie-consent' ),
					'edit_item'    => __( 'Edit cookie category', 'webtoffee-gdpr-cookie-consent' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'rewrite'             => true,
				'hierarchical'        => false,
				'show_in_menu'        => true,
			)
		);

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
	 * Returns necessary category id's
	 *
	 * @return array
	 */
	public function get_necessary_category_ids() {
		global $wpdb;
		$necessory_categories   = self::get_strictly_necessory_categories();
		$necessory_category_ids = array();

		$categories_count = count( $necessory_categories );
		$placeholders = array_fill(0, $categories_count, '%s');
		$format = implode(', ', $placeholders);
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug IN( $format )";

		$term_data = $wpdb->get_results( $wpdb->prepare($query, $necessory_categories), ARRAY_A );
		foreach ($term_data as $key => $term) {
			if( isset( $term['term_id'] ) ) {
				$necessory_category_ids[] = $term['term_id'];
			}
		}
		return $necessory_category_ids;
	}
	public function get_necessary_categories() {
		if ( ! $this->necessary_categories ) {
			$this->necessary_categories = $this->get_necessary_category_ids();
		}
		return $this->necessary_categories;
	}
	public function get_default_cookie_categories() {

		$cookie_categories = array(

			'necessary'     => array(
				'title'       => 'Necessary',
				'description' => 'Necessary cookies are absolutely essential for the website to function properly. These cookies ensure basic functionalities and security features of the website, anonymously.',
			),
			'functional'    => array(
				'title'       => 'Functional',
				'description' => 'Functional cookies help to perform certain functionalities like sharing the content of the website on social media platforms, collect feedbacks, and other third-party features.',
				'priority'    => 5,
			),
			'performance'   => array(
				'title'       => 'Performance',
				'description' => 'Performance cookies are used to understand and analyze the key performance indexes of the website which helps in delivering a better user experience for the visitors.',
				'priority'    => 4,
			),
			'analytics'     => array(
				'title'       => 'Analytics',
				'description' => 'Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics the number of visitors, bounce rate, traffic source, etc.',
				'priority'    => 3,
			),
			'advertisement' => array(
				'title'       => 'Advertisement',
				'description' => 'Advertisement cookies are used to provide visitors with relevant ads and marketing campaigns. These cookies track visitors across websites and collect information to provide customized ads.',
				'priority'    => 2,
			),
			'others'        => array(
				'title'       => 'Others',
				'description' => 'Other uncategorized cookies are those that are being analyzed and have not been classified into a category as yet.',
				'priority'    => 1,
			),
		);
		return $cookie_categories;

	}
	public function get_default_cookies() {

		$default_cookies = array(
			'viewed_cookie_policy'               => array(
				'title'       => 'viewed_cookie_policy',
				'description' => 'The cookie is set by the GDPR Cookie Consent plugin and is used to store whether or not user has consented to the use of cookies. It does not store any personal data.',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),
			'cookielawinfo-checkbox-necessary'   => array(
				'title'       => 'cookielawinfo-checkbox-necessary',
				'description' => 'This cookie is set by GDPR Cookie Consent plugin. The cookies is used to store the user consent for the cookies in the category "Necessary".',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),
			'cookielawinfo-checkbox-functional'  => array(
				'title'       => 'cookielawinfo-checkbox-functional',
				'description' => 'The cookie is set by GDPR cookie consent to record the user consent for the cookies in the category "Functional".',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),
			'cookielawinfo-checkbox-performance' => array(
				'title'       => 'cookielawinfo-checkbox-performance',
				'description' => 'This cookie is set by GDPR Cookie Consent plugin. The cookie is used to store the user consent for the cookies in the category "Performance".',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),
			'cookielawinfo-checkbox-analytics'   => array(
				'title'       => 'cookielawinfo-checkbox-analytics',
				'description' => 'This cookie is set by GDPR Cookie Consent plugin. The cookie is used to store the user consent for the cookies in the category "Analytics".',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),
			'cookielawinfo-checkbox-others'      => array(
				'title'       => 'cookielawinfo-checkbox-others',
				'description' => 'This cookie is set by GDPR Cookie Consent plugin. The cookie is used to store the user consent for the cookies in the category "Other.',
				'category'    => 'necessary',
				'type'        => 0,
				'expiry'      => '11 months',
				'sensitivity' => 'necessary',
			),

		);
		return $default_cookies;
	}
	public function load_default_cookies() {

		$default_cookies = $this->get_default_cookies();

		foreach ( $default_cookies as $slug => $cookie_data ) {

			if ( false === $this->wt_cli_post_exists_by_slug( $slug ) ) {

				$category = get_term_by( 'slug', $cookie_data['category'], 'cookielawinfo-category' );
				if ( $category && is_object( $category ) ) {

					$category_id = $category->term_id;
					$cookie_post_data = array(
						'post_type'     => CLI_POST_TYPE,
						'post_title'    => $cookie_data['title'],
						'post_name'     => $slug,
						'post_content'  => $cookie_data['description'],
						'post_category' => array( $category_id ),
						'post_status'   => 'publish',
						'ping_status'   => 'closed',
						'post_author'   => 1,
						'meta_input'    => array(
							'_cli_cookie_type'        => $cookie_data['type'],
							'_cli_cookie_duration'    => $cookie_data['expiry'],
							'_cli_cookie_sensitivity' => $cookie_data['category'],
							'_cli_cookie_slugid'      => $slug,
						),
					);
					$post_id     = wp_insert_post( $cookie_post_data );
					wp_set_object_terms( $post_id, $cookie_data['category'], 'cookielawinfo-category' );
				}
			}
		}
	}
	public function wt_cli_post_exists_by_slug( $post_slug ) {
		$args_posts = array(
			'post_type'      => CLI_POST_TYPE,
			'post_status'    => 'any',
			'name'           => $post_slug,
			'posts_per_page' => 1,
		);
		$loop_posts = new WP_Query( $args_posts );
		if ( ! $loop_posts->have_posts() ) {
			return false;
		} else {
			$loop_posts->the_post();
			return $loop_posts->post->ID;
		}
	}
	public function load_default_terms() {

		// wt_cli_temp_fix.
		$cookie_categories = $this->get_default_cookie_categories();

		foreach ( $cookie_categories as $slug => $data ) {

			$existing = get_term_by( 'slug', $slug, 'cookielawinfo-category' );

			if ( $existing === false ) {

				$description            = $data['description'];
				$cookie_audit_shortcode = sprintf( '[cookie_audit category="%s" style="winter" columns="cookie,duration,description"]', $slug );
				$description           .= "\n";
				$description           .= $cookie_audit_shortcode;

				$term = wp_insert_term(
					$data['title'],
					'cookielawinfo-category',
					array(
						'description' => $description,
						'slug'        => $slug,
					)
				);

				if ( is_wp_error( $term ) ) {
					continue;
				}
				$term_id = ( isset( $term['term_id'] ) ? $term['term_id'] : false );

				if ( $term_id !== false ) {

					$priority = isset( $data['priority'] ) ? $data['priority'] : 0;
					$this->update_term_meta( $term_id, 'CLIpriority', $priority );
					Cookie_Law_Info_Languages::get_instance()->maybe_set_term_language( $term_id ); // In polylang plugin the default language will not be get assigned.
				}
			}
		}
		do_action( 'wt_cli_after_create_cookie_categories' );
	}
	public function get_cookie_category_options( $default_lang = false ) {

		$cookie_data              = array();
		$necessary_categories     = array();
		$non_necessary_categories = array();

		$terms = $this->get_cookie_category_terms();

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {

					$strict           = false;
					$term_id          = $term->term_id;
					$current_term_id     = $term_id;
					$term_slug        = $term->slug;
					$term_name        = $term->name;
					$term_description = $term->description;

					$cli_cookie_head_scripts = $this->get_term_meta( $term_id, '_cli_cookie_head_scripts', true );
					$cli_cookie_body_scripts = $this->get_term_meta( $term_id, '_cli_cookie_body_scripts', true );

					$head_scripts = isset( $cli_cookie_head_scripts ) ? wp_unslash( $cli_cookie_head_scripts ) : '';
					$body_scripts = isset( $cli_cookie_body_scripts ) ? wp_unslash( $cli_cookie_body_scripts ) : '';

					if ( Cookie_Law_Info_Languages::get_instance()->is_multilanguage_plugin_active() === true ) {

						$default_language = Cookie_Law_Info_Languages::get_instance()->get_default_language_code();
						$current_language = Cookie_Law_Info_Languages::get_instance()->get_current_language_code();

						if ( $current_language !== $default_language ) {
							$default_term = Cookie_Law_Info_Languages::get_instance()->get_term_by_language( $term_id, $default_language );

							if ( $default_term && $default_term->term_id ) {
								$term_slug = $default_term->slug;
								$term_id   = $default_term->term_id;
								// if ( true === $default_lang ) {
								// 	$term_description = $default_term->description;
								// 	$term_name        = $default_term->name;
								// 	$head_scripts     = $this->get_meta_data_from_db( $term_id, '_cli_cookie_head_scripts' );
								// 	$body_scripts     = $this->get_meta_data_from_db( $term_id, '_cli_cookie_body_scripts' );
								// 	$head_scripts     = isset( $head_scripts ) ? wp_unslash( $head_scripts ) : '';
								// 	$body_scripts     = isset( $body_scripts ) ? wp_unslash( $body_scripts ) : '';
								// }
							}
						}
					}

					$cookies           = $this->get_cookies_by_term( 'cookielawinfo-category', $term_slug );
					$cli_default_state = $this->get_meta_data_from_db( $term_id, 'CLIdefaultstate' );
					$priority          = $this->get_meta_data_from_db( $term_id, 'CLIpriority' );
					$cli_ccpa_optout   = $this->get_meta_data_from_db( $term_id, 'CLIccpaoptout' );
					$cli_load_on_start = $this->get_meta_data_from_db( $term_id, 'CLIloadonstart' );

					$category_data = array(
						'id'            => $term_id,
						'status'        => true,
						'priority'      => isset( $priority ) ? intval( $priority ) : 0,
						'title'         => $term_name,
						'strict'        => $strict,
						'default_state' => isset( $cli_default_state ) && $cli_default_state === 'enabled' ? true : false,
						'ccpa_optout'   => isset( $cli_ccpa_optout ) && intval( $cli_ccpa_optout ) === 1 ? true : false,
						'loadonstart'   => isset( $cli_load_on_start ) && intval( $cli_load_on_start ) === 1 ? true : false,
						'description'   => $term_description,
						'head_scripts'  => $head_scripts,
						'body_scripts'  => $body_scripts,
						'cookies'       => $cookies,
					);

					if ( $this->check_strictly_necessary_category( $term_id ) === true  || $this->check_strictly_necessary_category( $current_term_id ) === true  ) {
						$strict                             = true;
						$category_data['strict']            = $strict;
						$necessary_categories[ $term_slug ] = $category_data;
					} else {
						$non_necessary_categories[ $term_slug ] = $category_data;
					}
				}
			}
			$non_necessary_categories = $this->order_category_by_key( $non_necessary_categories, 'priority' );
			$cookie_data              = $necessary_categories + $non_necessary_categories;
		}
		return $cookie_data;

	}
	public function get_cookies() {

		if ( ! $this->cookies ) {
			$this->cookies = $this->get_cookie_category_options();
		}
		return $this->cookies;
	}
	public function get_cookies_by_term( $taxonomy, $slug ) {
		$cookies = array();
		$args    = array(
			'posts_per_page' => -1,
			'post_type'      => 'cookielawinfo',
			'tax_query'      => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slug,
				),
			),
		);
		$posts   = get_posts( $args );
		if ( $posts ) {
			$cookies = $this->pre_process_cookies( $posts );
		}
		return $cookies;
	}
	public function pre_process_cookies( $cookies_raw ) {
		$cookies = array();
		foreach ( $cookies_raw as $key => $cookie ) {
			$cookie_id       = $cookie->ID;
			$cookie_meta     = get_post_custom( $cookie_id );
			$description     = $cookie->post_content;
			$cookie_name     = $cookie->post_title;
			$cookie_slug 	 = $cookie->post_name;
			$cookie_type     = ( isset( $cookie_meta['_cli_cookie_type'][0] ) ) ? $cookie_meta['_cli_cookie_type'][0] : '';
			$cookie_duration = ( isset( $cookie_meta['_cli_cookie_duration'][0] ) ) ? $cookie_meta['_cli_cookie_duration'][0] : '';
			$cookie_slug_id     = ( isset( $cookie_meta['_cli_cookie_slugid'][0] ) ) ? $cookie_meta['_cli_cookie_slugid'][0] : '';
			$sensitivity     = ( isset( $cookie_meta['_cli_cookie_sensitivity'][0] ) ) ? $cookie_meta['_cli_cookie_sensitivity'][0] : '';

			$head_scripts = ( isset( $cookie_meta['_cli_cookie_headscript_meta'][0] ) ) ? $cookie_meta['_cli_cookie_headscript_meta'][0] : '';
			$body_scripts = ( isset( $cookie_meta['_cli_cookie_bodyscript_meta'][0] ) ) ? $cookie_meta['_cli_cookie_bodyscript_meta'][0] : '';

			$cookie_data = array(
				'id'           => $cookie->ID,
				'slug'         => $cookie_slug,
				'slug_id'      => $cookie_slug_id,
				'sensitivity'  => $sensitivity,
				'title'        => $cookie_name,
				'description'  => $description,
				'type'         => $cookie_type,
				'duration'     => $cookie_duration,
				'head_scripts' => $head_scripts,
				'body_scripts' => $body_scripts,
			);
			$cookies[]   = $cookie_data;
		}
		return $cookies;
	}
	/*
	* Category default state add form
	*/
	public function add_defaultstate_meta( $term ) {
		?>
		<div class="form-field term-defaultstate-field">
			<label for="CLIdefaultstate"><?php _e( 'Category default state', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			<input type="radio" name="CLIdefaultstate" value="enabled"  /><?php _e( 'Enabled', 'webtoffee-gdpr-cookie-consent' ); ?>
			<input type="radio" name="CLIdefaultstate" value="disabled" checked /><?php _e( 'Disabled', 'webtoffee-gdpr-cookie-consent' ); ?>	
			<p class="description"><?php _e( 'If you enable this option, the category toggle button will be in the active state for cookie consent.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
		</div>
		<?php
	}
	public function check_strictly_necessary_category( $term_id ) {

		$strict_enabled = $this->get_necessary_categories();
		if ( in_array( $term_id, $strict_enabled ) ) {
			return true;
		}
		return false;
	}
	/*
	* Category Active State edit form
	*/
	public function edit_defaultstate_meta( $term ) {
		// put the term ID into a variable
		$t_id                 = $term->term_id;
		$term_CLIdefaultstate = $this->get_term_meta( $t_id, 'CLIdefaultstate', true );

		if ( $this->check_strictly_necessary_category( $t_id ) === false ) {
			?>
		<tr class="form-field term-defaultstate-field">
			<th><label for="CLIdefaultstate"><?php _e( 'Category default state', 'webtoffee-gdpr-cookie-consent' ); ?></label></th>			 
			<td>
				<input type="radio" name="CLIdefaultstate" value="enabled" <?php checked( $term_CLIdefaultstate, 'enabled' ); ?>/><label><?php _e( 'Enabled', 'webtoffee-gdpr-cookie-consent' ); ?></label>
				<input type="radio" name="CLIdefaultstate" value="disabled" <?php checked( $term_CLIdefaultstate, 'disabled' ); ?>/><label><?php _e( 'Disabled', 'webtoffee-gdpr-cookie-consent' ); ?></label>		 
				<p class="description"><?php _e( 'If you enable this option, the category toggle button will be in the active state for cookie consent.', 'webtoffee-gdpr-cookie-consent' ); ?></p>
			</td>
		</tr>
			<?php
		}
	}

	/*
	* Category Active State save form
	*/
	public function save_defaultstate_meta( $term_id ) {
		if ( isset( $_POST['CLIdefaultstate'] ) ) {
			$term_CLIdefaultstate = sanitize_text_field( $_POST['CLIdefaultstate'] );

			if ( $term_CLIdefaultstate ) {
				$this->update_term_meta( $term_id, 'CLIdefaultstate', $term_CLIdefaultstate );
			}
		} else {
			$this->update_term_meta( $term_id, 'CLIdefaultstate', 'disabled' );
		}

	}

	public function add_scripts_meta( $term ) {
		?>
		<div class="form-field term-head-scripts-field">
			<p>	
				<label><b><?php _e( 'Head scripts', 'webtoffee-gdpr-cookie-consent' ); ?></b></label>
				<label>Script: eg:-  &lt;script&gt; enableGoogleAnalytics(); &lt;/script&gt; </label><br />
				<textarea id="_cli_cookie_head_scripts" rows=5 name="_cli_cookie_head_scripts" class="wt-cli-code-editor"></textarea>
			</p>
		</div>
		<div class="form-field term-body-scripts-field">
			<p>	
				<label><b><?php _e( 'Body scripts', 'webtoffee-gdpr-cookie-consent' ); ?></b></label>
				<label>Script: eg:-  &lt;script&gt; enableGoogleAnalytics(); &lt;/script&gt; </label><br />
				<textarea id="_cli_cookie_body_scripts" rows="5" name="_cli_cookie_body_scripts" class="wt-cli-code-editor" ></textarea>
			</p>
		</div>
		<?php
	}

	public function edit_scripts_meta( $term ) {
		// put the term ID into a variable
		$term_id      = $term->term_id;
		$head_scripts = $this->get_term_meta( $term_id, '_cli_cookie_head_scripts', true );
		$body_scripts = $this->get_term_meta( $term_id, '_cli_cookie_body_scripts', true );
		?>
		<tr class="form-field term-body-scripts-field">
			<th>
				<label for="_cli_cookie_head_scripts"><?php _e( 'Head scripts', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			</th>			 
			<td>
				<textarea id="_cli_cookie_head_scripts" rows="5" name="_cli_cookie_head_scripts" class="wt-cli-code-editor"><?php echo wp_unslash( $head_scripts ); ?></textarea>
			</td>
		</tr>
		<tr class="form-field term-head-scripts-field">
			<th>
				<label for="_cli_cookie_body_scripts"><?php _e( 'Body scripts', 'webtoffee-gdpr-cookie-consent' ); ?></label>
			</th>			 
			<td>
				<textarea  id="_cli_cookie_body_scripts" rows="5" name="_cli_cookie_body_scripts" class="wt-cli-code-editor"><?php echo wp_unslash( $body_scripts ); ?></textarea>
			</td>
		</tr>
		<?php
	}

	public function save_scripts_meta( $term_id ) {
		$head_scripts = ( isset( $_POST['_cli_cookie_head_scripts'] ) ? wp_unslash( $_POST['_cli_cookie_head_scripts'] ) : '' );
		$body_scripts = ( isset( $_POST['_cli_cookie_body_scripts'] ) ? wp_unslash( $_POST['_cli_cookie_body_scripts'] ) : '' );

		$this->update_term_meta( $term_id, '_cli_cookie_head_scripts', $head_scripts );
		$this->update_term_meta( $term_id, '_cli_cookie_body_scripts', $body_scripts );

	}
	public function get_cookie_db_version() {
		$current_db_version = get_option( 'wt_cli_cookie_db_version', '1.0' );
		return $current_db_version;
	}
	public function check_if_old_category_table() {
		return ! is_null( $this->get_cookie_db_version() ) && version_compare( $this->get_cookie_db_version(), '2.0', '<' ) === true;
	}
	public function get_cookies_by_meta( $meta, $value ) {
		$cookies = array();
		$args    = array(
			'post_type'  => CLI_POST_TYPE,
			'meta_query' => array(
				array(
					'key'   => $meta,
					'value' => $value,
				),
			),

		);
		$posts = get_posts( $args );
		if ( $posts ) {
			$cookies = $posts;
		}
		return $cookies;
	}
	/** Display the custom meta box for head script */
	public function metabox_headscript() {
		global $post;
		$custom                      = get_post_custom( $post->ID );
		$_cli_cookie_headscript_meta = ( isset( $custom['_cli_cookie_headscript_meta'][0] ) ) ? $custom['_cli_cookie_headscript_meta'][0] : '';

		?>
		<style>.width99 {width:99%;}</style>
		<p>
			<label>Script: eg:-  &lt;script&gt; enableGoogleAnalytics(); &lt;/script&gt; </label><br />
			<textarea rows="5" name="_cli_cookie_headscript_meta" class="width99"><?php echo $_cli_cookie_headscript_meta; ?></textarea>
		</p>
		<?php
	}

	/** Display the custom meta box for body script */
	public function metabox_bodyscript() {
		global $post;
		$custom                      = get_post_custom( $post->ID );
		$_cli_cookie_bodyscript_meta = ( isset( $custom['_cli_cookie_bodyscript_meta'][0] ) ) ? $custom['_cli_cookie_bodyscript_meta'][0] : '';

		?>
		<style>.width99 {width:99%;}</style>
		<p>
				<label>Script: eg:-  &lt;script&gt; enableGoogleAnalytics(); &lt;/script&gt; </label><br />
			<textarea rows="5" name="_cli_cookie_bodyscript_meta" class="width99"><?php echo $_cli_cookie_bodyscript_meta; ?></textarea>
		</p>
		<?php
	}
	/**
	 * Get meta data directly from the DB
	 *
	 * @param int    $term_id Term id.
	 * @param string $meta_key Meta key name.
	 * @return string
	 */
	public function get_meta_data_from_db( $term_id, $meta_key ) {
		global $wpdb;
		$term_value = false;
		$term_meta  = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $wpdb->termmeta WHERE term_id = %d AND meta_key = %s", $term_id, $meta_key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		if ( $term_meta ) {
			$term_value = $term_meta->meta_value;
		}
		return $term_value;
	}
	/**
	 * Get term data directly from DB to avoid hooks by other plugins.
	 *
	 * @param string $key using this key the row is fetched.
	 * @param string $value value of the corresponding key.
	 * @return array
	 */
	public function get_term_data_from_db( $key, $value ) {
		global $wpdb;
		$term_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->terms WHERE $key = %s", $value ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		if ( $term_data && is_object( $term_data ) ) {
			return $term_data;
		}
		return false;
	}
	/**
	 * Re order the categories based on the key
	 *
	 * @param array  $categories Category array.
	 * @param string $key The key based on which an array is sorted.
	 * @param string $order The sort order default DESC.
	 * @return array
	 */
	public function order_category_by_key( $categories, $meta_key, $order = 'DESC' ) {
		$sort_order  = SORT_DESC;
		$meta_values = array();
		if ( 'ASC' === $order ) {
			$sort_order = SORT_ASC;
		}
		if ( ! empty( $categories ) && is_array( $categories ) ) {
			foreach ( $categories as $key => $category ) {
				if ( isset( $category[ $meta_key ] ) ) {
					$meta_values[] = $category[ $meta_key ];
				}
			}
			if ( ! empty( $meta_values ) && is_array( $meta_values ) ) {
				array_multisort( $meta_values, $sort_order, $categories );
			}
		}
		return $categories;
	}
}
Cookie_Law_Info_Cookies::get_instance();
