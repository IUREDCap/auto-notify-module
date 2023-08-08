<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class UserRightsTest extends TestCase
{
    public function testCreate()
    {
        $userRights = new UserRights();
        $this->assertNotNull($userRights, 'Object creation test');

        $userRights->setProjectId(1001);
        $projectId = $userRights->getProjectId();
        $this->assertEquals(1001, $projectId, 'Project ID test');

        $userRights->setHasUserRights(1);
        $this->assertEquals(1, $userRights->getHasUserRights(), 'Has user rights test');

        $userRights->setDesign(1);
        $this->assertEquals(1, $userRights->getDesign(), 'Design test');

        $userRights->setHasApiToken(1);
        $this->assertEquals(1, $userRights->getHasApiToken(), 'Has API token test');

        $userRights->setApiExport(1);
        $this->assertEquals(1, $userRights->getApiExport(), 'API export test');

        $userRights->setApiImport(1);
        $this->assertEquals(1, $userRights->getApiImport(), 'API import test');

        $userRights->setMobileApp(1);
        $this->assertEquals(1, $userRights->getMobileApp(), 'API mobile app test');

        $userRights->addExternalModuleId(123);
        $userRights->addExternalModuleId(456);
        $this->assertEquals([123, 456], $userRights->getExternalModuleIds(), 'External module ID add test');

        $ids = [1, 2, 3];
        $userRights->setExternalModuleIds($ids);
        $this->assertEquals($ids, $userRights->getExternalModuleIds(), 'External modules IDs set test');

        $userRights->addCppDestinationProjectId(100);
        $userRights->addCppDestinationProjectId(200);
        $this->assertEquals(
            [100, 200],
            $userRights->getCppDestinationProjectIds(),
            'CPP destination project ID add test'
        );

        $ids = [10, 20, 30];
        $userRights->setCppDestinationProjectIds($ids);
        $this->assertEquals($ids, $userRights->getCppDestinationProjectIds(), 'CPP destination project IDs set test');
    }
}
