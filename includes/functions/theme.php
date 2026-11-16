<?php
/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

function cc_theme_enqueue_styles() {

    wp_enqueue_style( 'main-styles', get_stylesheet_uri() );

    wp_enqueue_style( 'global', CC_THEME_URI . 'includes/style/global.css' );
    wp_enqueue_style( 'layout', CC_THEME_URI . 'includes/style/layout.css' );
    /*wp_enqueue_style( 'layout', CC_THEME_URI . 'includes/style/layout-mobile.css' );*/
}
add_action( 'wp_enqueue_scripts', 'cc_theme_enqueue_styles' );

function cc_theme_setup() {

    register_nav_menus( array(
        'primary-menu' => __( 'Primary Menu', 'text-domain' ),
    ) );
}
add_action( 'init', 'cc_theme_setup' );

function cc_theme_setup_features() {

    // CRITICAL: This line forces WordPress to display the Featured Image button
    add_theme_support( 'post-thumbnails' ); 

    // Your existing editor styles setup from earlier
    add_theme_support( 'editor-styles' ); 
    add_editor_style( 'style-editor.css' ); 
}
add_action( 'after_setup_theme', 'cc_theme_setup_features' );

function cc_create_section_post_type() {
    
    $labels = array(
        'name'               => _x( 'Page Sections', 'post type general name' ),
        'singular_name'      => _x( 'Page Section', 'post type singular name' ),
        'menu_name'          => _x( 'Page Sections', 'admin menu' ),
        'name_admin_bar'     => _x( 'Page Section', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'section' ),
        'add_new_item'       => __( 'Add New Section' ),
        'new_item'           => __( 'New Section' ),
        'edit_item'          => __( 'Edit Section' ),
        'view_item'          => __( 'View Section' ),
        'all_items'          => __( 'All Sections' ),
        'search_items'       => __( 'Search Sections' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'sections' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => true, // Allows parent-child relationships
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-layout', // Layout icon in admin dashboard
        'show_in_rest'       => true, // Enables Gutenberg editor if desired
        'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ) // page-attributes allows ordering
    );

    register_post_type( 'page_sections', $args );
}
add_action( 'init', 'cc_create_section_post_type' );
