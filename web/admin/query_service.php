<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#----------------------------------------------------------------
# Web service for getting SQL query for query conditions
#----------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */

# Check that the user has admin permission
$module->checkAdminPagePermission();

require_once __DIR__ . '/../../vendor/autoload.php';

use IU\AutoNotifyModule\Conditions;
use IU\AutoNotifyModule\Query;

$jsonConditions = '';
if (array_key_exists('jsonConditions', $_POST)) {
    $jsonConditions = $_POST['jsonConditions'];
} else {
    $jsonConditions = 'no data found';
}

$variables = $module->getVariables();

$sqlQuery = '';

$conditions = new Conditions();
$conditions->setFromJson($jsonConditions);
try {
    $conditions->validate($variables);
    $sqlQuery = Query::queryConditionsToSql($variables, $jsonConditions);
} catch (\Exception $exception) {
    $sqlQuery = 'Error: ' . $exception->getMessage();
}


//$sqlQuery = $jsonConditions;
// $sqlQuery = print_r($_POST, true);

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $sqlQuery);
