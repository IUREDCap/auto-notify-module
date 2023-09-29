<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing the query results for a custom REDCap users query.
 */
class UsersQueryResults
{
    # Map from username to User object
    private $users;

    /** @var array map from project ID to ProjectInfo object for REDCap projects */
    private $projectInfoMap;

    /** @var array map from project ID to ProjectInfo object for REDCap projects that are secondary to the
     *             main projects of the query (e.g., destination projects for cross-project piping)
     */
    private $secondaryProjectInfoMap;

    private $externalModules;

    /** @var array of column names to use in the "applicable project info" table */
    private $projectTableColumns;

    public function __construct()
    {
        $this->users = [];

        $this->projectInfoMap = [];

        $this->secondaryProjectInfoMap = [];

        $this->externalModules = [];

        $this->projectTableColumns = [];
    }

    public function hasUser($username)
    {
        $hasUser = false;
        if (array_key_exists($username, $this->users)) {
            $hasUser = true;
        }
        return $hasUser;
    }

    /**
     * Adds the specified project information to the query results.
     *
     * @param ProjectInfo $projectInfo the project information to add to the query results.
     */
    public function addOrUpdateProjectInfo($projectInfo)
    {
        $pid = $projectInfo->getId();

        if (array_key_exists($pid, $this->projectInfoMap) && $this->projectInfoMap[$pid] != null) {
            # Merge existing usernames with new usernames
            $usernames = array_merge($this->projectInfoMap[$pid]->getUsernames(), $projectInfo->getUsernames());
            $projectInfo->setUsernames($usernames);
        }

        $this->projectInfoMap[$pid] = $projectInfo;
    }

    public function addOrUpdateSecondaryProjectInfo($secondaryProjectInfo)
    {
        $pid = $secondaryProjectInfo->getId();

        if (array_key_exists($pid, $this->secondaryProjectInfoMap)) {
            # Merge existing usernames with new usernames
            $usernames = array_merge(
                $this->secondaryProjectInfoMap[$pid]->getUsernames(),
                $secondaryProjectInfo->getUsernames()
            );
            $secondaryProjectInfo->setUsernames($usernames);
        }

        $this->secondaryProjectInfoMap[$pid] = $secondaryProjectInfo;
    }


    #----------------------------------------------------------
    # Getters and Setters
    #----------------------------------------------------------
    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }

    public function getUser($username)
    {
        $user = null;
        if (array_key_exists($username, $this->users)) {
            $user = $this->users[$username];
        }

        return $user;
    }

    public function addOrUpdateUser($user)
    {
        $username = $user->getUsername();
        $this->users[$username] = $user;
    }

    public function getNumberOfUsers()
    {
        $numberOfUsers = 0;
        if ($this->users != null && is_array($this->users)) {
            $numberOfUsers = count($this->users);
        }

        return $numberOfUsers;
    }

    public function getExternalModules()
    {
        return $this->externalModules;
    }

    public function setExternalModules($externalModules)
    {
        $this->externalModules = $externalModules;
    }

    public function getProjectInfoMap()
    {
        return $this->projectInfoMap;
    }

    public function getProjectInfo($pid)
    {
        return $this->projectInfoMap[$pid];
    }

    public function getSecondaryProjectInfoMap()
    {
        return $this->secondaryProjectInfoMap;
    }

    public function getSecondaryProjectInfo($pid)
    {
        return $this->secondaryProjectInfoMap[$pid];
    }

    public function getProjectTableColumns()
    {
        return $this->projectTableColumns;
    }

    public function setProjectTableColumns($projectTableColumns)
    {
        $this->projectTableColumns = $projectTableColumns;
    }
}
