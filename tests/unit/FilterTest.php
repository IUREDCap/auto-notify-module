<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testCreate()
    {
        $filter = new Filter();
        $this->assertNotNull($filter, 'Object not null test');
    }
}
