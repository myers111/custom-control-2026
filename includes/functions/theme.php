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
