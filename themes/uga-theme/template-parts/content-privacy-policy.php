<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <header class="entry-header">
    <?php the_title( '<h1 class="cmp-heading-1">', '</h1>' ); ?>
  </header><!-- .entry-header -->

  <?php uga_theme_post_thumbnail(); ?>

  <div>
    <h2>XXXX</h2>
    <?php get_template_part( 'template-parts/site', 'rfi' ); ?>
    <?php
    the_content();

    wp_link_pages( array(
      'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'uga-theme' ),
      'after'  => '</div>',
    ) );
    ?>
  </div><!-- .entry-content -->

  <?php if ( get_edit_post_link() ) : ?>
    <footer>
      <?php
      edit_post_link(
        sprintf(
          wp_kses(
            /* translators: %s: Name of current post. Only visible to screen readers */
            __( 'Edit <span class="screen-reader-text">%s</span>', 'uga-theme' ),
            array(
              'span' => array(
                'class' => array(),
              ),
            )
          ),
          get_the_title()
        ),
        '<span>',
        '</span>'
      );
      ?>
    </footer>
  <?php endif; ?>
</article>
