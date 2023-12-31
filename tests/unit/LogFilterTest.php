<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class LogFilterTest extends TestCase
{
    public function testCreate()
    {
        $logFilter = new LogFilter();
        $this->assertNotNull($logFilter, 'Object creation test');

        $exceptionCaught = false;
        try {
            $logFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Default log valid test');
    }

    public function testInvalidLogFilter()
    {
        $logFilter = new LogFilter();

        # Null start date
        $logFilter->set([LogFilter::START_DATE => null, LogFilter::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $logFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null start date test');

        # Null end date
        $logFilter->set([LogFilter::START_DATE => '01/02/2023', LogFilter::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $logFilter->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null end date test');
        $this->assertStringContainsString('end date', $exception->getMessage(), 'End date message test');

        # End date before start date
        $logFilter->set([LogFilter::START_DATE => '01/02/2023', LogFilter::END_DATE => '01/01/2023']);
        $exceptionCaught = false;
        try {
            $logFilter->validate();
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
