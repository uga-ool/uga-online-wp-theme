<?php get_header(); ?>

<div class="obj-reading-width util-margin-vert-xl">
  <?php custom_breadcrumbs(); ?>
</div>

<?php
  $id = get_queried_object_id();
  $post = get_post($id);
  $postType = $post->post_type;

  if ($postType == "program"):
    get_template_part( 'template-parts/content', 'program-hero' );
  endif;
    
?>

<div class="obj-reading-width util-margin-vert-xl util-margin-vert-lg@print">
  <?php
    while ( have_posts() ) :
      the_post();
      get_template_part( 'template-parts/content', get_post_type() );
    endwhile; // End of the loop.
  ?>
</div>

<?php get_footer();
