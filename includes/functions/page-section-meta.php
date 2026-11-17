<?php
/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

// Register the Meta Box

function cc_add_section_page_assignment_meta_box() {

    add_meta_box(
        'section_page_assignment',          // Unique ID
        'Assign to Page',                   // Box title
        'cc_render_section_page_assignment_box', // Callback function to render HTML
        'page_sections',                    // Post type to display it on
        'side',                             // Context ('normal', 'side', 'advanced')
        'default'                           // Priority
    );
}
add_action( 'add_meta_boxes', 'cc_add_section_page_assignment_meta_box' );

// Render the Dropdown of Pages

function cc_render_section_page_assignment_box( $post ) {

    // Add a nonce field for security verification

    wp_nonce_field( 'save_section_page_assignment', 'section_page_assignment_nonce' );

    // Retrieve the currently saved page ID (if any)

    $current_page_id = get_post_meta( $post->ID, '_assigned_page_id', true );

    // Output a dropdown populated with all published WordPress pages

    wp_dropdown_pages( array(
        'name'             => 'assigned_page_id',
        'selected'         => $current_page_id,
        'show_option_none' => '-- Select a Page --',
        'sort_column'      => 'post_title',
    ) );
}

// Save the Selected Page ID when the Custom Post is saved

function cc_save_section_page_assignment_meta( $post_id ) {

    // Security check 1: Verify nonce

    if ( ! isset( $_POST['section_page_assignment_nonce'] ) || ! wp_verify_nonce( $_POST['section_page_assignment_nonce'], 'save_section_page_assignment' ) ) {
        return;
    }

    // Security check 2: Check autosave

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Security check 3: Check user permissions

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if this post currently has a parent assigned

    $parent_id = wp_get_post_parent_id( $post_id );

    // Delete page assignment if a parent exists OR if the dropdown was left empty

    $page_id = 0;

    if ( $parent_id > 0 || empty( $_POST['assigned_page_id'] ) ) {

        delete_post_meta( $post_id, '_assigned_page_id' );
    }
    else {
        
        $page_id = sanitize_text_field( $_POST['assigned_page_id'] );

        update_post_meta( $post_id, '_assigned_page_id', $page_id );
    }

    // Update the '_page_id' meta key to reflect the assigned page, even if it's inherited from a parent section

    $parent_id = wp_get_post_parent_id( $post_id );

    if ( !$page_id && !$parent_id ) return;

    $the_post_id = $post_id;

    while ( !$page_id ) {

        $the_post_id = wp_get_post_parent_id( $the_post_id );

        $page_id = get_post_meta( $the_post_id, '_assigned_page_id', true );
    }

    if ( $page_id ) {

        update_post_meta( $post_id, '_page_id', $page_id );
    }
    else {

        delete_post_meta( $post_id, '_page_id' );
    }
}
add_action( 'save_post_page_sections', 'cc_save_section_page_assignment_meta' );

// Enqueue the JavaScript for the admin screen to disable the publish buttons

function cc_enqueue_section_admin_scripts( $hook ) {

    global $post_type;

    // Only load on post edit/add screens for our specific CPT

    if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && $post_type === 'page_sections' ) {
        wp_enqueue_script(
            'section-admin-toggle',
            get_template_directory_uri() . '/includes/js/section-admin.js',
            array( 'jquery' ),
            '1.1',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'cc_enqueue_section_admin_scripts' );
