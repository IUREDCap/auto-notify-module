<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class DateInfoTest extends TestCase
{
    public function testCreate()
    {
        $dateInfo = new DateInfo();
        $this->assertNotNull($dateInfo, 'Object creation test');
    }

    public function testDate1()
    {
        $dateTime = \DateTime::createFromFormat('d-m-Y H:i:s', '30-11-2022 08:15:00');
        $timestamp = $dateTime->getTimestamp();

        $dateInfo = new DateInfo($timestamp);
        $dayOfWeekName = $dateInfo->getDayOfWeekName();

        $this->assertEquals('Wednesday', $dayOfWeekName, 'Day of week check.');

        $daysInMonth = $dateInfo->getDaysInMonth();
        $this->assertEquals(30, $daysInMonth, 'Days in month check.');

        $isFirstWeekDay = $dateInfo->isFirstWeekDayOfMonth();
        $this->assertFalse($isFirstWeekDay, 'First weekday of month check');

        $isSecondWeekDay = $dateInfo->isSecondWeekDayOfMonth();
        $this->assertFalse($isSecondWeekDay, 'Second weekday of month check');

        $isThirdWeekDay = $dateInfo->isThirdWeekDayOfMonth();
        $this->assertFalse($isThirdWeekDay, 'Third weekday of month check');

        $isFourthWeekDay = $dateInfo->isFourthWeekDayOfMonth();
        $this->assertFalse($isFourthWeekDay, 'Fourth weekday of month check');

        $isLastWeekDay = $dateInfo->isLastWeekDayOfMonth();
        $this->assertTrue($isLastWeekDay, 'Last weekday of month check');

        $numberOfWeekDayInMonth = $dateInfo->getNumberOfWeekDayInMonth();
        $this->assertEquals(5, $numberOfWeekDayInMonth, 'Number of week day in month check.');
    }

    public function testDate2()
    {
        $dateTime = \DateTime::createFromFormat('d-m-Y H:i:s', '28-02-2023 14:20:00');
        $timestamp = $dateTime->getTimestamp();

        $dateInfo = new DateInfo($timestamp);
        $dayOfWeekName = $dateInfo->getDayOfWeekName();

        $this->assertEquals('Tuesday', $dayOfWeekName, 'Day of week check.');

        $daysInMonth = $dateInfo->getDaysInMonth();
        $this->assertEquals(28, $daysInMonth, 'Days in month check.');

        $isFirstWeekDay = $dateInfo->isFirstWeekDayOfMonth();
        $this->assertFalse($isFirstWeekDay, 'First weekday of month check');

        $isSecondWeekDay = $dateInfo->isSecondWeekDayOfMonth();
        $this->assertFalse($isSecondWeekDay, 'Second weekday of month check');

        $isThirdWeekDay = $dateInfo->isThirdWeekDayOfMonth();
        $this->assertFalse($isThirdWeekDay, 'Third weekday of month check');

        $isFourthWeekDay = $dateInfo->isFourthWeekDayOfMonth();
        $this->assertTrue($isFourthWeekDay, 'Fourth weekday of month check');

        $isLastWeekDay = $dateInfo->isLastWeekDayOfMonth();
        $this->assertTrue($isLastWeekDay, 'Last weekday of month check');

        $numberOfWeekDayInMonth = $dateInfo->getNumberOfWeekDayInMonth();
        $this->assertEquals(4, $numberOfWeekDayInMonth, 'Number of week day in month check.');
    }

    public function testMonthDay()
    {
        $dayOfWeek       = 2; // Tuesday
        $dayOfWeekNumber = 5; // Last (Tuesday of the month)
        $month           = 2; // February
        $year            = 2022;

        $day = DateInfo::getMonthDayNumber($dayOfWeek, $dayOfWeekNumber, $month, $year);
        $this->assertEquals(22, $day, 'Last Tuesday of February 2022 check.');


        $dayOfWeek       = 5; // Friday
        $dayOfWeekNumber = 2; // Second
        $month           = 4; // April
        $year            = 2022;

        $day = DateInfo::getMonthDayNumber($dayOfWeek, $dayOfWeekNumber, $month, $year);
        $this->assertEquals(8, $day, 'Second Friday of April 2022 check.');
    }

    public function testSetToDateTime()
    {
        $dateInfo = new DateInfo();
        $dateInfo->setToDateTime("2023-03-17 01:23");
        $this->assertNotNull($dateInfo, 'Object not null test');

        $this->assertEquals(31, $dateInfo->getDaysInMonth(), 'Days in month test');

        $this->assertEquals('March', $dateInfo->getMonthName(), 'Month name test');

        $this->assertEquals(1, $dateInfo->getHours(), 'Hours test');
        $this->assertEquals(23, $dateInfo->getMinutes(), 'Minutes test');
        $this->assertFalse($dateInfo->isLastDayOfMonth(), 'Last day of month test');

        $this->assertEquals(strtotime("2023-03-17 01:23:00"), $dateInfo->getTimestamp(), 'Timestamp test');
    }

    public function testValidateTimestamp()
    {
        $exceptionCaught = false;
        try {
            DateInfo::validateMdyHmTimestamp("01/01/2023 14:00");
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Valid date time check');

        $exceptionCaught = false;
        try {
            DateInfo::validateMdyHmTimestamp(null);
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null date time check');

        $exceptionCaught = false;
        try {
            DateInfo::validateMdyHmTimestamp("01 01 2023 02:00");
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Ivalid date time format check');

        $exceptionCaught = false;
        try {
            DateInfo::validateMdyHmTimestamp("04/31/2023 14:00");
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Invalid date time check');
    }

    public function testValidateDate()
    {
        try {
            DateInfo::validateMdyDate('2023-01-01');
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Invalid date format check');
    }

    public function testValidateTime()
    {
        try {
            DateInfo::validateTime('10 24');
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Invalid time format check');
    }

    public function testConvertTimestamp()
    {
        $ymdTimestamp = DateInfo::convertMdyTimestampToYmdTimestamp('1/2/2023 12:34');
        $this->assertEquals($ymdTimestamp, '2023-01-02 12:34', 'Timestamp conversion test');

        $exceptionCaught = false;
        try {
            $ymdTimestamp = DateInfo::convertMdyTimestampToYmdTimestamp('');
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Exception caught for blank timestamp string');
    }

    public function testGetMonthDayNumber()
    {
        $dayOfWeek = 0; // Sunday
        $weekNumber = 3; // 3rd week
        $month = 1; // January
        $year = 2023;
        $monthDayNumber = DateInfo::getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year);
        $this->assertEquals(15, $monthDayNumber, 'Month day number check 1');

        $dayOfWeek = 1; // Monday
        $weekNumber = 4; // 4th week
        $month = 2; // February
        $year = 2023;
        $monthDayNumber = DateInfo::getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year);
        $this->assertEquals(27, $monthDayNumber, 'Month day number check 2');

        $dayOfWeek = 6; // Saturday
        $weekNumber = 5; // last week
        $month = 4; // April
        $year = 2023;
        $monthDayNumber = DateInfo::getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year);
        $this->assertEquals(29, $monthDayNumber, 'Month day number check 3');

        $dayOfWeek = 3; // Wednesday
        $weekNumber = 1; // 1st week
        $month = 5; // May
        $year = 2023;
        $monthDayNumber = DateInfo::getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year);
        $this->assertEquals(3, $monthDayNumber, 'Month day number check 4');

        $dayOfWeek = 1; // Monday
        $weekNumber = 2; // 1st week
        $month = 6; // June
        $year = 2023;
        $monthDayNumber = DateInfo::getMonthDayNumber($dayOfWeek, $weekNumber, $month, $year);
        $this->assertEquals(12, $monthDayNumber, 'Month day number check 5');
    }

    public function testNumberOfDaysInMonth()
    {
        $month = 1; // January
        $year = 2023;
        $numberOfDaysInMonth = DateInfo::getNumberOfDaysInMonth($month, $year);
        $this->assertEquals(31, $numberOfDaysInMonth, 'Days in month test 1');

        $month = 2; // February
        $year = 2023;
        $numberOfDaysInMonth = DateInfo::getNumberOfDaysInMonth($month, $year);
        $this->assertEquals(28, $numberOfDaysInMonth, 'Days in month test 2');
    }

    public function testTimestampToString()
    {
        $dateTimeString = '2022-12-31 12:34:56';

        $timestamp = strtotime($dateTimeString);

        $timestampString = DateInfo::timestampToString($timestamp);
        $this->assertEquals($dateTimeString, $timestampString, 'timestamp string check');
    }
}
