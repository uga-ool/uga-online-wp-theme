<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
  return;
}
?>

<div id="comments">

  <?php
  // You can start editing here -- including this comment!
  if ( have_comments() ) :
    ?>
    <h2 class="cmp-heading-3">
      <?php
      $uga_theme_comment_count = get_comments_number();
      if ( '1' === $uga_theme_comment_count ) {
        printf(
          /* translators: 1: title. */
          esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'uga-theme' ),
          '<span>' . get_the_title() . '</span>'
        );
      } else {
        printf( // WPCS: XSS OK.
          /* translators: 1: comment count number, 2: title. */
          esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $uga_theme_comment_count, 'comments title', 'uga-theme' ) ),
          number_format_i18n( $uga_theme_comment_count ),
          '<span>' . get_the_title() . '</span>'
        );
      }
      ?>
    </h2><!-- .comments-title -->

    <?php the_comments_navigation(); ?>

    <ol class="util-delist">
      <?php
      wp_list_comments( array(
        'style'      => 'ol',
        'short_ping' => true,
      ) );
      ?>
    </ol><!-- .comment-list -->

    <?php
    the_comments_navigation();

    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() ) :
      ?>
      <p><?php esc_html_e( 'Comments are closed.', 'uga-theme' ); ?></p>
      <?php
    endif;

  endif; // Check for have_comments().

  comment_form();
  ?>

</div><!-- #comments -->
