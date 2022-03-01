<!doctype html>
<html class="no-js safe-focus" <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <?php get_template_part( 'template-parts/meta', 'icons' ); ?>
  </head>

  <body>
    <?php wp_body_open(); ?>

    <?php get_template_part( 'template-parts/site', 'icons' ); ?>
    <a class="cmp-skip-to-content" href="#main-content"><?php esc_html_e( 'Skip to main content', 'uga-theme' ); ?></a>
    <div class="util-position-relative js-site">

        <?php get_template_part( 'template-parts/university', 'header' ); ?>
        <?php get_template_part( 'template-parts/site', 'header' ); ?>

        <main id="main-content">
