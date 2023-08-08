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
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Log;
use IU\AutoNotifyModule\ModuleLog;
use IU\AutoNotifyModule\RedCapDb;

$selfUrl       = $module->getUrl(AutoNotifyModule::LOG_PAGE);
$logServiceUrl = $module->getUrl(AutoNotifyModule::LOG_SERVICE);

$cssFile = $module->getUrl('resources/notify.css');

$log = new ModuleLog($module);

$logFilter = new Log();

$adminConfig = $module->getAdminConfig();


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

<h5>Log</h5>

<form action="<?php echo $selfUrl;?>" name="testForm" method="post" style="margin-bottom: 17px;">

    <fieldset class="config">
        Start date: 
            <input id="startDate" name="<?php echo Log::START_DATE; ?>"
               value="<?php echo Filter::escapeForHtml($logFilter->getStartDate()); ?>"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        End date:
        <input id="endDate" name="<?php echo Log::END_DATE; ?>"
               value="<?php echo Filter::escapeForHtml($logFilter->getEndDate()); ?>"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        <input type="submit" name="submitValue" id="Display" value="Display"
               style="padding-left: 2em; padding-right: 2em; font-weight: bold;"/>
    </fieldset>

    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>


<table class="data-table">
    <thead>
        <tr>
            <th>Time</th> <th>Log ID</th> <th title="Notification ID">NID</th>
            <th>Subject</th> <th>From</th> <th>To</th> <th>Message</th>
            <th>Settings</th>
        </tr>
        <?php
        $logData = $log->getData($logFilter->getStartDate(), $logFilter->getEndDate());
        foreach ($logData as $key => $entry) {
            echo "<tr>\n";
            echo "<td>{$entry['timestamp']}</td>\n";
            echo "<td style=\"text-align: right;\">{$entry['log_id']}</td>\n";
            echo "<td style=\"text-align: right;\">{$entry['notificationId']}</td>\n";

            echo "<td>{$entry['subject']}</td>\n";

            echo "<td>{$entry['from']}</td>\n";

            # To
            echo '<td style="text-align:center;">'
            . '<input type="image" src="' . APP_PATH_IMAGES . 'group.png" alt="USERS" '
            . ' class="viewToButton" value="' . $entry['log_id'] . '"/>'
            . "</td>\n";


            # Message
            echo '<td style="text-align:center;">';
            echo '<input type="image" src="' . APP_PATH_IMAGES . 'page_white_text.png" alt="VIEW" '
                . ' class="viewMessageButton" value="' . $entry['log_id'] . '"/>';
            echo "</td>\n";

            # Settings
            echo '<td style="text-align:center;">';
            echo '<input type="image" src="' . APP_PATH_IMAGES . 'gear.png" alt="CONFIG" '
                . ' class="viewSettingsButton" value="' . $entry['log_id'] . '"/>';
            echo "</td>\n";

            /*
            echo '<td style="text-align:center;">'
            . "<form action=\"{$selfUrl}\" method=\"post\">\n"
            . '<input type="hidden" name="message" value="' . 'TEST' . '"/>'
            . '<input type="image" src="' . APP_PATH_IMAGES . 'page_white_text.png" alt="MESSAGE" '
            . ' id="message' . $entry['log_id'] . '"'
            . ' style="cursor: pointer;">'
            . "</form>\n"
            . "</td>\n";
             */

            echo "<tr>\n";
        }
        ?>
    </thead>
    <tbody>
    </tbody>
</table>

<p>&nbsp;</p>

<?php
if (isset($adminConfig) && $adminConfig->getDebugMode()) {
    #$logData = $log->getData();
    #foreach ($logData as $entry) {
    #    print "<pre>\n";
    #    print_r($entry);
    #    print "</pre>\n";
    #}
}
?>


<?php
#--------------------------------------
# Display message
#--------------------------------------
?>
<div id="message-dialog"
    title="Message"
    style="display: none;"
    >
    <form id="message-form" action="<?php echo $selfUrl;?>" method="post">
    To delete the notification <span id="notification-to-delete" style="font-weight: bold;"></span>,
    click on the <span style="font-weight: bold;">Delete notification</span> button.
    <input type="hidden" name="deleteNotificationId" id="delete-notification-id" value="">
    </form>
</div>


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
