<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing a notification query.
 */
class Query
{
    public const OBJECT_VERSION = 1;     // Version number to be incremented when structure changes

    public const DEFAULT_COLUMNS = [
        'username',
        'user_email',
        'user_email2',
        'user_email3',
        'user_firstname',
        'user_lastname',
        'user_comments',
        'project_id',
        'app_title',
        'external_module_id'
    ];

    public const DEFAULT_PROJECT_TABLE_COLUMNS = ['project_id', 'app_title'];

    private $objectVersion;
    private $id;
    private $name;
    private $description;
    private $conditions;
    private $columns;
    private $projectTableColumns;

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->id          = null;  // ID will get set the first time the query is stored
        $this->name        = null;
        $this->description = null;
        $this->conditions  = null;

        $this->columns = self::DEFAULT_COLUMNS;

        $this->projectTableColumns = self::DEFAULT_PROJECT_TABLE_COLUMNS;
    }

    public function migrate()
    {
    }


    /**
     * Validates the query.
     *
     * @param array map from variable name to array of variable data for query variables.
     */
    public function validate($variables)
    {
        if ($this->name == null) {
            throw new \Exception("No query name specified.\n");
        } else {
            $this->name = trim($this->name);
            if (empty($this->name)) {
                throw new \Exception("No query name specified.\n");
            }
        }

        if ($this->conditions == null) {
            throw new \Exception("No query conditions were specified.\n");
        } else {
            $this->conditions->validate($variables);
        }
    }

    public function toJson()
    {
        $conditionsJson = $this->conditions->toJson();

        $projectTableColumnsJson = '["' . implode('","', $this->projectTableColumns) . '"]';

        $json = '{'
            . '"objectVersion":"' . $this->objectVersion . '",'
            . '"id":"' . $this->id . '",'
            . '"name":"' . $this->name . '",'
            . '"description":"' . $this->description . '",'
            . '"conditions":' . $conditionsJson . ','
            . '"projectTableColumns":' . $projectTableColumnsJson
            ;
        $json .= '}';

        return $json;
    }

    public function setFromJson($json)
    {
        if (!empty($json)) {
            $obj = null;
            try {
                $obj = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            } catch (\Exception $exception) {
                throw new \Exception("Invalid JSON query: '{$json}' - {$exception->getMessage()}");
            }

            $this->setFromJsonObject($obj);
        }
    }

    public function setFromJsonObject($obj)
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->id          = null;
        $this->name        = null;
        $this->description = null;
        $this->conditions  = null;
        $this->projectTableColumns = ['project_id', 'app_title'];

        if (property_exists($obj, 'objectVersion')) {
            $this->objectVersion = $obj->objectVersion;
        }

        if (property_exists($obj, 'id')) {
            $this->id = $obj->id;
        }

        if (property_exists($obj, 'name')) {
            $this->name = $obj->name;
        }

        if (property_exists($obj, 'description')) {
            $this->description = $obj->description;
        }

        if (property_exists($obj, 'conditions')) {
            $this->conditions = new Conditions();
            $this->conditions->setFromJsonObject($obj->conditions);
        }

        if (property_exists($obj, 'projectTableColumns')) {
            $this->projectTableColumns = $obj->projectTableColumns;
        }
    }

    /**
     * @param string $conditions a JSON string of query conditions.
     */
    public static function queryConditionsToSql(
        $variables,
        $jsonConditions,
        $getProjectInfo = false,
        $nowDateTime = null
    ) {
        $conditions = new Conditions();

        $conditions->setFromJson($jsonConditions);
        $sql = $conditions->toSql($variables, $getProjectInfo, $nowDateTime);
        return $sql;
    }

    public function setConditionsFromJson($jsonConditions)
    {
        $conditions = new Conditions();
        $conditions->setFromJson($jsonConditions);
        $this->setConditions($conditions);
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    public function getProjectTableColumns()
    {
        return $this->projectTableColumns;
    }

    public function setProjectTableColumns($projectTableColumns)
    {
        $this->projectTableColumns = $projectTableColumns;
    }
}
