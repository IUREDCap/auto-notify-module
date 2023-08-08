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
class QueryPage
{
    /**
     * @param int $groupNumber The grouping operator (ALL/ANY/etc.) number (1-based index) to add the conditions to.
     */
    public static function addCondition($session, $groupNumber)
    {
        $page = $session->getPage();

        # Find the table row where the first element matches the query ID name, and then get the
        # 4th column element and click it
        $elements = $page->findAll("css", '.anmAddCondition');
        if ($elements == null || count($elements) < $groupNumber) {
            throw new \Exception("Group number {$groupNumber} does not exits.");
        }

        $button = $elements[$groupNumber - 1];
        $button->click();
    }
}
