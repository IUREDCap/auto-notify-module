<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

#------------------------------------------------------------
# Test page for running notification send tests for a
# specified data range
#------------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */


#---------------------------------------------
# Check that the user has access permission
#---------------------------------------------
$module->checkAdminPagePermission();


use ExternalModules\ExternalModules;
use IU\AutoNotifyModule\AutoNotifyModule;
use IU\AutoNotifyModule\Conditions;
use IU\AutoNotifyModule\Config;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\ProjectInfo;
use IU\AutoNotifyModule\RedCapDb;

$selfUrl   = $module->getUrl(AutoNotifyModule::USERS_PAGE);
$conditionsServiceUrl = $module->getUrl(AutoNotifyModule::CONDITIONS_SERVICE);
$userProjectsUrl = $module->getUrl(AutoNotifyModule::USER_PROJECTS_PAGE);


?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();

$htmlPage = new HtmlPage();
$htmlPage->PrintHeaderExt();
include APP_PATH_VIEWS . 'HomeTabs.php';

# require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
# $cssFile = $module->getUrl('resources/notify.css');
// $cssFile = $module->getUrl('resources/table.css');
// $link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">';
// $buffer = str_replace('</head>', "    " . $link . "\n</head>", $buffer);
$cssFile = $module->getUrl('resources/table.css');
$buttonsCssFile = $module->getUrl('resources/buttons.dataTables.css');
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">'
    . "\n"
    . '<link href="' . $buttonsCssFile . '" rel="stylesheet" type="text/css" media="all">'
    ;
$jsInclude =
    '<script type="text/javascript" src="' . ($module->getUrl('resources/dataTables.buttons.min.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/buttons.html5.min.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/buttons.colVis.min.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/queryBuilder.js')) . '"></script>'
    . "\n"
    . '<script type="text/javascript" src="' . ($module->getUrl('resources/projectTableColumns.js')) . '"></script>';
$buffer = str_replace('</head>', "    {$link}\n{$jsInclude}\n</head>", $buffer);

echo $buffer;

$jsonConditions = '';
if (array_key_exists('tableJsonConditions', $_POST)) {
    $jsonConditions = $_POST['tableJsonConditions'];
}

$queryName = '';
if (array_key_exists('tableQueryName', $_POST)) {
    $queryName = Filter::sanitizeString($_POST['tableQueryName']);
}

try {
    $db = $module->getDb();

    # Validate conditions
    $conditions = new Conditions();
    $conditions->setFromJson($jsonConditions);
    $variables = $module->getVariables();
    $conditions->validate($variables);

    $queryResults = $db->getUsersFromJsonConditions($jsonConditions);
    $users = $queryResults->getUsers();
} catch (\Exception $exception) {
    $error = 'Error: ' . $exception->getMessage();
}


#print "<pre>";
#print_r($users);
#print "</pre>";

?>

<div style="margin-top: 60px;">
<div>

<?php
#print "<pre>\n";
#print_r($_POST);
#print "</pre>\n";
?>

<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php
$module->renderAdminMessageHeader($error, $warning, $success);
?>


<div style="font-weight: bold; font-size: 120%; text-align: center;">
Users
<?php
if (!empty($queryName)) {
    echo ' for query "' . Filter::escapeForHtml($queryName) . '"';
}
?>
</div>
<div id="resultsDisplay" style="margin-top: 17px; padding: 5px; border: 1px solid #777777;">
    <?php if (isset($users)) { ?>
        <div>
            <div id="colVis" style="float: left;"></div>
            <div id="userTableButtons" style="float: right; margin-bottom: 7px;">
            </div>
            <div style="clear: both;"></div>
        </div>

        <div id="userTableDiv" style="display: none;">
            <table id="userTable" style="white-space: nowrap;">
                <thead>
                    <tr> 
                        <th>username</th>
                        <th>user first name</th>
                        <th>user last name</th>
                        <th>user e-mail</th>
                        <th>user e-mail 2</th>
                        <th>user e-mail 3</th>
                        <th>last login</th>
                        <th>creation time</th>
                        <th># of projects</th>
                        <th>suspended time</th>
                        <th>expiration</th>
                        <th>display on email users</th>
                        <th>comments</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ($users as $user) {
                        $username = $user->getUsername();

                        # Display on Email users
                        $displayOnEmailUsers = $user->getDisplayOnEmailUsers();
                        $variable = $variables['display_on_email_users'];
                        $displayOnEmailUsersLabel = $variable->getSelectValueLabel($displayOnEmailUsers);
                        $displayOnEmailUsersLabel = ProjectInfo::convertTrueFalseToYesNo($displayOnEmailUsersLabel);

                        echo "<tr>";
                        echo '<td><a href="' . APP_PATH_WEBROOT . 'ControlCenter/view_users.php?username='
                            . $username . '" target="_blank">' . $username . '</a>';
                        echo "<td>{$user->getFirstName()}</td>";
                        echo "<td>{$user->getLastName()}</td>";
                        echo "<td>{$user->getEmail()}</td>";
                        echo "<td>{$user->getEmail2()}</td>";
                        echo "<td>{$user->getEmail3()}</td>";
                        echo "<td>{$user->getLastLogin()}</td>";
                        echo "<td>{$user->getCreationTime()}</td>";
                        echo '<td style="text-align: right;">'
                            . '<button value="' . $user->getUsername() . '" class="userProjectsButton">'
                            . $user->getNumberOfProjects()
                            . '</button>'
                            . '</td>';
                        echo "<td>{$user->getSuspendedTime()}</td>";
                        echo "<td>{$user->getExpiration()}</td>";
                        echo "<td>{$displayOnEmailUsersLabel}</td>";
                        echo '<td class="ellipsis"'
                            . ' title="' . $user->getComments() . '"'
                            . '>'
                            . $user->getComments()
                            . '</td>';
                        echo "</tr>\n";
                    }
                    ?>
                </tbody>

            </table>
        </div>

        <script>
        $(document).ready(function() {

            $("#userTable").DataTable({
                "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]],
                "iDisplayLength": 10,
                // dom: 'Bfrtip',
                scrollX: true,
                initComplete: function () {
                    var api = this.api();
                    $('#userTableDiv').show();
                    api.columns.adjust();
                }
            });

            $("#userTable").show();

            var buttons = new $.fn.dataTable.Buttons($("#userTable"), {
                buttons: [
                    {
                        text: '<i class="fa fa-eye"></i> Show Query Conditions',
                        className: 'userTable',
                        action: function ( e, dt, node, config ) {
                            let jsonConditions = $("#jsonConditions").text();

                            jQuery.post("<?php echo $conditionsServiceUrl; ?>", {jsonConditions: jsonConditions},
                            function(data) {
                                $( '<div id="showConditions"><pre>' + data + '</pre></div>' ).dialog({
                                    title: "Query Conditions",
                                    resizable: false,
                                    height: "auto",
                                    width: 800,
                                    modal: false,
                                    buttons: {
                                        Close: function() {
                                            $( this ).dialog( "close" );
                                        }
                                    }
                                });
                            });
                        }
                    }
                ]
            }).container().appendTo($('#userTableButtons'));

            var buttons = new $.fn.dataTable.Buttons($("#userTable"), {
                buttons: [
                    {
                        extend: 'csv',
                        filename: 'redcap_users',
                        text: '<i class="fa fa-file-arrow-down"></i> CSV Download',
                        className: 'userTable',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            }).container().appendTo($('#userTableButtons'));

            var buttons2 = new $.fn.dataTable.Buttons($("#userTable"), {
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-table-columns"></i> Show/Hide Columns',
                        className: 'userTable'
                    }
                ]
            }).container().appendTo($('#colVis'));

            $('body').on("click", ".userProjectsButton", function() {
                let username = $( this ).val();
                $("#tableUserName").val(username);

                let jsonConditions = $("#jsonConditions").text();
                $("#tableJsonConditions").val(jsonConditions);

                $("#tableForm").submit();
                // alert(username);
            });
        });
        </script>
    <?php } // End if isset($users) ?>
</div>

<form style="display: none">
    <input type="hidden" id="emailList" name="emailList"></input>
</form>

<form id="tableForm" style="display: none;" target="_blank" action="<?php echo $userProjectsUrl;?>" method="post">
    <input type="hidden" id="tableJsonConditions" name="tableJsonConditions"/>
    <input type="hidden" id="tableQueryName" name="tableQueryName"
           value="<?php echo Filter::escapeForHtml($queryName); ?>"/>
    <input type="hidden" id="tableUserName" name="tableUserName"/>
    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>


<div id="jsonConditions" hidden>
<pre>
<?php echo Filter::escapeForHtml($jsonConditions) . "\n"; ?>
</pre>
</div>

<!-- START OF FOOTER -->

<?php $htmlPage->PrintFooterExt(); ?>
