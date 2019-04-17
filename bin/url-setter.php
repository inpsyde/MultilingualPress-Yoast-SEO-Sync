<?php
namespace Inpsyde\Multilingualpress\CodeceptionsTests;

if (PHP_SAPI !== 'cli') {
    return;
}

function setupConfig($url)
{
    $dir = __DIR__ . '/tests';
    $files = glob("{$dir}/*.suite.template.yml");

    $replacedCount = 0;
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $replaced = str_replace('http://0.0.0.0', $url, $content, $count);
        $name = strtok(basename(str_replace('\\', '/', $file)), '.');
        file_put_contents("{$dir}/{$name}.suite.yml", $replaced) and $replacedCount ++;
        $replacedCount += $count;
    }

    if ($replacedCount === count($files) * 2) {
        fwrite(STDOUT, "Codeception config files ready.\n");
        exit(0);
    }

    fwrite(STDERR, "Codeception config files ready.\n");
    exit(1);
}

$url = $args[0] ?? '';
$url and setupConfig($url);
