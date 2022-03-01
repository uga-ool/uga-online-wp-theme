<?php
/**
 * Template part for displaying program requests for information
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

?>
<?php
    $pageName = $post->post_title;
?>
<h2 class="cmp-heading-4">Request Information: <?php echo($pageName); ?></h2>
<?php echo do_shortcode("[hubspot type=form portal=5244817 id=167221ba-2880-4fa7-a21b-97b35178a880]"); ?>