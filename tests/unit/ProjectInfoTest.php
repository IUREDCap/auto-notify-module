<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ProjectInfoTest extends TestCase
{
    public function testCreate()
    {
        $projectInfo = new ProjectInfo();
        $this->assertNotNull($projectInfo, 'Object creation test');

        $projectInfo->setId(123);
        $id = $projectInfo->getId();
        $this->assertEquals(123, $id, 'Project ID test');

        $projectInfo->setName('Test project');
        $this->assertEquals('Test project', $projectInfo->getName(), 'Project name test');

        $projectInfo->setStatus('Production');
        $this->assertEquals('Production', $projectInfo->getStatus(), 'Project status test');

        $projectInfo->setPurpose('Research');
        $this->assertEquals('Research', $projectInfo->getPurpose(), 'Project purpose test');

        $projectInfo->setSurveysEnabled(1);
        $this->assertEquals(1, $projectInfo->getSurveysEnabled(), 'Project surveys enabled test');

        $projectInfo->setIsLongitudinal(1);
        $this->assertEquals(1, $projectInfo->getIsLongitudinal(), 'Project is longitudinal test');

        $completedTime = '2023-01-02 03:04';
        $projectInfo->setCompletedTime($completedTime);
        $this->assertEquals($completedTime, $projectInfo->getCompletedTime(), 'Project completed time test');
    }

    public function testStatic()
    {
        $value = ProjectInfo::convertTrueFalseToYesNo('true');
        $this->assertEquals('yes', $value, 'True to yes check');

        $value = ProjectInfo::convertTrueFalseToYesNo('false');
        $this->assertEquals('no', $value, 'false to no check');
    }
}
