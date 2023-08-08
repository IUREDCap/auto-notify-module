<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreate()
    {
        $user = new User();
        $this->assertNotNull($user, 'Object creation test');

        $username = 'test_user';
        $user->setUsername($username);
        $getUsername = $user->getUsername();
        $this->assertEquals($username, $getUsername, 'Username getter and setter test');

        $email = 'user@someuniversity.edu';
        $user->setEmail($email);
        $getEmail = $user->getEmail();
        $this->assertEquals($email, $getEmail, 'Email getter and setter test');

        $firstName = 'Test';
        $user->setFirstName($firstName);
        $getFirstName = $user->getFirstName();
        $this->assertEquals($firstName, $getFirstName, 'First name getter and setter test');

        $lastName = 'User';
        $user->setLastName($lastName);
        $getLastName = $user->getLastName();
        $this->assertEquals($lastName, $getLastName, 'Last name getter and setter test');

        $userRights = $user->getUserRights();
        $this->assertTrue(is_array($userRights), 'User rights is an array check');
        $this->assertEquals(0, count($userRights), 'User rights size check');
    }
}
