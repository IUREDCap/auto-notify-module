<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function testCreate()
    {
        $settings = new Settings(null, null);
        $this->assertNotNull($settings, 'Object creation test');
    }
}
