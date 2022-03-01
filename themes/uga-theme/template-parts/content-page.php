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
  <!-- .entry-header -->

  <?php uga_theme_post_thumbnail(); ?>

  <div>
    <?php
    
    the_content();

    if(get_field('display_feature_video')):
      get_template_part( 'template-parts/content', 'page-feature-video' );
    endif;

    if(have_rows('accordion')):
      get_template_part( 'template-parts/content', 'page-accordion' );
    endif;

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
