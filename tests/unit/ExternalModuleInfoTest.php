<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ExternalModuleInfoTest extends TestCase
{
    public function testCreate()
    {
        $externalModuleInfo = new ExternalModuleInfo();
        $this->assertNotNull($externalModuleInfo, 'Object creation test');

        $externalModuleInfo->setId(123);
        $this->assertEquals(123, $externalModuleInfo->getId(), 'Id check');

        $externalModuleInfo->setName('Auto-Notify Module');
        $this->assertEquals('Auto-Notify Module', $externalModuleInfo->getName(), 'Name check');

        $externalModuleInfo->setDirectoryPrefix('auto_notify_module');
        $this->assertEquals('auto_notify_module', $externalModuleInfo->getDirectoryPrefix(), 'Directory prefix check');

        $externalModuleInfo->setVersion('v1.2.3');
        $this->assertEquals('v1.2.3', $externalModuleInfo->getVersion(), 'Version check');
    }

    public function testConvertToIdMap()
    {
        $e1 = new ExternalModuleInfo();
        $e1->setId(2);
        $e1->setName('REDCap-ETL Module');
        $e1->setDirectoryPrefix('redcap-etl-module');
        $e1->setVersion('v1.2.3');

        $e2 = new ExternalModuleInfo();
        $e2->setId(4);
        $e2->setName('Auto-Notify Module');
        $e2->setDirectoryPrefix('auto-notify-module');
        $e2->setVersion('v1.0.0');

        $extModInfos = [$e1, $e2];

        $map = ExternalModuleInfo::convertToIdMap($extModInfos);

        $keys = array_keys($map);
        $this->assertEquals([2, 4], $keys, 'Keys check');

        $this->assertEquals($e1, $map[2], 'Ext. Mod. with ID 2 check');
        $this->assertEquals($e2, $map[4], 'Ext. Mod. with ID 4 check');
    }
}
