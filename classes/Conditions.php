<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Query Conditions class.
 */
class Conditions
{
    public const OBJECT_VERSION = 1;

    public const ALL_OP     = 'ALL';
    public const ANY_OP     = 'ANY';
    public const NOT_ALL_OP = 'NOT_ALL';
    public const NOT_ANY_OP = 'NOT_ANY';

    public const AGE_VALUE_PATTERN = '/^\s*(-?\d+)\s*(years?|months?|weeks?|days?|hours?|minutes?|seconds?)\s*$/i';

    private $objectVersion;

    private $variable;
    private $operator;
    private $value;

    # Table map from REDCap table name to alias for generation of SQL queries
    public const TABLE_MAP = [
        'redcap_user_information' => 'info',
        'redcap_user_rights'      => 'rights',
        'redcap_projects'         => 'projects',

        'redcap_external_module_settings' => 'em_settings',
        'redcap_external_modules'         => 'ems',

        'cdos' => 'cdos', // Copy Data on Save external module pseudo table (generated from query)

        'cpp' => 'cpp' // Cross-Project Piping external module pseudo table (generated from query)
    ];

    /**
     * @var array Array of Condition objects; this will only be set
     *     for the case where the operator is "ALL", "ANY", "NOT ALL", or "NOT ANY".
     */
    private $conditions;

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->variable   = null;
        $this->operator   = null;
        $this->value      = null;
        $this->conditions = null;
    }

    public function migrate()
    {
        #-----------------------------------------------
        # Recusively call migrate for conditions
        #-----------------------------------------------
        if (property_exists($this, 'conditions') && !empty($this->conditions)) {
            foreach ($this->conditions as $conditions) {
                $conditions->migrate();
            }
        }

        if ($this->objectVersion < self::OBJECT_VERSION) {
        }
    }

    public function setFromJson($json)
    {
        if (!empty($json)) {
            $obj = null;
            try {
                $obj = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            } catch (\Exception $exception) {
                throw new \Exception("Invalid JSON query conditions: '{$json}' - {$exception->getMessage()}");
            }

            $this->setFromJsonObject($obj);
        }
    }

    public function setFromJsonObject($obj)
    {
        $this->variable   = null;
        $this->operator   = null;
        $this->value      = null;
        $this->conditions = null;

        if (property_exists($obj, 'variable')) {
            $this->variable = $obj->variable;
        }

        if (property_exists($obj, 'operator')) {
            // There should always be an operator
            $this->operator = $obj->operator;
        }

        if (property_exists($obj, 'value')) {
            $this->value = $obj->value;
        }

        if (property_exists($obj, 'conditions')) {
            $this->conditions = [];
            foreach ($obj->conditions as $objCondition) {
                $condition = new Conditions();
                $condition->setFromJsonObject($objCondition);
                $this->conditions[] = $condition;
            }
        }
    }

    public function toJson()
    {
        $json = '{';

        if (!empty($this->variable)) {
            $json .= '"variable":"' . $this->variable . '",';
        }

        $json .= '"operator":"' . $this->operator . '",';

        if (empty($this->conditions)) {
            $json .= '"value":"' . $this->value . '"';
        } else {
            $json .= '"conditions":[';

            $isFirst = true;
            foreach ($this->conditions as $condition) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $json .= ',';
                }
                $json .= $condition->toJson();
            }

            $json .= ']';
        }

        $json .= '}';
        return $json;
    }

    public function hasVariable($variable)
    {
        $has = false;
        if ($variable === $this->variable) {
            $has = true;
        } else {
            if (!empty($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    $has = $condition->hasVariable($variable);
                    if ($has === true) {
                        break;
                    }
                }
            }
        }

        return $has;
    }

    /**
     * Indicates if the Condition (including all sub-conditions) has
     * a variable that is related to project, sush as 'Project ID'.
     */
    public function hasProjectVariable($variables)
    {
        $has = false;
        if ($this->variable != null) {
            $variableObj = $variables[$this->variable];

            if ($variableObj === null) {
                throw new \Exception('Condition variable "' . $this->variable . '" not found.');
            }

            if ($variableObj->isProjectVariable()) {
                $has = true;
            }
        } else {
            if (!empty($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    $has = $condition->hasProjectVariable($variables);
                    if ($has === true) {
                        break;
                    }
                }
            }
        }

        return $has;
    }


    /**
     * Recursively gets all variables.
     */
    /* NOT CURRENTLY USED
    public function getVariables()
    {
        $variables = [];

        if (!empty($this->variable)) {
            $variables[] = $this->variable;
        }

        if (!empty($this->conditions)) {
            foreach ($this->conditions as $condition) {
                $subVariables = $condition->getVariables();
                if (!empty($subVariables)) {
                    $variables = array_merge($variables, $subVariables);
                }
            }
        }
        return $variables;
    }
    */

    public function set($variable, $operator, $value, $conditions = null)
    {
        $this->variable   = $variable;
        $this->operator   = $operator;
        $this->value      = $value;
        $this->conditions = $conditions;
    }

    public function addSubCondition($subCondition)
    {
        if ($this->conditions == null) {
            $this->conditions = [];
        }

        $this->conditions[] = $subCondition;
    }

    /**
     * Validates this object. Most of the errors this method checks for
     * should never occur. Unless there is an internal coding error
     * or external hacking, missing or incorrect value
     * errors are the only errors that should occur.
     */
    public function validate($variables)
    {
        #------------------------------------------------------
        # Check that there is a non-blank operator
        #------------------------------------------------------
        if (!property_exists($this, 'operator') || empty($this->operator)) {
            throw new \Exception("Missing operator in query conditions.");
        }

        $operator = $this->operator;

        if (
            $operator === self::ALL_OP
            || $operator === self::ANY_OP
            || $operator === self::NOT_ALL_OP
            || $operator === self::NOT_ANY_OP
        ) {
            if (property_exists($this, 'conditions')) {
                if ($this->conditions != null && is_array($this->conditions)) {
                    foreach ($this->conditions as $conditions) {
                        $conditions->validate($variables);
                    }
                }
            }
        } else {
            #------------------------------------------
            # Check condition's variable
            #------------------------------------------
            if (!property_exists($this, 'variable') || empty($this->variable)) {
                throw new \Exception('Query condition is missing variable.');
            }

            if (array_key_exists($this->variable, $variables)) {
                $variableInfo = $variables[$this->variable];

                #------------------------
                # Check operator
                #------------------------
                if (!property_exists($this, 'operator')) {
                    $message = 'Query condition for variable "' . $this->variable . '" is missing an operator.';
                    throw new \Exception($message);
                } elseif (!in_array($this->operator, $variableInfo->getOperators())) {
                    $message = 'Operator "' . $this->operator . '"'
                        . ' is not a valid operator for variable "' . $this->variable . '".';
                    throw new \Exception($message);
                }

                #------------------------
                # Check value
                #------------------------
                if (!property_exists($this, 'value')) {
                    $message = 'Variable "' . $this->variable . '"'
                        . ' has no value.'
                        ;
                    throw new \Exception($message);
                }

                $value = $this->value;

                $valueType = $variableInfo->getValueType();

                if ($valueType === Variable::DATE_TIME_NULL_VALUE_TYPE) {
                    if (in_array($this->operator, ['is', 'is not'])) {
                        if (strcasecmp($value, 'null') !== 0) {
                            $message = 'Invalid non-null value "' . $value . '"'
                                . ' for variable "' . $this->variable . '"'
                                . ' and operator "' . $this->operator . '"'
                                ;
                            throw new \Exception($message);
                        }
                    } elseif (preg_match('/^age/', $this->operator) === 1) {
                        if (preg_match(self::AGE_VALUE_PATTERN, $value) !== 1) {
                            $message = 'Invalid age value "' . $value . '"'
                                . ' for variable "' . $this->variable . '"'
                                . ' and operator "' . $this->operator . '"'
                                . '. Ages must start with an integer or negative integer'
                                . ' and be followed by one of the following:'
                                . ' "years", "months", "weeks", "days", "hours", "minutes" "seconds"'
                                . ', for example: "2 weeks".'
                                ;
                            throw new \Exception($message);
                        }
                    } else {
                        try {
                            DateInfo::validateMdyHmTimestamp($value);
                        } catch (\Exception $exception) {
                            $message = 'Invalid date time value "' . $value . '"'
                                . ' for variable "' . $this->variable . '"'
                                . ' and operator "' . $this->operator . '"'
                                . ': ' . $exception->getMessage();
                                ;
                            throw new \Exception($message);
                        }
                    }
                } elseif ($valueType === Variable::INPUT_TEXT_VALUE_TYPE) {
                    ; // Just escape single quotes???
                } elseif ($valueType === Variable::NULL_VALUE_TYPE) {
                    if (strcasecmp($value, 'null') !== 0) {
                        $message = 'Invalid non-null value "' . $value . '"'
                        . ' for variable "' . $this->variable . '".';
                        throw new \Exception($message);
                    }
                } elseif ($valueType === Variable::SELECT_VALUE_TYPE) {
                    $selectValueKeys = array_column($variableInfo->getSelectValues(), 0);
                    if (!in_array($value, $selectValueKeys)) {
                        $message = 'The select value "' . $value . '"'
                        . ' for variable "' . $this->variable . '" is not valid.';
                        throw new \Exception($message);
                    }
                } else {
                    # Internal error - this should never happen
                    $message = 'Value type "' . $valueType . '"'
                        . ' for variable "' . $this->variable . '".'
                        . ' is not a valid.';
                    throw new \Exception($message);
                }
            } else {
                throw new \Exception('Condition variable "' . $this->variable . '" is not a valid variable name.');
            }
        }
    }

    public function toString($variables, $level = 0)
    {
        $string = '';

        $tab = "    ";
        $indent = str_repeat($tab, $level);

        $operator = $this->operator;
        if (in_array($operator, [self::ALL_OP, self::ANY_OP, self::NOT_ALL_OP, self::NOT_ANY_OP])) {
            if (
                $this->conditions != null
                && is_array($this->conditions)
                && count($this->getSqlWhereClauseConditions()) > 0
            ) {
                if ($operator === self::NOT_ALL_OP || $operator === self::NOT_ANY_OP) {
                    $string .= $indent . "NOT(\n";
                } else {
                    $string .= $indent . "(\n";
                }

                $isFirst = true;
                $nextIndent = str_repeat($tab, $level + 1);
                foreach ($this->getSqlWhereClauseConditions() as $condition) {
                    if ($isFirst) {
                        $isFirst = false;
                    } else {
                        if ($operator === self::ALL_OP || $operator === self::NOT_ALL_OP) {
                            $string .= $nextIndent . "AND\n";
                        } else {
                            $string .= $nextIndent . "OR\n";
                        }
                    }
                    $string .= $condition->toString($variables, $level + 1);
                }
                $string .= $indent . ")\n";
            }
        } else {
            $variableInfo = $variables[$this->variable];
            $value = $this->value;
            if ($variableInfo->getValueType() === Variable::SELECT_VALUE_TYPE) {
                $value = "'" . $variableInfo->getSelectValueLabel($value) . "'";
            } elseif (in_array('like', $variableInfo->getOperators())) {
                $value = "'" . $value . "'";
            }
            $variableLabel = str_replace(' ', '_', $variableInfo->getLabel());
            $string .= $indent . $variableLabel . ' ' . $this->operator . ' ' . $value . "\n";
        }

        return $string;
    }

    /**
     * Generates the SQL corresponding to this Conditions object.
     *
     * @param array $variables map from variable name to Variable object for the condition variables.
     */
    public function toSql($variables, $getProjectInfo = false, $nowDateTime = null)
    {
        $query = 'SELECT DISTINCT info.username, info.user_email, info.user_firstname, info.user_lastname,' . "\n"
            . '        info.user_lastlogin, info.user_creation, info.user_suspended_time, info.user_expiration,' . "\n"
            . '        rights.project_id, projects.app_title,' . "\n"
            . '        em_settings.external_module_id'
            ;

        if ($getProjectInfo) {
            $query .= ",\n" . '        projects.status';
            $query .= ",\n" . '        projects.purpose';
            $query .= ",\n" . '        projects.surveys_enabled';
            $query .= ",\n" . '        projects.repeatforms';
            $query .= ",\n" . '        projects.creation_time';
            $query .= ",\n" . '        projects.completed_time';
            $query .= ",\n" . '        projects.date_deleted';
            $query .= ",\n" . "        rights.user_rights";
            $query .= ",\n" . "        rights.design";
            $query .= ",\n" . "        if(rights.api_token is null, 'no', 'yes') as has_api_token";
            $query .= ",\n" . "        rights.api_export";
            $query .= ",\n" . "        rights.api_import";
            $query .= ",\n" . "        rights.mobile_app";
        }

        if ($this->hasVariable('cpp_destination_project_id')) {
            $query .= ",\n" . '        cpp.cpp_destination_project_id';
            $query .= ",\n" . '        cpp.cpp_destination_project_name' . "\n";
        } else {
            $query .= "\n";
        }

        if ($this->hasVariable('cdos_source_project_id')) {
            $query .= ",\n" . '        cdos.cdos_source_project_id';
            $query .= ",\n" . '        cdos.cdos_source_project_name' . "\n";
        } else {
            $query .= "\n";
        }

        $query .= '    FROM redcap_user_information info' . "\n";

        if ($this->hasProjectVariable($variables)) {
            $query .=
                '        JOIN redcap_user_rights rights ON info.username = rights.username' . "\n"
                . '        JOIN redcap_projects projects ON rights.project_id = projects.project_id' . "\n"
                ;
        } else {
            $query .=
                '        LEFT JOIN redcap_user_rights rights ON info.username = rights.username' . "\n"
                . '        LEFT JOIN redcap_projects projects ON rights.project_id = projects.project_id' . "\n"
                ;
        }

        $query .=
            '        LEFT JOIN redcap_external_module_settings em_settings' . "\n"
            . '            ON projects.project_id = em_settings.project_id' . "\n"
            . "            AND (em_settings.key = 'enabled' AND em_settings.value = 'true')\n"
            . '        LEFT JOIN redcap_external_modules ems ' . "\n"
            . '            ON em_settings.external_module_id = ems.external_module_id' . "\n"
            ;

        if ($this->hasVariable('cpp_destination_project_id')) {
            $query .=
                "        LEFT JOIN (\n"
                . "            SELECT em_settings.project_id as cpp_destination_project_id,\n"
                . "                     em_settings.value as cpp_source_project_ids,\n"
                . "                     cpp_dest_projects.app_title as cpp_destination_project_name\n"
                . "                FROM redcap_external_modules ems, redcap_external_module_settings em_settings,\n"
                . "                        redcap_external_module_settings em_settings2,\n"
                . "                        redcap_projects cpp_dest_projects\n"
                . "                WHERE ems.directory_prefix = 'cross_project_piping'\n"
                . "                    AND em_settings.project_id = cpp_dest_projects.project_id\n"
                . "                    AND ems.external_module_id = em_settings.external_module_id\n"
                . "                    AND em_settings.external_module_id = em_settings2.external_module_id\n"
                . "                    AND em_settings.`key`= 'project-id'\n"
                . "                    AND (em_settings2.key = 'enabled' and em_settings2.value = 'true')) AS cpp\n"
                . "            ON instr(cpp.cpp_source_project_ids, CONCAT('\"', projects.project_id, '\"'))\n"
                ;
        }

        if ($this->hasVariable('cdos_source_project_id')) {
            $query .=
                "        LEFT JOIN (\n"
                . "            SELECT em_settings.project_id as cdos_source_project_id,\n"
                . "                    em_settings.value as cdos_dest_project_ids,\n"
                . "                    cdos_source_projects.app_title as cdos_source_project_name\n"
                . "                FROM redcap_external_modules ems,\n"
                . "                        redcap_external_module_settings em_settings,\n"
                . "                        redcap_external_module_settings em_settings2,\n"
                . "                        redcap_projects cdos_source_projects\n"
                . "                WHERE ems.directory_prefix = 'copy_data_on_save'\n"
                . "                    AND em_settings.project_id = cdos_source_projects.project_id\n"
                . "                    AND ems.external_module_id = em_settings.external_module_id\n"
                . "                    AND em_settings.external_module_id = em_settings2.external_module_id\n"
                . "                    AND em_settings.`key`= 'dest-project'\n"
                . "                    AND (em_settings2.key = 'enabled' and em_settings2.value = 'true')\n"
                . "      ) AS cdos\n"
                . "      ON instr(cdos_dest_project_ids, CONCAT('\"', projects.project_id, '\"'))\n"
                ;
        }

        $query .= "    WHERE \n"
            . "        (info.username IS NOT NULL) \n"
            ;

        //info.user_suspended_time is null
        //order by username, project_id

        $queryConditions = $this->conditionsToSql($variables, 2, $nowDateTime);

        if (!empty(trim($queryConditions))) {
            $query .= "        AND\n";
            $query .= $queryConditions;
        }

        $query .= "    ORDER BY info.username, rights.project_id \n";

        return $query;
    }


    /**
     * Convert conditions to an SQL string.
     *
     * @param array $variables map from variable name to Variable object for the condition variables.
     * @param int $level the nesting level for the call, which is used to determine the indent amount.
     * @param string $nowDateTime the current date/time in yyyy-mm-dd hh:mm:ss format, e.g., '2022-12-31 14:45'.
     */
    public function conditionsToSql($variables, $level, $nowDateTime = null)
    {
        $tab = "    ";
        $indent = str_repeat($tab, $level);

        $tableMap = [
            'redcap_user_information' => 'info',
            'redcap_user_rights'      => 'rights',
            'redcap_projects'         => 'projects',

            'redcap_external_module_settings' => 'em_settings',
            'redcap_external_modules'         => 'ems',

            'cdos' => 'cdos', // Copy Data on Save eternal module pseudo table (generated from query)

            'cpp' => 'cpp' // Cross-Project Piping external module pseudo table (generated from query)
        ];

        $string = '';
        $operator = $this->operator;

        if (in_array($operator, [self::ALL_OP, self::ANY_OP, self::NOT_ALL_OP, self::NOT_ANY_OP])) {
            if (
                $this->conditions != null
                && is_array($this->conditions)
                && count($this->getSqlWhereClauseConditions()) > 0
            ) {
                if ($operator === self::NOT_ALL_OP || $operator === self::NOT_ANY_OP) {
                    $string .= $indent . "NOT(\n";
                } else {
                    $string .= $indent . "(\n";
                }

                $isFirst = true;
                $nextIndent = str_repeat($tab, $level + 1);
                foreach ($this->getSqlWhereClauseConditions() as $condition) {
                    if ($isFirst) {
                        $isFirst = false;
                    } else {
                        if ($operator === self::ALL_OP || $operator === self::NOT_ALL_OP) {
                            $string .= $nextIndent . "AND\n";
                        } else {
                            $string .= $nextIndent . "OR\n";
                        }
                    }
                    $string .= $condition->conditionsToSql($variables, $level + 1, $nowDateTime);
                }
                $string .= $indent . ")\n";
            }
        } elseif (array_key_exists($this->variable, $variables)) {
            $variable = $variables[$this->variable];

            if ($variable->getName() === 'cross_project_piping_source') {
                ;
            } else {
                $table     = $variable->getTable();
                $valueType = $variable->getValueType();

                $tableAlias = $tableMap[$table];
                $field = $tableAlias . '.' . $variable->getName();

                $value = $this->value;

                if ($valueType === Variable::INPUT_TEXT_VALUE_TYPE || $valueType === Variable::SELECT_VALUE_TYPE) {
                    $value = str_replace("'", "''", $value); // escape internal single-quotes
                    $value = "'" . $value . "'";   // add beginning and end quotes
                } elseif ($valueType === Variable::DATE_TIME_NULL_VALUE_TYPE) {
                    if ($operator === 'is' || $operator === 'is not') {
                        $operator = strtoupper($operator);
                        $value = 'NULL';
                    } elseif (preg_match('/^age/', $this->operator) === 1) {
                        #----------------------------
                        # Process age operator
                        #----------------------------
                        $matches = array();
                        $result = preg_match(self::AGE_VALUE_PATTERN, $value, $matches);
                        if ($result !== 1 || count($matches) != 3) {
                            // Validate should prevent this from ever happening
                            throw new \Exception("Invalid age value for date.");
                        }
                        $number = $matches[1];
                        $units  = $matches[2];
                        $units = strtoupper(preg_replace('/s$/', '', $units));

                        # OLD (based on TIMESTAMPDIFF):
                        # if (empty($nowDateTime)) {
                        #     $field = "TIMESTAMPDIFF({$units}, {$variable->getName()}, NOW())";
                        # } else {
                        #     $field = "TIMESTAMPDIFF({$units}, {$variable->getName()}, '{$nowDateTime}')";
                        # }
                        #
                        # $value = $number;
                        # $operator = preg_replace('/^age/', '', $operator);

                        # adjust operator
                        $operator = preg_replace('/^age\s*/', '', $operator);
                        if ($operator === '<') {
                            $operator = '>';
                        } elseif ($operator === '<=') {
                            $operator = '>=';
                        } elseif ($operator === '>') {
                            $operator = '<';
                        } elseif ($operator === '>=') {
                            $operator = '<=';
                        }

                        if (empty($nowDateTime)) {
                            $value = "DATE_SUB(NOW(), INTERVAL {$number} {$units})";
                        } else {
                            $value = "DATE_SUB({$nowDateTime}, INTERVAL {$number} {$units})";
                        }
                    } else {
                        $value = DateInfo::convertMdyTimestampToYmdTimestamp($value);
                        $value = "'" . $value . "'";   // add beginning and end quotes
                    }
                }

                $string = $indent . $field . ' ' . $operator . ' ' . $value . "\n";
            }
        } else {
            throw new \Exception('Variable "' . $this->variable . '" not found.');
        }

        return $string;
    }

    /**
     * Gets only the conditions that will generate SQL WHERE clauses.
     */
    public function getSqlWhereClauseConditions()
    {
        $whereConditions = null;

        if (!empty($this->conditions)) {
            $whereConditions = [];

            foreach ($this->conditions as $condition) {
                #if ($condition->variable === 'cross_project_piping_source') {
                #    ; // don't add
                #}
                if (in_array($condition->operator, [self::ALL_OP, self::ANY_OP, self::NOT_ALL_OP, self::NOT_ANY_OP])) {
                    $subConditions = $condition->getSqlWhereClauseConditions();
                    if (!empty($subConditions)) {
                        $whereConditions[] = $condition;
                    }
                } else {
                    $whereConditions[] = $condition;
                }
            }
        }

        return $whereConditions;
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getVariable()
    {
        return $this->variable;
    }

    public function setVariable($variable)
    {
        $this->variable = $variable;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }
}
