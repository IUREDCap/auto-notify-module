<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Date information class.
 */
class DateInfo
{
    private $timestamp;
    private $info;
    private $daysInMonth;

    # Numbering corresponds to wday for PHP's getdate
    public const WEEKDAY_NAMES = array(
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
    );

    public const MONTH_NAMES = array(
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    );

    public const WEEK_NUMBER_NAME = array(
        1 => 'first',
        2 => 'second',
        3 => 'third',
        4 => 'fourth',
        5 => 'last'
    );

    public function __construct($timestamp = null)
    {
        if ($timestamp == null) {
            $timestamp = time();
        }

        $this->timestamp = $timestamp;

        $this->info = getdate($timestamp);
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->info['mon'], $this->info['year']);
    }


    /**
     * Modify the date/time using values such as "+1 year" "-2 weeks";
     * see strtotime documentation (https://www.php.net/manual/en/function.strtotime.php)
     * for more information.
     */
    public function modify($value)
    {
        $this->timestamp = strtotime($value, $this->timestamp);
        $this->info = getdate($timestamp);
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->info['mon'], $this->info['year']);
    }

    /**
     * @param string $dateTime date time string
     */
    public function setToDateTime($dateTime)
    {
        $this->timestamp = strtotime($dateTime);
        $this->info = getdate($this->timestamp);
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->info['mon'], $this->info['year']);
    }

    /**
     * Checks to see if a datetime string has formay "m/d/y hh:mm"
     * @param string $dateTime A datetime string.
     */
    public static function validateMdyHmTimestamp($dateTime)
    {
        if ($dateTime == null) {
            throw new \Exception('Invalid null date time.');
        }

        $dateInfo = explode(' ', $dateTime);
        if (count($dateInfo) !== 2) {
            throw new \Exception('Date time "' . $dateTime . '" does not have the correct "m/d/yyyy hh:mm" format.');
        }

        $date = $dateInfo[0];
        $time = $dateInfo[1];

        self::validateMdyDate($date);
        self::validateTime($time);
    }

    public static function validateMdyDate($date)
    {
        $matches = array();

        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $date, $matches) === 1) {
            $month = $matches[1];
            $day   = $matches[2];
            $year  = $matches[3];
            if (!checkdate($month, $day, $year)) {
                throw new \Exception('Date "' . $date . '" does not have valid month, day and/or year values.');
            }
        } else {
            throw new \Exception('Date "' . $date . '" is not in m/d/y format.');
        }
    }

    /**
     * Validates a time in the format hh:mm (e.g., 01:30, 20:00)
     */
    public static function validateTime($time)
    {
        $matches = array();

        if (preg_match('/(\d{2}):(\d{2})/', $time, $matches) === 1) {
            $hours   = $matches[1];
            $minutes = $matches[2];

            if ($hours > 24 || $minutes > 60) {
                throw new \Exception('Time "' . $time . '" does not have a valid value.');
            }
        } else {
            throw new \Exception('Time "' . $time . '" is not in hh:mm format.');
        }
    }

    public static function convertMdyTimestampToYmdTimestamp($mdyTimestamp)
    {
        if ($mdyTimestamp == null || trim($mdyTimestamp) === '') {
            throw new \Exception('Date time "' . $mdyTimestamp . '" is blank.');
        }

        list($date, $time) = explode(' ', $mdyTimestamp);
        list($month, $day, $year) = explode('/', $date);
        list($hours, $minutes) = explode(':', $time);

        if (strlen($month) === 1) {
            $month = '0' . $month;
        }

        if (strlen($day) === 1) {
            $day = '0' . $day;
        }

        $timestampString = "{$year}-{$month}-{$day} {$hours}:{$minutes}";

        return $timestampString;
    }

    /**
     * Gets the day number for a specified ordinal week number and day of week for a specified month and year,
     * for example, the day number (9) for the second Saturday for April 2022.
     *
     * @param int $dayOfWeek The day number of the week (0 => Sunday, 1 => Monday, ...).
     * @param int $weekNumber The week ordinal number (1 => 1st, 2 => 2nd, 2 => 3rd, 4 => 4th, 5 => last).
     * @param int $month The month number (1 => January, 2 => February, ...).
     * @param int $year The year, e.g., 2023.
     *
     * @return int The number of the specified day (e.g., 1, 2, ..., 31).
     *
     */
    public static function getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year)
    {
        # print "\n----------------------------------------------------------\n";
        # print "Day of week: {$dayOfWeek} - week number: {$weekNumber} - {$month}/{$year}\n";
        $day = null;

        // Get a DateInfo object for the month
        $timestamp = strtotime($year . '-' . $month . '-01 12:00:00');
        $dateInfo = new DateInfo($timestamp);

        $firstDayOfWeek = $dateInfo->getDayOfWeek();

        # print "first day of week: {$firstDayOfWeek}\n";

        if ($firstDayOfWeek <= $dayOfWeek) {
            $firstDayNumber = $dayOfWeek - $firstDayOfWeek + 1;
        } else {
            $firstDayNumber = 8 - ($firstDayOfWeek - $dayOfWeek);
        }

        # print "first day number: {$firstDayNumber}\n";

        if ($weekNumber == 1) {
            $day = $firstDayNumber;
        } elseif ($weekNumber == 2) {
            $day = $firstDayNumber + 7;
        } elseif ($weekNumber == 3) {
            $day = $firstDayNumber + 14;
        } elseif ($weekNumber == 4) {
            $day = $firstDayNumber + 21;
        } elseif ($weekNumber == 5) {
            $day = $firstDayNumber + 28;
            if ($day > $dateInfo->daysInMonth) {
                $day -= 7;
            }
        }

        return $day;
    }


    public static function getNumberOfDaysInMonth($month, $year)
    {
        // Get a DateInfo object for the month
        $timestamp = strtotime($year . '-' . $month . '-01 12:00:00');
        $dateInfo = new DateInfo($timestamp);
        return $dateInfo->daysInMonth;
    }

    public static function timestampToString($timestamp)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);
        $dateTimeString = date_format($dateTime, "Y-m-d H:i:s");
        return $dateTimeString;
    }


    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getTimestampString()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->timestamp);
        $value = date_format($dateTime, "Y-m-d H:i");
        return $value;
    }

    public function getYmdDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->timestamp);
        $value = date_format($dateTime, "Y-m-d");
        return $value;
    }

    public function getMdyDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->timestamp);
        $value = date_format($dateTime, "m/d/Y");
        return $value;
    }

    public function getTime()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->timestamp);
        $value = date_format($dateTime, "H:i");
        return $value;
    }

    public function getYear()
    {
        return $this->info['year'];
    }

    /**
     * Returns the month number, e.g., 1 for January, 2 for February, etc.
     */
    public function getMonth()
    {
        return $this->info['mon'];
    }

    public function getMonthName()
    {
        return $this->info['month'];
    }

    public function getDay()
    {
        return $this->info['mday'];
    }

    public function getDayOfWeek()
    {
        $day = $this->info['wday'];
        return $day;
    }

    public function getDayOfWeekName()
    {
        $day = $this->info['weekday'];
        return $day;
    }

    public function getHours()
    {
        return $this->info['hours'];
    }

    public function getMinutes()
    {
        return $this->info['minutes'];
    }

    public function getDaysInMonth()
    {
        return $this->daysInMonth;
    }

    /**
     * Indicates if it is the first week day of the month, i.e., if the
     * current week day is Monday, this method indicates if the date
     * is the first Monday oc the month.
     */
    public function isFirstWeekDayOfMonth()
    {
        $isFirst = $this->info['mday'] <= 7;
        return $isFirst;
    }

    public function isSecondWeekDayOfMonth()
    {
        $isSecond = $this->info['mday'] > 7 && $this->info['mday'] <= 14;
        return $isSecond;
    }

    public function isThirdWeekDayOfMonth()
    {
        $isThird = $this->info['mday'] > 14 && $this->info['mday'] <= 21;
        return $isThird;
    }

    public function isFourthWeekDayOfMonth()
    {
        $isFourth = $this->info['mday'] > 21 && $this->info['mday'] <= 28;
        return $isFourth;
    }

    public function isLastWeekDayOfMonth()
    {
        $isLast = ($this->daysInMonth - $this->info['mday']) < 7;
        return $isLast;
    }

    public function isLastDayOfMonth()
    {
        return $this->info['mday'] == $this->daysInMonth;
    }

    public function getNumberOfWeekDayInMonth()
    {
        $monthDay = $this->info['mday'];
        return (floor(($monthDay - 1) / 7) + 1);
    }
}
