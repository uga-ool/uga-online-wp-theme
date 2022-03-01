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
   if ( has_post_thumbnail() ) {
     $image_id = get_post_thumbnail_id();
     $xxl_image_url = wp_get_attachment_image_src( $image_id, '2048x2048' )[0];
     $xl_image_url = wp_get_attachment_image_src( $image_id, '1536x1536' )[0];
     $large_image_url = wp_get_attachment_image_src ( $image_id, 'large' )[0];
     $medium_large_image_url = wp_get_attachment_image_src ( $image_id, 'medium_large' )[0];
     $medium_image_url = wp_get_attachment_image_src ( $image_id, 'medium' )[0];
     $full_image_url = wp_get_attachment_image_src ( $image_id, 'full' )[0];
     $alt = get_post_meta ( $image_id, '_wp_attachment_image_alt', true );
  }
  ?>
  <div class="cmp-hero cmp-hero--noquote">
    <picture>
      <source srcset="<?php echo($xxl_image_url) ?>.webp" media="only screen and (min-width: 2560px)" />
      <source srcset="<?php echo($xxl_image_url) ?>.webp" media="(min-width: 1800px)" />
      <source srcset="<?php echo($xl_image_url) ?>.webp" media="(min-width: 1200px)" />
      <source srcset="<?php echo($large_image_url) ?>.webp" media="(min-width: 768px)" />
      <source srcset="<?php echo($medium_large_image_url) ?>.webp" media="(min-width: 480px)" />
      <source srcset="<?php echo($medium_image_url) ?>.webp" media="(min-width: 0px)" />
      <img class="cmp-hero__image" src="<?php echo($full_image_url) ?>" alt="<?php echo($alt) ?>"/>
    </picture>
  </div>