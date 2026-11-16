<?php
/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

function cc_render_page_section( $post_id, $depth = 0 ) {

    $depth_class = ( $depth > 0 ) ? 'is-child-section depth-' . $depth : 'is-parent-section'; ?>

    <article class="section-row <?php echo esc_attr( $depth_class ); ?>">
        <div class="section-main-content">
            <div class="section-image">
                <?php if ( has_post_thumbnail( $post_id ) ) { echo get_the_post_thumbnail( $post_id, array(200,200) ); } ?>
            </div>
            <div class="section-text">
                <?php if ( $depth === 0 ) : ?>
                    <h2><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
                <?php else : ?>
                    <h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
                <?php endif; ?>
                <?php echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ); ?>
            </div>
        </div><!-- section-main-content --><?php 

        // Query for any child sections assigned to this specific section

        $child_args = array(
            'post_type'      => 'page_sections',
            'posts_per_page' => -1,
            'post_parent'    => $post_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        );

        $child_sections = get_posts( $child_args );

        if ( ! empty( $child_sections ) ) { ?>
            <div class="nested-child-sections"><?php 
                foreach ( $child_sections as $child ) { // Recursively render each child (and any sub-children)

                    cc_render_page_section( $child->ID, $depth + 1 );
                } ?>
            </div><?php
        } ?>

    </article><?php
}
