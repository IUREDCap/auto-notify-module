<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCreate()
    {
        $config = new Config();
        $this->assertNotNull($config, 'Object creation test');

        $objectVersion = $config->getObjectVersion();
        $this->assertEquals(Config::OBJECT_VERSION, $objectVersion, 'Object version test');
    }

    public function testSet()
    {
        $config = new Config();

        $properties = [Config::TEST_MODE => 1, Config::EMAIL_ADDRESS => 'test@test.org', Config::DEBUG_MODE => 1];
        $config->set($properties);

        $this->assertTrue($config->getTestMode(), 'Test mode true test');
        $this->assertEquals('test@test.org', $config->getEmailAddress(), 'E-mail test');
        $this->assertTrue($config->getDebugMode(), 'Debug mode true test');

        $properties = [Config::EMAIL_ADDRESS => 'test2@test.org'];
        $config->set($properties);

        $this->assertFalse($config->getTestMode(), 'Test mode falsetest');
        $this->assertEquals('test2@test.org', $config->getEmailAddress(), 'E-mail test 2');
        $this->assertFalse($config->getDebugMode(), 'Debug mode falsetest');
    }
}
