<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for specifying filtering of log results.
 */
class LogFilter
{
    public const SUBJECT_PATTERN = 'subjectPattern';

    public const START_DATE = 'startDate';
    public const END_DATE   = 'endDate';

    private $subjectPattern;
    private $startDate;
    private $endDate;


    public function __construct()
    {
        $this->subjectPattern = null;

        $now = time();
        $oneMonthAgo = strtotime('-1 month', $now);
        $this->startDate = date('m/d/Y', $oneMonthAgo);
        $this->endDate   = date('m/d/Y', $now);
    }

    public function set($properties)
    {
        if ($properties != null && is_array($properties)) {
            if (array_key_exists(self::SUBJECT_PATTERN, $properties)) {
                $this->subjectPattern = Filter::sanitizeString($properties[self::SUBJECT_PATTERN]);
            }

            if (array_key_exists(self::START_DATE, $properties)) {
                $this->startDate = Filter::sanitizeDate($properties[self::START_DATE]);
            }

            if (array_key_exists(self::END_DATE, $properties)) {
                $this->endDate = Filter::sanitizeDate($properties[self::END_DATE]);
            }
        }
    }

    public function validate()
    {
        if (empty($this->startDate)) {
            throw new \Exception("No start date specified.");
        } else {
            DateInfo::validateMdyDate($this->startDate);
        }

        if (empty($this->endDate)) {
            throw new \Exception("No end date specified.");
        } else {
            DateInfo::validateMdyDate($this->endDate);
        }

        $startTimestamp = strtotime($this->startDate);
        $endTimestamp   = strtotime($this->endDate);
        if ($endTimestamp < $startTimestamp) {
            throw new \Exception("The end date is before the start date.");
        }
    }

    public function getSubjectPattern()
    {
        return $this->subjectPattern;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
