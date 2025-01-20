<?php # -*- coding: utf-8 -*-

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
}
