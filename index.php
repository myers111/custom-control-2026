<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @packagecustom-control-2026
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div id="banner" class="site-banner">

<?php

if ( has_post_thumbnail() ) {

    the_post_thumbnail( 'medium', array( 'class' => 'primary-theme-img' ) );
}

$secondary_img_id = get_post_meta(get_the_ID(), '_secondary_image_id', true);

if ($secondary_img_id) {

    echo wp_get_attachment_image($secondary_img_id, 'medium', false, array('class' => 'secondary-featured-image'));
}

?>

</div><!-- #banner -->

<div id="content" class="site-content">

</div><!-- #content -->

<?php

get_footer();
