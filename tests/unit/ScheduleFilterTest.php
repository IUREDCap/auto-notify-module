<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ScheduleFilterTest extends TestCase
{
    public function testCreate()
    {
        $scheduleFilter = new ScheduleFilter();
        $this->assertNotNull($scheduleFilter, 'Object creation test');

        $exceptionCaught = false;
        try {
            $scheduleFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Default log valid test');
    }

    public function testInvalidScheduleFilter()
    {
        $scheduleFilter = new ScheduleFilter();

        # Null start date
        $scheduleFilter->set([ScheduleFilter::START_DATE => null, ScheduleFilter::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $scheduleFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null start date test');

        # Null end date
        $scheduleFilter->set([ScheduleFilter::START_DATE => '01/02/2023', ScheduleFilter::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $scheduleFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null end date test');
        $this->assertStringContainsString('end date', $exception->getMessage(), 'End date message test');

        # End date before start date
        $scheduleFilter->set([ScheduleFilter::START_DATE => '01/02/2023', ScheduleFilter::END_DATE => '01/01/2023']);
        $exceptionCaught = false;
        try {
            $scheduleFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'End date before start date test');
        $this->assertStringContainsString(
            'is before',
            $exception->getMessage(),
            'End date before start date message test'
        );
    }
}
