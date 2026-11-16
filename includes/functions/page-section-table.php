<?php
/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

// Register the custom column header

function cc_register_page_sections_column( $columns ) {

    // This adds the column to the end of the list table array

    $columns['page'] = __( 'Page', 'text-domain' );

    return $columns;
}
add_filter( 'manage_page_sections_posts_columns', 'cc_register_page_sections_column' );

// Populate the custom column with data for each row

function cc_populate_page_sections_column( $column_name, $post_id ) {

    if ( $column_name === 'page' ) {

        // Retrieve the data you want to display (e.g., custom field data)
                
        $page_id = get_post_meta( $post_id, '_page_id', true );
/*
        $page_id = get_post_meta( $post_id, '_assigned_page_id', true );

        while ( !$page_id ) {

            $post_id = wp_get_post_parent_id( $post_id );

            $page_id = get_post_meta( $post_id, '_assigned_page_id', true );
        }
*/
        // Echo the data or show a default fallback

        if ( $page_id ) {

            $page_title = get_the_title( $page_id );

            echo '<a href="' . esc_url( get_edit_post_link( $page_id ) ) . '">' . esc_html( $page_title ) . '</a>';

        } else {

            echo '<span class="description">—</span>';
        }
    }
}
add_action( 'manage_page_sections_posts_custom_column', 'cc_populate_page_sections_column', 10, 2 );

// Make the column sortable UI-wise

function cc_make_page_sections_column_sortable( $sortable_columns ) {

    // Left side: Column ID | Right side: Orderby reference key

    $sortable_columns['page'] = 'page';

    return $sortable_columns;
}
add_filter( 'manage_edit-page_sections_sortable_columns', 'cc_make_page_sections_column_sortable' );

// Alphabetically sort by the parent's title via an SQL Join on the meta value

function cc_sort_sections_by_parent_title( $query ) {

    global $pagenow, $wpdb;

    if ( is_admin() && 'edit.php' === $pagenow && $query->is_main_query() && 'page_sections' === $query->get('post_type') ) {
        
        if ( 'page' === $query->get('orderby') ) {

            $order = ( 'desc' === strtolower( $query->get('order') ) ) ? 'DESC' : 'ASC';

            // Join the meta table to get the assigned page ID, then join the posts table to get that page's title

            add_filter( 'posts_join', function( $join ) use ( $wpdb ) {
                $join .= " LEFT JOIN {$wpdb->postmeta} AS post_meta ON {$wpdb->posts}.ID = post_meta.post_id AND post_meta.meta_key = '_page_id' ";
                $join .= " LEFT JOIN {$wpdb->posts} AS parent_posts ON post_meta.meta_value = parent_posts.ID ";
                return $join;
            });

            // Order the query alphabetically by the parent's post_title

            add_filter( 'posts_orderby', function( $orderby ) use ( $order ) {
                return " parent_posts.post_title {$order} ";
            });
        }
    }
}
add_action( 'pre_get_posts', 'cc_sort_sections_by_parent_title' );

// Add a dropdown filter at the top of the page_sections list table

function cc_add_assigned_page_dropdown_filter() {

    global $pagenow, $wpdb;

    // Only output the dropdown on the 'page_sections' list edit view

    if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'page_sections' === $_GET['post_type'] ) {
        
        // Fetch all unique assigned page IDs currently in use to keep the dropdown clean

        $assigned_ids = $wpdb->get_col( "
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_assigned_page_id' AND meta_value != ''
        " );

        if ( ! empty( $assigned_ids ) ) {

            $current_filter = isset( $_GET['filter_assigned_page'] ) ? sanitize_text_field( $_GET['filter_assigned_page'] ) : '';
            
            echo '<select name="filter_assigned_page">';
            echo '<option value="">' . __( 'All Parent Pages', 'text-domain' ) . '</option>';
            
            foreach ( $assigned_ids as $id ) {

                if ( ! get_post( $id ) ) continue; // Skip if the parent page was deleted
                
                $title = get_the_title( $id );
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $id ),
                    selected( $current_filter, $id, false ),
                    esc_html( $title )
                );
            }

            echo '</select>';
        }
    }
}
add_action( 'restrict_manage_posts', 'cc_add_assigned_page_dropdown_filter' );

// Filter the WP_Query results when a dropdown option is selected

function cc_filter_sections_by_assigned_page_dropdown( $query ) {

    global $pagenow;

    if ( is_admin() && 'edit.php' === $pagenow && $query->is_main_query() && 'page_sections' === $query->get('post_type') ) {

        if ( ! empty( $_GET['filter_assigned_page'] ) ) {

            $parent_id = sanitize_text_field( $_GET['filter_assigned_page'] );
            
            // Apply standard meta_query filtering

            $meta_query = array(
                array(
                    'key'     => '_assigned_page_id',
                    'value'   => $parent_id,
                    'compare' => '='
                )
            );
            $query->set( 'meta_query', $meta_query );
        }
    }
}
add_action( 'pre_get_posts', 'cc_filter_sections_by_assigned_page_dropdown' );
