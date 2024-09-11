<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

use ExternalModules\ExternalModules;

/**
 * Class for methods that access the REDCap database directly.
 */
class RedCapDb
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }


    /**
     * Gets the users specified by the specified users specification :-).
     *
     * @param UsersSpecification $usersSpecificatio
     *
     * @return UsersQueryResults the users query results.
     */
    public function getUsers($usersSpecification, $nowDateTime = null)
    {
        $usersQueryResults = new UsersQueryResults();

        if (!empty($usersSpecification)) {
            $query = $usersSpecification->toQuery($this->module);
            $usersQueryResults = $this->getUsersFromQuery($query, $nowDateTime);
        }

        return $usersQueryResults;
    }


    /**
     * @return array map from external module directory prefixes to ExternalModuleInfo objects.
     */
    public function getExternalModuleInfoMap()
    {
        $map = [];

        $query = 'SELECT modules.external_module_id, modules.directory_prefix, settings.value AS version'
            . ' FROM redcap_external_modules modules, redcap_external_module_settings settings'
            . ' WHERE modules.external_module_id = settings.external_module_id'
            . " AND settings.key = 'version'"
            . ' ORDER BY modules.external_module_id'
            ;
        $parameters = [];

        $result = $this->module->query($query, $parameters);
        while ($row = db_fetch_assoc($result)) {
            $id = $row['external_module_id'];
            $prefix = $row['directory_prefix'];
            $version = $row['version'];

            $config = ExternalModules::getConfig($prefix);
            $name = $config['name'];

            $externalModuleInfo = new ExternalModuleInfo();
            $externalModuleInfo->setId($id);
            $externalModuleInfo->setName($name);
            $externalModuleInfo->setDirectoryPrefix($prefix);
            $externalModuleInfo->setVersion($version);

            $map[$prefix] = $externalModuleInfo;
        }

        return $map;
    }


    public function getRedCapInfo()
    {
        $info = new RedCapInfo();

        // homepage contact and e-mail are Admin info on config page
        $sql = "select field_name, value from redcap_config"
            . " where field_name in ("
            . "     'from_email', 'homepage_contact', 'homepage_contact_email'"
            . "     , 'redcap_base_url', 'institution', 'site_org_type'"
            . ")";
        $parameters = [];

        $result = $this->module->query($sql, $parameters);
        # print ("<pre>\n");
        while ($row = db_fetch_assoc($result)) {
            # print_r($row);
            if ($row['field_name'] === 'redcap_base_url') {
                $info->setUrl($row['value']);
            } elseif ($row['field_name'] === 'institution') {
                $info->setInstitution($row['value']);
            }
        }
        # print ("</pre>\n");

        return $info;
    }

    /**
     * Get the users query results for a specified users query.
     *
     * @param Query $query Users query.
     *
     * @return UsersQueryResults the query results from the specified users query.
     */
    public function getUsersFromQuery($query, $nowDateTime = null)
    {
        $queryResults = new UsersQueryResults();
        $conditions = $query->getConditions();
        $jsonConditions = $conditions->toJson();

        $getProjectInfo = false;

        $queryResults = $this->getUsersFromJsonConditions($jsonConditions, $getProjectInfo, $nowDateTime);

        $queryResults->setProjectTableColumns($query->getProjectTableColumns());

        return $queryResults;
    }

    /*
     * @return UsersQueryResults the query results from the specified JSON query conditions.
     */
    public function getUsersFromJsonConditions($jsonConditions, $getProjectInfo = false, $nowDateTime = null)
    {
        $queryResults = new UsersQueryResults();

        $parameters = [];
        $variables = $this->module->getVariables();
        $query = Query::queryConditionsToSql($variables, $jsonConditions, $getProjectInfo, $nowDateTime);

        $result = $this->module->query($query, $parameters);

        while ($row = db_fetch_assoc($result)) {
            $user = null;
            $username = $row['username'];

            if ($queryResults->hasUser($username)) {
                $user = $queryResults->getUser($username);
            } else {
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($row['user_email']);
                $user->setEmail2($row['user_email2']);
                $user->setEmail3($row['user_email3']);
                $user->setFirstName($row['user_firstname']);
                $user->setLastName($row['user_lastname']);
                $user->setLastLogin($row['user_lastlogin']);
                $user->setCreationTime($row['user_creation']);
                $user->setSuspendedTime($row['user_suspended_time']);
                $user->setExpiration($row['user_expiration']);
                $user->setComments($row['user_comments']);
                $user->setDisplayOnEmailUsers($row['display_on_email_users']);
            }

            $projectId = $row['project_id'];

            # error_log("User: {$username} - pid: {$projectId}\n", 3, __DIR__ . '/test.log');

            if (!empty($projectId)) {
                if (!$user->userRightsProjectExists($projectId)) {
                    $userRights = new UserRights();
                    $userRights->setProjectId($projectId);
                    if ($getProjectInfo) {
                        $userRights->setRoleName($row['role_name']);
                        $userRights->setHasUserRights($row['user_rights']);
                        $userRights->setDesign($row['design']);
                        $userRights->setHasApiToken($row['has_api_token']);
                        $userRights->setApiExport($row['api_export']);
                        $userRights->setApiImport($row['api_import']);
                        $userRights->setMobileApp($row['mobile_app']);
                    }
                } else {
                    $userRights = $user->getUserRightsForProject($projectId);
                }

                if (array_key_exists('external_module_id', $row)) {
                    $externalModuleId = $row['external_module_id'];
                    if (!empty($externalModuleId)) {
                        $userRights->addExternalModuleId($externalModuleId);
                    }
                }

                # Copy Data on Save
                if (array_key_exists('cdos_source_project_id', $row)) {
                    $cdosSourceProjectId = $row['cdos_source_project_id'];
                    if (!empty($cdosSourceProjectId)) {
                        $userRights->addCdosSourceProjectId($cdosSourceProjectId);

                        $projectName = $row['cdos_source_project_name'];
                        $secondaryProjectInfo = new ProjectInfo();
                        $secondaryProjectInfo->setId($cdosSourceProjectId);
                        $secondaryProjectInfo->setName($projectName);
                        $queryResults->addOrUpdateSecondaryProjectInfo($secondaryProjectInfo);
                    }
                }

                # Cross-Project Piping
                if (array_key_exists('cpp_destination_project_id', $row)) {
                    $cppDestinationProjectId = $row['cpp_destination_project_id'];
                    if (!empty($cppDestinationProjectId)) {
                        $userRights->addCppDestinationProjectId($cppDestinationProjectId);

                        $projectName = $row['cpp_destination_project_name'];
                        $secondaryProjectInfo = new ProjectInfo();
                        $secondaryProjectInfo->setId($cppDestinationProjectId);
                        $secondaryProjectInfo->setName($projectName);
                        $queryResults->addOrUpdateSecondaryProjectInfo($secondaryProjectInfo);
                    }
                }

                $user->addOrUpdateUserRights($userRights);

                #-------------------------------------
                # Update project information
                #-------------------------------------
                $projectName = $row['app_title'];
                $projectInfo = new ProjectInfo();
                $projectInfo->setId($projectId);
                $projectInfo->setName($projectName);
                if ($getProjectInfo) {
                    $projectInfo->setStatus($row['status']);
                    $projectInfo->setPurpose($row['purpose']);
                    $projectInfo->setIsLongitudinal($row['repeatforms']);
                    $projectInfo->setIsOnline($row['online_offline']);
                    $projectInfo->setSurveysEnabled($row['surveys_enabled']);
                    $projectInfo->setCreationTime($row['creation_time']);
                    $projectInfo->setCompletedTime($row['completed_time']);
                    $projectInfo->setDeletedTime($row['date_deleted']);
                    $projectInfo->setPiEmail($row['project_pi_email']);
                    $projectInfo->setPiFirstName($row['project_pi_firstname']);
                    $projectInfo->setPiLastName($row['project_pi_lastname']);
                    $projectInfo->addUsername($username);
                }
                $queryResults->addOrUpdateProjectInfo($projectInfo);
            }

            $queryResults->addOrUpdateUser($user);
        }

        return $queryResults;
    }


    /**
     * Starts a database transaction.
     */
    public function startTransaction()
    {
        db_query("SET AUTOCOMMIT=0");
        db_query("BEGIN");
    }

    /**
     * Ends a database transaction.
     *
     * @param boolean $commit indicates if the transaction should be committed.
     */
    public function endTransaction($commit)
    {
        try {
            if ($commit) {
                db_query("COMMIT");
            } else {
                db_query("ROLLBACK");
            }
        } catch (\Exception $exception) {
            ;
        }
        db_query("SET AUTOCOMMIT=1");
    }
}
