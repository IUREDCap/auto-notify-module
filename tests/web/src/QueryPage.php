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


    /**
     * @param int $conditionNumber The condition number (1-based index) to set.
     */
    public static function setCondition($session, $conditionNumber, $variable, $operator, $value)
    {
        $page = $session->getPage();
        $elements = $page->findAll("css", 'li.condition');

        if ($elements == null || count($elements) < $conditionNumber) {
            throw new \Exception("Condition number {$conditionNumber} does not exits.");
        }

        $condition = $elements[$conditionNumber - 1];

        #---------------------------
        # Set the variable
        #---------------------------
        $variableSelect = $condition->find("css", "select.anmVariableSelect");
        // print "{$variableSelect->getText()}\n";
        $variableSelect->selectOption($variable);

        #---------------------------
        # Set the operator
        #---------------------------
        $operatorSelect = $condition->find("css", "select.anmOperatorSelect");
        $operatorSelect->selectOption($operator);

        #---------------------------
        # Set the value
        #---------------------------
        $valueElement = $operatorSelect->find("xpath", "/following-sibling::*");
        $valueTag = $valueElement->getTagName();

        if ($valueElement->hasAttribute('readonly')) {
            ; // don't update value
        } elseif ($valueTag === 'input') {
            $valueType = $valueElement->getAttribute("type");
            if ($valueType === 'text') {
                $valueElement->setValue($value);
            }
        } elseif ($valueTag === 'select') {
            $valueElement->selectOption($value);
        }
    }
}
