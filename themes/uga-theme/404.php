<?php get_header(); ?>

  <section class="obj-reading-width util-margin-vert-xxl util-margin-vert-lg@print">
    <header>
      <h2><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'uga-theme' ); ?></h2>
    </header><!-- .page-header -->

    <div>
      <p>It looks like nothing was found at this location. Perhaps you can return back to the <a href="/">homepage</a> and see if you can find what you are looking for. Or, you can try finding it by using the search form below.</p>

      <div class="util-margin-top-xxl" style="max-width:30rem;">
        <?php get_search_form(); ?>
      </div>
    </div>
  </section>

<?php get_footer();
