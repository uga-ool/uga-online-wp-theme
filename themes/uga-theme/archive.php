<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

get_header();
?>

  <div class="obj-reading-width util-margin-vert-xxl">
    <?php custom_breadcrumbs(); ?>
  </div>

  <div class="obj-reading-width util-margin-vert-xxl util-margin-vert-lg@print">
    <?php if ( have_posts() ) : ?>

      <header class="page-header">
        <?php
        the_archive_title( '<h2 class="cmp-heading-2">', '</h2>' );
        the_archive_description( '<div class="archive-description">', '</div>' );
        ?>
      </header><!-- .page-header -->

      <?php
      /* Start the Loop */
      while ( have_posts() ) :
        the_post();

        /*
         * Include the Post-Type-specific template for the content.
         * If you want to override this in a child theme, then include a file
         * called content-___.php (where ___ is the Post Type name) and that will be used instead.
         */
        get_template_part( 'template-parts/content', 'search' );

      endwhile;

      the_posts_navigation();

    else :

      get_template_part( 'template-parts/content', 'none' );

    endif;
    ?>
  </div>

<?php
get_footer();
