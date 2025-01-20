<?php # -*- coding: utf-8 -*-

namespace acceptance;

use CodeCeptionTester;

class PostMetaboxTabCest
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
        $i->amOnPage('/wp-admin/post.php?post=1&action=edit');
        $i->seeElement('#tab-anchor-multilingualpress-site-2-tab-yoast');
    }

    /**
     * Copy Yoast title and description from site 1 to site 2.
     */
    public function copyTitleAndDescriptionToRemoteSite(CodeCeptionTester $i)
    {
        $i->connectTwoPosts();

        // disable Gutenberg popup
        $i->wait(2);
        $i->click('.nux-dot-tip__disable');

        // set yoast title and description values
        $i->executeJS("jQuery('#yoast_wpseo_title').val('Yoast SEO Title')");
        $i->executeJS("jQuery('#yoast_wpseo_metadesc').val('Yoast Meta Description')");

        // fill yoast title and description fields in yoast tab
        $i->wait(2);
        $i->click('#tab-anchor-multilingualpress-site-2-tab-yoast');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_title', 'Remote Yoast SEO Title');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_metadesc', 'Remote Yoast Meta Description');

        // update post
        $i->executeJS('jQuery( ".editor-post-publish-button" ).trigger( "click" );');
        $i->wait(3);

        // go to site 2 and check yoast title and description
        $i->amOnPage('/site2/wp-admin/post.php?post=1&action=edit');
        $i->assertEquals('Remote Yoast SEO Title', $i->grabValueFrom('#yoast_wpseo_title'));
        $i->assertEquals('Remote Yoast Meta Description',
            $i->grabValueFrom('#yoast_wpseo_metadesc'));
    }

    /**
     * Copy Focus Keyphase to remote site.
     */
    public function copyFocusKeyphaseToRemoteSite(CodeCeptionTester $i)
    {
        $i->connectTwoPosts();

        // disable Gutenberg popup
        $i->wait(2);
        $i->click('.nux-dot-tip__disable');

        // set focus keyphase value
        $i->fillField('#focus-keyword-input-metabox', 'Focus Keyphase value');

        // fill focus keyphase field in yoast tab
        $i->wait(2);
        $i->click('#tab-anchor-multilingualpress-site-2-tab-yoast');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_focuskw', 'Remote Focus Keyphase value');

        // update post
        $i->executeJS('jQuery( ".editor-post-publish-button" ).trigger( "click" );');
        $i->wait(3);

        // go to site 2 and check focus keyphase
        $i->amOnPage('/site2/wp-admin/post.php?post=1&action=edit');
        $i->assertEquals('Remote Focus Keyphase value', $i->grabValueFrom('#focus-keyword-input-metabox'));
    }

    /**
     * Copy Canonical to remote site.
     */
    public function copyCanonicalToRemoteSite(CodeCeptionTester $i)
    {
        $i->connectTwoPosts();

        // disable Gutenberg popup
        $i->wait(2);
        $i->click('.nux-dot-tip__disable');

        // set canonical value
        $i->click('#wpseo-collapsible-advanced-settings-button');
        $i->wait(1);
        $i->fillField('#yoast_wpseo_canonical', 'https://inpsyde.com');

        // fill focus keyphase field in yoast tab
        $i->wait(2);
        $i->click('#tab-anchor-multilingualpress-site-2-tab-yoast');
        $i->fillField('#multilingualpress-site-2-yoast_wpseo_canonical', 'https://remote-inpsyde.com');

        // update post
        $i->executeJS('jQuery( ".editor-post-publish-button" ).trigger( "click" );');
        $i->wait(3);

        // go to site 2 and check focus keyphase
        $i->amOnPage('/site2/wp-admin/post.php?post=1&action=edit');
        $i->click('#wpseo-collapsible-advanced-settings-button');
        $i->wait(1);
        $i->assertEquals('https://remote-inpsyde.com', $i->grabValueFrom('#yoast_wpseo_canonical'));
    }
}
