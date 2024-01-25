<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/** * Class for representing a user's project rights.  */
class UserRights
{
    private $projectId;
    private $hasUserRights;
    private $design;    // design rights for the specified project

    private $hasApiToken;
    private $apiExport;
    private $apiImport;
    private $mobileApp;

    private $externalModuleIds;
    private $cppDestinationProjectIds;  // cross-project piping destination projects IDs
    private $cdosSourceProjectIds;  // Copy Data on Save external module source projects IDs

    public function __construct()
    {
        $this->externalModuleIds = array();
        $this->cppDestinationProjectIds = array();
        $this->cdosSourceProjectIds = array();
    }

    public function addExternalModuleId($id)
    {
        if (!in_array($id, $this->externalModuleIds)) {
            $this->externalModuleIds[] = $id;
        }
    }

    public function addCppDestinationProjectId($id)
    {
        if (!in_array($id, $this->cppDestinationProjectIds)) {
            $this->cppDestinationProjectIds[] = $id;
        }
    }

    public function addCdosSourceProjectId($id)
    {
        if (!in_array($id, $this->cdosSourceProjectIds)) {
            $this->cdosSourceProjectIds[] = $id;
        }
    }

    #----------------------------------------------------------
    # Getters and Setters
    #----------------------------------------------------------
    public function getProjectId()
    {
        return $this->projectId;
    }

    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getHasUserRights()
    {
        return $this->hasUserRights;
    }

    public function setHasUserRights($hasUserRights)
    {
        $this->hasUserRights = $hasUserRights;
    }

    public function getDesign()
    {
        return $this->design;
    }

    public function setDesign($design)
    {
        $this->design = $design;
    }

    public function getHasApiToken()
    {
        return $this->hasApiToken;
    }

    public function setHasApiToken($hasApiToken)
    {
        $this->hasApiToken = $hasApiToken;
    }

    public function getApiExport()
    {
        return $this->apiExport;
    }

    public function setApiExport($apiExport)
    {
        $this->apiExport = $apiExport;
    }

    public function getApiImport()
    {
        return $this->apiImport;
    }

    public function setApiImport($apiImport)
    {
        $this->apiImport = $apiImport;
    }

    public function getMobileApp()
    {
        return $this->mobileApp;
    }

    public function setMobileApp($mobileApp)
    {
        $this->mobileApp = $mobileApp;
    }

    public function getExternalModuleIds()
    {
        return $this->externalModuleIds;
    }

    public function setExternalModuleIds($externalModuleIds)
    {
        $this->externalModuleIds = $externalModuleIds;
    }

    public function getCppDestinationProjectIds()
    {
        return $this->cppDestinationProjectIds;
    }

    public function setCppDestinationProjectIds($cppDestinationProjectIds)
    {
        $this->cppDestinationProjectIds = $cppDestinationProjectIds;
    }

    public function getCdosSourceProjectIds()
    {
        return $this->cdosSourceProjectIds;
    }

    public function setCdosSourceProjectIds($cdosSourceProjectIds)
    {
        $this->cdosSourceProjectIds = $cdosSourceProjectIds;
    }
}
