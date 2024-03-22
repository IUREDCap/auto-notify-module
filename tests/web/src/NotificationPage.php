<?php
#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule\WebTests;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Class for interacting with the admin "Notifications" page. Note that all methods assume
 * that the current page is the notification page. If it is not, then errors will be generated.
 */
class NotificationPage
{
    /**
     * WORK IN PROGRESS - does NOT work
     */
    public static function enterMessage($session, $message)
    {
        $window = $session->getWindowName();
        $page = $session->getPage();

        # $element = $page->findById('notification-message');
        # $element = $page->find('css', '.tox-tinymce');
        # $element = $page->find('css', '.tox-editor-container');
        # $element = $page->find('css', '.tox-edit-area');

        $session->switchToIFrame('message_ifr');
        $page = $session->getPage();
        $element = $page->find('css', 'html');
        $element->mouseOver();
        $element->keyPress('t');
        $element->keyPress('e');
        #$element->click();
        $element = $page->find('css', 'body');
        $element->mouseOver();
        # $element->keyDown('t');
        # $element->keyUp('t');
        $element->keyPress('e');
        $element->keyPress('s');
        $element->keyPress('t');
        print $element->getOuterHtml() . "\n";
        flush();
        sleep(10);

        $session->switchToWindow($window);

        if ($element === null) {
            throw new \Exception('Could not find notification message');
        }
    }

    /**
     * Inserts the specified variable into the message on the notitication page.
     *
     * @param string $group the group name for the variable (e.g., "REDCap", "user").
     * @param string $variable the name of the variable to insert (e.g., "username", "e-mail").
     */
    public static function insertVariable($session, $group, $variable)
    {
        $page = $session->getPage();

        #-------------------------------------------------------------
        # Click on "Insert Variable" button
        #-------------------------------------------------------------
        $element = $page->find('css', '.tox-mbtn__select-label');
        $element->click();
        #sleep(1);
        
        #-------------------------------------------------------------
        # Select the variable group
        #-------------------------------------------------------------
        $element = $page->find('xpath', "//div[@class='tox-collection__item-label' and text()='" . $group ."']");
        #<div class="tox-collection__item-label">user</div>
        # $element = $page->find('css', 'div.tox-menu:nth-child(1) > div:nth-child(1) > div:nth-child(2)');
        $element->mouseOver();
        #sleep(1);

        #-------------------------------------------------------------
        # Select the variable
        #-------------------------------------------------------------
        # outer html: <div class="tox-collection__item-label">username</div>
        $element = $page->find('xpath', "//div[@class='tox-collection__item-label' and text()='" . $variable . "']");
        $element->click();
    }

    /**
     * Inserts a horizontal rule in the message on the notification page.
     */
    public static function insertHorizontalRule($session)
    {
        $page = $session->getPage();

        $element = $page->find('xpath', "//button[@class='tox-tbtn' and @aria-label='Horizontal line']");
        $element->click();
    }
}
