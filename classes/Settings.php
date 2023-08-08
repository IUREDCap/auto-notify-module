<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for managing the storage and retrieval of external module settings stored in the REDCap database.
 */
class Settings
{
    public const VERSION_KEY = 'version';

    public const CONFIG_KEY        = 'config';        // for storing configuration values entered in admin interface
    public const LAST_RUN_TIME_KEY = 'last-run-time'; // for storing last cron run time
    public const QUERIES_KEY       = 'queries';       // for storing queries
    public const NOTIFICATIONS_KEY = 'notifications'; // for storing notifications

    private $module;

    /** @var RedCapDb $db REDCap database object. */
    private $db;

    public function __construct($module, $db)
    {
        $this->module = $module;
        $this->db     = $db;
    }


    public function getVersion()
    {
        $version = $this->module->getSystemSetting(self::VERSION_KEY);
        return $version;
    }

    #-------------------------------------------------------------------
    # Last run time methods
    #-------------------------------------------------------------------

    /**
     * Gets the last time that the Notification cron jobs were run
     */
    public function getLastRunTime()
    {
        $lastRunTime = $this->module->getSystemSetting(self::LAST_RUN_TIME_KEY);
        if (empty($lastRunTime)) {
            $lastRunTime = null;
        }
        return $lastRunTime;
    }

    public function setLastRunTime($timestamp)
    {
        $this->module->setSystemSetting(self::LAST_RUN_TIME_KEY, $timestamp);
    }


    #----------------------------------------------------------------------------
    # Admin configuration methods (configuration done in admin interface; not
    # the module's configuration file information).
    #----------------------------------------------------------------------------

    public function getAdminConfig()
    {
        $config = new Config();

        $phpSerialization = $this->module->getSystemSetting(self::CONFIG_KEY);
        if ($phpSerialization != null || !empty($phpSerialization)) {
            $config = unserialize($phpSerialization);
            $config->migrate();   // Upgrade object to lastest version
        }

        return $config;
    }

    public function setAdminConfig($config)
    {
        $phpSerialization = serialize($config);
        $this->module->setSystemSetting(self::CONFIG_KEY, $phpSerialization);
    }

    #---------------------------------------------------------------------
    # Query methods
    #---------------------------------------------------------------------

    public function getQueries()
    {
        $queries = new Queries();

        $phpSerialization = $this->module->getSystemSetting(self::QUERIES_KEY);
        if ($phpSerialization != null && !empty($phpSerialization)) {
            $queries = unserialize($phpSerialization);
            $queries->migrate();
            foreach ($queries as $query) {
                $query->migrate();
                $query->conditions->migrate();
            }
        }

        return $queries;
    }

    public function getQuery($queryId)
    {
        $queries = $this->getQueries();

        $query = $queries->getQuery($queryId);
        return $query;
    }

    public function addOrUpdateQuery($query)
    {
        $commit = true;
        $queries = new Queries();

        $this->db->startTransaction();

        $exception = null;
        try {
            $queries = $this->getQueries();
            $queries->addOrUpdate($query);
            $phpSerialization = serialize($queries);
            $this->module->setSystemSetting(self::QUERIES_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }

    public function deleteQueryById($queryId)
    {
        $commit = true;
        $queries = new Queries();

        $this->db->startTransaction();

        $exception = null;
        try {
            $queries = $this->getQueries();
            $queries->delete($queryId);
            $phpSerialization = serialize($queries);
            $this->module->setSystemSetting(self::QUERIES_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }

    public function copyQueryById($queryId)
    {
        $commit = true;
        $queries = new Queries();

        $this->db->startTransaction();

        try {
            $queries = $this->getQueries();
            $queries->copy($queryId);
            $phpSerialization = serialize($queries);
            $this->module->setSystemSetting(self::QUERIES_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }



    #---------------------------------------------------------------------
    # Notifications methods
    #---------------------------------------------------------------------

    public function getNotifications()
    {
        $notifications = new Notifications();

        $phpSerialization = $this->module->getSystemSetting(self::NOTIFICATIONS_KEY);
        # print "<hr/>SERIALIZATION: |{$phpSerialization}|</hr/>\n";
        if ($phpSerialization != null && !empty($phpSerialization)) {
            $notifications = unserialize($phpSerialization);
            $notifications->migrate();
            foreach ($notifications->getNotifications() as $notification) {
                $notification->migrate();
                $notification->getUsersSpecification()->migrate();
                $notification->getSchedule()->migrate();
            }
        }

        return $notifications;
    }

    public function getActiveNotifications()
    {
        $notifications = $this->getNotifications();

        $activeNotifications = $notifications->getActiveNotifications();

        return $activeNotifications;
    }

    public function getNotification($notificationId)
    {
        $notifications = $this->getNotifications();

        $notification = $notifications->getNotification($notificationId);

        return $notification;
    }

    public function addOrUpdateNotification($notification)
    {
        $commit = true;
        $notifications = new Notifications();

        $this->db->startTransaction();

        $exception = null;
        try {
            $notifications = $this->getNotifications();
            $notifications->addOrUpdate($notification);
            $phpSerialization = serialize($notifications);
            $this->module->setSystemSetting(self::NOTIFICATIONS_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }


    public function copyNotificationById($notificationId)
    {
        $commit = true;
        $notifications = new Notifications();

        $this->db->startTransaction();

        $exception = null;
        try {
            $notifications = $this->getNotifications();
            $notification = $notifications->getNotification($notificationId);

            if ($notification != null) {
                $copy = $notification->getDraftCopy();
                $notifications->addOrUpdate($copy);
            }

            $phpSerialization = serialize($notifications);
            $this->module->setSystemSetting(self::NOTIFICATIONS_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }

    public function deleteNotificationById($notificationId)
    {
        $commit = true;
        $notifications = new Notifications();

        $this->db->startTransaction();

        $exception = null;
        try {
            $notifications = $this->getNotifications();
            $notifications->delete($notificationId);
            $phpSerialization = serialize($notifications);
            $this->module->setSystemSetting(self::NOTIFICATIONS_KEY, $phpSerialization);
        } catch (\Exception $exception) {
            $commit = false;
        }

        $this->db->endTransaction($commit);

        if ($exception != null) {
            throw $exception;
        }
    }
}
