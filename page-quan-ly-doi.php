<?php
/**
 * Template Name: Page Quan Ly Doi
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="tournament_register_page team_portal_page" id="main-content">
    <section class="tournament_register_page__main">
        <div class="container">
            <?php vs_render_team_portal_page(); ?>
        </div>
    </section>
</main>

<?php
get_footer();
