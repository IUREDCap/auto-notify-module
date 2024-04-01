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

$username = '';
if (array_key_exists('tableUserName', $_POST)) {
    $username = Filter::sanitizeString($_POST['tableUserName']);
}

try {
    $db = $module->getDb();

    $userCondition = new Conditions();
    $userCondition->set('username', '=', $username);

    $conditions = new Conditions();
    $conditions->setFromJson($jsonConditions);
    $conditions->addSubCondition($userCondition);

    # Validate conditions
    $variables = $module->getVariables();
    $conditions->validate($variables);

    $jsonConditions = $conditions->toJson();

    $getProjectInfo = true;
    $queryResults = $db->getUsersFromJsonConditions($jsonConditions, $getProjectInfo);

    $users          = $queryResults->getUsers();
    $projectInfoMap = $queryResults->getProjectInfoMap();
} catch (\Exception $exception) {
    $error = 'Error: ' . $exception->getMessage();
}


# print "<pre>";
# print_r($conditions);
# print_r($users);
# print "</pre>";

?>

<div style="margin-top: 60px;">
<div>

<?php
# print "<pre>\n";
# print_r($_POST);
# print "</pre>\n";
?>

<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>

<?php
$module->renderAdminMessageHeader($error, $warning, $success);
?>


<div style="font-weight: bold; font-size: 120%; text-align: center;">
Projects for user "<?php echo Filter::escapeForHtml($username); ?>"
<?php
if (!empty($queryName)) {
    echo ' for query "' . Filter::escapeForHtml($queryName) . '"';
}
?>
</div>
<div id="resultsDisplay" style="margin-top: 17px; padding: 5px; border: 1px solid #777777;">
    <?php
    if (isset($users) && array_key_exists($username, $users)) {
        $user = $users[$username];
        $userRights = $user->getUserRights();
        ?>
        <div>
            <div id="colVis" style="float: left;"></div>
            <div id="userProjectsTableButtons" style="float: right; margin-bottom: 7px;">
            </div>
            <div style="clear: both;"></div>
        </div>

        <div id="userProjectsTableDiv" style="display: none">
            <table id="userProjectsTable" style="white-space: nowrap;">
                <thead>
                    <tr> 
                        <th colspan="8">Project Information</th>
                        <th colspan="6">User Project Rights</th>
                    </tr>
                    <tr> 
                        <th>Project ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Purpose</th>
                        <th>Surveys Enabled</th>
                        <th>Is Longitudinal</th>
                        <th>Creation Time</th>
                        <th>Completed Time</th>
                        <th>Deleted Time</th>

                        <th>Design Rights</th>
                        <th>User Rights</th>
                        <th>API Token</th>
                        <th>API Export</th>
                        <th>API Import</th>
                        <th>Mobile App</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ($userRights as $userProject) {
                        $projectId = $userProject->getProjectId();
                        $projectInfo = $projectInfoMap[$projectId];
                        $projectName = $projectInfo->getName();

                        $projectUrl =  APP_PATH_WEBROOT . 'index.php?pid=' . $projectId;

                        $projectStatus  = $projectInfo->getStatusLabel($variables);
                        $projectPurpose = $projectInfo->getPurposeLabel($variables);

                        $projectSurveysEnabled = $projectInfo->getSurveysEnabled();
                        $variable = $variables['surveys_enabled'];
                        $projectSurveysEnabledLabel = $variable->getSelectValueLabel($projectSurveysEnabled);
                        $projectSurveysEnabledLabel = ProjectInfo::convertTrueFalseToYesNo($projectSurveysEnabledLabel);

                        $projectIsLongitudinal = $projectInfo->getIsLongitudinal();
                        $variable = $variables['repeatforms'];
                        $projectIsLongitudinalLabel = $variable->getSelectValueLabel($projectIsLongitudinal);
                        $projectIsLongitudinalLabel = ProjectInfo::convertTrueFalseToYesNo($projectIsLongitudinalLabel);

                        $projectCreationTime = $projectInfo->getCreationTime();

                        $projectCompletedTime = $projectInfo->getCompletedTime();

                        $projectDeletedTime = $projectInfo->getDeletedTime();

                        $design = $userProject->getDesign();
                        $variable = $variables['design'];
                        $designLabel = $variable->getSelectValueLabel($design);
                        $designLabel = ProjectInfo::convertTrueFalseToYesNo($designLabel);

                        $hasUserRights = $userProject->getHasUserRights();
                        $variable = $variables['user_rights'];
                        $hasUserRightsLabel = $variable->getSelectValueLabel($hasUserRights);
                        $hasUserRightsLabel = ProjectInfo::convertTrueFalseToYesNo($hasUserRightsLabel);

                        $hasApiToken = $userProject->getHasApiToken();

                        $apiExport = $userProject->getApiExport();
                        $variable = $variables['api_export'];
                        $apiExportLabel = $variable->getSelectValueLabel($apiExport);
                        $apiExportLabel = ProjectInfo::convertTrueFalseToYesNo($apiExportLabel);

                        $apiImport = $userProject->getApiImport();
                        $variable = $variables['api_import'];
                        $apiImportLabel = $variable->getSelectValueLabel($apiImport);
                        $apiImportLabel = ProjectInfo::convertTrueFalseToYesNo($apiImportLabel);

                        $mobileApp = $userProject->getMobileApp();
                        $variable = $variables['mobile_app'];
                        $mobileAppLabel = $variable->getSelectValueLabel($mobileApp);
                        $mobileAppLabel = ProjectInfo::convertTrueFalseToYesNo($mobileAppLabel);

                        echo "<tr>";
                        echo "<td style=\"text-align: right;\">{$projectId}</td>";
                        echo '<td><a href="' . $projectUrl . '" target="_blank">' . $projectName . '</a></td>';
                        echo "<td>{$projectStatus}</td>";
                        echo "<td>{$projectPurpose}</td>";
                        echo "<td>{$projectSurveysEnabledLabel}</td>";
                        echo "<td>{$projectIsLongitudinalLabel}</td>";
                        echo "<td>{$projectCreationTime}</td>";
                        echo "<td>{$projectCompletedTime}</td>";
                        echo "<td>{$projectDeletedTime}</td>";
                        echo "<td>{$designLabel}</td>";
                        echo "<td>{$hasUserRightsLabel}</td>";
                        echo "<td>{$hasApiToken}</td>";
                        echo "<td>{$apiExportLabel}</td>";
                        echo "<td>{$apiImportLabel}</td>";
                        echo "<td>{$mobileAppLabel}</td>";
                        echo "</tr>\n";
                    }
                    ?>
                </tbody>

            </table>
        </div>

        <script>
        $(document).ready(function() {
            $("#userProjectsTable").DataTable({
                "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]],
                "iDisplayLength": 10,
                // dom: 'Bfrtip',
                scrollX: true,
                initComplete: function () {
                    var api = this.api();
                    $('#userProjectsTableDiv').show();
                    api.columns.adjust();
                }

            });

            var buttons = new $.fn.dataTable.Buttons($("#userProjectsTable"), {
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
            }).container().appendTo($('#userProjectsTableButtons'));

            var buttons = new $.fn.dataTable.Buttons($("#userProjectsTable"), {
                buttons: [
                    {
                        extend: 'csv',
                        filename: 'redcap_user_projects',
                        text: '<i class="fa fa-file-arrow-down"></i> CSV Download',
                        className: 'userTable',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            }).container().appendTo($('#userProjectsTableButtons'));

            var buttons2 = new $.fn.dataTable.Buttons($("#userProjectsTable"), {
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-table-columns"></i> Show/Hide Columns',
                        className: 'userTable'
                    }
                ]
            }).container().appendTo($('#colVis'));
        });
        </script>
    <?php } // End if isset($users) ?>
</div>

<form style="display: none">
    <input type="hidden" id="emailList" name="emailList"></input>
</form>

<div id="jsonConditions" hidden>
<pre>
<?php echo Filter::escapeForHtml($jsonConditions) . "\n"; ?>
</pre>
</div>

<!-- START OF FOOTER -->

<?php $htmlPage->PrintFooterExt(); ?>
