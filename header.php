<?php
/**
 * The header for Astra Theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package custom-control-2026
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title('|', true, 'right'); ?></title>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

	<header>
		<div class="left-box">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/logo.png" alt="Home" />
			</a>
		</div>
		<div class="left-box">
			<nav class="theme-navigation-wrapper"> <?php
				if ( has_nav_menu( 'primary-menu' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary-menu',
						'container'      => 'nav',
						'container_class'=> 'plugin-menu-container',
						'menu_class'     => 'nav-menu-list'
					) );
				} ?>
			</nav>
		</div>
		<div class="left-box">
			<span class="phone"><a href="tel:+19137220343">913-722-0343</a></span>
			<span class="email"><a href="mailto:sales@customcontrolmfr.com">sales@customcontrolmfr.com</a></span>
		</div>
	</header>
