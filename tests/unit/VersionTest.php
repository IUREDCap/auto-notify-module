<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testCreate()
    {
        $versionNumber = Version::RELEASE_NUMBER;
        $this->assertEquals(1, preg_match('/\d+\.\d+\.\d+/', $versionNumber), 'Version number format test');
    }
}
