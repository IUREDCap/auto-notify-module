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
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\Notification;
use IU\AutoNotifyModule\Query;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\Schedule;
use IU\AutoNotifyModule\UsersSpecification;

$selfUrl = $module->getUrl(AutoNotifyModule::NOTIFICATION_PAGE);

$notificationsUrl = $module->getUrl(AutoNotifyModule::NOTIFICATIONS_PAGE);

$username = USERID;

$notification = new Notification();


$notificationId = null;
if (array_key_exists('notificationId', $_GET)) {
    $notificationId = Filter::sanitizeInt($_GET['notificationId']);
    $notification = $module->getNotification($notificationId);
    if ($notification == null) {
        $notification = new Notification();
    }
}

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
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all"/>';

$jsFile  = $module->getUrl('resources/notification.js');
$tinymce = '<script type="text/javascript" src="' . $jsFile . '"></script>';

$buffer = str_replace('</head>', "    " . $link . "\n" . $tinymce . "\n</head>", $buffer);
echo $buffer;
?>

<h4>
<i class="fa fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php


$users = null;
$parameters = [];

$externalModuleInfoMap = $module->getExternalModuleInfoMap();

#print "<pre>";
#print "SCHEDULE:\n";
#print $notification->getSchedule()->toString();
#print_r($_POST);
#print "</pre>";

#-------------------------
# Get the submit value
#-------------------------
$error = null;
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
    if ($submitValue === 'SendSchedule' || $submitValue === 'Save as Draft') {
        $parameters = $_POST;
        $notification->set($parameters);

        # Set notification status
        if ($submitValue === 'SendSchedule') {
            try {
                $notification->validate();

                # If this is a new notification, it needs to be saved to get it's ID
                $module->addOrUpdateNotification($notification);

                if ($notification->isScheduledForNow()) {
                    $notification->sendNow($module);
                    $notification->setStatus(Notification::STATUS_EXPIRED);
                } else {
                    $notification->setStatus(Notification::STATUS_ACTIVE);
                }
                $module->addOrUpdateNotification($notification);

                # Go to the notifications page
                header('Location: ' . $notificationsUrl);
                exit();
            } catch (\Exception $exception) {
                $error = $exception->getMessage();
            }
        } else {
            $notification->setStatus(Notification::STATUS_DRAFT);
            $module->addOrUpdateNotification($notification);
            $success = "Draft saved.";
            ; // Stay on page
        }
    }
}
?>

<?php
# Page Content Header
$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);
?>

<?php
$id = $notification->getId();
if ($id == null) {
    $idString = '[New]';
} else {
    $idString = '[ID: ' . Filter::escapeForHtml($id) . ']';
}
?>

<h6 style="float: left;">Notification <?php echo $idString; ?></h6>


<div id="notification-help" style="font-size: 140%; float: right;">
<i id="notificationHelp" class="fa fa-question-circle" style="color: blue;"></i>
</div>

<div style="clear: both;"></div>

<form action="<?php echo $selfUrl;?>" name="mailForm" method="post">

    <input type="hidden" name="<?php echo Notification::NOTIFICATION_ID; ?>"
           value="<?php echo Filter::escapeForHtml($notification->getId()); ?>"/>

    <!-- SUBJECT ================================================================================== -->
    <fieldset class="config">
        <legend>Subject</legend>

        <p>
        <input type="text" name="<?php echo Notification::SUBJECT; ?>"
               value="<?php echo Filter::escapeForHtml($notification->getSubject()); ?>" size="48"/>
        </p>
    </fieldset>

    <!-- FROM ================================================================================== -->
    <fieldset class="config">
        <legend>From</legend>

        <p>
        e-mail:
        <input type="text" name="<?php echo Notification::FROM_EMAIL; ?>" size="44"
               value="<?php echo Filter::escapeForHtml($notification->getFromEmail()); ?>"/>
        </p>
    </fieldset>


    <!-- TO ================================================================================== -->
    <fieldset class="config">
        <legend>To</legend>

        <div style="margin-bottom: 12px;">
            <select name="<?php echo UsersSpecification::USERS_OPTION;?>">
                <?php
                $usersSpecification = $notification->getUsersSpecification();
                $selectedUsersOption = $usersSpecification->getUsersOption();
                foreach (UsersSpecification::USERS_OPTIONS as $usersOption) {
                    $selected = '';
                    if ($usersOption === $selectedUsersOption) {
                        $selected = ' selected';
                    }
                    echo '<option value="' . $usersOption . '"' . $selected . '>'
                        . ucwords(UsersSpecification::toUsersOptionString($usersOption)) . "</option>\n";
                }
                ?>
                <!--
                <option value="<?php echo UsersSpecification::USERS_OPT_API_TOKEN;?>">API Token Users</option>
                <option value="<?php echo UsersSpecification::USERS_OPT_EXT_MOD;?>">External Module Users</option>
                -->
            </select>
        </div>

        <!-- EXTERNAL MODULE USERS -->
        <div id="externalModuleUsers" style="display: none;">
            <fieldset class="config">
                <?php
                $selectedOption = $notification->getUsersSpecification()->getExternalModuleOption();
                ?>
                <legend>External Module Use</legend>
                <p>
                <?php
                $checked = '';
                if ($selectedOption === UsersSpecification::EXT_MOD_OPT_ANY) {
                    $checked = ' checked';
                }
                ?>
                <input type="radio"
                       name="<?php echo UsersSpecification::EXTERNAL_MODULE_OPTION; ?>"
                       value="<?php echo UsersSpecification::EXT_MOD_OPT_ANY; ?>"
                       <?php echo $checked; ?>
                />
                Any external module
                </p>
                <hr/>
                <p>
                <?php
                $checked = '';
                if ($selectedOption === UsersSpecification::EXT_MOD_OPT_ANY_OF) {
                    $checked = ' checked';
                }
                ?>
                <input type="radio"
                       name="<?php echo UsersSpecification::EXTERNAL_MODULE_OPTION; ?>"
                       value="<?php echo UsersSpecification::EXT_MOD_OPT_ANY_OF; ?>"
                       <?php echo $checked; ?>
                />
                Any of the following external modules:
                </p>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th> <th>ID</th> <th>External Module</th> <th>Version</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $directoryPrefixes = $notification->getUsersSpecification()->getExternalModules();

                        foreach ($externalModuleInfoMap as $directoryPrefix => $externalModuleInfo) {
                            $id              = $externalModuleInfo->getId();
                            $name            = $externalModuleInfo->getName();
                            $version         = $externalModuleInfo->getVersion();
                            $directoryPrefix = $externalModuleInfo->getDirectoryPrefix();

                            echo "<tr>\n";

                            $checked = '';
                            if (in_array($directoryPrefix, $directoryPrefixes)) {
                                $checked = ' checked';
                            }
                            echo '<td> <input type="checkbox"'
                                . ' name="' . UsersSpecification::EXTERNAL_MODULES . '[]"'
                                . ' value="' . Filter::escapeForHtmlAttribute($directoryPrefix) . '"'
                                . ' class="externalModuleOption" '
                                . $checked . '/> </td>' . "\n";

                            echo "<td>" . Filter::escapeForHtml($id) . "</td>\n";
                            echo "<td>" . Filter::escapeForHtml($name) . "</td>\n";
                            echo "<td>" . Filter::escapeForHtml($version) . "</td>\n";
                            echo "</tr>\n";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Cross-Project Piping Source Projects -->
                <hr/>
                <p>
                <?php
                $checked = '';
                if ($selectedOption === UsersSpecification::EXT_MOD_OPT_CPP_SOURCE) {
                    $checked = ' checked';
                }
                ?>
                <input type="radio"
                       name="<?php echo UsersSpecification::EXTERNAL_MODULE_OPTION; ?>"
                       value="<?php echo UsersSpecification::EXT_MOD_OPT_CPP_SOURCE; ?>"
                       <?php echo $checked; ?>
                />
                Cross-Project Piping external module source projects
                </p>
            </fieldset>

            <p>
                <?php
                $projectOwners = $notification->getUsersSpecification()->getProjectOwners();
                $checked = '';
                if ($projectOwners) {
                    $checked = ' checked';
                }
                ?>
                <input name="<?php echo UsersSpecification::PROJECT_OWNERS; ?>"
                       type="checkbox" <?php echo $checked; ?>/> Project owners only
                <i id="project-owners-help" class="fa fa-question-circle" style="color: blue; margin-left: 7px;"></i>
            </p>

            <?php
                # $query = $usersSpecification->toQuery($module);
                # $conditions = $query->getConditions();
                # $variables = $module->getVariables();
                # print "<pre>";
                # print_r($conditions->toString($variables, 0));
                # print "</pre>";
            ?>

        </div>

        <!-- API TOKEN USERS -->
        <div id="apiTokenUsers" style="display: none;">
            <!--
            <p>
            <input type="radio" name="apiTokenUseType"/> Any API Token
            </p>
            -->
        </div>

        <!-- INPUTS COMMON TO ALL FORM USER SPECIFICATIONS (i.e., NOT CUSTOM QUERIES) -->
        <div id="commonFormInputs" style="display: none;">
            <p>
                <?php
                $excludeSuspendedUsers = $notification->getUsersSpecification()->getExcludeSuspendedUsers();
                $checked = '';
                if ($excludeSuspendedUsers) {
                    $checked = ' checked';
                }
                ?>
                <input name="<?php echo UsersSpecification::EXCLUDE_SUSPENDED_USERS; ?>"
                       type="checkbox" <?php echo $checked; ?>/> Exclude suspended users
                <!--
                <i id="project-owners-help" class="fa fa-question-circle" style="color: blue; margin-left: 7px;"></i>
                -->

                <br/>

                <?php
                $excludeUsersWithExpiredRights =
                    $notification->getUsersSpecification()->getExcludeUsersWithExpiredRights();
                $checked = '';
                if ($excludeUsersWithExpiredRights) {
                    $checked = ' checked';
                }
                ?>
                <input name="<?php echo UsersSpecification::EXCLUDE_USERS_WITH_EXPIRED_RIGHTS; ?>"
                       type="checkbox" <?php echo $checked; ?>/> Exclude users with expired project rights
                <!--
                <i id="project-owners-help" class="fa fa-question-circle" style="color: blue; margin-left: 7px;"></i>
                -->
            </p>
        </div>


        <!-- CUSTOM QUERY USERS -->
        <div id="customQuery" style="display: none;">
            <!--
            <p>
            <input type="radio" name="apiTokenUseType"/> Any API Token
            </p>
            -->
            <table class="data-table" style="margin-bottom: 17px;">
                <thead>
                    <tr>
                        <th>&nbsp;</th> <th>ID</th> <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $customQueries = $module->getQueries();
                    $customQueryId = $notification->getUsersSpecification()->getCustomQueryId();
                    foreach ($customQueries->getQueries() as $query) {
                        echo "<tr>\n";
                        $checked = '';
                        if ($query->getId() == $customQueryId) {
                            $checked = ' checked';
                        }
                        echo '<td><input type="radio" name="customQueryId"'
                            . 'value="' . $query->getId() . '" ' . $checked . '/></td>';
                        echo "<td>{$query->getId()}</td>";
                        echo "<td>{$query->getName()}</td>";
                        echo "</tr>\n";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!--
        Opt-out list:
        <br/>
        <textarea name="<?php echo UsersSpecification::OPT_OUT_LIST; ?>"
        rows="4" cols="84"><?php echo $notification->getUsersSpecification()->getOptOutList(); ?></textarea>
        -->
    </fieldset>

    <!-- MESSAGE ================================================================================== -->
    <fieldset class="config">
        <legend>Message</legend>
        <textarea name="<?php echo Notification::MESSAGE; ?>"
                  id="<?php echo Notification::MESSAGE; ?>"
                  rows="17" cols="84"><?php echo $notification->getMessage(); ?></textarea>
    </fieldset>


    <!-- SCHEDULE ================================================================================== -->
    <fieldset class="config">
        <legend>Scheduling</legend>
        <div>
            <?php
            $schedule = $notification->getSchedule();

            $checked = '';
            if ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_NOW) {
                $checked = ' checked';
            }
            echo '<input type="radio" name="' . Schedule::SCHEDULING_OPTION . '"'
                . ' value="' . Schedule::SCHED_OPT_NOW . '"' . $checked . '> Send Now </input>';
            ?>
        </div>
        <hr/>
        <div>
            <?php
            $checked = '';
            if ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_FUTURE) {
                $checked = ' checked';
            }
            echo '<input type="radio" name="' . Schedule::SCHEDULING_OPTION . '"'
                . ' value="' . Schedule::SCHED_OPT_FUTURE . '"' . $checked . '> Schedule for: </input>';
            ?>
            <input type="text" id="scheduledTime"
                   name="<?php echo Schedule::SEND_TIME; ?>"
                   value="<?php echo $schedule->getSendTime(); ?>"
                   style="text-align: right;"/>
        </div>
        <hr/>
        <div>
            <?php
            $checked = '';
            if ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_RECURRING) {
                $checked = ' checked';
            }
            echo '<input type="radio" name="' . Schedule::SCHEDULING_OPTION . '"'
                . ' value="' . Schedule::SCHED_OPT_RECURRING . '"' . $checked . '> Schedule Recurring: </input>';
            ?>
        </div>

        <div style="padding:4px; margin-top: 12px; margin-bottom: 12px;">
        <table class="data-table" style="background-color: white; padding: 4px; margin-left: 2em;">
            <thead>
                <tr>
                    <th> Frequency </th> <th> Details </th>
                </tr>
            </thead>
            <tbody>

                <!-- DAILY/WEEKLY -->
                <tr>
                    <td>
                        <?php
                        $checked = '';
                        if ($schedule->getRecurringOption() === Schedule::REC_OPT_DAILY) {
                            $checked = ' checked';
                        }
                        echo '<input type="radio" name="' . Schedule::RECURRING_OPTION . '"'
                            . ' value="' . Schedule::REC_OPT_DAILY . '"' . $checked . '> daily/weekly </input>';
                        ?>
                    </td>
                    <td>
                        <table class="data-table" style="margin: 4px; font-size: 90%;">
                            <tr> <th>day</th> <th>time</th> </tr>
                            <?php

                            $checks = $schedule->getDayChecks();

                            # For each day of the week: Sunday, Monday, ...
                            foreach (range(0, 6) as $day) {
                                echo "<tr>\n";

                                #-------------------
                                # Day checkbox
                                #-------------------
                                echo "<td>";
                                $dayName = DateInfo::WEEKDAY_NAMES[$day];

                                $checked = '';
                                if (in_array($day, $checks)) {
                                    $checked = ' checked';
                                }

                                echo '<input type="checkbox"'
                                    . ' name="' . Schedule::DAY_CHECKS . '[]" value="' . $day . '"'
                                    . $checked . '/>' . "\n";
                                echo "{$dayName}\n";

                                echo "</td>\n";

                                #-------------------
                                # Day time
                                #-------------------
                                echo "<td>";
                                echo '<input type="text" id="' . strtoLower($dayName)
                                    . 'Time" size="6" style="text-align: right;"'
                                    . ' name="' . SCHEDULE::DAY_TIMES . '[]"'
                                    . ' value="' . $schedule->getDayTimes()[$day] . '"></input>';
                                echo "</td>\n";

                                echo "</tr>\n";
                            }
                            ?>
                        </table>
                    </td>
                </tr>

                <!-- MONTHLY/YEARLY -->
                <tr>
                    <td>
                        <?php
                        $checked = '';
                        if ($schedule->getRecurringOption() === Schedule::REC_OPT_MONTHLY) {
                            $checked = ' checked';
                        }
                        echo '<input type="radio" name="' . Schedule::RECURRING_OPTION . '"'
                            . ' value="' . Schedule::REC_OPT_MONTHLY . '"' . $checked . '> monthly/yearly </input>';
                        ?>
                    </td>
                    <td>
                        <table class="data-table" style="margin: 4px; font-size: 90%;">
                            <tr> <th>month</th> <th>day</th> <th>time</th> </tr>
                            <?php
                            $checks = $schedule->getMonthChecks();

                            foreach (range(1, 12) as $month) {
                                $monthName = DateInfo::MONTH_NAMES[$month];

                                echo '<tr>';

                                #---------------------------------------
                                # Month checkbox
                                #---------------------------------------
                                $checked = '';
                                if (in_array($month, $checks)) {
                                    $checked = ' checked';
                                }

                                echo '<td>';
                                echo '<input type="checkbox"'
                                    . ' name="' . Schedule::MONTH_CHECKS . '[]" value="' . $month . '"'
                                    . $checked . '/>' . "\n";
                                echo "{$monthName}\n";
                                echo "</td>\n";

                                #----------------------------------
                                # Month day number specification
                                #----------------------------------
                                echo '<td>';
                                $checked = '';
                                if ($schedule->getMonthDayOptions()[$month] === Schedule::MON_DAY_OPT_NUMBER) {
                                    $checked = ' checked';
                                }
                                echo '<input type="radio" name="' . Schedule::MONTH_DAY_OPTION . $month . '"'
                                    . ' value="' . Schedule::MON_DAY_OPT_NUMBER . '"' . $checked . '/>' . "\n";

                                echo '<select name="' . Schedule::MONTH_DAYS . $month . '"' . ">\n";

                                if ($month === 2) {
                                    $lastDay = 28;
                                } elseif ($month === 4 || $month === 6 || $month === 9 || $month === 11) {
                                    $lastDay = 30;
                                } else {
                                    $lastDay = 31;
                                }

                                foreach (range(1, $lastDay) as $day) {
                                    $selected = '';
                                    if ($schedule->getMonthDays()[$month] == $day) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="' . $day . '"' . $selected . '>' . $day . "</option>\n";
                                }
                                echo "</select>\n";

                                #----------------------------------
                                # Week day of month specification
                                #----------------------------------
                                $monthDaySpecs = array(
                                    1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'last'
                                );

                                $checked = '';
                                if ($schedule->getMonthDayOptions()[$month] === Schedule::MON_DAY_OPT_WEEK_DAY) {
                                    $checked = ' checked';
                                }
                                echo '<input type="radio" name="' . Schedule::MONTH_DAY_OPTION . $month . '"'
                                    . ' value="' . Schedule::MON_DAY_OPT_WEEK_DAY
                                    . '" ' . $checked . ' style="margin-left: 1em;"/>' . "\n";

                                # Week number select (first, second, ...)
                                echo '<select name="' . Schedule::MONTH_WEEK_NUMBER . '[]">' . "\n";
                                foreach ($monthDaySpecs as $number => $dayOrder) {
                                    $selected = '';
                                    if ($schedule->getMonthWeekNumber()[$month - 1] == $number) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="' . $number . '"' . $selected .  '>' . $dayOrder
                                        . "</option>\n";
                                }
                                echo "</select>\n";

                                # Day of week select (Sunday, Monday ...)
                                echo '<select name="' . Schedule::MONTH_DAY_OF_WEEK . '[]">' . "\n";
                                foreach (DateInfo::WEEKDAY_NAMES as $number => $name) {
                                    $selected = '';
                                    if ($schedule->getMonthDayOfWeek()[$month - 1] == $number) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="' . $number . '"' . $selected . '>' . $name . "</option>\n";
                                }
                                echo "</select>\n";

                                echo '</td>';

                                #------------------------------
                                # Time
                                #------------------------------
                                $id = "month{$month}Time";
                                echo '<td>';
                                echo '<input type="text" id="' . $id . '" size="6" style="text-align: right;"'
                                    . ' name ="' . Schedule::MONTH_TIMES . '[]"'
                                    . ' value="' . $schedule->getMonthTimes()[$month - 1] . '"' . "/>\n";
                                echo '</td>';
                                echo "<tr>\n";
                            }
                            ?>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
        </div>

            <p>Range:</p>

            <p>
            Start Date:
            <input name="<?php echo Schedule::START_DATE; ?>"
                   value="<?php echo $notification->getSchedule()->getStartDate(); ?>"
                   type="text" id="startDate" style="text-align: right; margin-right: 2em;" size="10"
            />

            <p>
            <?php
            $checked = '';
            if ($notification->getSchedule()->getEndDateChecked()) {
                $checked = ' checked';
            }
            ?>
            <input type="checkbox" name="<?php echo Schedule::END_DATE_CHECKED?>" <?php echo $checked;?>>
            End after:
            <input name="<?php echo Schedule::END_DATE; ?>"
                   value="<?php echo $notification->getSchedule()->getEndDate(); ?>"
                   type="text" id="endDate" style="text-align: right;" size="10"
            />
            </p>

            </p>
            <p>
            <?php
            $checked = '';
            if ($schedule->getTimesPerUserCheck()) {
                $checked = ' checked';
            }
            ?>
                <input type="checkbox" name="<?php echo Schedule::TIMES_PER_USER_CHECK; ?>" <?php echo $checked; ?>>
            Limit notification to

            <input type="text"
                   name="<?php echo Schedule::TIMES_PER_USER; ?>" 
                   value="<?php echo $schedule->getTimesPerUser(); ?>" 
                   size="3" style="text-align: right;"/>
            time(s) per user
            </p>
    </fieldset>

    <input type="submit" name="submitValue" value="Save as Draft" class="submit-button" style="margin-right: 2em;"/>
    <input type="submit" name="submitValue" value="Send/Schedule"class="submit-button" />
</form>


<script>

    $(document).ready(function() {
        //$( "#startDate" ).datepicker({ minDate: 0 }).datepicker("setDate", new Date());
        $( "#startDate" ).datepicker({ minDate: 0 }).datepicker();

        $( "#endDate" ).datepicker({ minDate: 0 });

        $( "#scheduledTime" ).datetimepicker();

        $( "#monthDayTime" ).timepicker();

        /* Month time pickers */
        $( "#month1Time" ).timepicker();
        $( "#month2Time" ).timepicker();
        $( "#month3Time" ).timepicker();
        $( "#month4Time" ).timepicker();
        $( "#month5Time" ).timepicker();
        $( "#month6Time" ).timepicker();
        $( "#month7Time" ).timepicker();
        $( "#month8Time" ).timepicker();
        $( "#month9Time" ).timepicker();
        $( "#month10Time" ).timepicker();
        $( "#month11Time" ).timepicker();
        $( "#month12Time" ).timepicker();


        /* Day time pickers */
        $( "#sundayTime" ).timepicker();
        $( "#mondayTime" ).timepicker();
        $( "#tuesdayTime" ).timepicker();
        $( "#wednesdayTime" ).timepicker();
        $( "#thursdayTime" ).timepicker();
        $( "#fridayTime" ).timepicker();
        $( "#saturdayTime" ).timepicker();

        $( "#debug" ).accordion({
            collapsible: true,
            active: false
        });

        AutoNotifyModule.initializeMessageEditor();

        // Users speification option display
        $("select[name=<?php echo UsersSpecification::USERS_OPTION;?>]").change(function() {
            var value = $(this).val();

            if (value === "<?php echo UsersSpecification::USERS_OPT_API_TOKEN; ?>") {
                $("#apiTokenUsers").show();
                $("#commonFormInputs").show();
                $("#externalModuleUsers").hide();
                $("#customQuery").hide();
            } else if (value === "<?php echo UsersSpecification::USERS_OPT_EXT_MOD; ?>") {
                $("#apiTokenUsers").hide();
                $("#commonFormInputs").show();
                $("#externalModuleUsers").show();
                $("#customQuery").hide();
            } else if (value === "<?php echo UsersSpecification::USERS_OPT_CUSTOM_QUERY; ?>") {
                $("#apiTokenUsers").hide();
                $("#commonFormInputs").hide();
                $("#externalModuleUsers").hide();
                $("#customQuery").show();
            } else {
                $("#adminUsers").hide();
                $("#apiTokenUsers").hide();
                $("#externalModuleUsers").hide();
            }
        }).change();

        //--------------------------------------------------------------------------------------------
        // Select the "Any of external module" option when a specific external module is selected
        //--------------------------------------------------------------------------------------------
        $(".externalModuleOption").change(function() {
            $('input[name="<?php echo UsersSpecification::EXTERNAL_MODULE_OPTION; ?>"]'
                + '[value="<?php echo UsersSpecification::EXT_MOD_OPT_ANY_OF; ?>"]').prop('checked', true);
        });

        //---------------------------------------------
        // Help dialog events
        //---------------------------------------------
        $("#notification-help").on("click", function () {
            $('#notification-help-dialog').dialog({dialogClass: 'auto-notify-help', width: 640, maxHeight: 440})
                .dialog('widget').position({my: 'left top', at: 'right+50 top-10', of: $(this)})
            ;
            return false;
        });

        $("#project-owners-help").on("click", function () {
            // alert("TEST");
            $('#project-owners-help-dialog').dialog({dialogClass: 'auto-notify-help', width: 640, maxHeight: 200})
                .dialog('widget').position({my: 'left top', at: 'right+50 top-10', of: $(this)})
            ;
            return false;
        });

    });
</script>

<!-- NOTIFICATION HELP DIALOG -->
<div id="notification-help-dialog" title="Notification Help" style="display: none;">
    <?php echo Help::getHelpWithPageLink('notification', $module); ?>
</div>

<!-- PROJECT OWNERS HELP DIALOG -->
<div id="project-owners-help-dialog" title="Project Owners Help" style="display: none;">
    <?php echo Help::getHelp('project-owners', $module); ?>
</div>


<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
