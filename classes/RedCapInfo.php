<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for representing REDCap information (WORK IN PROGRESS)
 */
class RedCapInfo
{
    private $url;
    private $institution;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getInstitution()
    {
        return $this->institution;
    }

    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }
}
