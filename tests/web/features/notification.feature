#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Notification
  In order to execute admin actions
  As an admin
  I need to be able to createa a new Notification

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Create new Notification
    When I follow "Notifications"
    And I follow "Saved Notifications"
    And I wait for 2 seconds
    And I delete notifications with subject "Notification creation web test"
    And I follow "Notification"
    And I fill in "subject" with "Notification creation web test"
    And I press "Save as Draft"
    And I follow "Notifications"
    And I follow "Saved Notifications"
    Then I should see "Notifications"
    And I should see "ID"
    And I should see "Status"
    And I should see "Notification creation web test"

  Scenario: Send new Notification
    # Delete existing send test notification, if any
    When I follow "Notifications"
    And I follow "Saved Notifications"
    And I wait for 2 seconds
    And I delete notifications with subject "Notification send web test"
    # Create send test notification
    And I follow "Notification"
    And I fill in "subject" with "Notification send web test"
    And I insert variable "user" "username" in message
    And I insert horizontal rule in message
    And I insert variable "user" "e-mail" in message
    And I insert horizontal rule in message
    And I insert variable "user" "applicable project info" in message
    ### And I enter message "Send test"
    And I wait for 2 seconds
    And I fill in "schedulingOption" with "schedOptNow"
    And I press "Send/Schedule"


  # Note: this scenario needs to go last, because it follows a link to a new page,
  # which can causes problems for Behat/Mink for scenarios that come after this
  Scenario: Notification help
    When I follow "Notifications"
    And I follow "Notification"
    And I click "notificationHelp"
    Then I should see "Notification Help"
    And I should see "Use this page to create a new notification"
    And I should see "View text on separate page"
    When I follow "View text on separate page" to new window
    And I wait for 2 seconds
    Then I should see "Use this page to create a new notification"
    But I should not see "View text on separate page"
