<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#----------------------------------------------------------------
# Web service for string value for JSON query conditions
#----------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */

# Check that the user has admin permission
$module->checkAdminPagePermission();

require_once __DIR__ . '/../../vendor/autoload.php';

use IU\AutoNotifyModule\Conditions;
use IU\AutoNotifyModule\Query;
use IU\AutoNotifyModule\UsersSpecification;

$usersSpecification = new UsersSpecification();
$usersSpecification->set($_POST);

$query = $usersSpecification->toQuery($module);
$conditions = $query->getConditions();

$variables = $module->getVariables();

$sqlQuery = '';

try {
    $conditions->validate($variables);
    $jsonConditions = $conditions->toJson();
    $sqlQuery = Query::queryConditionsToSql($variables, $jsonConditions);
} catch (\Exception $exception) {
    $sqlQuery = 'Error: ' . $exception->getMessage();
}


//$sqlQuery = $jsonConditions;
// $sqlQuery = print_r($_POST, true);

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $sqlQuery);

