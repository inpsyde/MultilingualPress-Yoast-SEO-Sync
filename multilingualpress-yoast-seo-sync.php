<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: MultilingualPress Yoast SEO Sync
 * Plugin URI:  https://github.com/inpsyde/multilingualpress-yoast-seo-sync
 * Description: This is a simple add-on for the MultilingualPress plugin to synchronize the post metadata of the Yoast SEO plugin between translated posts.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com/
 * Version:     1.0.0
 * Licence:     GPLv3
 * Network:     true
 */

namespace MultilingualPressYoastSEOSync;

if ( ! function_exists( 'add_action' ) ) {
	return;
}

add_action( 'mlp_and_wp_loaded', __NAMESPACE__ . '\initialize' );

/**
 * Initializes the plugin.
 *
 * @wp-hook mlp_and_wp_loaded
 *
 * @return void
 */
function initialize() {

	require_once __DIR__ . '/inc/Synchronizer.php';

	add_action( 'wp_insert_post', array( new Synchronizer(), 'synchronize' ), 10, 2 );
}
