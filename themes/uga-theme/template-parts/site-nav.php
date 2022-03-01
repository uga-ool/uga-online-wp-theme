
<?php
wp_nav_menu( array(
  'menu'            => 'Main Menu',
  'container'       => '',
  'container_class' => '',
  'container_id'    => '',
  'menu_class'      => '',
  'menu_id'         => '',
  'echo'            => true,
  'fallback_cb'     => 'wp_page_menu',
  'before'          => '',
  'after'           => '',
  'link_before'     => '',
  'link_after'      => '',
  'items_wrap'      => '<ul class="cmp-nav__list">%3$s</ul>',
  'item_spacing'    => 'preserve',
  'depth'           => 0,
  'walker'          => new Primary_Nav(),
  'theme_location'  => 'primary',
) );

/**
* Custom walker class.
*/
class Primary_Nav extends Walker_Nav_Menu {

  /**
  * Starts the list before the elements are added.
  *
  * Adds classes to the unordered list sub-menus.
  *
  * @param string $output Passed by reference. Used to append additional content.
  * @param int    $depth  Depth of menu item. Used for padding.
  * @param array  $args   An array of arguments. @see wp_nav_menu()
  */
  function start_lvl( &$output, $depth = 0, $args = array() ) {
    $output .= ' ';
  }

  function end_lvl( &$output, $depth = 0, $args = array() ) {
    $output .= '</ul>';
  }

  /**
  * Start the element output.
  *
  * Adds main/sub-classes to the list items and links.
  *
  * @param string $output Passed by reference. Used to append additional content.
  * @param object $item   Menu item data object.
  * @param int    $depth  Depth of menu item. Used for padding.
  * @param array  $args   An array of arguments. @see wp_nav_menu()
  * @param int    $id     Current item ID.
  */
  function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
    global $wp_query;
    $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent
    $n = "\n";

    // Depth-dependent classes.
    $depth_classes = array(
       ( $depth == 0 ? 'cmp-nav__item' : 'cmp-subnav__item' )
    );
    $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );

    // Passed classes.
    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
    $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

    // Build HTML.
    $output .= $n . $indent . '<li class="' . $depth_class_names . ' util-position-relative">';

    // Link attributes.
    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
    $attributes .= ' class="' . ( $depth > 0 ? 'cmp-subnav__link' : 'cmp-nav__link' ) . ' "';

    $link = '<a '. $attributes .'>' . apply_filters( 'the_title', $item->title, $item->ID ) . '</a>';

    if ($depth == 0 && $args->walker->has_children) {
      $output .= '<div class="cmp-nav__subnav-actions">' . $n;
      $output .= $link . $n;
      $output .= '<button class="cmp-nav__toggle js-subnav" id="nav-'. $item->db_id .'" data-icon-expanded="icon-up-arrow" data-icon-hidden="icon-down-arrow">' . $n;
      $output .= '<span class="util-visually-hidden">Open Navigation</span>' . $n;
      $output .= '</button>' . $n;
      $output .= '</div>' . $n;
      $output .= '<ul class="cmp-subnav" aria-labelledby="nav-' . $item->db_id . '">' . $n;
    }
    else {
      $output .= $link . $n;
    }
  }

  public function end_el( &$output, $object, $depth = 0, $args = array() ) {
    if ($depth == 0 && $args->walker->has_children) {
      $output .= '</li>' . $n;
    }
    else {
      $output .= '</li>';
    }

  }
}
?>
