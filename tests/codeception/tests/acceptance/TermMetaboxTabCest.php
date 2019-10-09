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

    public function copyYoastSeoValuesToRemoteSite(CodeCeptionTester $i)
    {
        $i->amOnPage('/wp-admin/term.php?taxonomy=category&tag_ID=1&post_type=post');
        $i->checkOption('#multilingualpress-site-2-relationship-new');
        $i->click('Update');

        $i->click('#tab-anchor-multilingualpress-site-2-tab-yoast');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_focuskw', 'Some focus keyphrase here');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_title', 'Some title here');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_metadesc', 'Some description here');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_canonical', 'http://canonic.al');

        $i->click('Update');
        $i->click('#tab-anchor-multilingualpress-site-2-tab-yoast');

        $i->seeInField('#multilingualpress-site-2-yoast_wpseo_focuskw', 'Some focus keyphrase here');
        $i->seeInField('#multilingualpress-site-2-yoast_wpseo_title', 'Some title here');
        $i->seeInField('#multilingualpress-site-2-yoast_wpseo_metadesc', 'Some description here');
        $i->seeInField('#multilingualpress-site-2-yoast_wpseo_canonical', 'http://canonic.al');
    }

    public function allowHtmlTagsInTermDescription()
    {
        // Create a post category and in the description create an H1 tag title.

        // Connect it to another site through MLP translation Metabox.

        // Once connected, edit category in the remote site, click update button.

        // Go back to source site edit category.
    }
}
