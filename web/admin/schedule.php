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

$selfUrl         = $module->getUrl(AutoNotifyModule::SCHEDULE_PAGE);
$notificationUrl = $module->getUrl(AutoNotifyModule::NOTIFICATION_PAGE);
$logServiceUrl   = $module->getUrl(AutoNotifyModule::LOG_SERVICE);

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

$module->renderAdminPageContentHeader($notificationUrl, $error, $warning, $success);
$module->renderAdminNotificationSubTabs($selfUrl);


$users = null;

$prefix = "auto-notify-module";
$config = ExternalModules::getConfig($prefix);


#-------------------------
# Get the submit value
#-------------------------
$startDateInfo = new DateInfo();
$endDateInfo   = new DateInfo($startDateInfo->getTimestamp());
$endDateInfo->modify("+1 year");

$startDate = $startDateInfo->getMdyDate();
$endDate   = $endDateInfo->getMdyDate();

$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}

if ($submitValue === 'Display') {
    if (array_key_exists('startDate', $_POST)) {
        $startDate = $_POST['startDate'];
    }

    if (array_key_exists('endDate', $_POST)) {
        $endDate = $_POST['endDate'];
        // NEED TO ADD CHECKING HERE AND FOR START DATE!!!!!!!!!!!!!!!!!!!!!!!!!!
        $endDateInfo->modify($endDate);
    }
}

$endDateInfo->modify("23:59");
$startTimestamp = $startDateInfo->getTimestamp();
$endTimestamp   = $endDateInfo->getTimestamp();

?>

<h5>Notification Schedule</h5>

<form action="<?php echo $selfUrl;?>" name="testForm" method="post" style="margin-bottom: 17px;">

    <fieldset class="config">
        Start date:
            <input id="startDate" name="startDate"
               value="<?php echo Filter::escapeForHtml($startDate); ?>"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        End date:
        <input id="endDate" name="endDate"
               value="<?php echo Filter::escapeForHtml($endDate); ?>"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        <input type="submit" name="submitValue" id="Display" value="Display"
               style="padding-left: 2em; padding-right: 2em; font-weight: bold;"/>
    </fieldset>

    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>


<?php

$now = new \DateTime('now');
$monthName = $now->format('F');
$month = $now->format('m');
$year = $now->format('Y');
$schedule = $notifications->getScheduledNotifications($startTimestamp, $endTimestamp);

?>

<table class="data-table">
    <tr>
        <th>Date</th> <th>Time</th> <th>Week Day</th> <th>Notification ID</th> <th>Subject</th>
    </tr>

    <?php
    foreach ($schedule as $timestamp => $notification) {
        $dateInfo = new DateInfo($timestamp);
        $dayOfWeek = $dateInfo->getDayOfWeekName();
        $date = $dateInfo->getYmdDate();
        $time = $dateInfo->getTime();

        $id = $notification->getId();
        $editUrl = $notificationUrl . '&notificationId=' . Filter::escapeForUrlParameter($id);

        echo "<tr>";
        echo "<td>{$date}</td>";
        echo "<td>{$time}</td>";
        echo "<td>{$dayOfWeek}</td>";
        echo "<td style=\"text-align: right;\"><a href=\"{$editUrl}\">{$id}</a></td>";
        echo "<td>{$notification->getSubject()}</td>";
        echo "</tr>\n";
    }
    ?>
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
