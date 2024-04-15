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
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    const CONFIG_FILE = __DIR__.'/../config.ini';

    private $testConfig;
    private $timestamp;
    private $baseUrl;

    private static $featureFileName;

    private $previousWindowName;

    private $session;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->timestamp = date('Y-m-d-H-i-s');
        $this->testConfig = new TestConfig(self::CONFIG_FILE);
        $this->baseUrl = $this->testConfig->getRedCap()['base_url'];
    }

    /** @BeforeFeature */
    public static function setupFeature($scope)
    {
        $feature = $scope->getFeature();
        $filePath = $feature->getFile();
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        self::$featureFileName = $fileName;
    }

    /** @AfterFeature */
    public static function teardownFeature($scope)
    {
    }


    /**
     * @BeforeScenario
     */
    public function setUpBeforeScenario()
    {
        echo "Feature file name :'".(self::$featureFileName)."'\n";

        $cookieName  = 'auto-notify-code-coverage-id';
        $cookieValue = 'web-test';

        $session = $this->getSession();
        #print_r($session);

        $this->setMinkParameter('base_url', $this->baseUrl);
        echo "Base URL set to: ".$this->baseUrl;

        $this->getSession()->visit($this->baseUrl);
        $this->getSession()->setCookie($cookieName, $cookieValue);
        echo "Cookie '{$cookieName}' set to '{$cookieValue}'\n";
    }

    /**
     * @AfterScenario
     */
    public function afterScenario($event)
    {
        $session = $this->getSession();
        $session->restart();

        // $session->reset();  # Tests run much slower using reset (contrary to the documentation)

        // $scenario = $event->getScenario();
        // $tags = $scenario->getTags();
    }


    /**
     * @Given /^I wait$/
     */
    public function iWait()
    {
        $this->getSession()->wait(10000);
    }



    /**
     * @Then /^I go to previous window$/
     */
    public function iGoToPreviousWindow()
    {
        if (!empty($this->previousWindowName)) {
            print "*** SWITCH TO PREVIOUS WINDOW {$this->previousWindowName}\n";
            $this->getSession()->switchToWindow($this->previousWindowName);
            $this->previousWindowName = '';
        }
    }

    /**
     * @Then /^Print element "([^"]*)" text$/
     */
    public function printElementText($css)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->find('css', $css);
        $text = $element->getText();
        print "{$text}\n";
    }

    /**
     * @Then /^Print element "([^"]*)" value$/
     */
    public function printValueText($css)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->find('css', $css);
        $value = $element->getValue();
        print "{$value}\n";
    }

    /**
     * @Then /^Field "([^"]*)" should contain value "([^"]*)"$/
     */
    public function fieldShouldContainValue($fieldLocator, $value)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->findField($fieldLocator);
        if (!isset($element)) {
            throw new \Exception("Field \"{$css}\" not found.");
        }

        $fieldValue = $element->getValue();

        if (strpos($fieldValue, $value) === false) {
            throw new \Exception("Field \"{$css}\" does not contain value \"{$value}\".");
        }
    }

    /**
    /**
     * @Then /^Print select "([^"]*)" text$/
     */
    public function printSelectText($selectCss)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $select = $page->find('css', $selectCss);
        if (!empty($select)) {
            #$html = $select->getHtml();
            #print "\n{$html}\n\n";
            $option = $page->find('css', $selectCss." option:selected");
            #$option = $select->find('css', "option:selected");
            #$option = $select->find('xpath', "//option[@selected]");
            if (!empty($option)) {
                $text = $option->getText();
                print "{$text}\n";
            } else {
                print "Selected option not found\n";
            }
        } else {
            print 'Select "'.$selectCss.'" not found'."\n";
        }
    }

    /**
     * @Then /^I should see tabs? ("([^"]*)"(,(\s)*"([^"]*)")*)$/
     */
    public function iShouldSeeTabs($tabs)
    {
        $tabs = explode(',', $tabs);
        for ($i = 0; $i < count($tabs); $i++) {
            # trim standard character plus quotes
            $tabs[$i] = trim($tabs[$i], " \t\n\r\0\x0B\"");
        }

        $session = $this->getSession();
        Util::checkTabs($session, $tabs);
    }
    
    
    /**
     * @Then /^tab ("([^"]*)") should be selected$/
     */
    public function tabShouldBeSelected($tab)
    {
        $tab = trim($tab, " \t\n\r\0\x0B\"");

        $session = $this->getSession();
        Util::isSelectedTab($session, $tab);
    }

    /**
     * @Then /^I should not see tabs? ("([^"]*)"(,(\s)*"([^"]*)")*)$/
     */
    public function iShouldNotSeeTabs($tabs)
    {
        $tabs = explode(',', $tabs);
        for ($i = 0; $i < count($tabs); $i++) {
            # trim standard character plus quotes
            $tabs[$i] = trim($tabs[$i], " \t\n\r\0\x0B\"");
        }

        $session = $this->getSession();
        $shouldFind = false;
        Util::checkTabs($session, $tabs, $shouldFind);
    }


    /**
     * @Then /^I should see table headers ("([^"]*)"(,(\s)*"([^"]*)")*)$/
     */
    public function iShouldSeeTableHeaders($headers)
    {
        $headers = explode(',', $headers);
        for ($i = 0; $i < count($headers); $i++) {
            # trim standard character plus quotes
            $headers[$i] = trim($headers[$i], " \t\n\r\0\x0B\"");
        }

        $session = $this->getSession();
        
        Util::checkTableHeaders($session, $headers);
    }



    /**
     * @When /^I print window names$/
     */
    public function iPrintWindowNames()
    {
        $windowName = $this->getSession()->getWindowName();
        $windowNames = $this->getSession()->getWindowNames();
        print "Current window: {$windowName} [".array_search($windowName, $windowNames)."]\n";
        print_r($windowNames);
    }

    /**
     * @When /^print link "([^"]*)"$/
     */
    public function printLink($linkId)
    {
        $session = $this->getSession();

        $page = $session->getPage();
        $link = $page->findLink($linkId);
        print "\n{$linkId}\n";
        print_r($link);
    }

    /**
     * @When /^I press first number of projects button$/
     */
    public function iPressFirstNumberOfProjectsButton()
    {
        $session = $this->getSession();

        UsersPage::pressFirstNumberOfProjectsButton($session);
    }

    /**
     * @When /^I test "([^"]*)"$/
     */
    public function iTest($text)
    {
        $session = $this->getSession();

        QueryPage::addCondition($session, 1);

        //Util::getTableColumnValues($session, $text);

        ##UsersPage::pressFirstNumberOfProjectsButton($session);

        # $id = NotificationsPage::getMaxId($session);
        # NotificationsPage::copyNotification($session, $id);
        # NotificationsPage::deleteLastNotification($session);
        #NotificationsPage::followNotification($session, $id);
    }

    /**
     * @When /^I click "([^"]*)"$/
     */
    public function iClick($id)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $element = $page->find('named', ['id_or_name', $id]);
        if ($element == null) {
            throw new \Exception('Could not find element "' . $id . '".');
        }
        $element->click();
    }

    /**
     * @When /^I click on element containing "([^"]*)"$/
     */
    public function iClickOnElementContaining($text)
    {
        $session = $this->getSession();

        $page = $session->getPage();
        $element = $page->find('xpath', "//*[contains(text(), '{$text}')]");
        $element->click();
    }


    /**
     * @When /^I wait for (\d+) seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
        sleep($seconds);
    }


    /**
     * @When /^I log in as admin$/
     */
    public function iLogInAsAdmin()
    {
        $session = $this->getSession();
        Util::loginAsAdmin($session);
    }


    /**
     * @When /^I log out$/
     */
    public function iLogOut()
    {
        $session = $this->getSession();
        Util::logOut($session);
    }

    /**
     * @When /^I access the admin interface$/
     */
    public function iAccessTheAdminInterface()
    {
        $session = $this->getSession();
        Util::logInAsAdminAndAccessAutoNotify($session);
    }

    /**
     * @When /^I select the test project$/
     */
    public function iSelectTheTestProject()
    {
        $session = $this->getSession();
        Util::selectTestProject($session);
    }

    /**
     * @When /^I follow "([^"]*)" to new window$/
     */
    public function iFollowLinkToNewWindow($link)
    {
        $session = $this->getSession();
        $this->previousWindowName = $session->getWindowName();
        Util::goToNewWindow($session, $link);
    }

    /**
     * @When /^I press button "([^"]*)" to new window$/
     */
    public function iPressButtonToNewWindow($button)
    {
        $session = $this->getSession();
        $this->previousWindowName = $session->getWindowName();
        Util::pressButtonToNewWindow($session, $button);
    }

    /**
     * @When /^I select user from "([^"]*)"$/
     */
    public function iSelectUserFromSelect($select)
    {
        $session = $this->getSession();
        Util::selectUserFromSelect($session, $select);
    }


    #---------------------------------
    # REDCAP USER
    #---------------------------------

    /**
     * @When /^I create user "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)"$/
     */
    public function iCreateUser($username, $password, $firstName, $lastName, $email)
    {
        $session = $this->getSession();
        Util::createUser($session, $username, $password, $firstName, $lastName, $email);
    }

    /**
     * @When /^I delete user "([^"]*)"$/
     */
    public function iDeleteUser($username)
    {
        $session = $this->getSession();
        Util::deleteUserIfExists($session, $username);
    }

    /**
     * @When /^I log in as user "([^"]*)" "([^"]*)"$/
     */
    public function iLogInAsUser($username, $password)
    {
        $session = $this->getSession();
        Util::logInAsUser($session, $username, $password);
    }

    #---------------------------------
    # NOTIFICATION
    #---------------------------------

    /**
     * @When /^I enter message "([^"]*)"$/
     */
    public function iEnterMessage($message)
    {
        $session = $this->getSession();
        NotificationPage::enterMessage($session, $message);
    }


    /**
     * @When /^I insert variable "([^"]*)" "([^"]*)" in message$/
     */
    public function iInsertVariableInMessage($group, $variable)
    {
        $session = $this->getSession();
        NotificationPage::insertVariable($session, $group, $variable);
    }

    /**
     * @When /^I insert horizontal rule in message$/
     */
    public function iInsertHorizontalRuleInMessage()
    {
        $session = $this->getSession();
        NotificationPage::insertHorizontalRule($session);
    }


    #---------------------------------
    # NOTIFICATIONS
    #---------------------------------

    /**
     * @When /^I delete notifications with subject "([^"]*)"$/
     */
    public function iDeleteNotificationsWithSubject($subject)
    {
        $session = $this->getSession();
        NotificationsPage::deleteNotificationsWithSubject($session, $subject);
    }

    #---------------------------------
    # QUERIES
    #---------------------------------

    /**
     * @When /^I delete queries with name "([^"]*)"$/
     */
    public function iDeleteQueriesWithName($name)
    {
        $session = $this->getSession();
        QueriesPage::deleteQueriesWithName($session, $name);
    }

    /**
     * @When /^I follow last query$/
     */
    public function iFollowLastQuery()
    {
        $session = $this->getSession();
        QueriesPage::followLastQuery($session);
    }

    /**
     * @When /^I show conditions for last query$/
     */
    public function iShowConditionsForLastQuery()
    {
        $session = $this->getSession();
        QueriesPage::showConditionsForLastQuery($session);
    }

    #---------------------------------
    # DATABASE CHECKS
    #---------------------------------

    /**
     * @Then /^database table "([^"]*)" should contain (\d+) rows?$/
     */
    public function databaseTableShouldContainRows($tableName, $numRows)
    {
        $db = new Database();

        $actualNumRows = $db->getNumberOfTableRows($tableName);

        if ($actualNumRows != $numRows) {
            $message = 'Database table "' . $tableName . '" has ' . $actualNumRows
                . ' when it was expected to have ' . $numRows . '.';
            throw new \Exception($message);
        }
    }
}
