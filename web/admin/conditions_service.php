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

$jsonConditions = '';
if (array_key_exists('jsonConditions', $_POST)) {
    $jsonConditions = $_POST['jsonConditions'];
    $conditions = new Conditions();
    $conditions->setFromJson($jsonConditions);

    $variables = $module->getVariables();
    $conditionsString = $conditions->toString($variables);
} else {
    $conditionsString = 'no data found';
}

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $conditionsString);
