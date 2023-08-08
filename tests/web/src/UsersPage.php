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
 * Class for interacting with the users page.
 */
class UsersPage
{
    public function pressFirstNumberOfProjectsButton($session)
    {
        # Save the current window names
        $windowNames = $session->getWindowNames();

        $page = $session->getPage();
        $elements = $page->findAll("xpath", "//tr/td[6]/button");
        if ($elements == null || count($elements) < 1) {
            throw new \Exception("No number of projects buttons found in users table.");
        }

        $firstButton = $elements[0];
        # print "\n". $firstButton->getHtml() . "\n";
        $firstButton->click();
        sleep(4);

        # See what window name was added (this should be the new window)
        $newWindowNames = $session->getWindowNames();
        $windowNamesDiff = array_diff($newWindowNames, $windowNames);
        $newWindowName = array_shift($windowNamesDiff); // There should be only 1 element in the diff

        $session->switchToWindow($newWindowName);

        return $newWindowName;
    }
}
