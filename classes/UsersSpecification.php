<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/** * Class for representing specification that describes a set of users.  */
class UsersSpecification
{
    public const OBJECT_VERSION = 1;

    # Users options for query (what kind of users)
    public const USERS_OPTION           = "usersOption";      // The users option for the query
    public const USERS_OPT_API_TOKEN    = "usersOptApiToken"; // API Token users
    public const USERS_OPT_EXT_MOD      = "usersOptExtMod";   // External module users
    public const USERS_OPT_CUSTOM_QUERY = "usersOptCutomQuery"; // Users generated from custom query

    public const USERS_OPT_ADMIN     = "usersOptAdmin";    // Admin users - currently unsupported

    public const USERS_OPTIONS = [self::USERS_OPT_API_TOKEN, self::USERS_OPT_EXT_MOD, self::USERS_OPT_CUSTOM_QUERY];

    # public const INCLUDE_SUSPENDED_USERS = "include_suspended_users";

    # External module options
    public const EXTERNAL_MODULE_OPTION = "externalModuleOption";
    public const EXT_MOD_OPT_ANY        = "extModOptAny";
    public const EXT_MOD_OPT_ANY_OF     = "extModOptAnyOf";
    public const EXT_MOD_OPT_CPP_SOURCE = "extModOptCrossProjectPipingSource";

    public const EXT_MOD_OPT_CDOS_DESTINATION = "extModOptCopyDataOnSaveDestination";

    public const EXT_MOD_OPTIONS        = [
        self::EXT_MOD_OPT_ANY,
        self::EXT_MOD_OPT_ANY_OF,
        self::EXT_MOD_OPT_CPP_SOURCE,
        self::EXT_MOD_OPT_CDOS_DESTINATION
    ];

    public const EXTERNAL_MODULES       = 'externalModules';

    # List of e-mail for users who should be excluded
    public const OPT_OUT_LIST           = 'optOutList';

    public const CUSTOM_QUERY_ID        = 'customQueryId';

    public const EXCLUDE_SUSPENDED_USERS = 'excludeSuspendedUsers';
    public const EXCLUDE_USERS_WITH_EXPIRED_RIGHTS = 'excludeUsersWithExpiredRights';
    public const EXCLUDE_NO_DISPLAY_ON_EMAIL_USERS = 'exludeNoDisplayOnEmailUsers';

    public const EXCLUDE_DELETED_PROJECTS = 'excludeDeletedProjects';
    public const EXCLUDE_COMPLETED_PROJECTS = 'excludeCompletedProjects';

    public const PROJECT_OWNERS = 'projectOwners';

    private $objectVersion;

    private $usersOption;
    private $externalModuleOption;
    private $externalModules;

    private $customQueryId;

    private $excludeSuspendedUsers;
    private $excludeUsersWithExpiredRights;
    private $excludeNoDisplayOnEmailUsers;

    private $excludeDeletedProjects;
    private $excludeCompletedProjects;

    private $projectOwners;

    private $optOutList;


    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->usersOption          = self::USERS_OPT_API_TOKEN;

        $this->externalModuleOption = self::EXT_MOD_OPT_ANY;
        $this->externalModules = [];

        $this->optOutList = '';

        $this->customQueryId = null;

        $this->excludeSuspendedUsers = true;
        $this->excludeUsersWithExpiredRights = true;
        $this->excludeNoDisplayOnEmailUsers = true;

        $this->excludeDeletedProjects = true;
        $this->excludeCompletedProjects = true;

        $this->projectOwners = true;
    }

    public function migrate()
    {
    }

    public function validate()
    {
        # Users Option
        if (empty($this->usersOption)) {
            throw new \Exception("No users option specified.");
        } elseif (!in_array($this->usersOption, self::USERS_OPTIONS)) {
            throw new \Exception('Invalid users option "' . $this->usersOption . '" specified.');
        }

        # External Module Option (if selected)
        if ($this->usersOption === self::USERS_OPT_EXT_MOD) {
            if (empty($this->externalModuleOption)) {
                throw new \Exception("No external module option specified.");
            } elseif (!in_array($this->externalModuleOption, self::EXT_MOD_OPTIONS)) {
                throw new \Exception('Invalid external module option "' . $this->externalModuleOption . '" specified.');
            } elseif ($this->externalModuleOption === self::EXT_MOD_OPT_ANY_OF) {
                if (empty($this->externalModules)) {
                    throw new \Exception('No external modules specified.');
                }
            }
        }

        # Opt-out list
        if (!empty($this->optOutList)) {
            $emails = explode(',', $this->optOutList);
            foreach ($emails as $email) {
                $email = trim($email);
                if (!Filter::isEmail($email)) {
                    throw new \Exception("Email \"{$email}\" in the opt-out list is not a valid e-mail.");
                }
            }
        }
    }

    public function set($properties)
    {
        # print "<pre>\n";
        # print_r($properties);
        # print "</pre>\n";

        if (array_key_exists(self::USERS_OPTION, $properties)) {
            $this->usersOption = Filter::sanitizeButtonLabel($properties[self::USERS_OPTION]);
        }

        if (array_key_exists(self::EXTERNAL_MODULE_OPTION, $properties)) {
            $this->externalModuleOption = Filter::sanitizeButtonLabel($properties[self::EXTERNAL_MODULE_OPTION]);
        }

        $this->externalModules = [];
        if (array_key_exists(self::EXTERNAL_MODULES, $properties)) {
            $this->externalModules = $properties[self::EXTERNAL_MODULES];
            if ($this->externalModules == null) {
                $this->externalModules = [];
            }
        }

        if (array_key_exists(self::USERS_OPTION, $properties)) {
            $this->usersOption = Filter::sanitizeButtonLabel($properties[self::USERS_OPTION]);
        }

        if (array_key_exists(self::OPT_OUT_LIST, $properties)) {
            $this->optOutList = $properties[self::OPT_OUT_LIST];
        }

        if (array_key_exists(self::CUSTOM_QUERY_ID, $properties)) {
            $this->customQueryId = Filter::sanitizeInt($properties[self::CUSTOM_QUERY_ID]);
        }


        if (array_key_exists(self::EXCLUDE_SUSPENDED_USERS, $properties)) {
            $this->excludeSuspendedUsers = true;
        } else {
            $this->excludeSuspendedUsers = false;
        }

        if (array_key_exists(self::EXCLUDE_USERS_WITH_EXPIRED_RIGHTS, $properties)) {
            $this->excludeUsersWithExpiredRights = true;
        } else {
            $this->excludeUsersWithExpiredRights = false;
        }

        if (array_key_exists(self::EXCLUDE_NO_DISPLAY_ON_EMAIL_USERS, $properties)) {
            $this->excludeNoDisplayOnEmailUsers = true;
        } else {
            $this->excludeNoDisplayOnEmailUsers = false;
        }


        if (array_key_exists(self::EXCLUDE_DELETED_PROJECTS, $properties)) {
            $this->excludeDeletedProjects = true;
        } else {
            $this->excludeDeletedProjects = false;
        }

        if (array_key_exists(self::EXCLUDE_COMPLETED_PROJECTS, $properties)) {
            $this->excludeCompletedProjects = true;
        } else {
            $this->excludeCompletedProjects = false;
        }

        if (array_key_exists(self::PROJECT_OWNERS, $properties)) {
            $this->projectOwners = true;
        } else {
            $this->projectOwners = false;
        }
    }

    /**
     * Provides a string representation of the users specification.
     */
    public function toString()
    {
        // WORK IN PROGRESS
        $value = '';
        if ($this->usersOption === self::USERS_OPT_API_TOKEN) {
            $value .= 'API token users';
        } elseif ($this->usersOption == self::USERS_OPT_EXT_MOD) {
            if ($this->externalModuleOptions === self::EXT_MOD_OPT_ANY) {
                $value .= 'Users of any external module';
            } elseif ($this->externalModuleOptions === self::EXT_MOD_OPT_ANY_OF) {
            } elseif ($this->externalModuleOptions === self::EXT_MOD_OPT_CPP_SOURCE) {
                $value .= 'Users with a source project of the Cross-Project Piping external module';
            } elseif ($this->externalModuleOptions === self::EXT_MOD_OPT_CDOS_DESTINATION) {
                $value .= 'Users with a destination project of the Copy Data on Save external module';
            }
        }
    }

    /**
     * Generates the query form for this users specification.
     *
     * @return Query $query the query corresponding to this users specifications.
     */
    public function toQuery($module)
    {
        $usersOption = $this->getUsersOption();

        if ($usersOption === UsersSpecification::USERS_OPT_API_TOKEN) {
            # API TOKEN USERS
            $subConditions = array();

            $tokenCondition = new Conditions();
            $tokenCondition->set('api_token', 'is not', 'null');
            $subConditions[] = $tokenCondition;

            $exclusionConditions = $this->getExclusionConditions();

            $subConditions = array_merge($subConditions, $exclusionConditions);

            $conditions = new Conditions();
            $conditions->set(null, Conditions::ALL_OP, null, $subConditions);

            $query = new Query();
            $query->setProjectTableColumns(['project_id', 'app_title']);
            $query->setConditions($conditions);
        } elseif ($usersOption === UsersSpecification::USERS_OPT_EXT_MOD) {
            # EXTERNAL MODULE USERS
            $subConditions = array();

            $externalModuleOption = $this->getExternalModuleOption();

            $query = new Query();

            $extModOptConditions = new Conditions();

            if ($externalModuleOption === UsersSpecification::EXT_MOD_OPT_CPP_SOURCE) {
                # Cross-Project Piping
                $query->setProjectTableColumns(['project_id', 'app_title', 'cpp_destination_project_id']);
                $extModOptConditions->set('cpp_destination_project_id', 'is not', 'null');
            } elseif ($externalModuleOption === UsersSpecification::EXT_MOD_OPT_CDOS_DESTINATION) {
                # Copy Data on Save
                $query->setProjectTableColumns(['project_id', 'app_title', 'cdos_source_project_id']);
                $extModOptConditions->set('cdos_source_project_id', 'is not', 'null');
            } elseif ($externalModuleOption === UsersSpecification::EXT_MOD_OPT_ANY) {
                # Users of any external module
                $query->setProjectTableColumns(['project_id', 'app_title', 'directory_prefix']);
                $extModOptConditions->set('directory_prefix', '<>', '');
            } elseif ($externalModuleOption === UsersSpecification::EXT_MOD_OPT_ANY_OF) {
                # Users of any specified external modules
                $query->setProjectTableColumns(['project_id', 'app_title', 'directory_prefix']);
                $externalModules = $this->getExternalModules();
                if ($externalModules == null || count($externalModules) === 0) {
                    $extModOptConditions->set('directory_prefix', '=', ' ');  // Condition which should always be false
                } else {
                    $extModOptConditions->set(null, Conditions::ANY_OP, null, []);

                    foreach ($externalModules as $directoryPrefix) {
                        $subCondition = new Conditions();
                        $subCondition->set('directory_prefix', '=', $directoryPrefix);
                        $extModOptConditions->addSubCondition($subCondition);
                    }
                }
            }
            $subConditions[] = $extModOptConditions;

            $projectOwners = $this->getProjectOwners();
            if ($projectOwners) {
                $condition1 = new Conditions();
                $condition1->set('user_rights', '=', 1);
                $condition2 = new Conditions();
                $condition2->set('design', '=', 1);
                $orConditions = new Conditions();
                $orConditions->set(null, Conditions::ANY_OP, null, [$condition1, $condition2]);
                $subConditions[] = $orConditions;
            }

            $exclusionConditions = $this->getExclusionConditions();

            $subConditions = array_merge($subConditions, $exclusionConditions);

            $conditions = new Conditions();
            $conditions->set(null, Conditions::ALL_OP, null, $subConditions);
            $query->setConditions($conditions);
        } elseif ($usersOption === UsersSpecification::USERS_OPT_CUSTOM_QUERY) {
            # Users generated from CUSTOM QUERY
            $queryId = $this->getCustomQueryId();
            $query = $module->getQuery($queryId);
        }

        return $query;
    }

    public function getExclusionConditions()
    {
        $exclusionConditions = [];

        if ($this->getExcludeSuspendedUsers()) {
            $excludeSuspendedCondition = new Conditions();
            $excludeSuspendedCondition->set('user_suspended_time', 'is', 'null');
            $exclusionConditions[] = $excludeSuspendedCondition;
        }

        if ($this->getExcludeUsersWithExpiredRights()) {
            $exclude1 = new Conditions();
            $exclude1->set('expiration', 'is', 'null');
            $exclude2 = new Conditions();
            $exclude2->set('expiration', 'age <', '0 seconds');
            $orConditions = new Conditions();
            $orConditions->set(null, Conditions::ANY_OP, null, [$exclude1, $exclude2]);
            $exclusionConditions[] = $orConditions;
        }

        if ($this->getExcludeNoDisplayOnEmailUsers()) {
            $excludeNoDisplayOnEmailUsersCondition = new Conditions();
            $excludeNoDisplayOnEmailUsersCondition->set('display_on_email_users', '=', 1);
            $exclusionConditions[] = $excludeNoDisplayOnEmailUsersCondition;
        }

        if ($this->getExcludeDeletedProjects()) {
            $excludeDeletedProjectsCondition = new Conditions();
            $excludeDeletedProjectsCondition->set('date_deleted', 'is', 'null');
            $exclusionConditions[] = $excludeDeletedProjectsCondition;
        }

        if ($this->getExcludeCompletedProjects()) {
            $excludeCompletedProjectsCondition = new Conditions();
            $excludeCompletedProjectsCondition->set('completed_time', 'is', 'null');
            $exclusionConditions[] = $excludeCompletedProjectsCondition;
        }

        return $exclusionConditions;
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }

    public function getUsersOption()
    {
        return $this->usersOption;
    }

    public function getExternalModuleOption()
    {
        return $this->externalModuleOption;
    }

    public function getUsersOptionString()
    {
        $value = self::toUsersOptionString($this->usersOption);
        return $value;
    }

    public function getExternalModules()
    {
        return $this->externalModules;
    }

    public function getOptOutList()
    {
        return $this->optOutList;
    }

    public function getCustomQueryId()
    {
        return $this->customQueryId;
    }

    public function getExcludeSuspendedUsers()
    {
        return $this->excludeSuspendedUsers;
    }

    public function getExcludeUsersWithExpiredRights()
    {
        return $this->excludeUsersWithExpiredRights;
    }

    public function getExcludeNoDisplayOnEmailUsers()
    {
        return $this->excludeNoDisplayOnEmailUsers;
    }

    public function getExcludeDeletedProjects()
    {
        return $this->excludeDeletedProjects;
    }

    public function getExcludeCompletedProjects()
    {
        return $this->excludeCompletedProjects;
    }

    public function getProjectOwners()
    {
        return $this->projectOwners;
    }

    public static function toUsersOptionString($usersOption)
    {
        $value = '';
        switch ($usersOption) {
            case self::USERS_OPT_API_TOKEN:
                $value = 'API token users';
                break;
            case self::USERS_OPT_EXT_MOD:
                $value = 'external module users';
                break;
            case self::USERS_OPT_ADMIN:
                $value = 'admin users';
                break;
            case self::USERS_OPT_CUSTOM_QUERY:
                $value = 'custom query';
                break;
        }

        return $value;
    }
}
