<?php // phpcs:disable
/*
 * Plugin Name: MultilingualPress Yoast SEO Sync
 * Plugin URI:
 * Description: Translate Yoast SEO post metadata between translated posts.
 * Author: Inpsyde GmbH
 * Author URI: https://inpsyde.com
 * Version: 1.0.0
 * Text Domain: multilingualpress-yoast-seo-sync
 * Domain Path: /languages/
 * License: GPLv2+
 * Network: true
 * Requires at least: 4.8
 * Requires PHP: 7.0
 */

namespace Inpsyde\MultilingualPress\YoastSeoSync;

use Inpsyde\MultilingualPress\Framework\Service\ServiceProvidersCollection;

if (version_compare(PHP_VERSION, '7', '<')) {
    $hooks = [
        'admin_notices',
        'network_admin_notices',
    ];
    foreach ($hooks as $hook) {
        add_action($hook, function () {
            $message = __(
                'MultilingualPress Yoast SEO Sync requires at least PHP version 7. <br />Please ask your server administrator to update your environment to PHP version 7.',
                'multilingualpress-yoast-seo-sync'
            );

            printf(
                '<div class="notice notice-error"><span class="notice-title">%1$s</span><p>%2$s</p></div>',
                esc_html__(
                    'The plugin MultilingualPress Yoast SEO Sync has been deactivated',
                    'multilingualpress-yoast-seo-sync'
                ),
                wp_kses($message, ['br' => true])
            );

            deactivate_plugins(plugin_basename(__FILE__));
        });
    }
    return;
}

function autoload()
{
    static $done;
    if (is_bool($done)) {
        return $done;
    }
    if (is_readable(__DIR__ . '/autoload.php')) {
        require_once __DIR__ . '/autoload.php';
        $done = true;

        return true;
    }
    if (is_readable(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $done = true;

        return true;
    }
    $done = false;

    return false;
}

if (!autoload()) {
    return;
}

/**
 * Bootstraps MultilingualPress Yoast SEO Sync.
 *
 * @return bool
 *
 * @wp-hook multilingualpress.add_service_providers
 */
add_action(
    'multilingualpress.add_service_providers',
    function (ServiceProvidersCollection $providers) {
        $providers
            ->add(new Core\ServiceProvider())
            ->add(new TranslationUi\ServiceProvider());
    },
    0
);
