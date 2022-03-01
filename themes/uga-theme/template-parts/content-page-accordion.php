<button class="cmp-button cmp-accordion-toggle-all js-toggle-all" aria-controls="accordion-list" aria-hidden="true">Open All</button>
<dl id="accordion-list" class="cmp-accordion">

<?php
    $item_count = 0;
    while( have_rows('accordion') ) : the_row();
        $acc_title = get_sub_field('accordion_title');
        $acc_text = get_sub_field('accordion_body');
?>      
    <dt>
        <button id=<?php echo($item_count) ?> class="cmp-accordion__button js-toggler" aria-expanded="false"><?php echo($acc_title) ?></button>
    </dt>
    <dd class="cmp-accordion__content" aria-labelledby=<?php echo($item_count) ?> aria-hidden="true">
        <?php echo($acc_text) ?>
    </dd>
        <?php $item_count++; ?>
    <?php endwhile; ?>
</dl>