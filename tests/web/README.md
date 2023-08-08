<!--
Copyright (C) 2023 The Trustees of Indiana University
SPDX-License-Identifier: BSD-3-Clause
-->

Auto-Notify Module Web Tests
============================

Automated web tests, which access the Auto-Notify External Module running in REDCap, have been developed
using [Behat](https://behat.org) with [Mink](https://mink.behat.org/en/latest/). The tests are
written in English based on standard and custom sentence patterns and are in the **tests/web/features**
directory.


One-time initial setup:
--------------------------

1. Install the Chrome browser if it is not already installed. For example, on Ubuntu 20 you can use the following:

    sudo apt install chromium-browser

2. Install Composer if you don't already have it, and run the following command in the tests/web directory:

    composer install

3. Run the following command in the top-level web tests directory:

    cp config-example.ini config.ini

4. Edit the config.ini file created above, and enter appropriate values for properties

5. If you want to collect test coverage data, you need to complete the following steps:

    * Make sure that the tests/web/coverage-data/ directory can be written to by your REDCap web server.
      The REDCap web server has to have permission to write to this directory for code coverage
      data to be collected.
    * Set coverage code to run at the beginning and end of each web test request. You need to set the
      PHP properties as shown below. The easiest way to do this is to set these in the php.ini file
      for the web server running REDCap.

        * **auto_prepend_file** - should be set to the full path of the **tests/web/start_coverage.php** script
        * **auto_append_file** - should be set to the full path of the **tests/web/end_coverage.php** script

    * If you are using the Apache web server, an alternative, more flexible approach to set up the coverage
      code is as follows (using Ubuntu as the example operating system):

        * Create an Apache configuration file **auto-notify-code-coverage.conf** in Apache's available configuration
          files directory (e.g., **/etc/apache2/conf-available/**) with the following contents
          (the script directory needs to be changed as appropriate):

            <pre>
            php_value auto_prepend_file /var/www/html/redcap/modules/auto-notify-module_v0.0.1/tests/web/start_coverage.php
            php_value auto_append_file  /var/www/html/redcap/modules/auto-notify-module_v0.0.1/tests/web/end_coverage.php
            </pre>

        * Enable the above configuration file with the following commands:

            <pre>
            sudo a2enconf auto-notify-code-coverage
            sudo systemctl reload apache2
            </pre>

        * Disable the configuration file with these commands:

            <pre>
            sudo a2disconf auto-notify-code-coverage
            sudo systemctl reload apache2
            </pre>


Setup each time before tests are run
---------------------------------------

Since the web tests need to access a running instance of the Auto-Notify external module, REDCap must be running
and have the Auto-Notify external module installed.

### Test coverage statistics

If you want to collect test coverage data, you will need to
clear any previous coverage data by executing the following in the **tests/web** directory:

    php clear_coverage_data.php

### Browser setup

For the automated web tests to run, you need to run an instance of the Chrome browser that the web tests
can access.
To run the browser in headless mode (the recommended approach), use the command shown below.
Running in headless mode will make the tests run faster, and can be used to run the entire set of tests at once,
but you won't see the browser running.

    chrome --disable-gpu --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222

If you want to actually see the tests interacting with the browser, use the command shown below 
to start Chrome instead of the command above.
If you use the command below, you will need to run the tests one feature at a time.

    chrome --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222

Note that if you installed **chromium-browser**, you will either need to make an alias named "chrome" for it, or
use "chromium-browser" in the commands above instead of "chrome".


Running the tests
----------------------

The web tests use Behat (https://docs.behat.org/en/latest/). You can use the following commands in the top-level
web tests directory (tests/web) to run the behat web tests:

    ./vendor/bin/behat
    ./vendor/bin/behat -f progress      # just prints summary of results
    ./vendor/bin/behat <path-to-feature-file>    # for testing a single feature file

Viewing the test coverage data
-------------------------------

Combine the coverage data:

    php combine_coverage.php

Open the following file with a web browser:

    tests/web/coverage/index.php

You can add the unit test coverage data by executing the following command in the top-level module directory:

    ./dev/bin/phpunit --coverage-php tests/web/coverage-data/coverage.unit

Then to update the coverage/index.php file, you need to re-run the combine_coverage.php script.

Similarly, you can also add manual test coverage data by setting the 'code-coverage-id' cookie in your browser,
and then going through your tests in that browser. For example, in Chrome:

* Enter &lt;CTRL&gt;&lt;SHIFT&gt;J to bring up the developer tools console
* In the web console, enter:

        document.cookie="auto-notify-code-coverage-id=manual"


Other commands
----------------------

See the definition expressions for behat:

    ./vendor/bin/behat -dl



