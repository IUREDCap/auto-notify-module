<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule\WebTests;

/**
 * Utility class that has helpful methods.
 */
class RedCapDb
{
    private $db;

    public function __construct()
    {
        $testConfig = new TestConfig(FeatureContext::CONFIG_FILE);

        $dbHostname = $testConfig->getRedCap()['db_hostname'];
        $dbName     = $testConfig->getRedCap()['db_name'];
        $dbUsername = $testConfig->getRedCap()['db_username'];
        $dbPassword = $testConfig->getRedCap()['db_password'];

        $this->db = new mysqli($dbHostname, $dbUsername, $dbPassword, $dbName);

        if ($mysqli->connect_errno) {
            echo "ERROR: connection to REDCap database failed: " . $mysqli->connect_error;
            exit();
        }
    }

    public function getDb()
    {
        return $this->db;
    }
}
