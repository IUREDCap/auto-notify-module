<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/** * Class for representing a variable for query conditions.  */
class Variable implements \JsonSerializable
{
    public const VARIABLES_JSON_FILE = __DIR__ . '/../resources/variables.json';

    public const DATE_TIME_NULL_VALUE_TYPE = "dateTimeNull";
    public const INPUT_TEXT_VALUE_TYPE     = "inputText";
    public const NULL_VALUE_TYPE           = "null";
    public const SELECT_VALUE_TYPE         = "select";

    private $name;
    private $table;
    private $label;
    private $operators;
    private $operatorClass;
    private $valueType;
    private $optgroup;
    private $selectValues;
    private $help;

    public function __construct()
    {
        $this->name  = null;
        $this->label = null;

        $this->operators     = array();
        $this->operatorClass = null;

        $this->valueType     = null;
        $this->selectValues  = array();

        $this->optgroup = '';
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public static function variablesToJson($variables)
    {
        $json = '[';
        $isFirst = true;
        foreach ($variables as $variableName => $variable) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $json .= ',';
            }
            $json .= json_encode($variable);
        }
        $json .= ']';
        return $json;
    }

    /**
     * Get query variable information from a JSON file and returns it as an array.
     *
     * @param array $externalModuleInfoMap Map from external module directory prefixes to ExternalModuleInfo objects.
     * @param string $jsonFilePath Path to JSON file that has variable information
     *
     * @return array map from variable names to Variable objects.
     */
    public static function getVariablesFromJsonFile($externalModuleInfoMap, $jsonFilePath = self::VARIABLES_JSON_FILE)
    {
        $variables = array();

        // ADD ERROR CHECKING!!!!!!!!!!!!!!!
        $json = file_get_contents($jsonFilePath);

        $objects = json_decode($json);

        foreach ($objects as $key => $object) {
            $variable = new Variable();

            $variable->name  = $object->name;
            $variable->table = $object->table;
            $variable->label = $object->label;

            $variable->operators     = $object->operators;

            if (property_exists($object, "operatorClass")) {
                $variable->operatorClass = $object->operatorClass;
            }

            $variable->valueType    = $object->valueType;

            $variable->optgroup     = $object->optgroup;

            #-------------------------------------------------
            # Set select values (if any)
            #-------------------------------------------------
            if ($variable->name === 'directory_prefix') {
                $variable->selectValues[] = ['', ''];
                foreach ($externalModuleInfoMap as $directoryPrefix => $externalModuleInfo) {
                    $variable->selectValues[] = [$directoryPrefix, $externalModuleInfo->getName()];
                }
            } elseif (property_exists($object, "selectValues")) {
                $variable->selectValues = $object->selectValues;
            }

            if (property_exists($object, "help")) {
                $variable->help = $object->help;
            } else {
                $variable->help = '';
            }

            $variables[$variable->name] = $variable;
        }

        return $variables;
    }

    /**
     * Indicates if the variable is related to projects, e.g., 'Project ID' and 'User Project API Token'
     * (as opposed to being only related to users, e.g., 'username').
     */
    public function isProjectVariable()
    {
        $isProjectVariable = false;
        if ($this->table === 'redcap_user_information') {
            $isProjectVariable = false;
        } else {
            $isProjectVariable = true;
        }

        return $isProjectVariable;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getOperators()
    {
        return $this->operators;
    }

    public function getValueType()
    {
        return $this->valueType;
    }

    public function getOptgroup()
    {
        return $this->optgroup;
    }

    public function getSelectValues()
    {
        return $this->selectValues;
    }

    public function getSelectValueLabel($value)
    {
        $label = '';

        foreach ($this->selectValues as $selectValue) {
            if ($value == $selectValue[0]) {
                $label = $selectValue[1];
                break;
            }
        }

        return $label;
    }
}
