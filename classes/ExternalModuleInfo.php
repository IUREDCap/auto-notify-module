<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing external module information.
 */
class ExternalModuleInfo
{
    private $id;
    private $name;
    private $directoryPrefix;
    private $version;

    /**
     * Returns a map with external module IDs as the keys and
     * ExternalModuleInfo objects as the values for a specified
     * array, or non-ID map, of ExternalModuleInfo objects.
     */
    public static function convertToIdMap($externalModuleInfos)
    {
        $map = null;
        if ($externalModuleInfos != null) {
            $map = [];
            foreach ($externalModuleInfos as $externalModuleInfo) {
                $map[$externalModuleInfo->getId()] = $externalModuleInfo;
            }
        }
        return $map;
    }

    #----------------------------------------------------------
    # Getters and Setters
    #----------------------------------------------------------
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

    public function getDirectoryPrefix()
    {
        return $this->directoryPrefix;
    }

    public function setDirectoryPrefix($directoryPrefix)
    {
        $this->directoryPrefix = $directoryPrefix;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }
}
