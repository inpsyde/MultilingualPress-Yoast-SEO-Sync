<?php # -*- coding: utf-8 -*-

namespace MultilingualPressAddonYoastSEO\SyncPostmeta;

require_once( __DIR__ . 'SyncPostmeta.php' );

/**
 * Initialize the plugin main object.
 */
function init() {

	if ( ! is_admin() || ! class_exists( 'Multilingual_Press' ) ) {
		return;
	}

	$plugin = new SyncPostmeta;
	add_action( 'admin_init', array( $plugin, 'run' ) );
}
