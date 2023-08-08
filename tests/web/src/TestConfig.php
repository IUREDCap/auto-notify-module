<?php
#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule\WebTests;

/**
 * Test Configuration class. Instances of this class are created
 * using a .ini configuration file.
 */
class TestConfig
{
    private $redCap;
    private $admin;

    /**
     * @param string $file path to file containing test configuration.
     */
    public function __construct($file)
    {
        $processSections = true;
        $properties = parse_ini_file($file, $processSections);

        foreach ($properties as $name => $value) {
            $matches = array();
            if ($name === 'redcap') {
                $this->redCap = $value;
            } elseif ($name === 'admin') {
                $this->admin = $value;
            }
        }
    }

    public function getRedCap()
    {
        return $this->redCap;
    }

    public function getAdmin()
    {
        return $this->admin;
    }
}
