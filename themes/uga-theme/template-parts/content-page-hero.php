<?php 
  $pageName = $post->post_title;
  $hero = get_field('hero');
  $hero_image = $hero['hero_image'];
  
  if ($hero_image):
    
    $filename = $hero_image['title'];
    $file_arr = explode(".", $hero_image['filename']);
    $file_ext = end($file_arr);
  else:
    $filename = 'uga-hero';
    $file_ext = 'jpeg';
  endif;
  ?>

<div class="cmp-hero cmp-hero--interior">

<picture>
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-2048x1365.<?php echo($file_ext) ?>.webp" media="only screen and (min-width: 2560px)" />
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-2048x1365.<?php echo($file_ext) ?>.webp" media="(min-width: 1800px)" />
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-1536x1024.<?php echo($file_ext) ?>.webp" media="(min-width: 1200px)" />
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-1024x683.<?php echo($file_ext) ?>.webp" media="(min-width: 768px)" />
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-768x512.<?php echo($file_ext) ?>.webp" media="(min-width: 480px)" />
    <source srcset="/wp-content/uploads/<?php echo($filename) ?>-300x200.<?php echo($file_ext) ?>.webp" media="(min-width: 0px)" />
    <img class="cmp-hero__image" src="/wp-content/uploads/<?php echo($filename) ?>-scaled.<?php echo($file_ext) ?>" alt="Books lining the shelves of a library."/>
    </picture>

    <div class="cmp-hero__container cmp-hero__container--pull-quote">
      <div class="cmp-hero__content cmp-hero__content--pull-quote">
        <h2 class="cmp-heading-2" aria-label=
        "Content Headline"><?php echo($pageName) ?></h2>

        <div class="util-pad-left-md util-pad-left-none@md">

          <p class="cmp-hero__pull-quote-body"><?php echo($hero['hero_text']); ?></p>

          <p class="cmp-hero__pull-quote-author"><?php echo($hero['hero_attribution']); ?></p>

        </div>

      </div>
    </div>

</div>