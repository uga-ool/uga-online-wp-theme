<?php
/**
 * uga-theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package uga-theme
 */

if ( ! function_exists( 'uga_theme_setup' ) ) :
  /**
   * Sets up theme defaults and registers support for various WordPress features.
   *
   * Note that this function is hooked into the after_setup_theme hook, which
   * runs before the init hook. The init hook is too late for some features, such
   * as indicating support for post thumbnails.
   */
  function uga_theme_setup() {

    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );

    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support( 'post-thumbnails' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
      'primary' => esc_html__( 'Primary', 'uga-theme' ),
      'actions' => esc_html__( 'Actions', 'uga-theme' )
    ) );

    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    ) );

    // Add theme support for selective refresh for widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'custom-logo' );
  }
endif;
add_action( 'after_setup_theme', 'uga_theme_setup' );

function uga_search_form( $form ) {
    $form = '
    <div class="cmp-header-search js-search">
      <form class="cmp-header-search__form" role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
        <div class="cmp-header-search__form-container">
          <label class="util-visually-hidden" for="s">' . __( 'Search for:' ) . '</label>
          <div class="cmp-header-search__field-container">
            <input class="cmp-header-search__field js-search-field" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="Search" />
          </div>
          <button class="cmp-header-search__button icon-search" type="submit" id="searchsubmit">
            <span class="util-visually-hidden">'. esc_attr__( 'Search' ) .'</span>
          </button>
        </div>
      </form>
    </div>';

    return $form;
}

add_filter( 'get_search_form', 'uga_search_form', 100 );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function uga_theme_widgets_init() {
  register_sidebar( array(
    'name'          => esc_html__( 'Prefooter', 'uga-theme' ),
    'id'            => 'prefooter',
    'description'   => esc_html__( 'Add widgets here.', 'uga-theme' ),
    'before_widget' => '<section id="%1$s" class="util-background-light-gray %2$s"><div class="obj-reading-width util-pad-vert-xxl">',
    'after_widget'  => '</div></section>',
    'before_title'  => '<h2 class="cmp-heading-4 util-text-center">',
    'after_title'   => '</h2>',
  ) );

  register_sidebar( array(
    'name'          => esc_html__( 'Homepage', 'home' ),
    'id'            => 'homepage',
    'description'   => esc_html__( 'Add widgets here.', 'uga-theme' ),
    'before_widget' => '<div id="%1$s" class="obj-grid__full obj-grid__third@lg util-text-center">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="cmp-heading-4">',
    'after_title'   => '</h2>',
  ) );
}

/** Removed this to get rid of widgets just above the footer. Remove comment to renable widgets.
* add_action( 'widgets_init', 'uga_theme_widgets_init' );
 */

/**
 * Enqueue scripts and styles.
 */
function uga_theme_styles() {
  /* wp_enqueue_style( 'uga-theme-style', 'https://design.online.uga.edu/css/base.css' ); */
  wp_enqueue_style( 'uga-theme-style', 'https://d1u7ulh8blq558.cloudfront.net/css/base.css' );
  wp_enqueue_style( 'uga-solutions-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'uga_theme_styles' );

function uga_theme_scripts() {
 /* wp_enqueue_script( 'uga-theme-style', 'https://design.online.uga.edu/js/scripts.js' ); */
 wp_enqueue_script( 'uga-theme-style', 'https://d1u7ulh8blq558.cloudfront.net/js/scripts.js' );

 if (is_page('degrees-certificates')) {
  wp_enqueue_script( 'vue', 'https://unpkg.com/vue@3' );
  wp_enqueue_script( 'axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.26.0/axios.min.js' );
  wp_enqueue_script( 'vueTest.js', get_template_directory_uri() . '/js/programSearch.js', 'vue', null, true );
 }
}
add_action( 'wp_footer', 'uga_theme_scripts' );

// Breadcrumbs
function custom_breadcrumbs() {
  $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $home = get_bloginfo('name'); // text for the 'Home' link
  $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  $beforeCurrent= '<li class="cmp-breadcrumbs__item cmp-breadcrumbs__item--current">';
  $before = '<li class="cmp-breadcrumbs__item">'; // tag before the current crumb
  $after = '</li>'; // tag after the current crumb
  $class = 'class="cmp-breadcrumbs__link"';

  global $post;
  $homeLink = get_bloginfo('url');
  if (!is_home() || !is_front_page()) {
    echo '<ol class="cmp-breadcrumbs">'. $before .'<a href="' . $homeLink . '" '. $class .'">' . $home . '</a>' . $after;

    // Category
    if (is_category()) {
      $thisCat = get_category(get_query_var('cat'), false);
      if ($thisCat->parent != 0) {
        echo get_category_parents($thisCat->parent, true, '');
      }
      echo $beforeCurrent . single_cat_title('', false) . $after;

    // Search
    } elseif (is_search()) {
      echo $beforeCurrent . 'Search results for "' . get_search_query() . '"' . $after;

    // Day
    } elseif (is_day()) {
      echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '" '. $class .'>' . get_the_time('Y') . '</a>' . $after;
      echo $before .'<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '"'. $class .'>' . get_the_time('F') . '</a>' . $after;
      echo $before . get_the_time('d') . $after;

    // Month
    } elseif (is_month()) {
      echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '"'. $class .'>' . get_the_time('Y') . '</a>' . $after;
      echo $before . get_the_time('F') . $after;

    // Year
    } elseif (is_year()) {
        echo $before . get_the_time('Y') . $after;

    // Single
    } elseif (is_single() && !is_attachment()) {
      if (get_post_type() != 'post') {
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        echo $before . '<a href="' . $homeLink . '/' . $slug['slug'] . '/"'. $class .'>' . $post_type->labels->singular_name . '</a>' . $after;
        if ($showCurrent == 1) {
          echo $beforeCurrent . get_the_title() . $after;
        }
      } else {
        $cat = get_the_category();
        $cat = $cat[0];
        $cats = get_category_parents($cat, false, '');
        echo $before . '<a href="' . get_category_link($cat) . '" class="cmp-breadcrumbs__link">' . $cats . '</a>';
        if ($showCurrent == 1) {
          echo $beforeCurrent . get_the_title() . $after;
        }
      }

    // Not Anything Else
    } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
      $post_type = get_post_type_object(get_post_type());
      echo $before . $post_type->labels->singular_name . $after;

    // Attachment
    } elseif (is_attachment()) {
      $parent = get_post($post->post_parent);
      $cat = get_the_category($parent->ID);
      $cat = $cat[0];
      echo get_category_parents($cat, true, '  ');
      echo $before .'<a href="' . get_permalink($parent) . '"'. $class .'>' . $parent->post_title . '</a>' . $after;
      if ($showCurrent == 1) {
        echo $beforeCurrent . get_the_title() . $after;
      }

    // Posts Listing Page
  } elseif ( is_home() && ! is_front_page() ) {
      $title = single_post_title( '', false );
      if ($showCurrent == 1) {
        echo $beforeCurrent . $title . $after;
      }

    // Page without Parent
    } elseif (is_page() && !$post->post_parent) {
      if ($showCurrent == 1) {
        echo $beforeCurrent . get_the_title() . $after;
      }

    // Page with Parent
    } elseif (is_page() && $post->post_parent) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = $before . '<a href="' . get_permalink($page->ID) . '"'. $class .'>' . get_the_title($page->ID) . '</a>' . $after;
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      for ($i = 0; $i < count($breadcrumbs); $i++) {
        echo $breadcrumbs[$i];
        if ($i != count($breadcrumbs)-1) {
          echo '';
        }
      }
      if ($showCurrent == 1) {
        echo $beforeCurrent . get_the_title() . $after;
      }

    // Tag
    } elseif (is_tag()) {
      echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;

    // Author
    } elseif (is_author()) {
      global $author;
      $userdata = get_userdata($author);
      echo $beforeCurrent . 'Articles by ' . $userdata->display_name . $after;

    // 404
    } elseif (is_404()) {
      echo $beforeCurrent . 'Error 404' . $after;
    }
    if (get_query_var('paged')) {
      if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
        echo ' (';
      }
      echo __('Page') . ' ' . get_query_var('paged');
      if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
        echo ')';
      }
    }
    echo '</ol>';
  }
}

// PAGINATION

function the_pagination() {
  $prev = '<svg class="cmp-pagination__icon"><use xlink:href="#icon-arrow-left"></use></svg><span class="util-visually-hidden">Previous</span>';
  $next = '<svg class="cmp-pagination__icon"><use xlink:href="#icon-arrow-right"></use></svg><span class="util-visually-hidden">Next</span>';

  $pagination = '<nav class="cmp-pagination" style="justify-content: space-around">';
  $pagination .= get_next_posts_link( $next );
  $pagination .= get_previous_posts_link( $prev );

  $pagination .= '</nav>';

  echo $pagination;
}

add_filter('next_posts_link_attributes', 'uga_next_link');
add_filter('previous_posts_link_attributes', 'uga_prev_link');

function uga_next_link() {
  return 'class="cmp-pagination__link cmp-pagination__link--next" aria-label="Next Page"';
}

function uga_prev_link() {
  return 'class="cmp-pagination__link cmp-pagination__link--prev" aria-label="Previous Page"';
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
  require get_template_directory() . '/inc/jetpack.php';
}