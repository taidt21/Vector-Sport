<?php
defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="vs-account-template ">
    <div class="container">
        <?php echo do_shortcode('[vectorsport_account]'); ?>
    </div>
</main>

<?php get_footer(); ?>