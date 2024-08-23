<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#-----------------------------------------------------------------------------
# Web service for getting notification data for the specified notification ID
#-----------------------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */

# Check that the user has admin permission
$module->checkAdminPagePermission();

require_once __DIR__ . '/../../vendor/autoload.php';

use IU\AutoNotifyModule\Filter;

$notificationData = array();
if (array_key_exists('notificationId', $_POST)) {
    $notificationId = Filter::sanitizeInt($_POST['notificationId']);

    $notification = $module->getNotification($notificationId);

    $notificationData['subject']  = $notification->getSubject();
    $notificationData['schedule'] = $notification->getSchedule()->toString();

    $notificationData = json_encode($notificationData, JSON_PRETTY_PRINT);
}

# Send back response to web service client
$fh = fopen('php://output', 'w');
fwrite($fh, $notificationData);
