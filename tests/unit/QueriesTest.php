<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class QueriesTest extends TestCase
{
    public function testCreate()
    {
        $queries = new Queries();
        $this->assertNotNull($queries, 'Object creation test');

        $objectVersion = $queries->getObjectVersion();
        $this->assertEquals(Queries::OBJECT_VERSION, $objectVersion, 'Object version test');

        $query = new Query();
        $query->setName('Query 1');
        $queryId = $queries->addOrUpdate($query);
        $this->assertEquals(1, $queryId, 'First query ID test');

        # Check that query ID remains the same after initial add
        $queryId = $queries->addOrUpdate($query);
        $this->assertEquals(1, $queryId, 'First query ID test');

        $queries->copy($query->getId());
        $numberOfQueries = count($queries->getQueries());
        $this->assertEquals(2, $numberOfQueries, 'Number of queries check');

        $q = $queries->getQuery(3);
        $this->assertNull($q, 'Non-existant query ID test');

        $q = $queries->getQuery(2);
        $this->assertNotNull($q, 'Get query test');

        $queries->delete(2);
        $q = $queries->getQuery(2);
        $this->assertNull($q, 'Query deleted test');
    }
}
