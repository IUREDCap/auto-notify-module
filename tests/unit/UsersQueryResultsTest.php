<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class UsersQueryResultsTest extends TestCase
{
    public function testCreate()
    {
        $queryResults = new UsersQueryResults();
        $this->assertNotNull($queryResults, 'Object creation test');
    }
}
