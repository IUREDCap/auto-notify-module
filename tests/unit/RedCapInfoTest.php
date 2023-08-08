<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class RedCapInfoTest extends TestCase
{
    public function testCreate()
    {
        $redCapInfo = new RedCapInfo();
        $this->assertNotNull($redCapInfo, 'Object creation test');

        $url = 'http://localhost/redcap/';
        $redCapInfo->setUrl($url);
        $this->assertEquals($url, $redCapInfo->getUrl(), 'URL test');

        $institution = 'Indiana University';
        $redCapInfo->setInstitution($institution);
        $this->assertEquals($institution, $redCapInfo->getInstitution(), 'Institution test');
    }
}
