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
use IU\AutoNotifyModule\UsersSpecification;

$usersSpecification = new UsersSpecification();
$usersSpecification->set($_POST);

$query = $usersSpecification->toQuery($module);
$conditions = $query->getConditions();

if (!empty($conditions)) {
    $jsonConditions = $conditions->toJson();
} else {
    $jsonConditions = 'no data found';
}

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $jsonConditions);
