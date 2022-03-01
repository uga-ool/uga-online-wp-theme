<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */
    
 
    $uga = 145;  # This is the post ID for the UGA institution
    $financial_aid = get_field('financial_aid', $uga);
    $corporate_assistance = get_field('corporate_assistance', $uga);
    $military_assistance = get_field('military_assistance', $uga);
    $technology_requirements = get_field('technology_requirements', $uga);
    $bursar_information = get_field('bursar_information', $uga);
    $fee_information = get_field('fee_information', $uga);
    $university_accreditation = get_field('university_accreditation', $uga);
?>

<article id="post-<?php the_ID(); ?>" class="util-margin-vert-xl util-margin-vert-xxl@lg">
  <header class="entry-header">
    <h2 class="cmp-heading-1 util-margin-all-none"><?php the_title(); ?></h2>

    <?php
        echo($financial_aid);
        echo($university_accreditation);
        the_field('test_field_1'); 
    ?>
    <br />
    <?php
        the_field('test_field_4');
    ?>

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

