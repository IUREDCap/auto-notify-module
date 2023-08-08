<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing notification queries.
 */
class Queries
{
    public const OBJECT_VERSION = 1;

    private $objectVersion;

    private $nextId;
    private $queries;  // Array of queries

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;
        $this->nextId = 1;

        $this->queries = array();
    }

    public function migrate()
    {
    }

    /**
     * @return array all query objects.
     */
    public function getQueries()
    {
        return $this->queries;
    }

    public function getQuery($queryId)
    {
        $query = null;

        if (array_key_exists($queryId, $this->queries)) {
            $query = $this->queries[$queryId];
        }
        return $query;
    }

    /**
     * Adds the specified query and returns an ID (the
     * index of where it was added in the array).
     */
    public function addOrUpdate($query)
    {
        $id = $query->getId();
        if ($id == null) {
            # add case, no ID set, so set ID to next ID (and imcrement it)
            $id = $this->nextId++;

            # Set ID in query so next time it will be updated, instead of added
            $query->setId($id);
        }

        $this->queries[$id] = $query;

        return $id;
    }

    public function delete($queryId)
    {
        if (array_key_exists($queryId, $this->queries)) {
            unset($this->queries[$queryId]);
        }
    }

    public function copy($queryId)
    {
        if ($queryId != null && array_key_exists($queryId, $this->queries)) {
            $query = clone ($this->queries[$queryId]);
            $id = $this->nextId++;
            $query->setId($id);
            $this->queries[$id] = $query;
        }
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }
}
