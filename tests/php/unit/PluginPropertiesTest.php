<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\YoastSeoSync\Tests\Unit;

use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\YoastSeoSync\PluginProperties;

class PluginPropertiesTest extends TestCase
{
    public function testPluginPropertiesGetCorrectInformations()
    {
        Functions\expect('plugin_basename')
            ->andReturn(
                basename(getenv('PLUGIN_PATH') . '/multilingualpress-extensions-boilerplate')
            );

        Functions\expect('plugin_dir_path')
            ->andReturn(getenv('PLUGIN_PATH'));

        Functions\expect('plugins_url')
            ->andReturn('PLUGINS_URL');

        Functions\expect('get_file_data')
            ->andReturn([
                'PluginName' => '',
                'PluginURI' => '',
                'TextDomain' => '',
                'DomainPath' => '',
            ]);

        $testee = new PluginProperties(getenv('PLUGIN_PATH'));

        self::assertInstanceOf(PluginProperties::class, $testee);
    }
}
