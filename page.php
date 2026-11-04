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

        if ( has_post_thumbnail() ) { ?>

            <div id="banner" class="banner" style="background-image: url('<?php echo get_the_post_thumbnail_url() ?>')">
                aaaaaaaaaaaaaaaa
            </div><!-- #banner --><?php
        } ?>

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

        $sections_query = new WP_Query( $args );

        if ( $sections_query->have_posts() ) : 

            $count = 0;

            echo '<div class="responsive-sections-container">'; // Layout wrapper

            while ( $sections_query->have_posts() ) : $sections_query->the_post();

                $count++;

                $layout_class = ($count % 2 == 0) ? 'is-reversed' : ''; ?>

                <article class="responsive-section-row <?php echo $layout_class; ?>"><?php 

                    if ( has_post_thumbnail() ) : ?>
                        <div class="section-column section-image"><?php 
                            the_post_thumbnail('large'); ?>
                        </div><?php 
                    endif; ?>

                    <div class="section-column section-text">
                        <h2><?php the_title(); ?></h2><?php 
                        the_content(); ?>
                    </div>

                </article><?php 

            endwhile;

            echo '</div>'; // End layout wrapper

            wp_reset_postdata();

        endif; ?>

        </div><!-- #content --> <?php

    endwhile;

endif;

get_footer();
