<?php
#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

require_once __DIR__.'/vendor/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;

$codeCoverageId = null;
if (array_key_exists('auto-notify-code-coverage-id', $_COOKIE)) {
    $codeCoverageId = $_COOKIE['auto-notify-code-coverage-id'];
}


if (!empty($codeCoverageId)) {
    $filter = new Filter;

    # Included files and directories
    $filter->includeFile(__DIR__.'/../../AutoNotifyModule.php');
    $filter->includeDirectory(__DIR__.'/../../classes');
    $filter->includeDirectory(__DIR__.'/../../web');

    # Excluded files
    # $filter->excludeFile(__DIR__.'/../../web/test.php');


    $selector = new Selector;

    $coverage = new CodeCoverage($selector->forLineCoverage($filter), $filter);

    $coverage->start($codeCoverageId);
}
