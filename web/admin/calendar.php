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
use IU\AutoNotifyModule\DateInfo;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\ModuleLog;
use IU\AutoNotifyModule\RedCapDb;

$selfUrl       = $module->getUrl(AutoNotifyModule::CALENDAR_PAGE);
$logServiceUrl = $module->getUrl(AutoNotifyModule::LOG_SERVICE);

$cssFile = $module->getUrl('resources/notify.css');

$log = new ModuleLog($module);

$adminConfig = $module->getAdminConfig();

$notifications = $module->getNotifications();


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
$jsInclude = '<script type="text/javascript" src="' . ($module->getUrl('resources/log.js')) . '"></script>';
$buffer = str_replace('</head>', "    {$link}\n{$jsInclude}\n</head>", $buffer);
echo $buffer;
?>



<h4>
<i class="fas fa-envelope"></i>&nbsp;
<!-- <img style="margin-right: 7px;" src="<?php echo APP_PATH_IMAGES ?>email.png" alt=""> -->
Auto-Notify
</h4>

<?php

$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);

$users = null;

$prefix = "auto-notify-module";
$config = ExternalModules::getConfig($prefix);


#-------------------------
# Get the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}

if ($submitValue === 'Display') {
    $logFilter->set($_POST);
}


?>

<h5>Calendar</h5>

<?php
   $now = new \DateTime('now');
   $monthName = $now->format('F');
   $month = $now->format('m');
   $year = $now->format('Y');

   echo "<div style=\"text-align: center; font-size: 120%; font-weight: bold; margin-bottom: 17px;\">";

   echo '<button style="margin-right: 1em;" id="previousYear"><i class="fa fa-angle-double-left"></i></button>';
   echo '<button style="margin-right: 1em;" id="previousMonth"><i class="fa fa-angle-left"></i></button>';

   echo "{$monthName} {$year}";

   echo '<button style="margin-left: 1em;" id="nextMonth"><i class="fa fa-angle-right"></i></button>';
   echo '<button style="margin-left: 1em;" id="nextYear"><i class="fa fa-angle-double-right"></i></button>';

   echo "</div>\n";
?>

<?php

$schedule = $notifications->getScheduledNotifications(strtotime("2023-10-17 00:00"), strtotime("2026-10-17 24:00"));
print "<pre>\n";
foreach ($schedule as $timestamp => $notification) {
    $dateInfo = new DateInfo($timestamp);
    $dayOfWeek = $dateInfo->getDayOfWeekName();
    print(
        $dayOfWeek . ' ' . DateInfo::timestampToString($timestamp)
        . " " . $notification->getId() . " " . $notification->getSubject() . "\n"
    );
}
print "</pre>\n";

?>

<table class="data-table calendar-table">
    <tr>
        <th>Sunday</th> <th>Monday</th> <th>Tuesday</th> <th>Wednesday</th>
        <th>Thursday</th> <th>Friday</th> <th>Saturday</th>
    </tr>
    <tr>
        <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td>
    </tr>
    <tr>
        <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td>
    </tr>
    <tr>
        <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td>
    </tr>
    <tr>
        <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td>
    </tr>
    <tr>
        <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td> <td> </td>
    </tr>
</table>



<script>

    $(document).ready(function() {

        $( function() {
            $( "#startDate" ).datepicker();
        } );

        $( function() {
            $( "#endDate" ).datepicker();
        } );

        $(".viewMessageButton").on("click", function() {
            let logId = $(this).attr('value');

            jQuery.post("<?php echo $logServiceUrl?>", {logId: logId}, function(data) {

                let dataObj = jQuery.parseJSON(data);

                $( '<div id="showLogMessage"><pre>' + dataObj.notification + '</pre></div>' ).dialog({
                    title: 'Message for Notification "' + dataObj.subject + '"',
                    resizable: false,
                    height: "auto",
                    width: 600,
                    modal: false,
                    buttons: {
                        Close: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            });

            event.preventDefault();
        });

        $(".viewToButton").on("click", function() {
            let logId = $(this).attr('value');

            jQuery.post("<?php echo $logServiceUrl?>", {logId: logId}, function(data) {

                let toDataString = "<table class=\"data-table\" style=\"margin-left: auto; margin-right: auto;\">\n";

                let dataObj = jQuery.parseJSON(data);

                let toData = [];
                if (dataObj.to != '') {
                    toData = dataObj.to.split(",");
                }

                toDataString += "<tr><th>username</th><th>e-mail</th><th>send status</th><th>send count</th></tr>\n";
                for (let i = 0; i < toData.length; i++) {
                    let [username, email, count, sendStatus] = toData[i].trim().split(" ", 4);

                    if (sendStatus == null) {
                        sendStatus = "";
                    }
                    else if (sendStatus == 1) {
                        sendStatus = "success";
                    }
                    else if (sendStatus == 0) {
                        sendStatus = "fail";
                    }

                    if (count == null) count = '';
                    toDataString += "<tr>";
                    toDataString += "<td>" + username + "</td>";
                    toDataString += "<td>" + email + "</td>";
                    toDataString += '<td>' + sendStatus + "</td>";
                    toDataString += '<td style="text-align: right;">' + count + "</td>";
                    toDataString += "</tr>\n";
                }
                toDataString += "</table>";

                //$( '<div id="showLogTo" style="background-color: #E9F1F8;">' + toDataString + '</div>' ).dialog({
                $( '<div id="showLogTo"">' + toDataString + '</div>' ).dialog({
                    title: 'To Users for Notification "' + dataObj.subject + '"',
                    resizable: false,
                    height: "auto",
                    width: 600,
                    modal: false
                    //buttons: {
                        //Close: function() {
                        //    $( this ).dialog( "close" );
                        //}
                    //}
                });
            });

            event.preventDefault();
        });

        $(".viewSettingsButton").on("click", function() {
            let logId = $(this).attr('value');

            jQuery.post("<?php echo $logServiceUrl?>", {logId: logId}, function(data) {
                let dataObj = jQuery.parseJSON(data);
                let schedule = dataObj.schedule;
                let userConditions = dataObj.userConditions;
                let testRun = dataObj.testRun;
                let cronTime = dataObj.cronTime;

                let settings = '';

                if (cronTime != null && cronTime != '') {
                    settings += '<p><span style="font-weight: bold;">Cron Time:</span></p>' + "\n";
                    settings += "<pre>" + cronTime + "</pre>\n";
                }

                settings += '<p><span style="font-weight: bold;">Schedule:</span></p>' + "\n";
                settings += "<pre>" + schedule + "</pre>\n";
               
                settings += '<p><span style="font-weight: bold;">User Conditions:</span></p>' + "\n";
                settings += "<pre>" + userConditions + "</pre>\n";

                if (testRun != null && testRun != '') {
                    settings += '<p><span style="font-weight: bold;">Test Configuration:</span></p>' + "\n";
                    settings += "<pre>" + testRun + "</pre>\n";
                }

                //alert("Settings: " + JSON.stringify(dataObj));

                //$( '<div id="showLogTo" style="background-color: #E9F1F8;">' + toDataString + '</div>' ).dialog({
                $( '<div id="showLogSettings"">' + settings + '</div>' ).dialog({
                    title: 'Settings for Notification "' + dataObj.subject + '"',
                    resizable: false,
                    height: "auto",
                    maxHeight: 600,
                    width: 720,
                    modal: false
                    //buttons: {
                        //Close: function() {
                        //    $( this ).dialog( "close" );
                        //}
                    //}
                });
            });

            event.preventDefault();
        });
    });

</script>

<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
