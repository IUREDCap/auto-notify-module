<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing a notification.
 */
class Notification
{
    public const OBJECT_VERSION = 1;

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_DRAFT   = 'draft';
    public const STATUS_EXPIRED = 'expired';

    public const NOTIFICATION_ID = 'notification_id';

    public const FROM_EMAIL = 'fromEmail';
    public const SUBJECT    = 'subject';
    public const MESSAGE    = 'message';

    private $objectVersion; // Object version number to keep track of changes to the structure of this object

    private $id;
    private $status;

    # FROM
    # TO
    # SUBJECT
    # MESSAGE
    # SCHEDULE
    # LIST OF COUNTS TO USERS (for case where you want to limit how many times a user sees an e-mail)

    private $usersSpecification;      // specification of users to which notification is to be sent
    private $schedule;

    /* ------------------------- */

    private $fromEmail;
    private $subject;
    private $message;

    // Map from username to count of how many times notification sent to user
    private $userCountMap;

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->id     = null;  // ID will get set the first time the notification is stored
        $this->status = self::STATUS_DRAFT;

        $this->fromEmail = AutoNotifyModule::getFromEmail();
        $this->message = '';
        $this->subject = '';

        $this->usersSpecification = new UsersSpecification();
        $this->schedule           = new Schedule();

        $this->userCountMap = array();
    }


    public function migrate()
    {
    }


    public function validate()
    {
        if (empty($this->subject)) {
            throw new \Exception("No subject specified.");
        }

        if (empty($this->fromEmail)) {
            throw new \Exception("No from e-mail specified.");
        } elseif (!Filter::isEmail($this->fromEmail)) {
            throw new \Exception("The specified from e-mail \"{$this->fromEmail}\" is not a valid e-mail.");
        }

        if ($this->message == null || trim($this->message) === '') {
            throw new \Exception("Invalid blank message. Blank messages are not allowed.");
        }

        $this->usersSpecification->validate();
        $this->schedule->validate();
    }

    public function getMessageWithVariablesSet(
        $user,
        $redCapInfo,
        $extModInfo,
        $projectInfoMap,
        $projectTableColumns,
        $queryVariables,
        $cronTimestamp = null
    ) {
        $message = $this->message;

        ksort($projectInfoMap, SORT_NUMERIC);

        #-------------------------------------
        # User variables
        #-------------------------------------
        $message = preg_replace('/\[username\]/', $user->getUsername(), $message);
        $message = preg_replace('/\[first_name\]/', $user->getFirstName(), $message);
        $message = preg_replace('/\[last_name\]/', $user->getLastName(), $message);
        $message = preg_replace('/\[email\]/', $user->getEmail(), $message);
        $message = preg_replace('/\[last_login\]/', $user->getLastLogin(), $message);

        #-------------------------------------
        # REDCap variables
        #-------------------------------------
        $message = preg_replace('/\[redcap_url\]/', $redCapInfo->getUrl(), $message);
        $message = preg_replace('/\[redcap_institution\]/', $redCapInfo->getInstitution(), $message);

        #-------------------------------------
        # Testing variables
        #-------------------------------------
        if ($cronTimestamp != null) {
            $cronTime = date("Y-m-d H:i:s", $cronTimestamp);
            $message = preg_replace('/\[cron_time\]/', $cronTime, $message);
        }

        #-----------------------------------------------------------
        # applicable project info
        #-----------------------------------------------------------
        $table = "";
        if (!empty($projectTableColumns)) {
            $table = '<table style="border-collapse: collapse;" border="1">' . "\n";
            $table .= '<tr>';

            # Table header
            foreach ($projectTableColumns as $column) {
                $queryVariable = $queryVariables[$column];
                $label = $queryVariable->getLabel();
                $table .= "<th style=\"margin: 1px 4px 1px 4px;\">{$label}</th>";
            }

            foreach ($user->getUserRights() as $userRights) {
                $pid = $userRights->getProjectId();
                $table .= "<tr>";
                foreach ($projectTableColumns as $column) {
                    if ($column === 'project_id') {
                        $table .= '<td style="margin: 1px 4px 1px 4px; text-align: right;">' . $pid;
                    } elseif ($column === 'app_title') {
                        $table .= '<td style="margin: 1px 4px 1px 4px;">' . ($projectInfoMap[$pid])->getName();
                    } elseif ($column === 'cpp_destination_project_id') {
                        $table .= '<td style="margin: 1px 4px 1px 4px;">';
                        $cppDestinationProjectIds = $userRights->getCppDestinationProjectIds();
                        sort($cppDestinationProjectIds, SORT_NUMERIC);
                        foreach ($cppDestinationProjectIds as $cppDestinationProjectId) {
                            $projectName = '';
                            if (array_key_exists($cppDestinationProjectId, $projectInfoMap)) {
                                $projectInfo = $projectInfoMap[$cppDestinationProjectId];
                                $projectName = $projectInfo->getName();
                            }
                            $table .= "{$projectName} [Project ID = {$cppDestinationProjectId}]<br/>";
                        }
                    } elseif ($column === 'directory_prefix') {
                        $emIds = $userRights->getExternalModuleIds();
                        $emNames = [];

                        $table .= '<td style="margin: 1px 4px 1px 4px;">';

                        foreach ($emIds as $emId) {
                            $info = $extModInfo[$emId];
                            $emName    = $info->getName();
                            $emVersion = $info->getVersion();
                            $emNames[] = "{$emName} ({$emVersion})";
                        }

                        sort($emNames);
                        foreach ($emNames as $emName) {
                            $table .= "{$emName}<br/>";
                        }

                        $table .= print_r($users, true) . '</td>';
                    } else {
                        $table .= '<td style="margin: 1px 4px 1px 4px;">' . "&nbsp;";
                    }
                    $table .= '</td>';
                }
                $table .= "</tr>\n";
            }
            $table .= "</tr>\n";
            $table .= "</table>\n";
        }

        $message = preg_replace('/\[applicable_project_info\]/', $table, $message);

        return $message;
    }


    public function sendNow($module, $testRun = null, $cronTimestamp = null)
    {
        $queryVariables = $module->getVariables();

        $nowDateTime = DateInfo::timestampToString($cronTimestamp);

        $usersQueryResults = $module->getUsers($this->usersSpecification, $nowDateTime);

        $users               = $usersQueryResults->getUsers();
        $projectInfoMap      = $usersQueryResults->getProjectInfoMap();
        $projectTableColumns = $usersQueryResults->getProjectTableColumns();


        $redCapInfo = $module->getRedCapInfo();
        # $extModInfo = RedCapDb::getExternalModuleInfo($module);
        $extModInfo = $module->getExternalModuleInfoMap();
        $extModInfo = ExternalModuleInfo::convertToIdMap($extModInfo);

        $adminConfig = $module->getAdminConfig();

        $timesPerUser = $this->schedule->getTimesPerUser();

        $emails = [];
        $toString = '';

        $subject = $this->getSubject();
        $from    = $this->getFromEmail();

        $toCount = 1;
        foreach ($users as $user) {
            $username = $user->getUsername();

            # Get the count of times this notification has been sent to the user;
            # set to zero if it is not currently set
            if (!array_key_exists($username, $this->userCountMap)) {
                $this->userCountMap[$username] = 0;
            }
            $userCount = $this->userCountMap[$username];

            $to = $user->getEmail();

            #-----------------------------------------------------------
            # Check for test "to e-mail". Test page "to e-mail"
            # takes precendence over Config page "to e-mail".
            #-----------------------------------------------------------
            if (isset($testRun)) {
                $to = $testRun->getTestEmail();
            } elseif ($adminConfig->getTestMode()) {
                $to = $adminConfig->getEmailAddress();
            }

            $message = $this->getMessageWithVariablesSet(
                $user,
                $redCapInfo,
                $extModInfo,
                $projectInfoMap,
                $projectTableColumns,
                $queryVariables,
                $cronTimestamp
            );

            $sendStatus = 'unsent';

            # bool REDCap::email ( string $to, string $from, string $subject, string $message
            # [, string $cc [, string $bcc [, string $fromName [, array $attachments ]]]] )
            if (!$this->schedule->getTimesPerUserCheck() || $userCount < $timesPerUser) {
                if (!isset($testRun) || $testRun->getSendEmails()) {
                    $sendStatus = \REDCap::email($to, $from, $subject, $message);
                }

                if (!isset($testRun) || $testRun->getUpdateUserNotificationCounts()) {
                    $this->userCountMap[$username]++;
                    $userCount = $this->userCountMap[$username];
                }

                if ($toCount > 1) {
                    $toString .= ", ";
                }
                $toString .= "{$username} {$to} {$userCount} {$sendStatus}";

                $toCount++;
            }

            $emails[] = $to;
        }

        $emailList = implode(", ", $emails);

        $logId = null;

        if (!isset($testRun) || $testRun->getLogEmails()) {
            #--------------------------------------------------------------
            # Log the send  - ADD LOG IF (PARTIAL) FAILURE!!!!!!!!!!!!!!!
            #--------------------------------------------------------------
            $query = $this->getUsersSpecification()->toQuery($module);
            if (!empty($query)) {
                $conditions = $query->getConditions();
                if (!empty($conditions)) {
                    $variables = $module->getVariables();
                    $userConditionsString = $conditions->toString($variables);
                }
            }

            $scheduleString = '';
            $schedule = $this->getSchedule();
            if (!empty($schedule)) {
                $scheduleString = $schedule->toString();
            }

            $testRunString = '';
            if (!empty($testRun)) {
                $testRunString = $testRun->toString();
            } elseif ($adminConfig->getTestMode()) {
                $testRunString = 'test mode e-mail: ' . $adminConfig->getEmailAddress() . "\n";
            }

            $cronTime = '';
            if (!empty($cronTimestamp)) {
                $cronTime = date("Y-m-d H:i:s", $cronTimestamp);
            }

            $logParams = [
                'notificationId'     => $this->getId(),
                'to'                 => $toString,
                'userConditions'     => $userConditionsString,
                'schedule'           => $scheduleString,
                'from'               => $from,
                'subject'            => $subject,
                'notification'       => htmlspecialchars($this->getMessage()),
                'testRun'            => $testRunString,
                'cronTime'          => $cronTime
            ];

            $logMessage = "E-mail \"{$subject}\" sent";

            #error_log("\n\nTEST\n", 3, __DIR__ . '/log.txt');
            #error_log(print_r($logParams, true), 3, __DIR__ . '/log.txt');

            $logId = $module->log($logMessage, $logParams);
            # $this->lastEtlRunLogId = $logId;
        }

        # Update this notification (user counts should have changed)
        $module->addOrUpdateNotification($this);

        return $logId;
    }

    /**
     * Send the notification if it should be sent after the last send time
     * and before or at the current time.
     */
    public function send($module, $lastSendTimestamp, $currentSendTimestamp, $testRun = null)
    {
        $schedule = $this->getSchedule();

        if ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_FUTURE) {
            $sendTimestamp = strtotime($schedule->getSendTime());

            if ($sendTimestamp > $lastSendTimestamp && $sendTimestamp <= $currentSendTimestamp) {
                $this->sendNow($module, $testRun, $currentSendTimestamp);
                if (!isset($testRun)) {
                    $this->status = self::STATUS_EXPIRED;
                    $module->addOrUpdateNotification($this);
                }
            }
        } elseif ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_RECURRING) {
            $endTimetamp  = $schedule->getEndTimestamp();
            if ($endTimestamp != null && $endTimestamp <= $lastSendTimestamp) {
                # If this notification has an end time specified, and it is
                # before or on the last send time, then don't send the
                # notification and mark it as expired (unless this is a test run).
                if (!isset($testRun)) {
                    $this->status = self::STATUS_EXPIRED;
                    $module->addOrUpdateNotification($this);
                }
            } else {
                $nextTimestamp = $schedule->getNextRecurringTimestamp($lastSendTimestamp);
                if (
                    $nextTimestamp != null
                    && $nextTimestamp > $lastSendTimestamp
                    && $nextTimestamp <= $currentSendTimestamp
                ) {
                    $this->sendNow($module, $testRun, $currentSendTimestamp);
                }
            }
        }
    }

    public function isScheduledForNow()
    {
        return $this->getSchedule()->getSchedulingOption() === Schedule::SCHED_OPT_NOW;
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getSubject()
    {
        return $this->subject;
    }


    /**
     * Sets the notification to the values in the specified properties array.
     */
    public function set($properties)
    {
        # Create subobjects
        $this->usersSpecification = new UsersSpecification();
        $this->schedule           = new Schedule();

        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::NOTIFICATION_ID, $properties)) {
                $this->id = Filter::sanitizeEmail($properties[self::NOTIFICATION_ID]);
            }

            if (array_key_exists(self::FROM_EMAIL, $properties)) {
                $this->fromEmail = Filter::sanitizeEmail($properties[self::FROM_EMAIL]);
            }

            if (array_key_exists(self::MESSAGE, $properties)) {
                $this->message = Filter::sanitizeMessage($properties[self::MESSAGE]);
            }

            if (array_key_exists(self::SUBJECT, $properties)) {
                $this->subject = Filter::sanitizeString($properties[self::SUBJECT]);
            }

            # Set the usersSpecification subobject
            $this->usersSpecification->set($properties);

            # Set the schedule subobject
            $this->schedule->set($properties);
        }
    }

    public function getDraftCopy()
    {
        $notification = clone($this);
        $notification->id = null;  // ID will get set the first time the notification is stored
        $notification->status = self::STATUS_DRAFT;

        return $notification;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }

    public function getUsersSpecification()
    {
        return $this->usersSpecification;
    }
}
