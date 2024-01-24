<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing a schedule for a notification.
 */
class Schedule
{
    public const OBJECT_VERSION = 1;

    public const SCHEDULING_OPTION   = 'schedulingOption';
    public const SCHED_OPT_NOW       = 'schedOptNow';
    public const SCHED_OPT_FUTURE    = 'schedOptFuture';
    public const SCHED_OPT_RECURRING = 'schedOptRecurring';
    public const SCHEDULING_OPTIONS  = [
        self::SCHED_OPT_NOW,
        self::SCHED_OPT_FUTURE,
        self::SCHED_OPT_RECURRING
    ];

    public const SEND_TIME = 'sendTime';

    public const RECURRING_OPTION = 'recurringOption';
    public const REC_OPT_DAILY    = 'recOptDaily';
    public const REC_OPT_MONTHLY  = 'recOptMonthy';

    public const DAY_CHECKS = 'dayChecks';
    public const DAY_TIMES  = 'dayTimes';

    public const MONTH_CHECKS = 'monthChecks';
    public const MONTH_TIMES = 'monthTimes';

    public const MONTH_DAY_OPTION     = 'monthDayOption';
    public const MON_DAY_OPT_NUMBER   = 'monDayOptNumber';
    public const MON_DAY_OPT_WEEK_DAY = 'monDayOptWeekDay';

    public const MONTH_DAYS  = 'monthDays';

    public const MONTH_WEEK_NUMBER = 'monthWeekNumber';
    public const MONTH_DAY_OF_WEEK = 'monthDayOfWeek';

    public const START_DATE = 'startDate';

    public const END_DATE_CHECKED = 'endDateChecked';
    public const END_DATE         = 'endDate';

    public const TIMES_PER_USER_CHECK = 'timesPerUserCheck';
    public const TIMES_PER_USER       = 'timesPerUser';

    // Day type constants
    public const MONTH_DAY_TYPE_NUMBER = 0;    // for this type, day = 21 => 21st
    public const MONTH_DAY_TYPE_FIRST  = 1;    // for this type, day = 6 => saturday
    public const MONTH_DAY_TYPE_SECOND = 2;
    public const MONTH_DAY_TYPE_THIRD  = 3;
    public const MONTH_DAY_TYPE_FOURTH = 4;
    public const MONTH_DAY_TYPE_LAST   = 5;

    private $objectVersion;

    private $schedulingOption;

    private $sendTime;   // for scheuling in the future option

    private $recurringOption;

    private $dayChecks;
    private $dayTimes;

    private $monthChecks;
    private $monthDayOptions;
    private $monthDays;
    private $monthTimes;  // Note: need to use (monthNumber - 1 ) to index the month times

    private $weekTimes;
    // private $monthTimes;  // null => no time set

    // For month day of week
    private $monthWeekNumber;   // Use (monthNumber - 1) as index
    private $monthDayOfWeek;   // Use (monthNumber - 1) as index

    private $startDate;

    private $endDateChecked;
    private $endDate;

    private $timesPerUserCheck;
    private $timesPerUser;


    // Month time array:
    // [0] day type: 0 => actual day of month, 1 => 1st, 2 => 2nd, 3 => 3rd, 4 => 4th, 5 => last
    // [1] day: if actual the month day, else 0 => Sunday, 1 => Monday, etc.
    // [2] hours
    // [3] minutes

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->startDate      = date('m/d/Y');
        $this->endDateChecked = false;
        $this->endDate        = null;

        $this->dayChecks = array();
        $this->dayTimes  = array();

        $this->weekTimes = array_fill(0, 7, null);

        $this->monthChecks = array();
        $this->monthTimes  = array_fill(1, 12, null);
    }

    public function migrate()
    {
    }

    public function validate()
    {
        if (empty($this->schedulingOption)) {
            throw new \Exception("No scheduling option specified.\n");
        } elseif (!in_array($this->schedulingOption, self::SCHEDULING_OPTIONS)) {
            throw new \Exception("The scheduling option \"{$this->schedulingOption}\" is not valid.\n");
        }

        if ($this->schedulingOption === self::SCHED_OPT_FUTURE) {
            if (empty($this->sendTime)) {
                throw new \Exception("No time was specified for scheduling in the future.");
            }
        } elseif ($this->schedulingOption === self::SCHED_OPT_RECURRING) {
            if (empty($this->recurringOption)) {
                throw new \Exception("No option specified for recurring schedule.");
            } elseif ($this->recurringOption === self::REC_OPT_DAILY) {
                if (empty($this->dayChecks)) {
                    throw new \Exception("No days selected for recurring scheduling.");
                } else {
                    foreach ($this->dayChecks as $dayCheck) {
                        $dayName = DateInfo::WEEKDAY_NAMES[$dayCheck];
                        $time = $this->dayTimes[$dayCheck];
                        if (empty($time)) {
                            throw new \Exception("Schedule has no time specified for {$dayName}.");
                        }
                        try {
                            DateInfo::validateTime($time);
                        } catch (\Exception $exception) {
                            $errorMessage = "Schedule time for day {$dayName} is invalid: {$exception->getMessage()}";
                            throw new \Exception($errorMessage);
                        }
                    }
                }
            } elseif ($this->recurringOption === self::REC_OPT_MONTHLY) {
                if (empty($this->monthChecks)) {
                    throw new \Exception("No months selected for recurring scheduling.");
                } else {
                    foreach ($this->monthChecks as $month) {
                        $monthName = DateInfo::MONTH_NAMES[$month];

                        if (empty($this->monthDayOptions[$month])) {
                            throw new \Exception("No day option selected for {$monthName}.");
                        } elseif ($this->monthDayOptions[$month] === self::MON_DAY_OPT_NUMBER) {
                            $day = $this->monthDays[$month];
                            if (empty($day) || !is_numeric($day) || $day < 1 || $day > 31) {
                                $errorMessage = "The selected day value \"{$day}\"for {$monthName} is invalid.";
                                throw new \Exception($errorMessage);
                            }
                        } elseif ($this->monthDayOptions[$month] === self::MON_DAY_OPT_WEEK_DAY) {
                            $weekNum = $this->monthWeekNumber[$month];
                            if (empty($weekNum) || $weekNum < 1 || $weekNum > 5) {
                                throw new \Exception("The week specification for {$monthName} is invalid.");
                            }

                            $dayOfWeek = $this->monthDayOfWeek[$month];
                            if ($dayOfWeek == null || !is_numeric($dayOfWeek) || $dayOfWeek < 0 || $dayOfWeek > 6) {
                                $errorMessage = "The day of week specification \"{$dayOfWeek}\""
                                   . " for {$monthName} is invalid.";
                                throw new \Exception($errorMessage);
                            }
                        } else {
                            $errorMessage = "Invalid month day option \"{$this->monthDayOptions[$month]}\""
                                . " for {$monthName} selected.";
                            throw new \Exception($errorMessage);
                        }

                        $time = $this->monthTimes[$month - 1];
                        try {
                            DateInfo::validateTime($time);
                        } catch (\Exception $exception) {
                            throw new \Exception("Month {$monthName} has a invalid time: {$exception->getMessage()}");
                        }
                    }
                }
            } else {
                throw new \Exception("The recurring scheduling option \"{$this->recurringOption}\" is not valid.\n");
            }
        }

        try {
            DateInfo::validateMdyDate($this->startDate);
        } catch (\Exception $exception) {
            throw new \Exception("Start date error: " . $exception->getMessage());
        }

        if (!empty($this->endDate)) {
            try {
                DateInfo::validateMdyDate($this->endDate);
            } catch (\Exception $exception) {
                throw new \Exception("End date error: " . $exception->getMessage());
            }
        }
    }

    public function setMonthTime($month, $dayType, $day, $hours, $minutes)
    {
        $time = [$dayType, $day, $hours, $minutes];
        $this->monthTimes[$month] = $time;
    }

    /**
     * If the schedule has a time in the range (startTime, endTime].
     * This does not apply to the "now" option.
     */
    public function hasTime($startTime, $endTime)
    {
        $hasTime = false;

        // If startTime is after endDate of schedule, then there is not a time within the range

        // TO BE COMPLETED

        // Get all the send times for the current day (what if range spans 2 days?????)
        // [For spanning 2 days, get all for both days, and for the first day any time after the last time is sent,
        // and for the next day any time before the current send time is sent]
        // if time is after last send time, but before current sent time, then send the notification
        // NEED TO TAKE START AND END TIME INTO ACCOUNT
    }

    public function set($properties)
    {
        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::SCHEDULING_OPTION, $properties)) {
                $this->schedulingOption = Filter::sanitizeLabel($properties[self::SCHEDULING_OPTION]);
            }

            if (array_key_exists(self::SEND_TIME, $properties)) {
                $this->sendTime = Filter::sanitizeDateTime($properties[self::SEND_TIME]);
            }

            if (array_key_exists(self::RECURRING_OPTION, $properties)) {
                $this->recurringOption = Filter::sanitizeLabel($properties[self::RECURRING_OPTION]);
            }


            #------------------------
            # Days
            #------------------------
            if (array_key_exists(self::DAY_CHECKS, $properties)) {
                $this->dayChecks = $properties[self::DAY_CHECKS];  // Need array filter here
            }

            if (array_key_exists(self::DAY_TIMES, $properties)) {
                $this->dayTimes = $properties[self::DAY_TIMES];  // Need array filter here
            }


            #------------------------
            # Months
            #------------------------
            if (array_key_exists(self::MONTH_CHECKS, $properties)) {
                $this->monthChecks = $properties[self::MONTH_CHECKS];  // Need array filter here
            }

            foreach (range(1, 12) as $month) {
                if (array_key_exists(self::MONTH_DAY_OPTION . $month, $properties)) {
                    // Need array filter here
                    $this->monthDayOptions[$month] = $properties[self::MONTH_DAY_OPTION . $month];
                }
            }

            foreach (range(1, 12) as $month) {
                if (array_key_exists(self::MONTH_DAYS . $month, $properties)) {
                    $this->monthDays[$month] = $properties[self::MONTH_DAYS . $month];  // Need array filter here
                }
            }

            if (array_key_exists(self::MONTH_WEEK_NUMBER, $properties)) {
                $this->monthWeekNumber = $properties[self::MONTH_WEEK_NUMBER];  // Need array filter here
            }

            if (array_key_exists(self::MONTH_DAY_OF_WEEK, $properties)) {
                $this->monthDayOfWeek = $properties[self::MONTH_DAY_OF_WEEK];  // Need array filter here
            }

            if (array_key_exists(self::MONTH_TIMES, $properties)) {
                $this->monthTimes = $properties[self::MONTH_TIMES];  // Need array filter here
            }


            #--------------------------------------------
            # Ending properties
            #--------------------------------------------
            if (array_key_exists(self::START_DATE, $properties)) {
                $this->startDate = Filter::sanitizeDate($properties[self::START_DATE]);
            }

            if (array_key_exists(self::END_DATE_CHECKED, $properties)) {
                $this->endDateChecked = true;
            } else {
                $this->endDateChecked = false;
            }

            if (array_key_exists(self::END_DATE, $properties)) {
                $this->endDate = Filter::sanitizeDate($properties[self::END_DATE]);
            }

            #----------------------------------------------
            # Times per user limit
            #----------------------------------------------
            if (array_key_exists(self::TIMES_PER_USER_CHECK, $properties)) {
                $this->timesPerUserCheck = true;
            } else {
                $this->timesPerUserCheck = false;
            }

            if (array_key_exists(self::TIMES_PER_USER, $properties)) {
                $this->timesPerUser = Filter::sanitizeDate($properties[self::TIMES_PER_USER]);
            }
        }
    }


    /**
     * Gets the next timestamp after the specified timestamp for the schedule that is
     * not after the end date for the schedule. If there is no such time, then a null
     * timestamp is returned.
     */
    public function getNextRecurringTimestamp($startTimestamp)
    {
        $timestamp = null;

        # NEED TO ADD IN END DATE - if next data is past end date, then null should be returned

        if ($this->schedulingOption === Schedule::SCHED_OPT_RECURRING) {
            #----------------------------------------------------------
            # Factor in the schedule's start time. This method should
            # not generate a timestamp before the schedule's
            # start date.
            #----------------------------------------------------------
            $scheduleStartTimestamp = $this->getStartTimestamp();
            if ($scheduleStartTimestamp > $startTimestamp) {
                $startTimestamp = $scheduleStartTimestamp;
            }

            $dateInfo = new DateInfo($startTimestamp);

            $endTimestamp = $this->getEndTimestamp();

            if ($this->recurringOption === Schedule::REC_OPT_DAILY) {
                $dayOfWeek = $dateInfo->getDayOfWeek();

                $dayIncrement = 0;
                for ($dayCount = $dayOfWeek; $dayCount <= $dayOfWeek + 6; $dayCount++) {
                    $day = $dayCount % 7;
                    if (in_array($day, $this->dayChecks)) {
                        $time = $this->dayTimes[$day];
                        list($hours, $minutes) = explode(':', $time);
                        $month = $dateInfo->getMonth();
                        $day   = $dateInfo->getDay();
                        $year  = $dateInfo->getYear();
                        $timestamp = mktime($hours, $minutes, 0, $month, $day + $dayIncrement, $year);
                        if ($endTimestamp != null && $timestamp > $endTimestamp) {
                            $timestamp = null;
                        }
                        break;
                    }
                    $dayIncrement++;
                }
            } elseif ($this->recurringOption === Schedule::REC_OPT_MONTHLY) {
                $startMonth     = $dateInfo->getMonth();
                $year           = $dateInfo->getYear();
                $startTimestamp = $dateInfo->getTimestamp();

                for ($monthCount = 0; $monthCount <= 11; $monthCount++) {
                    $month = $monthCount + $startMonth;
                    $yearIncrement = 0;
                    if ($month > 12) {
                        $month -= 12;
                        $yearIncrement = 1;
                    }

                    if (in_array($month, $this->monthChecks)) {
                        $time = $this->monthTimes[$month - 1];

                        list($hours, $minutes) = explode(':', $time);

                        $day = 0;
                        if ($this->monthDayOptions[$month] === self::MON_DAY_OPT_NUMBER) {
                            # error_log("SCHEDULE: month day option\n", 3, __DIR__ . '/../send-log.txt');
                            $day = $this->monthDays[$month];
                            # error_log("SCHEDULE: day={$day}.\n", 3, __DIR__ . '/../send-log.txt');
                        } elseif ($this->monthDayOptions[$month] === self::MON_DAY_OPT_WEEK_DAY) {
                            $dayOfWeek = $this->monthDayOfWeek[$month - 1];
                            $dayOfWeekNumber = $this->monthWeekNumber[$month - 1];

                            $day = DateInfo::getMonthDayNumber(
                                $dayOfWeek,
                                $dayOfWeekNumber,
                                $month,
                                $year + $yearIncrement
                            );
                        } else {
                            throw new \Exception("Invalid month day option.");
                        }

                        # error_log("", 3, __DIR__ . '/../send-log.txt');
                        # error_log("SCHEDULE: hours=\"{$hours}\", minutes=\"{$minutes}\", $month={$month},"
                        #   . "day={$day}, year="
                        #   . ($year + $yearIncrement) . ".\n",
                        #    3, __DIR__ . '/../send-log.txt');
                        # error_log("SCHEDULE: startTimestamp=\"{$startTimestamp}\"\n",
                        # 3, __DIR__ . '/../send-log.txt');
                        # error_log("SCHEDULE: endTimestamp=\"{$endTimestamp}\"\n", 3, __DIR__ . '/../send-log.txt');

                        $timestamp = mktime($hours, $minutes, 0, $month, $day, $year + $yearIncrement);
                        if ($endTimestamp != null && $timestamp > $endTimestamp) {
                            $timestamp = null;
                            break;
                        } elseif ($timestamp > $startTimestamp) {
                            break;
                        } else {
                            # timestamp is before or equal to start timestamp
                            $timestamp = null;
                        }
                    }
                }
            }
        }

        return $timestamp;
    }

    public function toString()
    {
        $value = '';

        if (empty($this->schedulingOption)) {
            $value = 'None';
        } elseif ($this->schedulingOption === self::SCHED_OPT_NOW) {
            $value = 'Now';
        } elseif ($this->schedulingOption === self::SCHED_OPT_FUTURE) {
            $value = 'Future: ' . $this->sendTime;
        } elseif ($this->schedulingOption === self::SCHED_OPT_RECURRING) {
            $value = "Recurring: \n";

            $value .= "    Start Date: {$this->startDate}\n";
            if ($this->endDateChecked) {
                $value .= "    End Date: {$this->endDate}\n";
            }
            if ($this->timesPerUserCheck) {
                $value .= "    Times per user: {$this->timesPerUser}\n";
            }

            if (empty($this->recurringOption)) {
                $value .= '    Unspecified';
            } elseif ($this->recurringOption === self::REC_OPT_DAILY) {
                $value .= "    Daily:\n";
                foreach ($this->dayChecks as $dayCheck) {
                    $value .= "        " . DateInfo::WEEKDAY_NAMES[$dayCheck]
                        . " " . $this->dayTimes[$dayCheck]
                        . "\n";
                }
            } elseif ($this->recurringOption === self::REC_OPT_MONTHLY) {
                $value .= "    Monthly:\n";
                foreach ($this->monthChecks as $monthCheck) {
                    $value .= "        " . DateInfo::MONTH_NAMES[$monthCheck];
                    if (empty($this->monthDayOptions[$monthCheck])) {
                        $value .= " None";
                    } elseif ($this->monthDayOptions[$monthCheck] === self::MON_DAY_OPT_NUMBER) {
                        $value .= ' ' . $this->monthDays[$monthCheck];
                    } elseif ($this->monthDayOptions[$monthCheck] === self::MON_DAY_OPT_WEEK_DAY) {
                        $value .= ' ' . DateInfo::WEEK_NUMBER_NAME[$this->monthWeekNumber[$monthCheck - 1]]
                            . ' ' . DateInfo::WEEKDAY_NAMES[$this->monthDayOfWeek[$monthCheck - 1]]
                            ;
                    } else {
                        $value .= " Invalid";
                    }
                    $value .= " " . $this->monthTimes[$monthCheck - 1] . "\n";
                }
                # print "\nMONTH CHECK: {$monthCheck}\n";
                # print_r($this->monthDays);
            } else {
                $value = '    Invalid';
            }
        } else {
            $value = 'Invalid';
        }

        return $value;
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getSchedulingOption()
    {
        return $this->schedulingOption;
    }

    public function getSendTime()
    {
        return $this->sendTime;
    }

    public function getRecurringOption()
    {
        return $this->recurringOption;
    }

    public function getDayChecks()
    {
        return $this->dayChecks;
    }

    public function getDayTimes()
    {
        return $this->dayTimes;
    }

    public function getMonthChecks()
    {
        return $this->monthChecks;
    }

    public function getMonthDayOptions()
    {
        return $this->monthDayOptions;
    }

    public function getMonthDays()
    {
        return $this->monthDays;
    }

    public function getMonthTimes()
    {
        return $this->monthTimes;
    }

    public function getMonthWeekNumber()
    {
        return $this->monthWeekNumber;
    }

    public function getMonthDayOfWeek()
    {
        return $this->monthDayOfWeek;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        return $this->startDate = $startDate;
    }

    public function getStartTimestamp()
    {
        $startTimestamp = strtotime($this->startDate . ' 00:00');
        return $startTimestamp;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getEndTimestamp()
    {
        $endTimestamp = null;
        if ($this->getEndDateChecked() && !empty($this->endDate)) {
            $endTimestamp = strtotime($this->endDate . ' 24:00');
        }

        return $endTimestamp;
    }

    public function getEndDateChecked()
    {
        return $this->endDateChecked;
    }

    public function getTimesPerUserCheck()
    {
        return $this->timesPerUserCheck;
    }

    public function getTimesPerUser()
    {
        return $this->timesPerUser;
    }
}
