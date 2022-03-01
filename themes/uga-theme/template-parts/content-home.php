<article>
  <header class="entry-header">
    <?php the_title( '<h2 class="cmp-heading-2">', '</h2>' ); ?>
  </header><!-- .entry-header -->

  <?php uga_theme_post_thumbnail(); ?>

  <div>
    <?php
    the_content();
    ?>
  </div><!-- .entry-content -->
</article>
