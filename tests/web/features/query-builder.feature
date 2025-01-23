#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Query builder
  In order to create queries for notifications
  As an admin
  I need to be able to be able to use the query builder to create and run queries

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Create new Query
    When I follow "Queries"
    And I follow "Saved Queries"
    And I delete queries with name "Query creation web test"
    And I follow "Query Builder"
    And I fill in "Query Name:" with "Query creation web test"
    And I add condition to group number 1
    And I set condition number 1 to "Project Status" "=" "Development"
    And I add condition to group number 1
    And I set condition number 2 to "Project is Online" "=" "true"
    And I add condition to group number 1
    And I set condition number 3 to "Project is Longitudinal" "=" "false"
    And I press "Save"
    Then I should see "Query saved."
    But I should not see "REDCap crashed"

    When I press button "viewProjectsButton" to new window
    And I select "All" from "projectsTable_length"
    Then I should see "Projects"
    And I should see "Project ID"
    And  table column "Status" should contain only "Development"
    And  table column "Is Online" should contain only "yes"
    And  table column "Is Longitudinal" should contain only "no"
    
    #  Scenario: Show SQL query
    #When I follow "Queries"
    #And I follow "Saved Queries"
    #And I follow last query
    #And I press "Show SQL Query"
    #And I wait for 2 seconds
    #Then I should see "SELECT DISTINCT"
    #And I should see "FROM"
    #And I should see "JOIN"
    #And I should see "WHERE"

    #  Scenario: Show conditions
    #When I follow "Queries"
    #And I follow "Saved Queries"
    #And I follow last query
    #And I press button "showConditionsButton" to new window
    #Then I should see "Query Conditions"
    #But I should not see "Error:"

    #  Scenario: Show queries conditions
    #When I follow "Queries"
    #And I follow "Saved Queries"
    #And I show conditions for last query
    #And I wait for 4 seconds
    #Then I should see "Query Conditions"
    #But I should not see "Error:"

    #  Scenario: View users
    #When I follow "Queries"
    #And I follow "Saved Queries"
    #And I follow last query
    #And I press button "viewUsersButton" to new window
    #Then I should see "user first name"
    #And I should see "user last name"
    #When I press first number of projects button
    #Then I should see "Projects for user"
    #And I should see "Project ID"
    #And I should see "Title"
    #And I should see "Status"
    #And I should see "Purpose"

    #  Scenario: View projects
    #When I follow "Queries"
    #And I follow "Saved Queries"
    #And I follow last query
    #And I press button "viewProjectsButton" to new window
    #Then I should see "Projects"
    #And I should see "Project ID"
    #And I should see "Title"
    #And I should see "Status"
    #And I should see "Purpose"

