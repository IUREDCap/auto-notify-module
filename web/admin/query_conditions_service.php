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

use IU\AutoNotifyModule\Query;

$queryId = null;

$conditions = 'no data found';

$variables = $module->getVariables();

if (array_key_exists('queryId', $_POST)) {
    $queryId = $_POST['queryId'];
    $query = $module->getQuery($queryId);
    if ($query != null) {
        $conditions = $query->getConditions()->toString($variables, 0);
    }
}


# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $conditions);
