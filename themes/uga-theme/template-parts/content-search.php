<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

?>

<article id="post-<?php the_ID(); ?>" class="util-margin-vert-xl util-margin-vert-xxl@lg">
  <header class="entry-header">
    <?php the_title( sprintf( '<h2 class="cmp-heading-5 util-margin-all-none"><a href="%s" class="util-color-red util-delink util-underline-hover" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

    <?php if ( 'post' === get_post_type() ) : ?>
    <div class="util-margin-vert-sm">
      <small>
        <?php
        uga_theme_posted_on();
        uga_theme_posted_by();
        ?>
      </small>
    </div>
    <?php endif; ?>
  </header>

  <div>
    <?php the_excerpt(); ?>
  </div>
</article><!-- #post-<?php the_ID(); ?> -->
