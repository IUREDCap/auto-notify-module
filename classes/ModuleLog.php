<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Logging class that uses REDCap's built-in logging tables for external modules.
 */
class ModuleLog
{
    public const LOG_FORMAT_VERSION = 1.0;

    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }


    /**
     * Gets the specified log data from the external module logging tables.
     *
     */
    public function getData($startDate = null, $endDate = null, $subjectPattern = null)
    {
        $query = "select log_id, timestamp, ui_id, project_id, message, notificationId,"
            . " `from`, `to`, notification, userConditions, schedule, subject, testRun, cronTime";

        $queryParameters = array();

        #----------------------------------------
        # Query start date condition (if any)
        #----------------------------------------
        if (!empty($startDate)) {
            $startTime = \DateTime::createFromFormat('m/d/Y', $startDate);
            $startTime = $startTime->format('Y-m-d');
            $query .= " where timestamp >= '" . Filter::escapeForMysql($startTime) . "'";
        }

        #---------------------------------------
        # Query end date condition (if any)
        #---------------------------------------
        if (!empty($endDate)) {
            $endTime = \DateTime::createFromFormat('m/d/Y', $endDate);
            $endTime->modify('+1 day');
            $endTime = $endTime->format('Y-m-d');
            if (!empty($startDate)) {
                $query .= ' and';
            } else {
                $query .= ' where';
            }
            $query .= " timestamp < '" . Filter::escapeForMysql($endTime) . "'";
        }

        #if (!empty($subjectPattern)) {
        #    if (!empty($startDate) || !empty($endDate)) {
        #        $query .= ' and';
        #    } else {
        #        $query .= ' where';
        #    }
        #    $query .= " subject like '%{$subjectPattern}%'";
        #}

        $query .= ' order by timestamp desc';

        #print "<pre>\n";
        #print "QUERY: {$query}\n";
        #print "</pre>\n";

        $logData = $this->module->queryLogs($query, $queryParameters);

        return $logData;
    }
}
