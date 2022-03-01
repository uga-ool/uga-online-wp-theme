<?php get_header(); ?>

  <div class="obj-reading-width util-margin-vert-xxl util-margin-vert-lg@print">

    <?php if ( have_posts() ) : ?>

      <header class="page-header">
        <h2>
          <?php
          /* translators: %s: search query. */
          printf( esc_html__( 'Search Results for: %s', 'uga-theme' ), '<span>' . get_search_query() . '</span>' );
          ?>
        </h2>
      </header><!-- .page-header -->

      <?php
      /* Start the Loop */
      while ( have_posts() ) :
        the_post();

        /**
         * Run the loop for the search to output the results.
         * If you want to overload this in a child theme then include a file
         * called content-search.php and that will be used instead.
         */
        get_template_part( 'template-parts/content', 'search' );

      endwhile;

      the_pagination();

    else :

      get_template_part( 'template-parts/content', 'none' );

    endif;
    ?>

  </div>
<?php get_footer();
