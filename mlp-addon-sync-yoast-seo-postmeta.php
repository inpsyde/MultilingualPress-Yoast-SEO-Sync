<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: Home24 Sync Postmeta
 * Description: Sync Postmeta to new translated posts
 * Version: 2015.07.24
 * Author: Inpsyde GmbH
 * Author URI: http://inpsyde.com/
 * Licence: MIT
 * Textdomain: home24_sync-postmeta
 */
namespace Home24\SyncPostmeta;

/**
 * @param string $dir (Basic directory to requires files from)
 *
 * @return callable
 */
$file_loader_builder = function ( $dir ) {

	/**
	 * @param string $file (The file name to require once)
	 *
	 * @return void
	 */
	return function ( $file ) use ( $dir ) {

		require_once $dir . $file;
	};
};

// Load static php files from the inc/ directory
$file_loader = $file_loader_builder( __DIR__ . '/inc/' );
$file_loader( 'init.php' );
$file_loader( 'register-autoloading.php' );

add_action( 'shared_autoloader_init', __NAMESPACE__ . '\init' );