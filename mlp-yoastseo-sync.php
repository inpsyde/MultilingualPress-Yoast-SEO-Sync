<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: MultilingualPress Yoast SEO Sync
 * Description: This is a simple add-on for the MultilingualPress plugin to sync Postmeta from Yoast SEO plugin to new translated posts.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com/
 * Version:     2015-11-29
 * Licence:     GPLv2+
 * Network:     true
 * Textdomain:  mlpyoastseosync
 */
namespace Multilingualpress\YoastSeoSync;

use Multilingualpress\YoastSeoSync\SyncPostmeta;

defined( 'ABSPATH' ) or die();

require_once __DIR__ . '/inc/SyncPostmeta.php';

add_action( 'admin_init', __NAMESPACE__  . '\init' );
/**
 * Initialize the plugin main object.
 *
 * @wp_hook admin_init
 */
function init() {

	if ( ! is_admin() || ! class_exists( 'Multilingual_Press' ) ) {
		return;
	}

	$plugin = new SyncPostmeta;
	$plugin->run();
}
