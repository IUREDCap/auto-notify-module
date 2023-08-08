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
use IU\AutoNotifyModule\Config;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\Version;

$selfUrl   = $module->getUrl(AutoNotifyModule::CONFIG_PAGE);

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

$redCapInfo = $module->getRedCapInfo();

$moduleConfig = $module->getConfig();
$cron = $moduleConfig['crons'][0];
$cronFrequency = $cron['cron_frequency'];

$adminConfig = $module->getAdminConfig();

#-------------------------
# Get the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
     $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}

if ($submitValue === 'Save') {
    $adminConfig->set($_POST);
    $module->setAdminConfig($adminConfig);
    $success = "Configuration saved.";
}

?>



<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php

$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);

?>

<?php
if ($adminConfig->getDebugMode()) {
    #print "<pre>\n";
    #print_r($adminConfig);
    #print "</pre>\n";
}
?>


<p style="float: left;">
External module version: <?php echo Version::RELEASE_NUMBER; ?>
</p>

<p id="config-help" style="font-size: 140%; float: right;">
<i class="fa fa-question-circle" style="color: blue;"></i>
</p>

<div style="margin: 0; padding: 0; clear: both;"></div>

<p>
Last cron run time: <?php echo Filter::escapeForHtml($lastRunTime); ?>
<br/>
Cron run frequency: <?php echo "every " . $cronFrequency / 60.0 . " minutes"; ?> 
</p>

<form action="<?php echo $selfUrl;?>" name="configForm" method="post">
    <?php
    $checked = "";
    if ($adminConfig->getTestMode()) {
        $checked = "checked";
    }
    ?>

    <input type="checkbox" name="<?php echo Config::TEST_MODE; ?>" <?php echo $checked; ?>/> Test mode
    <div style="margin-left: 4em;">
        E-mail all notifications to:
        <input type="text" size="40" name="<?php echo Config::EMAIL_ADDRESS; ?>"
               value="<?php echo $adminConfig->getEmailAddress(); ?>"/>
    <!--
        <p>
        <input type="radio"/> Send all e-mail to: <input type="text"> <br/>
        </p>

        <p>
        <input type="radio"/> Send all e-mail to domain: <input type="text">
        </p>
    -->
    </div>

    <p>
    <?php
    $checked = "";
    if ($adminConfig->getDebugMode()) {
        $checked = "checked";
    }
    ?>
    <!--
    <input type="checkbox" name="<?php echo Config::DEBUG_MODE; ?>" <?php echo $checked; ?>/> Debug mode
    -->
    </p>

    <p>
    <input type="submit" name="submitValue" class="submit-button" value="Save"/>
    </p>

</form>

<?php
#print "<pre>\n";
#print_r($redCapInfo);
#print "</pre>\n";
?>

<script>

$(document).ready(function() {
    //---------------------------------------------
    // Help dialog events
    //---------------------------------------------
    $("#config-help").on("click", function () {
        $('#config-help-dialog').dialog({dialogClass: 'auto-notify-help', width: 640, maxHeight: 440})
            .dialog('widget').position({my: 'left top', at: 'right+50 top-90', of: $(this)})
            ;
        return false;
    });
});
</script>

<!-- CONFIG HELP DIALOG -->
<div id="config-help-dialog" title="Config Help" style="display: none;">
    <?php echo Help::getHelpWithPageLink('config', $module); ?>
</div>


<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
