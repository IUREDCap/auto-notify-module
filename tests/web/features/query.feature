#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Notifications
  In order to execute admin actions
  As an admin
  I need to be able to createa a new Query

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Create new Query
    When I follow "Queries"
    And I delete queries with name "Query creation web test"
    And I follow "Query"
    And I fill in "Query Name:" with "Query creation web test"
    And I press "Save"
    And I follow "Queries"
    Then I should see "Queries"
    And I should see "Query creation web test"

  Scenario: Show SQL query
    When I follow "Queries"
    And I follow last query
    And I press "Show SQL Query"
    And I wait for 2 seconds
    Then I should see "SELECT DISTINCT"
    And I should see "FROM"
    And I should see "JOIN"
    And I should see "WHERE"

  Scenario: Show conditions
    When I follow "Queries"
    And I follow last query
    And I press button "showConditionsButton" to new window
    Then I should see "Query Conditions"
    But I should not see "Error:"

  Scenario: Show queries conditions
    When I follow "Queries"
    And I show conditions for last query
    And I wait for 4 seconds
    Then I should see "Query Conditions"
    But I should not see "Error:"

  Scenario: View users
    When I follow "Queries"
    And I follow last query
    And I press button "viewUsersButton" to new window
    Then I should see "user first name"
    And I should see "user last name"
    When I press first number of projects button
    Then I should see "Projects for user"
    And I should see "Project ID"
    And I should see "Title"
    And I should see "Status"
    And I should see "Purpose"

  Scenario: View projects
    When I follow "Queries"
    And I follow last query
    And I press button "viewProjectsButton" to new window
    Then I should see "Projects"
    And I should see "Project ID"
    And I should see "Title"
    And I should see "Status"
    And I should see "Purpose"

