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
 * Class for interacting with the Queries page.
 */
class QueriesPage
{
    public static function followQuery($session, $queryId)
    {
        $page = $session->getPage();

        # Find the table row where the first element matches the query ID name, and then get the
        # 4th column element and click it
        $element = $page->find("xpath", "//tr/td[text()='" . $queryId . "']/following-sibling::td[3]");
        $element->click();
    }

    public static function followLastQuery($session)
    {
        $page = $session->getPage();

        $elements = $page->findAll("xpath", "//tr/td[4]");
        $lastElement = end($elements);
        $lastElement->click();
    }

    public static function showConditionsForLastQuery($session)
    {
        $page = $session->getPage();

        $elements = $page->findAll("xpath", "//tr/td[3]");
        $lastElement = end($elements);
        $lastElement->click();
    }

    public static function deleteQueriesWithName($session, $name)
    {
        $page = $session->getPage();

        $elements = $page->findAll("xpath", "//td[2]");
        for($i = count($elements) - 1; $i >= 0; $i--) {
            $element = $elements[$i];
            $subjectElement = $element;
            $deleteLink = $subjectElement->find("xpath", "//following-sibling::td[4]/input");
            if ($subjectElement->getText() === $name) {
                $deleteLink->click();

                # Handle confirmation dialog
                $page->pressButton("Delete query");
            }
        }
    }
}
