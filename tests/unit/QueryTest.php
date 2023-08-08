<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testCreate()
    {
        $query = new Query();
        $this->assertNotNull($query, 'Object creation test');

        $objectVersion = $query->getObjectVersion();
        $this->assertEquals(Query::OBJECT_VERSION, $objectVersion, 'Object version test');

        $projectTableColumns = ['project_id', 'app_title'];
        $this->assertEquals($projectTableColumns, $query->getProjectTableColumns(), 'Project table columns test');
    }

    public function testValidation()
    {
        $query = new Query();
        $this->assertNotNull($query, 'Object creation test');

        $exceptionCaught = false;
        try {
            $query->validate([]);
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Missing name exception caught');
        $this->assertStringContainsString(
            'No query name',
            $exception->getMessage(),
            'Query name in exception messsage check'
        );

        $queryName = 'Test query';
        $query->setName($queryName);
        $this->assertEquals($queryName, $query->getName(), 'Query name test');
    }
}
