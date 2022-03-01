<?php get_header(); ?>


  <?php
  get_template_part( 'template-parts/content', 'homepage-hero' );
  get_template_part( 'template-parts/content', 'homepage-quote' );
  get_template_part( 'template-parts/content', 'homepage-video' );
  // get_template_part( 'template-parts/site', 'rfi' );

  /* Include something like this if we want to include blog posts on the homepage
  while ( have_posts() ) :
    the_post();

    get_template_part( 'template-parts/content', 'home' );

  endwhile; // End of the loop.
  */

  ?>

  <?php if ( is_active_sidebar( 'homepage' ) ) { ?>
    <div class="obj-grid obj-grid--gap-lg">
      <?php dynamic_sidebar( 'homepage' ); ?>
    </div>
  <?php } ?>


<?php get_footer(); ?>
