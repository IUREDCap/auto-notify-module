#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: Notifications
  In order to execute admin actions
  As an admin
  I need to be able to use the Notifications Pages

  Background:
    Given I am on "/"
    When I access the admin interface

  Scenario: Access the Notifications page
    When I follow "Notifications"
    When I follow "Saved Notifications"
    Then I should see "Notifications"
