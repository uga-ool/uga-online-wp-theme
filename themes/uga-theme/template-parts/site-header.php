<header class="cmp-header"><?php /* TODO: Make sticky header optional */ ?>
  <div class="cmp-header__title-bar">
    <div class="cmp-title-bar">
      <div class="cmp-site-title">
        <a href="/" class="cmp-site-title__link"><?php /* TODO: Make work with text title */ ?>
          <?php
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
          ?>
          <?php if ( has_custom_logo() ) { ?>
            <img src="<?php echo $logo[0]; ?>" class="cmp-site-title__logo" alt="<?php echo get_bloginfo('name'); ?>" />
          <?php } else { ?>
            <img src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" class="cmp-site-title__logo" alt="<?php echo get_bloginfo('name'); ?>" />
          <?php } ?>
        </a>
      </div>

      <div class="cmp-title-bar__features">
        <button class="cmp-title-bar__menu-toggle js-menu-open">
          <svg viewBox="0 0 16 14" class="cmp-title-bar__menu-icon">
            <path d="M1,1 15,1 M1,7 15,7 M1,13 15,13" /></svg>
          Menu
        </button>
        <div class="cmp-title-bar__actions">

          <nav class="cmp-actions-nav">
                  <a href="http://uga-online-wp-dev.local/request-information/" class="cmp-actions-nav__link">Request Info</a>
                  <a href="#" class="cmp-actions-nav__link">Apply</a>
          </nav>
          <!-- NOTE: I do not understand how this template part works. I removed it and added the nav item above and everything seems to work fine. -->
          <?php /* get_template_part( 'template-parts/site', 'actions-nav' ); */ ?>
        </div>
        <button class="cmp-title-bar__search-toggle js-search-toggle" data-icon-hidden="icon-search" data-icon-expanded="icon-up-arrow">
          <span class="util-visually-hidden">Search</span>
        </button>
      </div>
      <?php get_search_form(); ?>

    </div>
  </div>

  <div class="cmp-nav js-menu">
    <nav>
      <div class="cmp-nav__actions">
        <button class="cmp-nav__close js-menu-close">
          <svg class="cmp-nav__close-icon" viewBox="0 0 16 16">
            <path d="M1,1 15,15 M 1,15, 15,1" /></svg>
          Close
        </button>

        <a href="/" class="cmp-nav__title icon-shield">
          <div class="util-margin-left-md"><?php echo get_bloginfo('name');  ?></div>
        </a>
      </div>
      <?php get_template_part( 'template-parts/site', 'nav' ); ?>
    </nav>
  </div>
</header>
