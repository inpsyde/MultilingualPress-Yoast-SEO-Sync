<?php # -*- coding: utf-8 -*-

namespace acceptance;

use CodeCeptionTester;

class PluginCest
{
    public function _before(CodeCeptionTester $i)
    {
        $i->loginAsAdmin();
    }

    public function pluginIsActivatedCorrectly(CodeCeptionTester $i)
    {
        $i->deactivatePluginNetwork('multilingualpress-3-yoast-seo-sync');
        $i->activatePluginNetwork('multilingualpress-3-yoast-seo-sync');
    }
}
