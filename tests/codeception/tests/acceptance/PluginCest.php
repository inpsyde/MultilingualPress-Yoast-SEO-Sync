<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress Extensions Boilerplate package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
