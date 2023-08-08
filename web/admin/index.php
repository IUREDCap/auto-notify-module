<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */


#---------------------------------------------
# Check that the user has access permission
#---------------------------------------------
$module->checkAdminPagePermission();

use ExternalModules\ExternalModules;
use IU\AutoNotifyModule\AutoNotifyModule;

$selfUrl   = $module->getUrl(AutoNotifyModule::ADMIN_HOME_PAGE);

$cssFile = $module->getUrl('resources/notify.css');


?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
$cssFile = $module->getUrl('resources/notify.css');
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">';
$buffer = str_replace('</head>', "    " . $link . "\n</head>", $buffer);
echo $buffer;
?>



<h4>
<i class="fas fa-envelope"></i>&nbsp;
<!-- <img style="margin-right: 7px;" src="<?php echo APP_PATH_IMAGES ?>email.png" alt=""> -->
Auto-Notify
</h4>


<?php
$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);
?>

<p>
The Auto-Notify external module allows admins to send e-mail notifications to users.
</p>
<p>
This module is similar to the E-mail Users functionality
built in to REDCap, but has the following additional features:
<ul>
<li> Scheduling of notifications to be sent at a future time and recurringly.</li>
<li> Selection of different sets of users as recipients (e.g., users of a specific external module).</li>
<li> Use of variables in notification messages, e.g., [last_name] for the user's last name.</li>
</ul>
</p>

<p>
This module provides a query builder that can be used to construct
custom queries for generating sets of users for notifications.
</p>

<table class="data-table">
    <tr>
        <th>Tab</th> <th>Page Description</th>
    </tr>
    <tr>
        <td><i class="fas fa-info-circle"></i> Info</td>
        <td>
        Overview of this module.
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-gear"></i> Config</td>
        <td>
        Configuration information and settings for this module.
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-check-square"></i> Test</td>
        <td>
        Page for testing the sending of notifications.
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-envelope"></i>&nbsp;Notification</td>
        <td>
        Page for creating and editing notifications.
        </td>
    </tr>

    <tr>
        <td style="width: 10em;"><i class="fas fa-bars"></i>&nbsp;Notifications</span></td>
        <td>
        Page that lists the notifications that have been created.
        </td>
    </tr>

    <tr>
        <td style="vertical-align: top;"><i class="fas fa-file-code"></i> Query</td>
        <td>
        Query builder for creating and editing custom queries that are used to
        generate lists of REDCap users for use as the recipients of notifications.
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-bars"></i> Queries</td>
        <td>
        Page that lists the queries that have been created.
        </td>
    </tr>
</table>

<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
