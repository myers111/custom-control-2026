<?php
/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

// Register the Meta Box

function cc_add_meta_box() {

    add_meta_box(
        'theme_properties meta box',
        'Theme Properties',
        'cc_render_meta_box',
        'page', // Change to 'page' or custom post type if needed
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'cc_add_meta_box');

// Render the Meta Box UI

function cc_render_meta_box($post) {

    wp_nonce_field('phrase_nonce_action', 'phrase_nonce');
    $phrase = get_post_meta($post->ID, '_phrase', true); ?>

    <table>
        <tr>
            <td>Phrase</td>
            <td><input name="phrase" id="phrase" value="<?php echo esc_attr($phrase); ?>" /></td>
        </tr>
    </table><?php
}

// Save the Data when the Page is Saved

function save_phrase_meta_box($post_id) {

    if (!isset($_POST['phrase_nonce']) || !wp_verify_nonce($_POST['phrase_nonce'], 'phrase_nonce_action')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['phrase'])) {

        update_post_meta($post_id, '_phrase', sanitize_text_field($_POST['phrase']));
    }
}
add_action('save_post', 'save_phrase_meta_box');
