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
use IU\AutoNotifyModule\ScheduleFilter;

try {
    $error   = '';
    $warning = '';
    $success = '';

    $selfUrl         = $module->getUrl(AutoNotifyModule::SCHEDULE_PAGE);
    $notificationUrl = $module->getUrl(AutoNotifyModule::NOTIFICATION_PAGE);

    $notificationServiceUrl = $module->getUrl(AutoNotifyModule::NOTIFICATION_SERVICE);

    $cssFile = $module->getUrl('resources/notify.css');

    $scheduleFilter = new ScheduleFilter();

    $notifications = $module->getNotifications();

    // Only active notifications will be scheduled to be sent in the future
    $activeNotifications = $notifications->getActiveNotifications();

    $notificationId = 0; // All notifications

    #------------------------------------------------------
    # Set the default start and end dates
    #------------------------------------------------------
    $startDateInfo = new DateInfo();
    $endDateInfo   = new DateInfo($startDateInfo->getTimestamp());
    $endDateInfo->modify("+1 year");

    $startDate = $startDateInfo->getMdyDate();
    $endDate   = $endDateInfo->getMdyDate();

    #-------------------------
    # Get the submit value
    #-------------------------
    $submitValue = '';
    if (array_key_exists('submitValue', $_POST)) {
        $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);

        if ($submitValue === 'Display') {
            $scheduleFilter->set($_POST);
            $notificationId = $scheduleFilter->getNotificationId();

            $startDate = $scheduleFilter->getStartDate();
            $startDateInfo->modify($startDate);

            $endDate   = $scheduleFilter->getEndDate();
            $endDateInfo->modify($endDate);

            $scheduleFilter->validate();
        }
    }

    $endDateInfo->modify("23:59");
    $startTimestamp = $startDateInfo->getTimestamp();
    $endTimestamp   = $endDateInfo->getTimestamp();
} catch (\Exception $exception) {
    $error = 'ERROR: ' . $exception->getMessage();
}

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

?>

<h5>Notification Schedule</h5>

<form action="<?php echo $selfUrl;?>" id="scheduleForm" method="post" style="margin-bottom: 17px;">

    <fieldset class="config">

        <!-- DISPLAY MODE -->
        <div style="margin-bottom: 14px;">
            <?php
            $listChecked = '';
            $calendarChecked = '';
            if ($scheduleFilter->getDisplayMode() == ScheduleFilter::CALENDAR_DISPLAY_MODE) {
                $calendarChecked = ' checked="checked" ';
            } else {
                $listChecked = ' checked="checked" ';
            }
            ?>

            Display mode: 
            <input type="radio" name="<?php echo ScheduleFilter::DISPLAY_MODE; ?>"
                                value="<?php echo ScheduleFilter::LIST_DISPLAY_MODE; ?>"
                                class="scheduleFilterInput"
                                <?php echo $listChecked; ?>>
            list

            <input type="radio" name="<?php echo ScheduleFilter::DISPLAY_MODE; ?>"
                                value="<?php echo ScheduleFilter::CALENDAR_DISPLAY_MODE; ?>"
                                class="scheduleFilterInput"
                                <?php echo $calendarChecked; ?>>
            calendar
        </div>


        <!-- NOTIFICATION -->
        <div style="margin-bottom: 14px;">
            <?php
            echo "Scheduled Notification(s):\n";
            echo '<select class="scheduleFilterInput" name="' . ScheduleFilter::NOTIFICATION_ID . '">' . "\n";

            if ($scheduleFilter->getNotificationId() == 0) {
                echo '<option value="0" selected>ALL</options>' . "\n";
            } else {
                echo '<option value="0">ALL</options>' . "\n";
            }

            foreach ($activeNotifications as $notification) {
                $id = $notification->getId();
                $subject = $notification->getSubject();

                $selected = '';
                if ($id == $scheduleFilter->getNotificationId()) {
                    $selected = ' selected';
                }

                echo '<option value="' . $id . '"' . $selected . '>' . $subject . ' [ID=' . $id . ']</option>' . "\n";
            }
            echo "</select>\n";
            ?>
        </div>

        <!-- START DATE -->
        Start date:
        <input id="startDate" name="<?php echo ScheduleFilter::START_DATE; ?>"
               value="<?php echo Filter::escapeForHtml($startDate); ?>"
               class="scheduleFilterInput"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        End date:
        <input id="endDate" name="<?php echo ScheduleFilter::END_DATE; ?>"
               value="<?php echo Filter::escapeForHtml($endDate); ?>"
               class="scheduleFilterInput"
               type="text" size="10" style="text-align: right; margin-right: 1em;"/>

        <input type="hidden" name="submitValue" id="Display" value="Display"/>
        <!--
        <input type="submit" name="submitValue" id="Display" value="Display"
               style="padding-left: 2em; padding-right: 2em; font-weight: bold;"/>
-->
    </fieldset>

    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>


<?php

$now = new \DateTime('now');
$monthName = $now->format('F');
$month = $now->format('m');
$year = $now->format('Y');
$schedule = $notifications->getScheduledNotifications($startTimestamp, $endTimestamp, $notificationId);

?>

<?php
if ($scheduleFilter->getDisplayMode() != ScheduleFilter::CALENDAR_DISPLAY_MODE) {
    ?>
    <!-- LIST DISPLAY -->
    <table class="data-table">
        <tr>
            <th>Date</th> <th>Time</th> <th>Week Day</th> <th>Notification ID</th> <th>Subject</th>
            <th>Settings</th>
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

            # Schedule
            echo '<td style="text-align:center;">';
            echo '<input type="image" src="' . APP_PATH_IMAGES . 'calendar_task.png" alt="CONFIG" '
                . ' class="viewScheduleButton" value="' . $id . '"/>';
            echo "</td>\n";

            echo "</tr>\n";
        }
        ?>
    </table>

    <?php
} else {
    ?>
    <!-- CALENDAR DISPLAY -->
    <?php
    $year = 0;
    $month = 0;
    foreach ($schedule as $timestamp => $notification) {
        $previousYear = $year;
        $previousMonth = $month;
        $dateInfo = new DateInfo($timestamp);
        $dayOfWeek = $dateInfo->getDayOfWeekName();
        $date = $dateInfo->getYmdDate();
        $time = $dateInfo->getTime();
        $year = $dateInfo->getYear();
        $month = $dateInfo->getMonth();
        $day = $dateInfo->getDay();
        $monthName = $dateInfo->getMonthName();

        $id = $notification->getId();
        $editUrl = $notificationUrl . '&notificationId=' . Filter::escapeForUrlParameter($id);

        if ($year != $previousYear) {
            if ($previousYear != 0) {
                echo "</table>\n";
                echo "<hr style=\"height: 4px; background-color: black; margin-top: 24px;\"/>\n";
            }
            echo '<p style="text-align: center; margin-top: 22px; margin-bottom: 0px;">'
                . '<span style="font-weight: bold; font-size: 140%;">'
                . $year . '</span></p>';
        }

        if ($month != $previousMonth) {
            if ($previousMonth != 0) {
                echo "</table>\n";
            }

            echo "<table class=\"data-table\" style=\"margin: 17px auto;\">\n";
            echo "<tr><th colspan=\"6\" style=\"font-size: 110%;\">{$monthName}</th></tr>\n";
            echo "<tr> <th>Day</th> <th>Time</th> <th>Week Day</th> <th>Notification Id</th>"
                . "<th>Subject</th> <th>Settings</th></tr>\n";
        }

        echo "<tr>";
        echo "<td style=\"text-align: right;\">{$day}</td>";
        echo "<td style=\"text-align: right;\">{$time}</td><td>{$dayOfWeek}</td>\n";
        echo "<td style=\"text-align: right;\"><a href=\"{$editUrl}\">{$id}</a></td>";
        echo "<td>{$notification->getSubject()}</td>";

        # Schedule
        echo '<td style="text-align:center;">';
        echo '<input type="image" src="' . APP_PATH_IMAGES . 'calendar_task.png" alt="CONFIG" '
            . ' class="viewScheduleButton" value="' . $id . '"/>';
        echo "</td>\n";

        echo "</tr>\n";
    }

    if (!empty($notifications)) {
        echo "</table>\n";
    }
    ?>

    <?php
}
?>

<script>

    $(document).ready(function() {

        $( "#startDate" ).datepicker({ minDate: 0 }).datepicker();
        $( "#endDate" ).datepicker({ minDate: 0 }).datepicker();

        $(".scheduleFilterInput").on("change", function() {
            $("#scheduleForm").submit();
        });

        $(".viewScheduleButton").on("click", function() {
            let notificationId = $(this).attr('value');

            jQuery.post("<?php echo $notificationServiceUrl?>", {notificationId: notificationId}, function(data) {
                let dataObj = jQuery.parseJSON(data);
                let schedule = dataObj['schedule'];

                let settings = '';

                settings += '<p><span style="font-weight: bold;">Schedule:</span></p>' + "\n";
                settings += "<pre>" + schedule + "</pre>\n";
               
                //alert("Settings: " + JSON.stringify(dataObj));

                //$( '<div id="showLogTo" style="background-color: #E9F1F8;">' + toDataString + '</div>' ).dialog({
                $( '<div id="showSchedule"">' + settings + '</div>' ).dialog({
                    title: 'Schedule for Notification wth ID ' + notificationId,
                    resizable: true,
                    height: "auto",
                    maxHeight: 600,
                    width: 440,
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
