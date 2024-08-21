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
use IU\AutoNotifyModule\Notification;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\Schedule;

$selfUrl         = $module->getUrl(AutoNotifyModule::NOTIFICATIONS_PAGE);
$notificationUrl = $module->getUrl(AutoNotifyModule::NOTIFICATION_PAGE);

$cssFile = $module->getUrl('resources/notify.css');

# Check for notification deletion
if (array_key_exists('deleteNotificationId', $_POST)) {
    #$deleteNotifiationId = Filter::sanitizeInt($_POST['deleteNotificationId']);
    $deleteNotificationId = Filter::sanitizeInt($_POST['deleteNotificationId']);
    $module->deleteNotificationById($deleteNotificationId);
}

if (array_key_exists('copyNotificationId', $_POST)) {
    $copyNotificationId = Filter::sanitizeInt($_POST['copyNotificationId']);

    $copyNotificationSubject = null;
    if (array_key_exists('copyNotificationSubject', $_POST)) {
        $copyNotificationSubject = Filter::sanitizeString($_POST['copyNotificationSubject']);
    }

    $module->copyNotificationById($copyNotificationId, $copyNotificationSubject);
    $success = 'Notification with ID ' . $copyNotificationId . ' copied.';
}

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
$jsInclude = '<script type="text/javascript" src="' . ($module->getUrl('resources/notifications.js')) . '"></script>';
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

# DEBUGGING
# print "<pre>\n";
# print_r($_POST);
# print "</pre>\n";

#-------------------------
# Get the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}




?>

<h5>Notifications</h5>

<div style="clear: both;"></div>

<div style="margin-bottom: 14px;">
<button onclick="window.location.href='<?php echo $notificationUrl; ?>';">
    <i class="fa fa-circle-plus" style="color: green;"></i> Add Notification
</button>
</div>


<table class="data-table">
    <tr>
        <th>ID</th> <th>Status</th> <th>Sched.</th> <th>Subject</th> <th>To</th>
        <th>Start Date</th> <th>End Date</th> <th>Edit</th> <th>Copy</th> <th>Delete</th>
    </tr>
    <?php
    foreach ($notifications->getNotifications() as $notification) {
        $id = $notification->getId();
        $editUrl = $notificationUrl . '&notificationId=' . Filter::escapeForUrlParameter($id);
        $schedule = $notification->getSchedule();

        echo "<tr>\n";
        echo '<td style="text-align: right;">' . $id . "</td>\n";

        # Status
        echo '<td>';
        $status = $notification->getStatus();
        if ($status === Notification::STATUS_ACTIVE) {
            echo '<img src="' . APP_PATH_IMAGES . 'circle_green.png" alt="" style="margin-bottom: 2px;">';
        } elseif ($status === Notification::STATUS_DRAFT) {
            echo '<img src="' . APP_PATH_IMAGES . 'circle_yellow.png" alt="" style="margin-bottom: 2px;">';
        } elseif ($status === Notification::STATUS_EXPIRED) {
            echo '<img src="' . APP_PATH_IMAGES . 'circle_red.png" alt="" style="margin-bottom: 2px;">';
        }
        echo "&nbsp;" . $status . "</td>\n";

        # Scheduling
        $schedulingOption = $schedule->getSchedulingOption();
        echo '<td style="text-align: center;">';
        if ($schedulingOption === Schedule::SCHED_OPT_NOW) {
            echo '<img title="now" src="' . APP_PATH_IMAGES . 'mail_arrow.png" alt="now" style="margin-bottom: 2px;">';
        } elseif ($schedulingOption === Schedule::SCHED_OPT_FUTURE) {
            echo '<img title="future" src="' . APP_PATH_IMAGES
                . 'calendar_arrow.png" alt="future" style="margin-bottom;">';
        } elseif ($schedulingOption === Schedule::SCHED_OPT_RECURRING) {
            echo '<img title="recurring" src="' . APP_PATH_IMAGES
                . 'arrow_circle_double_135.png" alt="future" style="margin-bottom;">';
        } else {
            echo '&nbsp;';
        }
        echo "</td>\n";

        # Subject
        echo "<td>" . $notification->getSubject() . "</td>\n";

        # To
        echo "<td>" . $notification->getUsersSpecification()->getUsersOptionString() . "</td>\n";

        echo "<td>" . $schedule->getStartDate() . "</td>\n";
        if ($schedule->getEndDateChecked()) {
            echo "<td>" . $schedule->getEndDate() . "</td>\n";
        } else {
            echo "<td>&nbsp;</td>\n";
        }

        # Edit
        echo '<td style="text-align:center;">'
            . '<a href="' . $editUrl . '">'
            . '<img src="' . APP_PATH_IMAGES . 'page_white_edit.png" alt="EDIT"></a>'
            . "</td>\n";

        # Copy
        echo '<td style="text-align:center;">'
            //. "<form action=\"{$selfUrl}\" method=\"post\">\n"
            //. '<input type="hidden" name="copyNotificationId" value="' . $id . '"/>'
            . '<input type="image" src="' . APP_PATH_IMAGES . 'page_copy.png" alt="COPY" '
            . ' id="copyNotification' . $id . '"'
            . ' style="cursor: pointer;"/>'
            //. '<input type="hidden" name="redcap_csrf_token" value="' . $module->getCsrfToken() . '"/>'
            //. "</form>\n"
            . "</td>\n";

        # Delete
        echo '<td style="text-align:center;">'
            .  '<input type="image" src="' . APP_PATH_IMAGES . 'delete.png" alt="DELETE"'
            . ' id="deleteNotification' . $id . '"'
            . ' style="cursor: pointer;">'
            . "</td>\n";

        echo "</tr>\n";
    }
    ?>
</table>

<?php
#--------------------------------------
# Copy notification dialog
#--------------------------------------
?>
<div id="copy-dialog"
    title="Notification Copy"
    style="display: none;"
    >
    <form id="copy-form" action="<?php echo $selfUrl;?>" method="post">
    To copy the notification with ID <span id="notification-to-copy" style="font-weight: bold;"></span>,
    click on the <span style="font-weight: bold;">Copy notification</span> button.
    <br/>
    Subject: <input type="text" size="32" name="copyNotificationSubject" id="copy-subject" value="">
    <input type="hidden" name="copyNotificationId" id="copy-notification-id" value="">
    <?php # Csrf::generateFormToken(); ?>
    </form>
</div>

<?php
#--------------------------------------
# Delete notification dialog
#--------------------------------------
?>
<div id="delete-dialog"
    title="Notification Deleteion"
    style="display: none;"
    >
    <form id="delete-form" action="<?php echo $selfUrl;?>" method="post">
    To delete the notification with ID <span id="notification-to-delete" style="font-weight: bold;"></span>,
    click on the <span style="font-weight: bold;">Delete notification</span> button.
    <input type="hidden" name="deleteNotificationId" id="delete-notification-id" value="">
    <?php # Csrf::generateFormToken(); ?>
    </form>
</div>


<?php

#-----------------------------------------------------------------------------
# Set up click event handlers for the notification copy/rename/delete buttons
#-----------------------------------------------------------------------------
echo "<script>\n";
echo "    // Event handler script\n";

foreach ($notifications->getNotifications() as $notification) {
    $id = $notification->getId();
    $subject = $notification->getSubject();

    echo '$("#deleteNotification' . $id . '").click({notificationId: "'
        . $id
        . '"}, AutoNotifyModule.deleteNotification);' . "\n";

    echo '$("#copyNotification' . $id . '").click({notificationId: "'
        . $id . '"' . ', notificationSubject: "' . $subject . '"'
        . '}, AutoNotifyModule.copyNotification);' . "\n";
}

echo "</script>\n";
?>

<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
