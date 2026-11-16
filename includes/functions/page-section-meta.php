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

// Intercept post saving to enforce either a Parent OR an Assigned Page

function cc_enforce_section_page_or_parent( $data, $postarr ) {

    // Only run on our custom post type

    if ( $data['post_type'] !== 'page_sections' ) {

        return $data;
    }

    // Ignore auto-drafts, trash, or revisions

    if ( in_array( $data['post_status'], array( 'auto-draft', 'trash', 'inherit' ), true ) ) {

        return $data;
    }

    // Check if a Parent Section is selected (post_parent > 0)

    $has_parent = ! empty( $postarr['post_parent'] ) && intval( $postarr['post_parent'] ) > 0;

    // Check if our custom meta box dropdown has a Page selected

    $has_assigned_page = isset( $_POST['assigned_page_id'] ) && ! empty( $_POST['assigned_page_id'] ) && intval( $_POST['assigned_page_id'] ) > 0;

    // If trying to publish (or keep published) and BOTH are missing

    if ( $data['post_status'] === 'publish' && ! $has_parent && ! $has_assigned_page ) {

        // Revert status to draft so it doesn't go live unattached

        $data['post_status'] = 'draft';
   }

    return $data;
}
add_filter( 'wp_insert_post_data', 'cc_enforce_section_page_or_parent', 10, 2 );

// Display an admin notice if validation failed
/*
function cc_show_section_validation_admin_notice() {

    $user_id = get_current_user_id();

    if ( get_transient( 'section_validation_error_' . $user_id ) ) {

        delete_transient( 'section_validation_error_' . $user_id );
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Publication Blocked:</strong> A Page Section must either be assigned to a Page or have a Parent Section selected. Your section has been saved as a <strong>Draft</strong>.</p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'cc_show_section_validation_admin_notice' );
*/
add_action( 'enqueue_block_editor_assets', function() {

    global $post;

    // Only load this script on your specific custom post type

    if ( ! $post || $post->post_type !== 'page_sections' )  return;

    $script = "
        ( function( wp ) {
            const { select, dispatch, subscribe } = wp.data;
            let wasSaving = false;

            subscribe( () => {
                const isSaving = select( 'core/editor' ).isSavingPost();
                const isAutosaving = select( 'core/editor' ).isAutosavingPost();

                // Detect when a manual save just finished
                if ( wasSaving && ! isSaving && ! isAutosaving ) {
                    const didSucceed = select( 'core/editor' ).didPostSaveRequestSucceed();

                    if ( didSucceed ) {
                        dispatch( 'core/notices' ).createNotice(
                            'success', // Options: 'success', 'info', 'warning', 'error'
                            'Your custom post has been saved!',
                            {
                                id: 'my-cpt-save-notice', // Prevents duplicate notices
                                isDismissible: true,
                                // type: 'snackbar', // Uncomment to show as a bottom-left toast instead of a top banner
                            }
                        );
                    }
                }

                wasSaving = isSaving;
            } );
        } )( window.wp );
    ";

    // Register a dummy handle to attach our inline script with required Gutenberg dependencies

    wp_register_script( 'my-cpt-editor-notices', '', [ 'wp-data', 'wp-editor', 'wp-notices' ], false, true );
    wp_enqueue_script( 'my-cpt-editor-notices' );
    wp_add_inline_script( 'my-cpt-editor-notices', $script );
});

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

    delete_post_meta( $post_id, '_display_page_id' );

    if ( $page_id ) {

        update_post_meta( $post_id, '_page_id', $page_id );
    }
    else {

        delete_post_meta( $post_id, '_page_id' );
    }
}
add_action( 'save_post_page_sections', 'cc_save_section_page_assignment_meta' );

function cc_enqueue_section_admin_scripts( $hook ) {

    global $post_type;

    // Only load on post edit/add screens for our specific CPT

    if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && $post_type === 'page_sections' ) {
        wp_enqueue_script(
            'section-admin-toggle',
            get_template_directory_uri() . '/js/section-admin.js',
            array( 'jquery' ),
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'cc_enqueue_section_admin_scripts' );
