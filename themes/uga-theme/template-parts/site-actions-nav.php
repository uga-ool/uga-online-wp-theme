
<?php
wp_nav_menu( array(
  'menu'            => 'Actions Menu',
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
  'items_wrap'      => '<nav class="cmp-actions-nav">%3$s</nav>',
  'item_spacing'    => 'preserve',
  'depth'           => 0,
  'walker'          => new Actions_Menu(),
  'theme_location'  => 'actions',
) );

/**
* Custom walker class.
*/
class Actions_Menu extends Walker_Nav_Menu {

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
    $output .= ' ';
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

    // Link attributes.
    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
    $attributes .= ' class="cmp-actions-nav__link"';

    $output .= '<a '. $attributes .'>' . apply_filters( 'the_title', $item->title, $item->ID ) . '</a>';

  }
}
?>
