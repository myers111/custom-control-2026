<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

if ( have_posts() ) :
    
	while ( have_posts() ) : the_post();

        wp_nonce_field('phrase_nonce_action', 'phrase_nonce');
        $phrase = get_post_meta(get_the_ID(), '_phrase', true);

        // Fallback to the page title if no phrase is set

        if (strlen($phrase) == 0) { $phrase = get_the_title(); } ?>

        <div id="banner" class="banner" style="background-<?php echo has_post_thumbnail() ? "image: url('" . get_the_post_thumbnail_url() : "color: #000000"; ?>">
            <span><?php echo $phrase; ?></span>
        </div><!-- #banner -->

        <div id="content" class="content"><?php

        the_content();

        // Target the ID of the page the user is currently visiting

        $current_page_id = get_the_ID();

        // Fetch only the sections explicitly assigned to this page

        $args = array(
            'post_type'      => 'page_sections',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order', 
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_assigned_page_id', // The custom meta key we registered
                    'value'   => $current_page_id,    // Must match the current page ID
                    'compare' => '=',
                ),
            ),
        );

        $sections_query = new WP_Query( $args ); ?>

        </div><!-- #content --> <?php

    endwhile;

endif;

get_footer();
