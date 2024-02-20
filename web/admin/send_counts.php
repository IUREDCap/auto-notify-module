<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#------------------------------------------------------------
# Test page for running notification send tests for a
# specified data range
#------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */


#---------------------------------------------
# Check that the user has access permission
#---------------------------------------------
$module->checkAdminPagePermission();


use ExternalModules\ExternalModules;
use IU\AutoNotifyModule\AutoNotifyModule;
use IU\AutoNotifyModule\Conditions;
use IU\AutoNotifyModule\Config;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\RedCapDb;

$selfUrl   = $module->getUrl(AutoNotifyModule::USERS_PAGE);
$conditionsServiceUrl = $module->getUrl(AutoNotifyModule::CONDITIONS_SERVICE);
$userProjectsUrl = $module->getUrl(AutoNotifyModule::USER_PROJECTS_PAGE);


?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();

$htmlPage = new HtmlPage();
$htmlPage->PrintHeaderExt();
include APP_PATH_VIEWS . 'HomeTabs.php';

# require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
# $cssFile = $module->getUrl('resources/notify.css');
// $cssFile = $module->getUrl('resources/table.css');
// $link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">';
// $buffer = str_replace('</head>', "    " . $link . "\n</head>", $buffer);
$cssFile = $module->getUrl('resources/table.css');
$buttonsCssFile = $module->getUrl('resources/buttons.dataTables.css');
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">'
    . "\n"
    . '<link href="' . $buttonsCssFile . '" rel="stylesheet" type="text/css" media="all">'
    ;
$jsInclude =
    '<script type="text/javascript" src="' . ($module->getUrl('resources/dataTables.buttons.min.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/buttons.html5.min.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/buttons.colVis.min.js')) . '"></script>'
    . "\n"
;
$buffer = str_replace('</head>', "    {$link}\n{$jsInclude}\n</head>\n", $buffer);

echo $buffer;

$notificationId = null;
if (array_key_exists('notificationId', $_POST)) {
    $notificationId = Filter::sanitizeInt($_POST['notificationId']);
}

$notification = null;
$userCountMap = null;

try {
    if (!empty($notificationId)) {
        $notification = $module->getNotification($notificationId);
        $userCountMap = $notification->getUserCountMap();
        if (!isset($notification)) {
            throw new \Exception("Notification with ID = {$notificationId} not found.");
        }
    }
} catch (\Exception $exception) {
    $error = 'Error: ' . $exception->getMessage();
}

?>

<div style="margin-top: 60px;">
<div>

<?php
# print "<pre>\n";
# print_r($_POST);
# print("ERROR: " . $error);
# print_r($userCountMap);
# print "</pre>\n";
?>

<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php
$module->renderAdminMessageHeader($error, $warning, $success);
?>


<div style="font-weight: bold; font-size: 120%; text-align: center;">
<?php if (empty($error) && !empty($notification)) { ?>
    <?php
    echo '<h4>Send Counts for Notification "'
        . $notification->getSubject() . '" (ID = ' . $notification->getId()
        . ")</h4>\n";
    ?>
    </div>
    <div id="resultsDisplay" style="margin-top: 17px; padding: 5px; border: 1px solid #777777;">
            <div>
                <div id="colVis" style="float: left;"></div>
                <div id="sendCountsTableButtons" style="float: right; margin-bottom: 7px;">
                </div>
                <div style="clear: both;"></div>
            </div>

            <div id="sendCountsTableDiv" style="display: none;">
                <table id="sendCountsTable" style="white-space: nowrap;">
                    <thead>
                        <tr> 
                            <th>username</th> <th>send count</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (!empty($userCountMap)) {
                            foreach ($userCountMap as $user => $sendCount) {
                                $username = $user;

                                echo "<tr>";
                                echo '<td><a href="' . APP_PATH_WEBROOT . 'ControlCenter/view_users.php?username='
                                    . $username . '" target="_blank">' . $username . '</a>';
                                echo "<td style=\"text-align: right;\">{$sendCount}</td>";
                                echo "</tr>\n";
                            }
                        }
                        ?>
                    </tbody>

                </table>
            </div>

            <script>
            $(document).ready(function() {

                $("#sendCountsTable").DataTable({
                    "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]],
                    "iDisplayLength": 10,
                    // dom: 'Bfrtip',
                    scrollX: true,
                    initComplete: function () {
                        var api = this.api();
                        $('#sendCountsTableDiv').show();
                        api.columns.adjust();
                    }
                });

                $("#sendCountsTable").show();

                var buttons = new $.fn.dataTable.Buttons($("#sendCountsTable"), {
                    buttons: [
                        {
                            extend: 'csv',
                            filename: 'send_counts',
                            text: '<i class="fa fa-file-arrow-down"></i> CSV Download',
                            className: 'sendCountsTable',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ]
                }).container().appendTo($('#sendCountsTableButtons'));

                var buttons2 = new $.fn.dataTable.Buttons($("#sendCountsTable"), {
                    buttons: [
                        {
                            extend: 'colvis',
                            text: '<i class="fa fa-table-columns"></i> Show/Hide Columns',
                            className: 'sendCountsTable'
                        }
                    ]
                }).container().appendTo($('#colVis'));

            });
            </script>
    </div>

    <?php
} else {
    echo "<h4>Send Counts for Notification</h4>\n";
    echo "<p>This notification has not been sent.</p>\n";
}
?>



<!-- START OF FOOTER -->

<?php $htmlPage->PrintFooterExt(); ?>
