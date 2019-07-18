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

class TermMetaboxTabCest
{
    public function _before(CodeCeptionTester $i)
    {
        $i->loginAsAdmin();
        $i->connectTwoSites();
    }

    /**
     * See Yoast tab in metabox.
     */
    public function seeTabInMetabox(CodeCeptionTester $i)
    {
        $i->amOnPage('/wp-admin/term.php?taxonomy=category&tag_ID=1&post_type=post');
        $i->checkOption('#multilingualpress-site-2-relationship-new');
        $i->click('Update');

        $i->seeElement('#tab-anchor-multilingualpress-site-2-tab-yoast');
    }
}
