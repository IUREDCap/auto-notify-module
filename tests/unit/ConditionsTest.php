<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    public function testCreate()
    {
        $conditions = new Conditions();
        $this->assertNotNull($conditions, 'Object creation test');

        $objectVersion = $conditions->getObjectVersion();
        $this->assertEquals(Conditions::OBJECT_VERSION, $objectVersion, 'Object version test');
    }


    public function testFromAndToJson()
    {
        $conditions = new Conditions();

        $json = '{"variable":"project_id", "operator":"=", "value":"123"}';
        $conditions->setFromJson($json);
        $this->assertNotNull($conditions, 'Object creation test 1');

        $json = '{"operator":"ANY","conditions":['
            . '{"variable":"project_id","operator":"=","value":"123"},'
            . '{"variable":"project_id","operator":"=","value":"456"}'
            . ']}';
        $conditions->setFromJson($json);
        $this->assertNotNull($conditions, 'Object creation test 2');

        $this->assertEquals('ANY', $conditions->getOperator());

        $this->assertTrue($conditions->hasVariable('project_id'), 'Has project_id variable test');
        $this->assertFalse($conditions->hasVariable('status'), 'Does not have status variable test');

        $subConditions = $conditions->getConditions();
        $this->assertEquals(2, count($subConditions), 'Number of conditions test');

        $sub0 = $subConditions[0];
        $this->assertEquals('project_id', $sub0->getVariable(), 'Condition 1 variable test');

        $toJson = $conditions->toJson();
        $this->assertEquals($json, $toJson, 'To JSON test');

        # Test set from JSON with invalid JSON
        $exceptionCaught = false;
        try {
            $conditions->setFromJson('{[123], abc[]{');
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Invalid JSON for conditions check');
    }

    public function testGettersAndSetters()
    {
        $conditions = new Conditions();
        $conditions->set('project_id', '=', '123', null);
        $this->assertEquals('project_id', $conditions->getVariable(), 'Variable test 1');
        $this->assertEquals('=', $conditions->getOperator(), 'Operator test 1');
        $this->assertEquals('123', $conditions->getValue(), 'Value test 1');
        $this->assertEquals(null, $conditions->getConditions(), 'Conditions test 1');


        $conditions = new Conditions();
        $conditions->setVariable('project_id');
        $conditions->setOperator('=');
        $conditions->setValue('123');
        $conditions->setConditions(null);

        $this->assertEquals('project_id', $conditions->getVariable(), 'Variable test 2');
        $this->assertEquals('=', $conditions->getOperator(), 'Operator test 2');
        $this->assertEquals('123', $conditions->getValue(), 'Value test 2');
        $this->assertEquals(null, $conditions->getConditions(), 'Conditions test 2');
    }

    public function testToSql()
    {
        $conditions = $this->createTestConditions();

        $this->assertNotNull($conditions, 'Non-null conditions check');

        $variables = $this->getVariables();

        $sql = $conditions->toSql($variables);

        $this->assertNotNull($sql, 'SQL not null test');
        $this->assertStringContainsString('user_suspended_time IS NULL', $sql, 'Suspended condition in SQL check');
    }

    public function testToString()
    {
        $conditions = $this->createTestConditions();

        $this->assertNotNull($conditions, 'Non-null conditions check');

        $variables = $this->getVariables();
        $expression = $conditions->toString($variables);

        $this->assertNotNull($expression, 'String not null test');
        $this->assertStringContainsString(
            'User_Suspended_Time is null',
            $expression,
            'Suspended condition in string check'
        );
    }

    public function testMigrate()
    {
        $conditions = $this->createTestConditions();

        $exceptionCaught = false;
        try {
            $conditions->migrate();
        } catch (\Exception $exception) {
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught);
    }

    public function testValidate()
    {
        $conditions = $this->createTestConditions();

        $exceptionCaught = false;
        try {
            $variables = $this->getVariables();
            $conditions->validate($variables);
        } catch (\Exception $exception) {
            print("\n" . $exception->getMessage());
            $exceptionCaught = true;
        }
        $this->assertFalse($exceptionCaught);
    }

    public function createTestConditions()
    {
        $conditions = new Conditions();
        $conditions->set(null, Conditions::ALL_OP, null, null);

        $excludeSuspendedCondition = new Conditions();
        $excludeSuspendedCondition->set('user_suspended_time', 'is', 'null');
        $conditions->addSubCondition($excludeSuspendedCondition);

        $exclude1 = new Conditions();
        $exclude1->set('expiration', 'is', 'null');
        $exclude2 = new Conditions();
        $exclude2->set('expiration', 'age <', '0 seconds');
        $orConditions = new Conditions();
        $orConditions->set(null, Conditions::ANY_OP, null, [$exclude1, $exclude2]);
        $conditions->addSubCondition($orConditions);

        return $conditions;
    }

    public function getVariables()
    {
        #------------------------------------
        # Create external module info map
        #------------------------------------
        $extModInfo = new ExternalModuleInfo();
        $extModInfo->setId(1);
        $extModInfo->setName('Auto-Notify Module');
        $extModInfo->setDirectoryPrefix('auto_notify_module');
        $extModInfo->setVersion('v0.0.1');

        $extModInfoMap = [];
        $extModInfoMap['auto_notify_module'] = $extModInfo;

        $variables = Variable::getVariablesFromJsonFile($extModInfoMap);

        return $variables;
    }
}
