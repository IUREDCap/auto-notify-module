<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

# This is required for cron jobs
// phpcs:disable
require_once(__DIR__.'/vendor/autoload.php');
// phpcs:enable

define('AUTO_NOTIFY_MODULE', 1);

/**
 * Main Auto-Notify Module class.
 */
class AutoNotifyModule extends \ExternalModules\AbstractExternalModule
{
    public const ADMIN_HOME_PAGE    = 'web/admin/index.php';
    public const QUERY_PAGE         = 'web/admin/query.php';

    public const CONDITIONS_SERVICE = 'web/admin/conditions_service.php';
    public const QUERY_CONDITIONS_SERVICE = 'web/admin/query_conditions_service.php';

    public const TO_CONDITIONS_SERVICE      = 'web/admin/to_conditions_service.php';
    public const TO_JSON_CONDITIONS_SERVICE = 'web/admin/to_json_conditions_service.php';
    public const TO_QUERY_SERVICE           = 'web/admin/to_query_service.php';

    public const CONFIG_PAGE        = 'web/admin/config.php';
    public const USERS_PAGE         = 'web/admin/users.php';
    public const USER_PROJECTS_PAGE = 'web/admin/user_projects.php';
    public const PROJECTS_PAGE      = 'web/admin/projects.php';
    public const SEND_COUNTS_PAGE   = 'web/admin/send_counts.php';

    public const TEST_PAGE          = 'web/admin/test.php';
    public const CALENDAR_PAGE      = 'web/admin/calendar.php';
    public const LOG_PAGE           = 'web/admin/log.php';
    public const LOG_SERVICE        = 'web/admin/log_service.php';

    public const NOTIFICATION_PAGE     = 'web/admin/notification.php';
    public const NOTIFICATIONS_PAGE    = 'web/admin/notifications.php';
    public const NOTIFICATION_SERVICE  = 'web/admin/notification_service.php';

    public const QUERIES_PAGE       = 'web/admin/queries.php';
    public const QUERY_SERVICE      = 'web/admin/query_service.php';

    public const SCHEDULE_PAGE      = 'web/admin/schedule.php';

    # Note: access $db or $settings using their getter methods instead of directly, since lazy evaluation is used
    #       in the getters to initialize them.
    private $db;
    private $settings;

    /**
     * Cron function that is run for this modules cron job.
     */
    public function cron()
    {
        try {
            $nowTimestamp = time();
            $lastRunTimestamp = $this->getLastRunTime();
            if (empty($lastRunTimestamp)) {
                # Need to get an initial last run time set, before notifications can run,
                # because they need last and current run times
                $lastRunTimestamp = $nowTimestamp;
                $this->setLastRunTime($lastRunTimestamp);
            } else {
                $this->processNotifications($lastRunTimestamp, $nowTimestamp);
                $this->setLastRunTime($nowTimestamp);
            }
        } catch (\Exception $exception) {
            # If an error occurs, send an e-mail to the from e-mail address
            # with an error message
            $subject = "Auto-Notify external module cron error.";
            $message = "ERROR: " . $exception->getMessage() . "\n";
            $email   = $this->getFromEmail();
            \REDCap::email($email, $email, $subject, $message);
        }
    }

    /**
     * Checks admin page access and exits if there is an issue.
     */
    public function checkAdminPagePermission()
    {
        if (!$this->isSuperUser()) {
            exit("Only super users can access this page!");
        }
    }

    public function isSuperUser()
    {
        $isSuperUser = false;
        if (defined('SUPER_USER') && SUPER_USER) {
            $isSuperUser = true;
        }
        return $isSuperUser;
    }

    public function processNotifications($lastRunTimestamp, $nowTimestamp, $testRun = null)
    {
        if ($nowTimestamp > $lastRunTimestamp) {
            $notifications = $this->getNotifications();
            $notifications->send($this, $lastRunTimestamp, $nowTimestamp, $testRun);
        }
    }




    public function renderAdminPageContentHeader($selfUrl, $errorMessage, $warningMessage, $successMessage)
    {
        $this->renderAdminTabs($selfUrl);
        $this->renderAdminMessageHeader($errorMessage, $warningMessage, $successMessage);
    }

    public function renderAdminMessageHeader($errorMessage, $warningMessage, $successMessage)
    {
        $this->renderErrorMessageDiv($errorMessage);
        $this->renderWarningMessageDiv($warningMessage);
        $this->renderSuccessMessageDiv($successMessage);
    }


    /**
     * Renders the page content tabs for the admin (Control Center) pages.
     */
    public function renderAdminTabs($activeUrl = '')
    {
        $adminUrl = $this->getUrl(self::ADMIN_HOME_PAGE);
        $adminLabel = '<span class="fas fa-info-circle"></span>'
           . ' Info';

        $configUrl = $this->getUrl(self::CONFIG_PAGE);
        $configLabel = '<span class="fas fa-gear"></span>'
           . ' Config';

        $testUrl = $this->getUrl(self::TEST_PAGE);
        $testLabel = '<span class="fas fa-check-square"></span>'
           . ' Test';

        $notificationUrl = $this->getUrl(self::NOTIFICATION_PAGE);
        $notificationLabel = '<span class="fas fa-envelope"></span>'
           . ' Notifications</span>';

        #$notificationsUrl = $this->getUrl(self::NOTIFICATIONS_PAGE);
        #$notificationsLabel = '<span class="fas fa-envelope"></span>'
           #. ' Notifications</span>';

        $builderUrl = $this->getUrl(self::QUERY_PAGE);
        $builderLabel = '<span class="fas fa-bars"></span>'
           . ' Queries</span>';

        $queriesUrl = $this->getUrl(self::QUERIES_PAGE);
        $queriesLabel = '<span class="fas fa-bars"></span>'
           . ' Queries</span>';

        #$calendarUrl = $this->getUrl(self::CALENDAR_PAGE);
        #$calendarLabel = '<span class="fas fa-calendar"></span>'
        #   . ' Cal.</span>';

        #$logUrl = $this->getUrl(self::LOG_PAGE);
        #$logLabel = '<span class="fas fa-book"></span>'
        #   . ' Log</span>';

        $tabs = array();

        $tabs[$adminUrl]         = $adminLabel;
        #$tabs[$usersUrl]         = $usersLabel;
        $tabs[$notificationUrl]  = $notificationLabel;
        #$tabs[$notificationsUrl] = $notificationsLabel;
        $tabs[$builderUrl]       = $builderLabel;
        $tabs[$configUrl]        = $configLabel;
        $tabs[$testUrl]          = $testLabel;

        #$tabs[$queriesUrl]       = $queriesLabel;
        #$tabs[$calendarUrl]      = $calendarLabel;
        #$tabs[$logUrl]           = $logLabel;

        $this->renderTabs($tabs, $activeUrl);
    }

    /**
     * Render sub-tabs for the admin query pages.
     */
    public function renderAdminQuerySubTabs($activeUrl = '')
    {
        $queryUrl = $this->getUrl(self::QUERY_PAGE);
        $queryLabel = '<span class="fas fa-cog"></span>'
           . ' Query Builder';

        $queriesUrl   = $this->getUrl(self::QUERIES_PAGE);
        $queriesLabel = '<span class="fas fa-list"></span>'
           . ' Saved Queries';

        $tabs = array();

        $tabs[$queryUrl]    = $queryLabel;
        $tabs[$queriesUrl]  = $queriesLabel;

        $this->renderSubTabs($tabs, $activeUrl);
    }

    /**
     * Render sub-tabs for the admin notification pages.
     */
    public function renderAdminNotificationSubTabs($activeUrl = '')
    {
        $notificationUrl = $this->getUrl(self::NOTIFICATION_PAGE);
        $notificationLabel = '<span class="fas fa-envelope"></span>'
           . ' Notification';

        $notificationsUrl   = $this->getUrl(self::NOTIFICATIONS_PAGE);
        $notificationsLabel = '<span class="fas fa-bars"></span>'
           . ' Saved Notifications';

        $logUrl   = $this->getUrl(self::LOG_PAGE);
        $logLabel = '<span class="fas fa-receipt"></span>'
           . ' Log';

        $scheduleUrl   = $this->getUrl(self::SCHEDULE_PAGE);
        $scheduleLabel = '<span class="fas fa-calendar-days"></span>'
           . ' Schedule';

        $tabs = array();

        $tabs[$notificationUrl]  = $notificationLabel;
        $tabs[$notificationsUrl] = $notificationsLabel;
        $tabs[$logUrl]           = $logLabel;
        $tabs[$scheduleUrl]      = $scheduleLabel;

        $this->renderSubTabs($tabs, $activeUrl);
    }

    /**
     * Renders sub-tabs (second-level tabs) on the page.
     *
     * @param array $tabs map from URL to tab label.
     * @param string $activeUrl the URL that should be marked as active.
     */
    public function renderSubTabs($tabs = array(), $activeUrl = '')
    {
        echo '<div style="text-align:right; margin-bottom: 17px; margin-top: 0px; padding-top: 0px;">';
        $isFirst = true;
        foreach ($tabs as $url => $label) {
            $style = '';
            if (strcasecmp($url, $activeUrl) === 0) {
                $style = ' style="padding: 1px; text-decoration: none; '
                    . 'font-weight: bold; border-bottom: 3px solid black;" ';
            } else {
                $style = ' style="padding: 1px; text-decoration: none;" ';
            }

            if ($isFirst) {
                $isFirst = false;
            } else {
                echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
            }
            echo '<a href="' . $url . '" ' . $style . '>' . "{$label}</a>";
        }
        echo "&nbsp;&nbsp;&nbsp;";
        echo "</div>\n";
    }

    public function renderErrorMessageDiv($message)
    {
        if (!empty($message)) {
            echo '<div align="center" class="red" style="margin: 20px 0;">' . "\n";
            echo '<img src="' . (APP_PATH_IMAGES . 'exclamation.png') . '" alt="">';
            echo '&nbsp;' . Filter::escapeForHtml($message) . "\n";
            echo "</div>\n";
        }
    }

    public function renderWarningMessageDiv($message)
    {
        if (!empty($message)) {
            echo '<div align="center" class="yellow" style="margin: 20px 0;">' . "\n";
            echo '<img src="' . (APP_PATH_IMAGES . 'warning.png') . '"  alt="" width="16px">';
            echo '&nbsp;' . Filter::escapeForHtml($message) . "\n";
            echo "</div>\n";
        }
    }

    public function renderSuccessMessageDiv($message)
    {
        if (!empty($message)) {
            echo '<div align="center" class="darkgreen" style="margin: 20px 0;">' . "\n";
            echo '<img src="' . (APP_PATH_IMAGES . 'accept.png') . '" alt="">';
            echo '&nbsp;' . Filter::escapeForHtml($message) . "\n";
            echo "</div>\n";
        }
    }

        /**
     * Renders tabs using built-in REDCap styles.
     *
     * @param array $tabs map from URL to tab label.
     * @param string $activeUrl the URL that should be marked as active.
     */
    public function renderTabs($tabs = array(), $activeUrl = '')
    {
        echo '<div id="sub-nav" style="margin:5px 0 20px;">' . "\n";
        echo '<ul>' . "\n";
        foreach ($tabs as $tabUrl => $tabLabel) {
            // Check for Active tab
            $isActive = false;
            $class = '';
            if (strcasecmp($tabUrl, $activeUrl) === 0) {
                $class = ' class="active"';
                $isActive = true;
            }
            echo '<li ' . $class . '>' . "\n";
            # Note: URLs created with the getUrl method, so they should already be escaped
            echo '<a href="' . $tabUrl . '" style="font-size:13px;color:#393733;padding:6px 9px 5px 10px;">';
            # Note: labels are static values in code, and not based on user input
            echo $tabLabel . '</a>' . "\n";
        }
        echo '</li>' . "\n";
        echo '</ul>' . "\n";
        echo '</div>' . "\n";
        echo '<div class="clear"></div>' . "\n";
    }

    /**
     * Get a REDCap "from e-mail" address.
     */
    public static function getFromEmail()
    {
        # Need to disable phpcs here, because the REDCap e-mail variables
        # ($from_email and $homepage_contact_email) don't use camel-case.
        // phpcs:disable
        global $from_email;
        global $homepage_contact_email;

        $fromEmail = '';

        if (!empty($from_email)) {
            $fromEmail = $from_email;
        } else {
            $fromEmail = $homepage_contact_email;
        }
        // phpcs:enable

        return $fromEmail;
    }

    public function getVersion()
    {
        return $this->getSettings()->getVersion();
    }

    public function getLastRunTime()
    {
        return $this->getSettings()->getLastRunTime();
    }

    public function setLastRunTime($timestamp)
    {
        $this->getSettings()->setLastRunTime($timestamp);
    }

    public function getAdminConfig()
    {
        return $this->getSettings()->getAdminConfig();
    }

    public function setAdminConfig($config)
    {
        $this->getSettings()->setAdminConfig($config);
    }

    /* External Module methods */
    public function getExternalModuleInfoMap()
    {
        return ($this->getDb())->getExternalModuleInfoMap();
    }

    /**
     * @return UsersQueryResults the users query results for the specified users.
     */
    public function getUsers($usersSpecification, $nowDateTime = null)
    {
        return ($this->getDb())->getUsers($usersSpecification, $nowDateTime);
    }

    /* RedCapInfo */
    public function getRedCapInfo()
    {
        return ($this->getDb())->getRedCapInfo();
    }


    /* Variables methods */

    /**
     * Gets information for the query variables.
     *
     * @return array Map from variable name to Variable object.
     */
    public function getVariables()
    {
        $externalModuleInfoMap = $this->getExternalModuleInfoMap();
        $variables = Variable::getVariablesFromJsonFile($externalModuleInfoMap);
        return $variables;
    }


    /* Query methods */
    public function getQueries()
    {
        return $this->getSettings()->getQueries();
    }

    public function getQuery($queryId)
    {
        return $this->getSettings()->getQuery($queryId);
    }

    public function addOrUpdateQuery($query)
    {
        $this->getSettings()->addOrUpdateQuery($query);
    }

    public function deleteQueryById($queryId)
    {
        $this->getSettings()->deleteQueryById($queryId);
    }

    public function copyQueryById($queryId)
    {
        $this->getSettings()->copyQueryById($queryId);
    }


    /* Notification methods */
    public function getNotifications()
    {
        return $this->getSettings()->getNotifications();
    }

    public function queryNotifications($order = 'descending', $status = 'all', $subjectFilter = '')
    {
        $query = [];

        $notifications = $this->getSettings()->getNotifications();

        if ($notifications !== null) {
            $query = $notifications->queryNotifications($order, $status, $subjectFilter);
        }

        return $query;
    }

    public function getActiveNotifications()
    {
        return $this->getSettings()->getActiveNotifications();
    }

    public function getNotification($notificationId)
    {
        return $this->getSettings()->getNotification($notificationId);
    }

    public function addOrUpdateNotification($notification)
    {
        $this->getSettings()->addOrUpdateNotification($notification);
    }

    public function copyNotificationById($notificationId, $newSubject = null)
    {
        $this->getSettings()->copyNotificationById($notificationId, $newSubject);
    }

    public function deleteNotificationById($notificationId)
    {
        $this->getSettings()->deleteNotificationById($notificationId);
    }

    /* REDCap Database */
    public function getDb()
    {
        if ($this->db == null) {
            $this->db = new RedCapDb($this);
        }
        return $this->db;
    }

    public function getSettings()
    {
        if ($this->settings == null) {
            $this->settings = new Settings($this, $this->getDb());
        }
        return $this->settings;
    }
}
