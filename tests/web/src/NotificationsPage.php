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
 * Class for interacting with the admin "Notifications" page.
 */
class NotificationsPage
{
    public static function followNotification($session, $notificationId)
    {
        $page = $session->getPage();

        # Find the table row where the first element matches the notification ID, and then get the
        # 7th column element and click it
        $notificationLink = $page->find("xpath", "//tr/td[text()='".$notificationId."']/following-sibling::td[7]/a");
        $notificationLink->click();
    }

    public static function getMaxId($session)
    {
        $page = $session->getPage();

        $elements = $page->findAll("xpath", "//td[1]");
        $maxId = 0;
        foreach ($elements as $element) {
            $maxId = max($maxId, intval($element->getText()));
            # print $element->getText() . "\n";
        }
        # print "Max ID: " . $maxId . "\n";
        return $maxId;
    }

    public static function copyNotification($session, $notificationId)
    {
        $page = $session->getPage();

        # Find the table row where the first element matches the notification ID, and then get the
        # corresponding copy notification form and submit it
        $form = $page->find("xpath", "//tr/td[text()='".$notificationId."']/following-sibling::td[8]/form");
        $form->submit();
    }


    /**
     * Deletes the last notification if it exsits.
     */
    public static function deleteLastNotification($session)
    {
        $page = $session->getPage();

        # Find the table row where the first element matches the server name, and then get the
        # 7th column element and click it
        $elements = $page->findAll("xpath", "//tr/td[10]");
        $lastElement = end($elements);
        # print "HTML: " . $lastElement->getOuterHtml() . "\n";
        $lastElement->click();

        # Handle confirmation dialog
        $page->pressButton("Delete notification");
    }

    public static function deleteNotificationsWithSubject($session, $subject)
    {
        $page = $session->getPage();

        $elements = $page->findAll("xpath", "//td[4]");
        for($i = count($elements) - 1; $i >= 0; $i--) {
            $element = $elements[$i];
            $subjectElement = $element;
            $deleteLink = $subjectElement->find("xpath", "//following-sibling::td[6]/input");
            if ($subjectElement->getText() === $subject) {
                $deleteLink->click();

                # Handle confirmation dialog
                $page->pressButton("Delete notification");
            }
        }
    }
}
