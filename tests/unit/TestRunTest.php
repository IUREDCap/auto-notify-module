<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class TestRunTest extends TestCase
{
    public function testCreate()
    {
        $testRun = new TestRun();
        $this->assertNotNull($testRun, 'Object creation test');

        $logEmails = $testRun->getLogEmails();
        $this->assertFalse($logEmails, 'Log e-mails test');
    }

    public function testSetAndValidate()
    {
        $testRun = new TestRun();
        $this->assertNotNull($testRun, 'Object creation test');

        $properties = [
            TestRun::START_DATE => '01/20/2022',
            TestRun::END_DATE => '12/31/2022',
            TestRun::TEST_EMAIL => 'auto-notify-tester@iu.edu',
            TestRun::NOTIFICATION_ID => 123,
            TestRun::SEND_EMAILS => 1,
            TestRun::LOG_EMAILS => 1,
            TestRun::UPDATE_USER_NOTIFICATION_COUNTS => 1
        ];

        $testRun->set($properties);

        $this->assertEquals('01/20/2022', $testRun->getStartDate(), 'Stat date check');
        $this->assertEquals('12/31/2022', $testRun->getEndDate(), 'End date check');
        $this->assertEquals('auto-notify-tester@iu.edu', $testRun->getTestEmail(), 'Test e-mail check');
        $this->assertEquals(123, $testRun->getNotificationId(), 'Notification ID check');
        $this->assertTrue($testRun->getSendEmails(), 'Send e-mails check');
        $this->assertTrue($testRun->getLogEmails(), 'Log e-mails check');
        $this->assertTrue($testRun->getUpdateUserNotificationCounts(), 'Update user notification counts check');

        $exceptionCaught = false;
        try {
            $testRun->validate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught, 'Validate no exception check');
    }
}
