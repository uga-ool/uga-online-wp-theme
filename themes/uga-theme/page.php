<?php get_header(); ?>



<?php 
  $display_hero = get_field('display_hero');

  if($display_hero):
    get_template_part( 'template-parts/content', 'page-hero' ); 
  endif;
?>

<div class="obj-reading-width util-margin-vert-xxl">
  <?php custom_breadcrumbs(); ?>
</div>

  <div class="obj-reading-width util-margin-vert-xxl util-margin-vert-lg@print">
    <?php
    while ( have_posts() ) :
      the_post();
      /* Page templates are assigned one of three ways. Either add the page and template-part names to the array below, 
      create a template-part file that matches the page name, or let the basic content-page.php template handle it. */
      $template_base = 'template-parts/content';
      $page_name = get_post_field( 'post_name', get_post());
      $templates = array(
        "page_name"=>"template_part_name",
        "degrees-certificates"=>"program-search"
      );

      if ($templates[$page_name]):
        get_template_part( 'template-parts/content', $templates[$page_name] );
      elseif (locate_template($template_base."-".$page_name.".php")): 
        get_template_part( 'template-parts/content', $page_name );
      else:
        get_template_part( 'template-parts/content', 'page' );
      endif;
      

      // If comments are open or we have at least one comment, load up the comment template.
      if ( comments_open() || get_comments_number() ) :
        comments_template();
      endif;

    endwhile; // End of the loop.
    ?>
  </div>
<?php 
  // get_template_part( 'template-parts/site', 'rfi' );
  get_footer();
?>
