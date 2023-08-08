<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class NotificationsTest extends TestCase
{
    public function testCreate()
    {
        $notifications = new Notifications();
        $this->assertNotNull($notifications, 'Object creation test');

        $objectVersion = $notifications->getObjectVersion();
        $this->assertEquals(Notifications::OBJECT_VERSION, $objectVersion, 'Object version test');
    }
}
