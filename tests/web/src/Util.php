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

use WebDriver\Exception\NoAlertOpenError;

/**
 * Utility class that has helpful methods.
 */
class Util
{
    public const TEST_PASSWORD = 'TestPassword#123';

    /**
     * Gets a web browser sessions. This can be useful for interacting with
     * a web browser outside of the context of a scenario.
     */
    public static function getSession()
    {
        $testConfig = new TestConfig(FeatureContext::CONFIG_FILE);
        $baseUrl = $testConfig->getRedCap()['base_url'];

        $driver = new \DMore\ChromeDriver\ChromeDriver('http://localhost:9222', null, $baseUrl);
        $session = new \Behat\Mink\Session($driver);
        $session->start();

        return $session;
    }

    /**
     * Logs in to REDCap as the admin.
     */
    public static function logInAsAdmin($session)
    {
        $testConfig = new TestConfig(FeatureContext::CONFIG_FILE);
        $baseUrl  = $testConfig->getRedCap()['base_url'];
        $username = $testConfig->getAdmin()['username'];
        $password = $testConfig->getAdmin()['password'];

        $session->visit($baseUrl);

        $page = $session->getPage();

        $page->fillField('username', $username);
        $page->fillField('password', $password);
        $page->pressButton('login_btn');
    }

    /**
     * Logs out of REDCap.
     */
    public static function logOut($session)
    {
        $page = $session->getPage();
        $page->clickLink('Log out');
    }


    /**
     * Logs in to REDCap as the admin and accesses the Auto-Notify admin interface.
     */
    public static function logInAsAdminAndAccessAutoNotify($session)
    {
        self::logInAsAdmin($session);

        $page = $session->getPage();

        $page->clickLink('Control Center');
        sleep(1);
        $page->clickLink('Auto-Notify');
    }


    /**
     * Checks for module page tabs.
     *
     * @param array $tabs array of strings that are tab names
     * @param boolean $shouldFind if true, checks that tabs exist, if false
     *     checks that tabs do not exist.
     */
    public static function checkTabs($session, $tabs, $shouldFind = true)
    {
        $page = $session->getPage();
        $element = $page->find('css', '#sub-nav');

        foreach ($tabs as $tab) {
            $link = $element->findLink($tab);
            if (empty($link)) {
                if ($shouldFind) {
                    throw new \Exception("Tab {$tab} not found.");
                }
            } else {
                if (!$shouldFind) {
                    throw new \Exception("Tab {$tab} found.");
                }                
            }
        }
    }
    
    public static function isSelectedTab($session, $tab)
    {
        $page = $session->getPage();
        $element = $page->find('css', '#sub-nav');

        $link = $element->findLink($tab);
        if (empty($link)) {
            throw new \Exception("Tab {$tab} not found.");
        }
        
        if (!$link->getParent()->hasClass('active')) {
            throw new \Exception("Tab {$tab} is not selected.");
        }
    }
    
    
    /**
     * Checks that the specified table headers exist on the current page.
     *
     * @param array $headers array of strings that are table headers.
     */
    public static function checkTableHeaders($session, $headers)
    {
        $page = $session->getPage();
        $elements = $page->findAll('css', 'th');
        
        $headersMap = array();
        if (!empty($elements)) {
            foreach ($elements as $element) {
                $headersMap[$element->getText()] = 1;
            }
        }

        foreach ($headers as $header) {
            if (!array_key_exists($header, $headersMap)) {
                throw new \Exception("Table header \"{$header}\" not found.");
            }
        }
    }
            
    public static function findTextFollowedByText($session, $textA, $textB)
    {
        $content = $session->getPage()->getContent();

        // Get rid of stuff between script tags
        $content = self::removeContentBetweenTags('script', $content);

        // ...and stuff between style tags
        $content = self::removeContentBetweenTags('style', $content);

        $content = preg_replace('/<[^>]+>/', ' ',$content);

        // Replace line breaks and tabs with a single space character
        $content = preg_replace('/[\n\r\t]+/', ' ',$content);

        $content = preg_replace('/ {2,}/', ' ',$content);

        if (strpos($content,$textA) === false) {
            throw new \Exception(sprintf('"%s" was not found in the page', $textA));
        }

        if ($textB) {
            $seeking = $textA . ' ' . $textB;
            if (strpos($content,$textA . ' ' . $textB) === false) {
                throw new \Exception(sprintf('"%s" was not found in the page', $seeking));
            }
        }
    }

    public static function findThisText($session, $see, $textA)
    {
        $content = $session->getPage()->getContent();

        // Get rid of stuff between script tags
        $content = self::removeContentBetweenTags('script', $content);

        // ...and stuff between style tags
        $content = self::removeContentBetweenTags('style', $content);

        $content = preg_replace('/<[^>]+>/', ' ',$content);

        // Replace line breaks and tabs with a single space character
        $content = preg_replace('/[\n\r\t]+/', ' ',$content);

        $content = preg_replace('/ {2,}/', ' ',$content);

        $seeError = "was not";
        if ($see === "should not") {
            $seeError = "was";
        }

        if ($see === 'should') {
            if (strpos($content,$textA) === false) {
               throw new \Exception(sprintf('"%s" was not found in the page', $textA));
            }
        } elseif ($see === 'should not') {
            if (strpos($content,$textA) === true) {
               throw new \Exception(sprintf('"%s" was found in the page', $textA));
            }
        } else {
            throw new \Exception(sprintf('"%s" option is unrecognized', $see));
        }
    }

    /**
     * Note: matches full values
     */
    public static function tableColumnContains($session, $columnName, $value)
    {
        $values = self::getTableColumnValues($session, $columnName);
        return in_array($value, $values);
    }

    /**
     * Note: matches full values
     */
    public static function tableColumnDoesNotContain($session, $columnName, $value)
    {
        $values = self::getTableColumnValues($session, $columnName);
        return !in_array($value, $values);
    }

    /**
     * Gets the values (td element text) for the specified table column name.
     */
    public static function getTableColumnValues($session, $columnName)
    {
        $page = $session->getPage();
        $elements = $page->findall('xpath', "//table//td[count(//table//th[text()='{$columnName}']/preceding-sibling::*) +1]");

        $values = [];
        if ($elements != null && is_array($elements)) {
            # $i = 0;
            foreach ($elements as $element) {
                $values[] = $element->getText();
                # print ("{$i}: " . $element->getText() . "\n");
                # $i++;
            }
        }

        return $values;
    }


    /**
     * Follow a link that goes to a new window.
     *
     * @param string $link the link that goes to a new window.
     *
     * @return string the name of the new window
     */
    public static function goToNewWindow($session, $link)
    {
        # Save the current window names
        $windowNames = $session->getWindowNames();

        # Follow the link (which should create a new window name)
        $page = $session->getPage();
        $page->clickLink($link);
        sleep(2); // Give some time for new window to open

        # See what window name was added (this should be the new window)
        $newWindowNames = $session->getWindowNames();
        $windowNamesDiff = array_diff($newWindowNames, $windowNames);
        $newWindowName = array_shift($windowNamesDiff); // There should be only 1 element in the diff

        $session->switchToWindow($newWindowName);

        return $newWindowName;
    }

    public static function pressButtonToNewWindow($session, $button)
    {
        # Save the current window names
        $windowNames = $session->getWindowNames();

        # Press the button (which should create a new window name)
        $page = $session->getPage();
        $page->pressButton($button);
        sleep(4); // Give some time for new window to open

        # See what window name was added (this should be the new window)
        $newWindowNames = $session->getWindowNames();
        $windowNamesDiff = array_diff($newWindowNames, $windowNames);
        $newWindowName = array_shift($windowNamesDiff); // There should be only 1 element in the diff

        $session->switchToWindow($newWindowName);

        return $newWindowName;
    }

    /**
     * Created the specified user in REDCap, including setting the user's password.
     * This method assumes that the admin account has already been logged into.
     */
    public static function createUser($session, $username, $password, $firstName, $lastName, $email)
    {
        $page = $session->getPage();
        $page->clickLink('Control Center');
        sleep(1);

        $page->clickLink('Add Users (Table-based Only)');
        sleep(1);
        $page->fillField('username', $username);
        $page->fillField('user_firstname', $firstName);
        $page->fillField('user_lastname', $lastName);
        $page->fillField('user_email', $email);
        sleep(1);
        $page->pressButton('Save');
        sleep(1);

        self::logOut($session);

        $mailHogApi = new MailHogApi();
        $messages = $mailHogApi->getMessages($email, 'REDCap access granted');

        if (count($messages) <= 0) {
            throw new \Exception('No password reset e-mail found for creation of user "' . $username . '".');
        }

        # Get the HTML for the first message
        $message = $messages[0];
        $htmlMessage = $message->getMessageHtml();

        # Get the password reset url
        $matches = array();
        preg_match_all('/<a(.*)href="([^"]+)"(.*)>(.*)<\/a>/', $htmlMessage, $matches);
        if ($matches === null || count($matches) < 3 || $matches[2] === null) {
            throw new \Exception("Password reset URL not found in password reset e-mail for user \"{$username}\".");
        }
        $url = $matches[2][0];

        sleep(2);

        $session->visit($url);
        $page = $session->getPage();

        $page->fillField('password', $password);
        $page->fillField('password2', $password);
        sleep(2);
        $page->pressButton('Submit');
        sleep(2);

        self::logOut($session);

        sleep(2);
        self::logInAsAdmin($session);
    }

    /**
     * Deletes the specified user from REDCap if they exist. This method assumes that the admin account has already been logged into.
     */
    public static function deleteUserIfExists($session, $username)
    {
        $page = $session->getPage();
        $page->clickLink('Control Center');
        sleep(1);

        $page->clickLink('Browse Users');
        sleep(1);
        $page->fillField('user_search', $username);
        $page->pressButton('Search');

        $pageText = $page->getText();

        if (str_contains($pageText, "User information for")) {
            sleep(1);
            $session->getDriver()->executeScript('window.confirm = function(){return true;}');
            $page->pressButton('Delete user from system');
            sleep(2);

        }

        sleep(1);
    }

    /**
     * Logs in to REDCap as the specified user.
     */
    public static function logInAsUser($session, $username, $password)
    {
        $testConfig = new TestConfig(FeatureContext::CONFIG_FILE);
        $baseUrl  = $testConfig->getRedCap()['base_url'];

        $session->visit($baseUrl);

        $page = $session->getPage();

        $page->fillField('username', $username);
        $page->fillField('password', $password);
        $page->pressButton('login_btn');
    }
}
