<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress Extensions Boilerplate package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Uninstall routines.
 *
 * This file is called automatically when the plugin is deleted per user interface.
 *
 * @see https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 */

namespace Inpsyde\MultilingualPressYoastSeoSync;

use function Inpsyde\MultilingualPress\bootstrap;
use function Inpsyde\MultilingualPress\resolve;
use Inpsyde\MultilingualPressYoastSeoSync\Installation\Uninstaller;

defined('ABSPATH') || die();

if (!defined('WP_UNINSTALL_PLUGIN')) {
    return;
}

if (!current_user_can('activate_plugins')) {
    return;
}

if (!is_multisite()) {
    return;
}

$mainPluginFile = __DIR__ . '/multilingualpress-yoast-seo-sync.php';

if (plugin_basename($mainPluginFile) !== WP_UNINSTALL_PLUGIN
    || !is_readable($mainPluginFile)
) {
    unset($mainPluginFile);

    return;
}

/** @noinspection PhpIncludeInspection */
require_once $mainPluginFile;

unset($mainPluginFile);

if (!bootstrap()) {
    return;
}

if (function_exists('resolve')) {
    return;
}

$uninstaller = resolve(Uninstaller::class);
