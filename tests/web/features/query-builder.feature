#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Query builder
  In order to create queries for notifications or for querying the REDCap database
  As an admin
  I need to be able to be able to use the query builder to create and run queries

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Create new Query with project conditions
    When I follow "Queries"
    And I follow "Saved Queries"
    And I delete queries with name "behat projects query 1"
    And I follow "Query Builder"
    And I fill in "Query Name:" with "behat projects query 1"
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
    
  Scenario: Create new Query with user conditions
    When I follow "Queries"
    And I follow "Saved Queries"
    And I delete queries with name "behat users query 1"
    And I follow "Query Builder"
    And I fill in "Query Name:" with "behat users query 1"
    And I add condition to group number 1
    And I set condition number 1 to "User Suspended Time" "is" "NULL"
    And I add condition to group number 1
    And I set condition number 2 to "User Expiration" "is" "NULL"
    And I press "Save"
    Then I should see "Query saved."
    But I should not see "REDCap crashed"

    When I press button "viewUsersButton" to new window
    And I select "All" from "userTable_length"
    Then I should see "Users"
    And I should see "username"
    And  table column "suspended time" should contain only ""
    And  table column "expiration" should contain only ""
