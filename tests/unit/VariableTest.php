<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{
    public function testCreate()
    {
        $variable = new Variable();
        $this->assertNotNull($variable, 'Object creation test');

        $variable->setName('username');
        $this->assertEquals('username', $variable->getName(), 'Name check');

        $variable->setTable('redcap_user_information');
        $this->assertEquals('redcap_user_information', $variable->getTable(), 'Table check');

        $variable->setLabel('Username');
        $this->assertEquals('Username', $variable->getLabel(), 'Label check');
    }
}
