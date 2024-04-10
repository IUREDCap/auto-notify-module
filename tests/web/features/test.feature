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

