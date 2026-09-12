<?php
/**
 * Master Mart Main Index Fallback
 *
 * @package MasterMart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// By default, render landing page
if ( is_front_page() || is_home() ) {
    get_template_part( 'front-page' );
} else {
    get_header();
    ?>
    <main class="mm-container" style="padding: 60px 16px; min-height: 60vh;">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h1 style="font-size: 28px; font-weight: 800; color: var(--mm-navy);"><?php the_title(); ?></h1>
                    <div style="font-size: 16px; line-height: 1.7; color: var(--mm-text-body);">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            endwhile;
        else :
            echo '<p>' . esc_html__( 'কোনো তথ্য পাওয়া যায়নি।', 'mastermart' ) . '</p>';
        endif;
        ?>
    </main>
    <?php
    get_footer();
}
