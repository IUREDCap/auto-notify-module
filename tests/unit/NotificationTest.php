<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testCreate()
    {
        $notification = new Notification();
        $this->assertNotNull($notification, 'Object creation test');

        $objectVersion = $notification->getObjectVersion();
        $this->assertEquals(Notification::OBJECT_VERSION, $objectVersion, 'Object version check');
    }
}
