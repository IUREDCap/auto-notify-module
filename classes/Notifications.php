<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing all notifications.
 */
class Notifications
{
    # The object version number should be updated any time there is a change
    # to the structure of this object. In addition, the migrate method
    # below should be modified so that objects with older version numbers
    # will be modified to have the new version number.
    public const OBJECT_VERSION = 1;

    private $objectVersion;

    private $nextId;
    private $notifications;  // Array of notifications

    public function __construct()
    {
        $this->objectVersion = self::OBJECT_VERSION;

        $this->nextId = 1;

        $this->notifications = array();
    }

    public function migrate()
    {
    }

    /**
     * @return array all notification objects.
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    public function getActiveNotifications()
    {
        $activeNotifications = [];

        foreach ($this->notifications as $notification) {
            if ($notification->isActive()) {
                $activeNotifications[] = $notification;
            }
        }

        return $activeNotifications;
    }

    /**
     * WORK IN PROGRESS
     *
     */
    public function getScheduledNotificationsForMonth($month, $year)
    {
    }

    /**
     * WORK IN PROGRESS
     *
     * Gets the notifications scheduled for the specified time range, specifically
     * notifications specified after the begin time and before or on the end time.
     */
    public function getScheduledNotifications($beginTime, $endTime, $notificationId = null)
    {
        $scheduledNotifications = array();

        if (empty($notificationId)) {
            $activeNotifications = $this->getActiveNotifications();
        } else {
            $activeNotifications[] = $this->getNotification($notificationId);
        }

        foreach ($activeNotifications as $notification) {
            $currentBeginTime = $beginTime;
            $schedule = $notification->getSchedule();
            if ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_FUTURE) {
                $sendTimestamp = strtotime($schedule->getSendTime());
                if ($sendTimestamp > $currentBeginTime && $sendTimestamp <= $endTime) {
                    $scheduledNotifications[$sendTimestamp] = $notification;
                }
            } elseif ($schedule->getSchedulingOption() === Schedule::SCHED_OPT_RECURRING) {
                while (($nextTimestamp = $schedule->getNextRecurringTimestamp($currentBeginTime)) != null) {
                    if ($nextTimestamp <= $endTime) {
                        $scheduledNotifications[$nextTimestamp] = $notification;
                        $currentBeginTime = $nextTimestamp;
                    } else {
                        break;
                    }
                }
            }
        }

        ksort($scheduledNotifications);

        return $scheduledNotifications;
    }

    public function getNotification($notificationId)
    {
        $notification = null;

        if (array_key_exists($notificationId, $this->notifications)) {
            $notification = $this->notifications[$notificationId];
        }
        return $notification;
    }

    /**
     * Adds the specified notification and returns an ID (the
     * index of where it was added in the array).
     */
    public function addOrUpdate($notification)
    {
        $id = $notification->getId();
        if ($id == null) {
            # add case, no ID set, so set ID to next ID (and imcrement it)
            $id = $this->nextId++;

            # Set ID in notification so next time it will be updated, instead of added
            $notification->setId($id);
        }

        $this->notifications[$id] = $notification;

        return $id;
    }

    public function delete($notificationId)
    {
        if (array_key_exists($notificationId, $this->notifications)) {
            unset($this->notifications[$notificationId]);
        }
    }

    /**
     * Sends active notifications that are scheduled to run after the specified start time
     * and before or on the specified end time.
     *
     * @param int $lastSendTimestamp time after which scheduled notifications should be run.
     * @param int $currentSendTimestamp time before or on scheduled notifications should be run.
     */
    public function send($module, $lastSendTimestamp, $currentSendTimestamp, $testRun = null)
    {
        $activeNotifications = $this->getActiveNotifications();

        $testNotificationId = 0;
        if (isset($testRun)) {
            $testNotificationId = $testRun->getNotificationId();
        }

        foreach ($activeNotifications as $notification) {
            if ($testNotificationId == 0 || $testNotificationId == $notification->getId()) {
                $notification->send($module, $lastSendTimestamp, $currentSendTimestamp, $testRun);
            }
        }
    }

    public function getObjectVersion()
    {
        return $this->objectVersion;
    }
}
