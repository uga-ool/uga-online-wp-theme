<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package uga-theme
 */

?>

<article id="post-<?php the_ID(); ?>" class="util-margin-vert-xl util-margin-vert-xxl@lg">
  <header class="entry-header">
    <h2 class="cmp-heading-1 util-margin-all-none"><?php the_title(); ?></h2>
  </header>
  <div id="app">
    <form class="util-background-light-gray util-pad-all-sm util-pad-all-md@sm util-pad-all-lg@md util-display-none@print">

      <fieldset>
        <legend class="cmp-heading-4 util-margin-bottom-sm util-text-center util-full-width">Degree & Certificate Demo</legend>

        <div class="cmp-form-field">

        <div class="obj-grid obj-grid--gap-md@sm util-margin-vert-lg">
          <div class="obj-grid__full obj-grid__half@sm obj-grid__quarter@md">
            <div class="cmp-form-select">
              <div v-cloak>
                <label for="GRE-select" class="cmp-form-label">
                  GRE Requirement
                </label>
                <div class="">
                  <select id="subject-select" class="cmp-form-select__dropdown" name="Search and Filter" v-model="gre">
                    <option value="false">GRE Required</option>
                    <option value="true">GRE Not Required</option>
                  </select>

                  {{ filters.gre }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
    <div v-cloak>
      <div class="obj-grid obj-grid--gap-md util-margin-top-lg">
        <div v-for="p, index in filteredPrograms" class="obj-grid__full obj-grid__half@sm obj-grid__third@md obj-grid__quarter@lg">
          <a class="util-delink util-underline-hover util-color-red" :href="p.link">
            <img v-if="imgLoaded" v-bind:aria-labelledby="p.slug" class="util-block util-full-width" v-bind:src="imgUrls[index]" alt="photo of random lady"/>
            <h3 v-bind:id="p.slug" class="cmp-heading-5 util-margin-top-sm util-margin-bottom-none" v-html="p.title"></h3>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- This can likely be deleted -->
  <div>
    <?php if ( has_post_thumbnail() ) { ?>
      <img class="cmp-post__image" src="<?php the_post_thumbnail_url() ?>" />
    <?php } ?>
    <?php the_content(); ?>
  </div>
  <!-- delete stop here -->
</article><!-- #post-<?php the_ID(); ?> -->
