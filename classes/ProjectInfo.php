<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing REDCap project information.
 */
class ProjectInfo
{
    private $id;
    private $name;
    private $status;
    private $purpose;
    private $surveysEnabled;
    private $isLongitudinal;
    private $isOnline;
    private $creationTime;
    private $completedTime;
    private $deletedTime;
    private $usernames; // array with usernames as key (set of usernames)

    public function __construct()
    {
        $this->usernames = array();
    }

    public static function convertTrueFalseToYesNo($value)
    {
        if (strcasecmp($value, 'true') === 0) {
            $value = 'yes';
        } elseif (strcasecmp($value, 'false') === 0) {
            $value = 'no';
        }

        return $value;
    }

    public function getStatusLabel($variables)
    {
        $status = $this->getStatus();
        $variable = $variables['status'];
        $statusLabel = $variable->getSelectValueLabel($status);
        return $statusLabel;
    }

    public function getPurposeLabel($variables)
    {
        $purpose = $this->getPurpose();
        $variable = $variables['purpose'];
        $purposeLabel = $variable->getSelectValueLabel($purpose);
        return $purposeLabel;
    }

    public function addUsername($username)
    {
        $this->usernames[$username] = 1;
    }

    public function getNumberOfUsers()
    {
        return count($this->usernames);
    }

    public function getUsernames()
    {
        return $this->usernames;
    }

    public function setUsernames($usernames)
    {
        if ($usernames != null && is_array($usernames)) {
            $this->usernames = $usernames;
        }
    }

    public function getUsernamesList()
    {
        return sort(array_keys($this->usernames));
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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getPurpose()
    {
        return $this->purpose;
    }

    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    public function getIsLongitudinal()
    {
        return $this->isLongitudinal;
    }

    public function setIsLongitudinal($isLongitudinal)
    {
        $this->isLongitudinal = $isLongitudinal;
    }

    public function getIsOnline()
    {
        return $this->isOnline;
    }

    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
    }

    public function getSurveysEnabled()
    {
        return $this->surveysEnabled;
    }

    public function setSurveysEnabled($surveysEnabled)
    {
        $this->surveysEnabled = $surveysEnabled;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getCompletedTime()
    {
        return $this->completedTime;
    }

    public function setCompletedTime($completedTime)
    {
        $this->completedTime = $completedTime;
    }

    public function getDeletedTime()
    {
        return $this->deletedTime;
    }

    public function setDeletedTime($deletedTime)
    {
        $this->deletedTime = $deletedTime;
    }
}
