<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    public function testCreate()
    {
        $help = new Help();
        $this->assertNotNull($help, 'Object creation test');
    }

    public function testStaticMethods()
    {
        $this->assertEquals('Query Builder', Help::getTitle('query-builder'), 'Get title test');

        $topics = Help::getTopics();
        $this->assertContains('notification', $topics, 'Topic check for notification');
        $this->assertContains('query-builder', $topics, 'Topic check for query-builder');

        $this->assertTrue(Help::isValidTopic('query-builder'), 'Valid topic test');
        $this->assertFalse(Help::isValidTopic('not-a-valid-topic'), 'Not a valid topic test');
    }
}
