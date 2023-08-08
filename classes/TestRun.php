<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for making test runs.
 */
class TestRun
{
    public const START_DATE = 'startDate';
    public const END_DATE   = 'endDate';

    public const TEST_EMAIL = 'testEmail';

    public const NOTIFICATION_ID = 'notificationId';

    public const SEND_EMAILS = 'sendEmails';
    public const LOG_EMAILS  = 'logEmails';

    public const UPDATE_USER_NOTIFICATION_COUNTS = 'updateUserNotificationCounts';

    private $startDate;
    private $endDate;
    private $testEmail;

    private $notificationId;

    private $sendEmails;
    private $logEmails;
    private $updateUserNotificationCounts;


    public function __construct()
    {
        $this->notificationId = 0;  // 0 => All notifications

        $this->sendEmails = true;

        $this->logEmails = false;

        $this->updateUserNotificationCounts = false;
    }

    public function set($properties)
    {
        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::START_DATE, $properties)) {
                $this->startDate = Filter::sanitizeDate($properties[self::START_DATE]);
            }

            if (array_key_exists(self::END_DATE, $properties)) {
                $this->endDate = Filter::sanitizeDate($properties[self::END_DATE]);
            }

            if (array_key_exists(self::TEST_EMAIL, $properties)) {
                $this->testEmail = Filter::sanitizeEmail($properties[self::TEST_EMAIL]);
            }

            if (array_key_exists(self::NOTIFICATION_ID, $properties)) {
                $this->notificationId = Filter::sanitizeInt($properties[self::NOTIFICATION_ID]);
            }

            if (array_key_exists(self::SEND_EMAILS, $properties)) {
                $this->sendEmails = true;
            } else {
                $this->sendEmails = false;
            }

            if (array_key_exists(self::LOG_EMAILS, $properties)) {
                $this->logEmails = true;
            } else {
                $this->logEmails = false;
            }

            if (array_key_exists(self::UPDATE_USER_NOTIFICATION_COUNTS, $properties)) {
                $this->updateUserNotificationCounts = true;
            } else {
                $this->updateUserNotificationCounts = false;
            }
        }
    }

    public function validate()
    {
        if (empty($this->startDate)) {
            throw new \Exception("No start date specified.");
        } else {
            DateInfo::validateMdyDate($this->startDate);
        }

        if (empty($this->endDate)) {
            throw new \Exception("No end date specified.");
        } else {
            DateInfo::validateMdyDate($this->endDate);
        }

        $startTimestamp = strtotime($this->startDate);
        $endTimestamp   = strtotime($this->endDate);
        if ($endTimestamp < $startTimestamp) {
            throw new \Exception("The end date is before the start date.");
        }

        if (empty($this->testEmail)) {
            throw new \Exception("No test e-mail specified.");
        } elseif (!Filter::isEmail($this->testEmail)) {
            throw new \Exception("The specified test e-mail \"{$this->fromEmail}\" is not a valid e-mail.");
        }

        if (!isset($this->notificationId)) {
            throw new \Exception("No notification specified.");
        } elseif (!is_int($this->notificationId) && !ctype_digit($this->notificationId)) {
            $message = 'Invalid format for notification. Non-integer value for notification ID: "'
                . $this->notificationId . '".';
            throw new \Exception($message);
        }
    }

    public function toString()
    {
        $notificationId = $this->notificationId;
        if ($notificationId == 0) {
            $notificationId = 'all';
        }

        $value = "start date: {$this->startDate}\n"
            . "end date: {$this->endDate}\n"
            . "test e-mail: {$this->testEmail}\n"
            . "notification ID: {$notificationId}\n"
            ;

        if ($this->sendEmails) {
            $value .= "send e-mails: true\n";
        } else {
            $value .= "send e-mails: false\n";
        }

        if ($this->logEmails) {
            $value .= "log e-mails: true\n";
        } else {
            $value .= "log e-mails: false\n";
        }

        if ($this->updateUserNotificationCounts) {
            $value .= "update user notification counts: true\n";
        } else {
            $value .= "update user notification counts: false\n";
        }

        return $value;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getTestEmail()
    {
        return $this->testEmail;
    }

    public function setTestEmail($testEmail)
    {
        $this->testEmail = $testEmail;
    }

    public function getNotificationId()
    {
        return $this->notificationId;
    }

    public function getSendEmails()
    {
        return $this->sendEmails;
    }

    public function getLogEmails()
    {
        return $this->logEmails;
    }

    public function getUpdateUserNotificationCounts()
    {
        return $this->updateUserNotificationCounts;
    }
}
