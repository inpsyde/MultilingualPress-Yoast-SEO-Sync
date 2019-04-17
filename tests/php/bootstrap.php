<?php # -*- coding: utf-8 -*-
$vendor = dirname(dirname(__DIR__)) . '/vendor/';
if (!file_exists($vendor . 'autoload.php')) {
    die("Please install via Composer before running tests.");
}

require_once $vendor . 'brain/monkey/inc/patchwork-loader.php';
require_once $vendor . 'autoload.php';
unset($vendor);

putenv('PLUGIN_PATH=' . dirname(__DIR__, 2));
putenv('TESTS_PATH=' . __DIR__);
require_once __DIR__ . '/stubs.php';
