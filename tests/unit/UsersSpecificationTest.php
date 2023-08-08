<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class UsersSpecificationTest extends TestCase
{
    public function testCreate()
    {
        $usersSpecification = new UsersSpecification();
        $this->assertNotNull($usersSpecification, 'Object creation test');

        $objectVersion = $usersSpecification->getObjectVersion();
        $this->assertEquals(UsersSpecification::OBJECT_VERSION, $objectVersion, 'Object version test');
    }
}
