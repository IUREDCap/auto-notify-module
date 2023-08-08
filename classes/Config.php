<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Configuration class for the module.
 */
class Config
{
    public const OBJECT_VERSION = 1;

    public const TEST_MODE  = "testMode";

    public const EMAIL_ADDRESS = "emailAddress";

    public const DEBUG_MODE = "debugMode";

    private $objectVersion;

    private $testMode;
    private $emailAddress;
    private $debugMode;

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->testMode  = false;
        $this->debugMode = false;
    }

    /**
     * Migrate an older version of the object to the latest version.
     */
    public function migrate()
    {
    }

    public function set($properties)
    {
        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::TEST_MODE, $properties)) {
                $this->testMode = true;
            } else {
                $this->testMode = false;
            }

            if (array_key_exists(self::EMAIL_ADDRESS, $properties)) {
                $this->emailAddress = Filter::sanitizeEmail($properties[self::EMAIL_ADDRESS]);
            }

            if (array_key_exists(self::DEBUG_MODE, $properties)) {
                $this->debugMode = true;
            } else {
                $this->debugMode = false;
            }
        }
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getTestMode()
    {
        return $this->testMode;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getDebugMode()
    {
        return $this->debugMode;
    }
}
