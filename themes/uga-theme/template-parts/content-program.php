<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

?>

<?php
  $pageName = $post->post_title;
  ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <!-- .entry-header -->

  <div class="obj-grid util-background-light-gray">
    
    <div class="obj-grid__12-12 obj-grid__4-12@md util-margin-horiz-md util-pad-all-lg util-background-white util-position-relative util-position-top-neg-xl util-position-top-neg-lg@md">
      <h1 class="cmp-heading-4">Program Quick Facts</h1>
      <ul>
        <li>Apply by <?php echo(get_field('application_deadline')) ?> to begin <?php echo(get_field('cohort_start')) ?></li>
        <li><?php echo(get_field('cost_description')) ?></li>
        <?php
          if(get_field('gre_waived')):
            echo("<li>No GRE Required</li>");
          endif; 
        ?>
        <?php 
          while( have_rows('program_highlights') ) : the_row();
            echo("<li>" . get_sub_field('highlight') . "</li>");
          endwhile;
        ?>
      </ul>
    </div>

    <div class="obj-grid__12-12 obj-grid__8-12@md util-margin-horiz-md util-margin-top-lg util-margin-top-none@md util-pad-all-lg util-background-white util-position-relative util-position-top-neg-xl util-position-top-neg-lg@md">
      <?php get_template_part( 'template-parts/content', 'program-rfi' ); ?>
    </div>

    <div class="obj-grid__12-12 util-margin-horiz-md util-margin-top-lg util-pad-all-lg util-background-white util-position-relative util-position-top-neg-xl util-position-top-neg-lg@md">
      <h2>Overview</h2>
      <?php the_content(); ?>
    </div>
    
      <?php
        if(have_rows('accordion')):
          echo('<div class="obj-grid__12-12 util-margin-horiz-md util-margin-top-lg util-pad-all-lg util-background-white util-position-relative util-position-top-neg-xl util-position-top-neg-lg@md">');
          echo('<h2>More Information</h2>');
          echo('<div class="util-position-relative util-position-top-neg-md util-position-top-neg-lg@md">');
          get_template_part( 'template-parts/content', 'page-accordion' );
          echo('</div></div>');
        endif;
      ?>
    
    <?php
    wp_link_pages( array(
      'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'uga-theme' ),
      'after'  => '</div>',
    ) );
    ?><!-- .entry-content -->
  </div>

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
