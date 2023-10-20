<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Calendar class.
 */
class Calendar
{
    /** @var array map from day number to notifications */
    private $days;


    public function __construct()
    {
        $this->days = array();
    }
}
