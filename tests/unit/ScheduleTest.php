<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ScheduleTest extends TestCase
{
    public function testCreate()
    {
        $schedule = new Schedule();
        $this->assertNotNull($schedule, 'Object creation test');

        $objectVersion = $schedule->getObjectVersion();
        $this->assertEquals(Schedule::OBJECT_VERSION, $objectVersion, 'Object version test');
    }

    public function testValues()
    {
        $schedule = new Schedule();
        $this->assertNotNull($schedule, 'Object creation test');

        $properties = [
            Schedule::START_DATE        => '03/01/2023',
            Schedule::SCHEDULING_OPTION => Schedule::SCHED_OPT_RECURRING,
            Schedule::RECURRING_OPTION  => Schedule::REC_OPT_DAILY,
            Schedule::DAY_CHECKS        => [1, 3, 5],
            Schedule::DAY_TIMES         => [null, '07:00', null, '08:00', null, '09:00', null]
        ];
        $schedule->set($properties);

        $startTimestamp = strtotime('2023-03-17 04:00'); // Friday
        $nextTimestamp  = $schedule->getNextRecurringTimestamp($startTimestamp);
        $dateInfo = new DateInfo($nextTimestamp);
        $this->assertEquals('2023-03-17 09:00', $dateInfo->getTimestampString(), 'First next time check.');

        $startTimestamp = strtotime('2023-03-14 23:00'); // Tuesday
        $nextTimestamp  = $schedule->getNextRecurringTimestamp($startTimestamp);
        $dateInfo = new DateInfo($nextTimestamp);
        $this->assertEquals('2023-03-15 08:00', $dateInfo->getTimestampString(), 'Second next time check.');

        $exceptionCaught = false;
        try {
            $schedule->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Validate valid schedule test');

        $value = $schedule->toString();
        $this->assertStringContainsString('Recurring', $value, 'Recurring in string check');
        $this->assertStringContainsString('Daily', $value, 'Daily in string check');
        $this->assertStringContainsString('Monday', $value, 'Monday in string check');
        $this->assertStringContainsString('Wednesday', $value, 'Wednesday in string check');
        $this->assertStringContainsString('Friday', $value, 'Friday in string check');
    }
}
