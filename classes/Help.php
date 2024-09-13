<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

class Help
{
    /** @var array map from help topic to help content */

    private static $help = [
        'config' =>
            "<p>"
            . "<b>Cron Frequency.</b> The cron job frequency, which determines how often this module"
            . " checks to see if any notifications need to be sent, is set in this module's config.json"
            . " file, and cannot be changed through REDCap."
            . "</p>"
            . "<p>"
            . "<b>Test Mode.</b> Selecting test mode and specifying an e-mail address for it"
            . " will cause all notifications to be sent to the specified"
            . " e-mail address."
            . "</p>"
        ,
        'notification' =>
            "<p>"
            . "Use this page to create a new notification or edit an existing one."
            . "</p>"
            . "<p>"
            . '<b>From.</b> The "From" section is used to specify the from e-mail address.'
            . ' It defaults to the'
            . ' "Universal FROM Email address" on REDCap\'s "General Configuration" page,'
            . ' if set, and if not, to the "Contact name email" on REDCap\'s '
            . ' "Home Page Settings" page.'
            . "</p>\n"
            . "<p>"
            . '<b>To.</b> The recipients for the notification are specified in the "To" section.'
            . ' The users who should receive the notification can be specified using either'
            . '  selectable forms provided in this section'
            . ' (e.g., the "API Token Users" form) or by specifying a previously created custom'
            . ' query that generates a set of users (the "Custom Query" option).'
            . "</p>"
            . "<p>"
            . '<b>Message.</b> This module allows variables to be used in the message that is sent.'
            . ' For example, the variable [last_name] displays the last name of the recipient'
            . ' of the notification. Variables can be inserted into a message by clicking'
            . ' on "Insert Variable" at the top of the message section.'
            . "</p>"
            . "<p>"
            . '<b>Scheduling.</b> There are three options for scheduling the notification:'
            . '<ol>'
            . '<li><b>Now.</b> The notification is sent right away.</li>'
            . '<li><b>Future.</b> The notification is sent at a specified date and time in the future.</li>'
            . '<li><b>Recurring.</b> The notification is sent on a recurring basis.'
            . ' A start date and end date can be specified and a limit on the number of times'
            . ' a notification is sent to each user can be specified.</li>'
            . '</ol>'
            . "<p>"
        ,
        'project-owners' =>
            "<p>"
            . 'Users of a project who have "Project Design and Setup" or'
            . ' "User Rights" privileges.'
            . "</p>"
        ,
        'projects' =>
            "<p>"
            . 'This page displays the projects for a query. Queries are constructed to find sets of users,'
            . ' so please note that:'
            . '<ul>'
            . '<li>Projects without any users will not be displayed.</li>'
            . '<li>Projects without any users that meet the user conditions specified'
            . '  (if any) will not be displayed.</li>'
            . '<li>The number of users displayed for the project will be the number of users that meet the specified'
            . ' user conditions (if any), which will be less than or equal to the total number of users'
            . ' for the project.</li>'
            . '</ul>'
            . "</p>"
            . "<p>"
            . ' For example, if you specify only the condition "Project Status = Development", then the query would'
            . ' find all users who have at least one Development project, and as a result, this page would'
            . ' display all Development projects with at least one user. If you had Development projects with no'
            . ' users, then these projects would not be displayed.'
            . "</p>"
            . "<p>"
            . 'As another example,'
            . ' if you specified only the condition "User Last Login age < 1 week", then this page would display'
            . ' the projects where at least one user of the project had logged in within the past week.'
            . ' The number of users for each project would be the number of users who had logged in'
            . ' within the past week (and not the total number of users).'
            . "</p>"
        ,
        'query-builder' =>
            '<p>'
            . 'The query builder allows you to create custom queries that'
            . ' return a set of REDCap users based on the conditions that'
            . ' you specify.'
            . ' The query can then be used in a notification to specify'
            . ' the users that the notification should be sent to.'
            . '</p>'
            . '<p>'
            . 'Custom queries consist of a nested list of condition grouping operators and'
            . ' conditions.'
            . "</p>\n"
            . '<p>'
            . '<h4>Grouping Operators</h4>'
            . 'The grouping operators are as follows:</p>'
            . '<ul>'
            . '<li><b>ALL.</b> ALL: <i>condition1</i> <i>condition2</i> = <i>condition1</i> AND <i>condition2</i></li>'
            . '<li><b>ANY.</b> ANY: <i>condition1</i> <i>condition2</i> = <i>condition1</i> OR <i>condition2</i></li>'
            . '<li><b>NOT ALL.</b> NOT ALL: <i>condition1</i> <i>condition2</i> ='
            . ' NOT(<i>condition1</i> AND <i>condition2</i>)</li>'
            . '<li><b>NOT ANY.</b> NOT ANY: <i>condition1</i> <i>condition2</i> ='
            . ' NOT(<i>condition1</i> OR <i>condition2</i>)</li>'
            . '</ul>'
            . "\n"
            . '<p>'
            . '<h4>Conditions</h4>'
            . 'Conditions have the following form:'
            . '<pre><i>variable</i> <i>operator</i> <i>value</i></pre>'
            . '</p>'
            . '<p>'
            . '<h5>Variables</h5>'
            . 'Query condition variables are divided into 4 groups:'
            . '<ul>'
            . '<li><b>User.</b> Variables that depend on the user and not the project. For example, the username'
            . ' and e-mail for a user will be the same across all projects; they are not project-specific.</li>'
            . '<li><b>User/Project.</b> Variables that depend on the combination of user and project.'
            . ' For example, the user roles for projects.</li>'
            . '<li><b>Project.</b> Variables that depend on the project and not the user. For example,'
            . ' the project ID and title'
            . ' of a project do not vary for different users.</li>'
            . '<li><b>Special.</b> Variables where their values depend on external module settings.</li>'
            . '</ul>'
            . '</p>'
            . '<p>'
            . '<h5>Values</h5>'
            . 'For condition values, note that:'
            . '<ul>'
            . '<li><b>Quotes.</b> Quotes will automatically be added for string values,'
            . ' so string values should be entered'
            . ' without quotes, unless the quotes are part of the actual value.</li>'
            . '<li><b>Age Operators.</b> For date variables with age operators, the allowed values are an integer'
            . ' or negative'
            . ' integer followed by one of: years, months, weeks, days, hours, minutes, seconds. For example:'
            . ' "2 weeks", "1 year", "6 months".'
            . '</li>'
            . '<li><b>Like Operator (Wildcards)</b> When using the "like" and "not like" operators, "%" can '
            . ' be used as a wildcard'
            . ' symbol in the value that will match zero or more characters, and "_" can be used to match'
            . ' any single character.</li>'
            . '</ul>'
            . '</p>'
            . '<p>'
            . '<h4>Buttons</h4>'
            . '<ul>'
            . '<li>'
            . '<button><i class="fa fa-circle-plus" style="color: green;"></i></button>'
            . ' adds a new condition.'
            . '</li>'
            . '<li> <button><i class="fa fa-folder-plus" style="color: #777777;"></i></button> '
            . ' adds a grouping operator that can have nested conditions.'
            . '</li>'
            . '<li> <button><i class="fa fa-remove" style="color: red;"></i></button> '
            . ' deletes a condition or a grouping operator and all its subconditions.'
            . '</li>'
            . '<li> <button style="background-color: green;">'
            . '<i class="fa fa-magnifying-glass" style="color: white;"></i>'
            . '</button>'
            . ' displays a query variable search dialog, which can be used to set the variable of a condition.'
            . '</li>'
            . '</ul>'
            . '</p>'
        ,
        'to-buttons' =>
            "<p>"
            . "<ul>"
            . "<li>"
            . "<b>Show Conditions.</b> Displays the conditions for selecting the \"to users\" as a logical expression."
            . "</li>"
            . "<li>"
            . "<b>Show SQL Query.</b> Displays the SQL query used by Auto-Notify to retrieve the set of"
            . " users the notification will be sent to."
            . "</li>"
            . "<li>"
            . "<b>View Users.</b> View the users who the notification will be sent to currently. Auto-notify"
            . " calculates the set of users to send a notification to just before the notification is sent."
            . "</li>"
            . "<li>"
            . "<b>View Projects.</b> View the projects based on the \"to users\" specification. For example, if"
            . " the users are specified as \"API token users\", the projects displayed will be all the projects"
            . " where a user has an API token."
            . "</li>"
            . "<li>"
            . "<b>View Send Counts.</b> View the number of times the notification has been sent to each user."
            . "</li>"
            . "</ul>"
            . "</p>"
        ,
        'test' =>
            "<p>"
            . "The test page allows you to simulate sending all notifications, or a single notification,"
            . " between two specified dates."
            . " You can specify an e-mail address to which all notifications should be sent, which will override"
            . " the recipient's e-mail address. You can add the [email] and/or [username] variables"
            . "  to the message of the"
            . " notification to see who the intended recipient was."
            . "</p>"
            . "<p>"
            . 'The variable [cron_time], which is not in the "Insert Variable" menu in the notification'
            . " message editor, can be added to a notification's message to see the send check time that"
            . " caused the notification to be sent."
            . "</p>"
            . "<p>"
            . "By default, test notification e-mails will be sent, but not be logged,"
            . " and user counts for notifications will"
            . " not be updated. This module keeps track of how many times each user has been"
            . " sent a notification, and this can be used to limit the number of times a user sees a notification."
            . "</p>"
    ];

    public static function getTitle($topic)
    {
        # Change dashes to blanks and capitalize the first letter of each word
        $title = str_replace('-', ' ', $topic);
        $title = ucwords($title);

        # Make adjustments
        $title = str_replace('Api', 'API', $title);
        $title = str_replace('Email', 'E-mail', $title);
        $title = str_replace('Sql', 'SQL', $title);

        return $title;
    }


    public static function getHelp($topic)
    {
        $help = self::$help[$topic];
        return $help;
    }

    public static function getHelpWithPageLink($topic, $module)
    {
        $help = self::getHelp($topic, $module);
        $help = '<a id="' . $topic . '-help-page" href="' . $module->getUrl('web/admin/help.php?topic=' . $topic) . '"'
            . ' target="_blank" style="float: right;"'
            . '>'
            . 'View text on separate page</a>'
            . '<div style="clear: both;"></div>'
            . $help;
        return $help;
    }

    public static function getTopics()
    {
        return array_keys(self::$help);
    }

    /**
     * Indicates if the specified topic is a valid help topic.
     *
     * @return boolean true if the specified topic is a valid help topic, and false otherwise.
     */
    public static function isValidTopic($topic)
    {
        $isValid = false;
        $topics = array_keys(self::$help);
        if (in_array($topic, $topics)) {
            $isValid = true;
        }
        return $isValid;
    }
}
