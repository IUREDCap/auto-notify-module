<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for specifying filtering of notification schedule results.
 */
class ScheduleFilter
{
    public const NOTIFICATION_ID = 'notificationId';

    public const START_DATE = 'startDate';
    public const END_DATE   = 'endDate';

    public const DISPLAY_MODE = 'displayMode';

    public const LIST_DISPLAY_MODE = 1;
    public const CALENDAR_DISPLAY_MODE = 2;

    private $notificationId;

    private $startDate;
    private $endDate;

    private $displayMode;


    public function __construct()
    {
        $this->notificationId = 0;

        $this->startDate = date('m/d/Y');
        $this->endDate   = date('m/d/Y');

        $this->displayMode = self::LIST_DISPLAY_MODE;
    }

    public function set($properties)
    {
        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::NOTIFICATION_ID, $properties)) {
                $this->notificationId = Filter::sanitizeInt($properties[self::NOTIFICATION_ID]);
            }

            if (array_key_exists(self::START_DATE, $properties)) {
                $this->startDate = Filter::sanitizeDate($properties[self::START_DATE]);
            }

            if (array_key_exists(self::END_DATE, $properties)) {
                $this->endDate = Filter::sanitizeDate($properties[self::END_DATE]);
            }

            if (array_key_exists(self::DISPLAY_MODE, $properties)) {
                $this->displayMode = Filter::sanitizeInt($properties[self::DISPLAY_MODE]);
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

        $todayTimestamp = strtotime('00:00');
        if ($startTimestamp < $todayTimestamp) {
            throw new \Exception('The start date "' . $this->startDate . '" is before the current date. ');
        }

        if ($endTimestamp < $startTimestamp) {
            throw new \Exception("The end date is before the start date.");
        }
    }

    public function getNotificationId()
    {
        return $this->notificationId;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getDisplayMode()
    {
        return $this->displayMode;
    }
}
