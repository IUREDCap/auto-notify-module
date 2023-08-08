<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ModuleLogTest extends TestCase
{
    public function testCreate()
    {
        $moduleLog = new ModuleLog(null);
        $this->assertNotNull($moduleLog, 'Object creation test');
    }
}
