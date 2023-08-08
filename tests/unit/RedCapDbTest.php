<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class RedCapDbTest extends TestCase
{
    public function testCreate()
    {
        $redCapDb = new RedCapDb(null);
        $this->assertNotNull($redCapDb, 'Object creation test');
    }
}
