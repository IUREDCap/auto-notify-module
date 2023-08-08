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
use IU\AutoNotifyModule\Config;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\Notification;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\TestRun;

$selfUrl   = $module->getUrl(AutoNotifyModule::TEST_PAGE);

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

$lastRunTimestamp = $module->getLastRunTime();
$dateTime = new DateTime();
$dateTime->setTimestamp($lastRunTimestamp);
$lastRunTime = $dateTime->format('Y-m-d H:i:s');

$version = $module->getVersion();

$moduleConfig = $module->getConfig();
$cron = $moduleConfig['crons'][0];
$cronFrequency = $cron['cron_frequency'];

$adminConfig = $module->getAdminConfig();
$notifications = $module->getNotifications();

$testRun = new TestRun();

#-------------------------
# Get the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
     $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}

if ($submitValue === 'Run') {
    $testRun->set($_POST);
    try {
        $testRun->validate();
        $startDate = $testRun->getStartDate();
        $endDate   = $testRun->getEndDate();

        $startTimestamp = strtotime($startDate . ' 00:00:00');
        $endTimestamp   = strtotime($endDate . ' 24:00:00');

        $lastTimestamp = $startTimestamp;

        if (empty($cronFrequency) || $cronFrequency <= 0) {
            $cronFrequency = 900; // Default to 15 minutes
        }

        #print("<pre>\n");
        for ($timestamp = $startTimestamp + $cronFrequency; $timestamp <= $endTimestamp; $timestamp += $cronFrequency) {
            #print("\n" . date('Y-m-d H:i:s', $timestamp) . "\n");
            $module->processNotifications($lastTimestamp, $timestamp, $testRun);
            $lastTimestamp = $timestamp;
        }
        #print("</pre>\n");

        $success = 'Tests completed.';
    } catch (\Exception $exception) {
        $error = $exception->getMessage();
    }
} else {
    # Set the default test e-mail to the current user's e-mail
    $userEmail = $module->getUser()->getEmail();
    $testRun->setTestEmail($userEmail);
}

?>



<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php

$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);

#print("<hr/> start timestamp: {$startTimestamp}\n");
#print("<hr/> end timestamp: {$endTimestamp}\n");
#print("<hr/> cron frequency: {$cronFrequency}\n");
#for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += $cronFrequency) {
    # error_log("Timestamp: {$timestamp}\n", 3, __DIR__ . '/timestamp-log.txt');
    # print ("<pre>{$timestamp}</pre>\n");
# }

# print "<pre>\n";
# print_r($module->getDb()->getExternalModuleInfoMap());
# print "</pre>\n";
?>


<?php

if ($adminConfig->getDebugMode()) {
    #print "<p>CRON FREQUENCY: {$cronFrequency}</p>\n";
    #print "<pre>\n";
    #print_r($testRun);
    #print "</pre>\n";
}

#print "<pre>\n";
#print(APP_PATH_DOCROOT);
#print "</pre>\n";
?>

<form action="<?php echo $selfUrl;?>" name="testForm" method="post">

    <table style="float: left;">
        <tr>
            <td>Start date: &nbsp;</td>
            <td>
                <input id="startDate" name="<?php echo TestRun::START_DATE; ?>"
                       value="<?php echo Filter::escapeForHtml($testRun->getStartDate()); ?>"
                       type="text" size="10" style="text-align: right;"/>
            </td>
        </tr>
        <tr>
            <td>End date:</td>
            <td>
                <input id="endDate" name="<?php echo TestRun::END_DATE; ?>"
                       value="<?php echo Filter::escapeForHtml($testRun->getEndDate()); ?>"
                       type="text" size="10" style="text-align: right;"/>
            </td>
        </tr>
    </table>

    <div id="test-help" style="font-size: 140%; float: right;">
    <i class="fa fa-question-circle" style="color: blue;"></i>
    </div>

    <div style="clear: both"></div>

    <!-- TEST E-MAIL -->
    <div style="margin-top: 1em;">
        E-mail all notifications to:
        <input type="text" size="40" name="<?php echo TestRun::TEST_EMAIL; ?>"
               value="<?php echo Filter::escapeForHtml($testRun->getTestEmail()); ?>">
        </input>
    </div>

    <!-- NOTIFICATION -->
    <div style="margin-top: 1em;">
        <?php
        echo "Notification(s):\n";
        echo '<select name="' . TestRun::NOTIFICATION_ID . '">' . "\n";

        if ($testRun->getNotificationId() == 0) {
            echo '<option value="0" selected>ALL</options>' . "\n";
        } else {
            echo '<option value="0">ALL</options>' . "\n";
        }

        foreach ($notifications->getNotifications() as $notification) {
            $id = $notification->getId();
            $subject = $notification->getSubject();

            $selected = '';
            if ($id == $testRun->getNotificationId()) {
                $selected = ' selected';
            }

            echo '<option value="' . $id . '"' . $selected . '>' . $subject . ' [ID=' . $id . ']</option>' . "\n";
        }
        echo "</select>\n";
        ?>
    </div>

    <!-- SEND E-MAILS -->
    <?php
    $checked = '';
    if ($testRun->getSendEmails()) {
        $checked = ' checked';
    }
    ?>
    <div style="margin-top: 1em;">
        <input type="checkbox" name="<?php echo TestRun::SEND_EMAILS; ?>" <?php echo $checked; ?>>
        Send test e-mails
    </div>

    <!-- LOG E-MAILS -->
    <?php
    $checked = '';
    if ($testRun->getLogEmails()) {
        $checked = ' checked';
    }
    ?>
    <div style="margin-top: 1em;">
        <input type="checkbox" name="<?php echo TestRun::LOG_EMAILS; ?>" <?php echo $checked; ?>>
        Log test e-mails
    </div>

    <!-- UPDATE USER NOTIFICATION COUNTS -->
    <?php
    $checked = '';
    if ($testRun->getUpdateUserNotificationCounts()) {
        $checked = ' checked';
    }
    ?>
    <div style="margin-top: 1em;">
        <input type="checkbox" name="<?php echo TestRun::UPDATE_USER_NOTIFICATION_COUNTS; ?>" <?php echo $checked; ?>>
        Update user notification counts
    </div>

    <!-- RUN BUTTON -->
    <p style="margin-top: 22px;">
    <input type="submit" name="submitValue" value="Run" class="submit-button"/>
    </p>

</form>

<script>

    $( function() {
        $( "#startDate" ).datepicker();
    } );

    $( function() {
        $( "#endDate" ).datepicker();
    } );

    $(document).ready(function() {
        //---------------------------------------------
        // Help dialog events
        //---------------------------------------------
        $("#test-help").on("click", function () {
            $('#test-help-dialog').dialog({dialogClass: 'auto-notify-help', width: 640, maxHeight: 440})
                .dialog('widget').position({my: 'left top', at: 'right+50 top-10', of: $(this)})
                ;
            return false;
        });
    });
</script>

<!-- TEST HELP DIALOG -->
<div id="test-help-dialog" title="Test Help" style="display: none;">
    <?php echo Help::getHelpWithPageLink('test', $module); ?>
</div>


<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
