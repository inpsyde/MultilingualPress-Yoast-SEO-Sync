<?php

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class CodeCeptionTester extends \Codeception\Actor
{
    use \_generated\AcceptanceTesterActions;

    /* --------------------------------------------------------------
     * Users Utility
     * ----------------------------------------------------------- */

    /**
     * Login as admin
     */
    public function loginAsAdmin()
    {
        $I = $this;
        $I->amOnPage('/wp-login.php');
        $I->wait(1);
        $I->fillField(['name' => 'log'], 'admin');
        $I->fillField(['name' => 'pwd'], 'password');
        $I->click('#wp-submit');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $I = $this;
        $I->amOnPage('/wp-login.php?action=logout');
        $I->click('log out');
    }


    /* --------------------------------------------------------------
     * Plugins Utility
     * ----------------------------------------------------------- */

    /**
     * Install Plugin.
     */
    public function installAndActivatePluginNetwork(string $slug)
    {
        $I = $this;

        $I->amOnPage('/wp-admin/network/plugin-install.php');

        $I->fillField('.wp-filter-search', $slug);
        $I->waitForElement(".plugin-card-{$slug}");

        $I->seeElement(".plugin-card-{$slug} .install-now");
        $I->click(".plugin-card-{$slug} .install-now");

        $I->waitForElementChange(".plugin-card-{$slug} .install-now", function ($el) {
            return false !== strpos($el->getAttribute('class'), 'activate-now');
        }, 100);
        $I->click(".plugin-card-{$slug} .activate-now");
        $I->see('Plugin activated.');
    }

    /**
     * Activate Plugin Network.
     *
     * @param string $slug
     */
    public function activatePluginNetwork(string $slug)
    {
        $I = $this;

        $I->amOnPage('/wp-admin/network/plugins.php');

        $I->seeElement("[data-slug=\"{$slug}\"]");

        $I->click("[data-slug=\"{$slug}\"] .activate a");
        $I->see('Plugin activated.');
    }

    /**
     * Deactivate Plugin Network.
     *
     * @param string $slug
     */
    public function deactivatePluginNetwork(string $slug)
    {
        $I = $this;

        $I->amOnPage('/wp-admin/network/plugins.php');

        try {
            $I->seeElement("[data-slug=\"{$slug}\"]");
            $I->click("[data-slug=\"{$slug}\"] .deactivate a");
        } catch (\Throwable $thr) {
            return;
        }

        $I->wait(2);
        $I->see('Plugin deactivated.');
    }

    /**
     * Delete Plugin.
     *
     * @param string $slug
     */
    public function uninstallAndDeletePlugin(string $slug)
    {
        $I = $this;

        $I->amOnPage('/wp-admin/network/plugins.php');

        try {
            $I->seeElement("[data-slug=\"{$slug}\"]");
            $I->click("[data-slug=\"{$slug}\"] .delete a");
            $I->acceptPopup();
        } catch (\Throwable $thr) {
            return;
        }

        $I->wait(3);
        $I->see('was successfully deleted.');
    }

    /* --------------------------------------------------------------
     * Network Site Utility
     * ----------------------------------------------------------- */

    /**
     * Connect two sites
     */
    public function connectTwoSites()
    {
        $I = $this;

        // edit Site 1 MultilingualPress tab
        $I->amOnPage('/wp-admin/network/sites.php?page=multilingualpress-site-settings&id=1');

        // see if English is selected
        $language = $I->grabValueFrom('#mlp-site-language-tag');
        $I->assertEquals('en-US', $language);

        // save changes
        $I->click('Save Changes');

        // edit Site 2 MultilingualPress tab
        $I->amOnPage('/wp-admin/network/sites.php?page=multilingualpress-site-settings&id=2');

        // select German language
        $I->executeJS("jQuery('#mlp-site-language-tag').val('de-DE')");

        // check Relationship checkbox (WordPress - en_US)
        $I->checkOption('#mlp-site-relations-1');

        // save changes
        $I->click('Save Changes');
    }

    /* --------------------------------------------------------------
  * Posts Utility
  * ----------------------------------------------------------- */

    /**
     * Connect two posts
     */
    public function connectTwoPosts()
    {
        $i = $this;

        $i->amOnPage('/wp-admin/post.php?post=1&action=edit');

        $i->executeJS('jQuery( "#multilingualpress-site-2-relationship-existing" ).trigger( "click" );');
        $i->fillField('#multilingualpress-site-2-search_post_id', 'Hello');

        $i->waitForElement('.search-results-row');
        $i->click('.search-results-row td label');

        $i->wait(1);
        $i->click('Update now');
    }
}
