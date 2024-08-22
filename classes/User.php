<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/** * Class for representing a user.  */
class User
{
    private $username;
    private $email;
    private $email2;
    private $email3;
    private $firstName;
    private $lastName;
    private $lastLogin;
    private $creationTime;
    private $suspendedTime;
    private $expiration;
    private $displayOnEmailUsers;
    private $comments;

    private $userRights;    // map from project ID to user rights for the project

    public function __construct()
    {
        $this->userRights = [];
        $this->displayOnEmailUsers = true;
        $this->comments = '';
    }

    # CURRENTLY UNUSED:
    # public function getProjectIds()
    # {
    #     $projectIds = [];
    #     foreach ($this->userRights as $rights) {
    #         $projectIds[] = $rights->getProjectId();
    #     }
    #     return $projectIds;
    # }

    # CURRENTLY UNUSED:
    # public static function getEmailList($users, $separator = ';')
    # {
    #     $emailList = '';
    #
    #     $emails = [];
    #     if (!empty($users) && is_array($users)) {
    #         foreach ($users as $user) {
    #             $emails[] = $user->getEmail();
    #         }
    #         $emailList = implode($separator, $emails);
    #     }
    #
    #     return $emailList;
    # }


    public function getNumberOfProjects()
    {
        $numberOfProjects = 0;
        if ($this->userRights != null && is_array($this->userRights)) {
            $numberOfProjects = count($this->userRights);
        }

        return $numberOfProjects;
    }

    public function addOrUpdateUserRights($userRights)
    {
        $this->userRights[$userRights->getProjectId()] = $userRights;
    }

    public function userRightsProjectExists($projectId)
    {
        return array_key_exists($projectId, $this->userRights);
    }

    public function getUserRightsForProject($projectId)
    {
        $userRights = null;
        if ($this->userRightsProjectExists($projectId)) {
            $userRights = $this->userRights[$projectId];
        }
        return $userRights;
    }

    #----------------------------------------------------------
    # Getters and Setters
    #----------------------------------------------------------
    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail2()
    {
        return $this->email2;
    }

    public function setEmail2($email2)
    {
        $this->email2 = $email2;
    }

    public function getEmail3()
    {
        return $this->email3;
    }

    public function setEmail3($email3)
    {
        $this->email3 = $email3;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getSuspendedTime()
    {
        return $this->suspendedTime;
    }

    public function setSuspendedTime($suspendedTime)
    {
        $this->suspendedTime = $suspendedTime;
    }

    public function getExpiration()
    {
        return $this->expiration;
    }

    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    public function getDisplayOnEmailUsers()
    {
        return $this->displayOnEmailUsers;
    }

    public function setDisplayOnEmailUsers($displayOnEmailUsers)
    {
        $this->displayOnEmailUsers = $displayOnEmailUsers;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function getUserRights()
    {
        return $this->userRights;
    }
}
