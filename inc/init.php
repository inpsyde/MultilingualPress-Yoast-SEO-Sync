<?php # -*- coding: utf-8 -*-

namespace Home24\SyncPostmeta;

use Requisite;

/**
 * setup the autoloading and initialize the plugin main object
 *
 * @param Requisite\SPLAutoLoader $requisite
 *
 * @wp-hook shared_autoloader_init
 */
function init( Requisite\SPLAutoLoader $requisite ) {

	if ( ! is_admin() || ! class_exists( 'Multilingual_Press' ) ) {
		return;
	}

	/** Todo: rethink this static binding on the directory/file  */
	$dir = dirname( __DIR__ );

	register_autoloading( $dir, $requisite );

	$plugin = new SyncPostmeta;
	add_action( 'admin_init', array( $plugin, 'run' ) );

}