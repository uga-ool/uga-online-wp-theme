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
    <h2 class="cmp-heading-1 util-margin-all-none"><?php the_title(); ?></h2>

    <?php if ( 'post' === get_post_type() ) : ?>
    <div class="util-margin-top-lg util-margin-bottom-xxl cmp-heading-6">
      <?php
        uga_theme_posted_on();
        uga_theme_posted_by();
      ?>
    </div>
    <?php endif; ?>
  </header>

  <div>
    <?php if ( has_post_thumbnail() ) { ?>
      <img class="cmp-post__image" src="<?php the_post_thumbnail_url() ?>" />
    <?php } ?>
    <?php the_content(); ?>
  </div>
</article><!-- #post-<?php the_ID(); ?> -->
