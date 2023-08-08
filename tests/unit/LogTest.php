<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testCreate()
    {
        $log = new Log();
        $this->assertNotNull($log, 'Object creation test');

        $exceptionCaught = false;
        try {
            $log->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Default log valid test');
    }

    public function testInvalidLog()
    {
        $log = new Log();

        # Null start date
        $log->set([Log::START_DATE => null, Log::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $log->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null start date test');

        # Null end date
        $log->set([Log::START_DATE => '01/02/2023', Log::END_DATE => null]);
        $exceptionCaught = false;
        try {
            $log->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Null end date test');
        $this->assertStringContainsString('end date', $exception->getMessage(), 'End date message test');

        # End date before start date
        $log->set([Log::START_DATE => '01/02/2023', Log::END_DATE => '01/01/2023']);
        $exceptionCaught = false;
        try {
            $log->validate();
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
