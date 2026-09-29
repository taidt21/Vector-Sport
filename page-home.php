<?php
/**
 * Template Name: Home Vovinam
 */

get_header();
?>
<main class="page-home" id="main-content">
    <?php get_template_part('template-parts/pages/home/section', 'hero'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'training'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'why-choose'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'categories'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'featured-products'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'about'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'activities'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'reviews'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'faq'); ?>
    <?php get_template_part('template-parts/pages/home/section', 'cta'); ?>
</main>
<?php get_footer(); ?>
