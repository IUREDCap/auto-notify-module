<!-- =================================================
Copyright (C) 2023 The Trustees of Indiana University
SPDX-License-Identifier: BSD-3-Clause
================================================== -->

Developer Guide
===================

Directory Structure
-----------------------

* __classes/__ - PHP classes other than the main module class
* config.json - module configuration file
* __dev/__ - development dependencies (if these have been installed; not committed to Git)
* __docs/__ - documents
* README.md - module description and usage requirements
* AutoNotifyModule.php - main module class
* __resources/__ - CSS, image, and JavaScript files
* __tests/__ - test files
    * __unit/__ - unit tests
    * __web/__ - web tests (that access a running instance of the module)
* __vendor/__ - production dependencies (committed to Git)
* __web/__ - user web pages
    * __admin/__ - admin web pages

Updating Dependencies
--------------------------

Dependencies are stored in the __dev/__ directory by Composer, and they are not committed to Git.
To avoid requiring Composer to be run when the module is installed, the non-development dependencies:

    * are copied to the __vendor/__ directory
    * the __vendor/__ directory is committed to Git.
    * the software uses the __vendor/__ directory as its source for dependencies

The dependencies that are committed to Git need to be stored in the __vendor/__ directory
so that they will be ignored by Vanderbilt's external module security scanner. If they
are stored in a directory with a different name, then the Vanderbilt scanner will scan them
and may generate errors for 3rd-party dependencies that cannot be modified.

To update the contents of __vendor/__ directory, the following commands
can be used from the top-level directory:

    composer update
    composer install --no-dev
    rm -rf vendor
    mv dev vendor
    composer install


To check for out of date dependencies, use:

    composer outdated --direct

__Automated Web Tests Dependencies__

There are also separate dependencies (not committed to Git) that are used for the automated web tests.
The configuration file for these dependencies is:

    tests/web/composer.json

And the dependencies are stored in the following directory:

    tests/web/vendor


Coding Standards Compliance
-----------------------------

This external module follows these PHP coding standards, except where
prevented from following them by REDCap:

* [PSR-1: Basic Coding Standard](http://www.php-fig.org/psr/psr-1/)
* [PSR-2: Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
* [PSR-4: Autoloader](http://www.php-fig.org/psr/psr-4/)
* Lower camel case variable names, e.g., $primaryKey


To check for coding standards compliance, enter the following command in the top-level directory:

    ./vendor/bin/phpcs -n
    
The "-n" option eliminated warnings. The configuration for phpcs is in file __phpcs.xml__ in the top-level directory.


Static Code Analyzer
--------------------------

This external module includes the Vimeo Psalm scanner.
This scanner is a static code analyzer, so it
does not require a running instance of the external module to work.
This scanner has been adopted by Vanderbilt
as a security scanner for REDCap external module submissions. To scan this external module, use the following
command in the top-level directory of the project:

    ./vendor/bin/psalm

A configuration file (psalm.xml) has been created that will cause Psalm to run in security analysis mode.

Automated Tests
--------------------------

To run the unit tests, enter the following command in the top-level directory:

    ./vendor/bin/phpunit
    
The configuration for phpunit is in file __phpunit.xml__ in the top-level directory.

The module also has web tests that access a running Auto-Notify external module. For
information on running these tests, see the file:

[tests/web/README.md](../tests/web/README.md)

