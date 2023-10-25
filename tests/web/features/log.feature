#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Log
  In order to execute admin actions
  As an admin
  I need to be able to view the log

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Create new Query
    When I follow "Notifications"
    And I follow "Log"
    And I press "Display"
    And I wait for 2 seconds
    Then I should see table headers "Time", "Log ID", "NID", "Subject", "From", "To", "Message", "Settings"
    But I should not see "Error:"

