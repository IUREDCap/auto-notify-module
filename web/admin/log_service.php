<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#----------------------------------------------------------------
# Web service for getting Users for query conditions
#----------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */

# Check that the user has admin permission
$module->checkAdminPagePermission();

require_once __DIR__ . '/../../vendor/autoload.php';

use IU\AutoNotifyModule\Filter;

$logData = 'no data found';
if (array_key_exists('logId', $_POST)) {
    $logId = Filter::sanitizeInt($_POST['logId']);

    $query = "select log_id, timestamp, ui_id, project_id, message, notificationId,"
        . " `from`, `to`, notification, userConditions, schedule, subject, testRun, cronTime";
    $query .= " where log_id = " . Filter::escapeForMysql($logId);

    $queryParameters = [];


    $result = $module->queryLogs($query, $queryParameters);
    $logData = db_fetch_assoc($result);

    $logData = json_encode($logData, JSON_PRETTY_PRINT);
}

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $logData);
