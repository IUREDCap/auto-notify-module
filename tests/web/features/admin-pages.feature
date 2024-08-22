#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Admin-Interface
  In order to execute admin actions
  As an admin
  I need to be able to access the Auto-Notify Admin Pages

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Access the admin home page
    Then I should see "Info"
    And I should see "Notifications"
    And I should see "Queries"
    And I should see "Config"
    And I should see "Test"
    But I should not see "REDCap crashed"

  Scenario: Access the admin Info page
    When I follow "Info"
    Then I should see "Tab"
    And I should see "Page Description"
    But I should not see "REDCap crashed"

  Scenario: Access the admin Config page
    When I follow "Config"
    Then I should see "External module version"
    And I should see "Test mode"
    But I should not see "REDCap crashed"

  Scenario: Access the admin Test page
    When I follow "Test"
    Then I should see "Start date:"
    And I should see "End date:"
    And I should see "E-mail all notifications to:"
    But I should not see "REDCap crashed"

  Scenario: Access the Notification page
    When I follow "Notifications"
    And I follow "Notification"
    Then I should see "Subject"
    And I should see "From"
    And I should see "To"
    But I should not see "Error:"
    And I should not see "REDCap crashed"


  Scenario: Access the Notifications page
    When I follow "Notifications"
    And I follow "Saved Notifications"
    Then I should see "Notifications"
    But I should not see "REDCap crashed"

  Scenario: Access the Query page
    When I follow "Queries"
    And I follow "Query Builder"
    Then I should see "Query Name"
    And I should see "ID"
    And I should see "Query Conditions"
    But I should not see "Error:"
    And I should not see "REDCap crashed"

  Scenario: Access the Queries page
    When I follow "Queries"
    And I follow "Saved Queries"
    Then I should see "Queries"
    And I should see "ID"
    And I should see "Name"
    And I should see "Conditions"
    But I should not see "Error:"
    And I should not see "REDCap crashed"

  Scenario: Access the Log page
    When I follow "Notifications"
    And I follow "Log"
    Then I should see "Log"
    And I should see "Start date:"
    And I should see "End date:"
    But I should not see "Error:"
    And I should not see "REDCap crashed"

  Scenario: Access the Schedule page
    When I follow "Notifications"
    And I follow "Schedule"
    Then I should see "Schedule"
    And I should see "list"
    And I should see "calendar"
    But I should not see "Error:"
    And I should not see "REDCap crashed"

  Scenario: Access the Schedule page
    When I follow "Notifications"
    And I follow "Schedule"
    Then I should see "Schedule"
    And I should see "Start date:"
    And I should see "End date:"
    But I should not see "Error:"
    And I should not see "REDCap crashed"

