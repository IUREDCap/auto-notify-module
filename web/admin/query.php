<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

/** @var \IU\AutoNotifyModule\AutoNotifyModule $module */

#---------------------------------------------
# Check that the user has access permission
#---------------------------------------------
$module->checkAdminPagePermission();


use ExternalModules\ExternalModules;
use IU\AutoNotifyModule\AutoNotifyModule;
use IU\AutoNotifyModule\Conditions;
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Help;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\Query;
use IU\AutoNotifyModule\Queries;
use IU\AutoNotifyModule\Variable;

try {
    $selfUrl  = $module->getUrl(AutoNotifyModule::QUERY_PAGE);
    $queryServiceUrl = $module->getUrl(AutoNotifyModule::QUERY_SERVICE);
    $conditionsServiceUrl = $module->getUrl(AutoNotifyModule::CONDITIONS_SERVICE);

    $usersUrl     = $module->getUrl(AutoNotifyModule::USERS_PAGE);
    $projectsUrl  = $module->getUrl(AutoNotifyModule::PROJECTS_PAGE);



    $externalModuleInfoMap = $module->getExternalModuleInfoMap();
    $variables = Variable::getVariablesFromJsonFile($externalModuleInfoMap);

    $users = null;

    #------------------------------------
    # Get the query ID (if any)
    #------------------------------------
    $queryId = Filter::sanitizeInt($_GET['queryId']);
    if (empty($queryId)) {
        $queryId = Filter::sanitizeInt($_POST['queryId']);
    }

    $queryName = Filter::sanitizeString($_POST['queryName']);
    $jsonConditions = $_POST['jsonConditions'];

    if (!array_key_exists("buttonValue", $_POST)) {
        if (!empty($queryId)) {
            $query = $module->getQuery($queryId);
            $conditions = $query->getConditions();
            if (empty($query)) {
                $queryId = null;
                $error = 'Query with ID "' . $queryId . '" not found.';
            } else {
                $queryName = $query->getName();
                $jsonConditions = $query->getConditions()->toJson();
            }
        }
    } elseif ($_POST['buttonValue'] === 'saveConditions') {
        # Save button
        if (empty($jsonConditions)) {
            $error = "No conditions specified for save,";
        } elseif (empty($queryName)) {
            $error = "No query name specified for save,";
        } else {
            $query = new Query();
            $query->setId($queryId);
            $query->setName($queryName);
            $query->setConditionsFromJson($jsonConditions);
            $query->validate($variables);
            $module->addOrUpdateQuery($query);
            $queryId = $query->getId(); // In case this is a new query, ID will now be set
            $success = "Query saved.";
        }
    } elseif ($_POST['buttonValue'] === 'viewUsers') {
        $jsonConditions = $_POST['jsonConditions'];
        $db = new RedCapDb($module);
        $users = ($db->getUsersFromJsonConditions($jsonConditions))->getUsers();
    }
} catch (\Exception $exception) {
    $error = 'ERROR: ' . $exception->getMessage();
}

?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
$cssFile = $module->getUrl('resources/notify.css');
$buttonsCssFile = $module->getUrl('resources/buttons.dataTables.css');
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">'
    . "\n"
    . '<link href="' . $buttonsCssFile . '" rel="stylesheet" type="text/css" media="all">'
    ;
#$jsInclude = '<script type="text/javascript" src="' . ($module->getUrl('resources/builder.js')) . '"></script>';
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
?>


<h4>
<i class="fas fa-envelope"></i>&nbsp;
Auto-Notify
</h4>


<div id="variablesJson" hidden>
<pre>
<?php
# Print Variables JSON for use by JavaScript
print Variable::variablesToJson($variables) . "\n";
?>
</pre>
</div>


<?php

$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);

?>


<h5>Query Builder</h5>

<form id="builderForm" style="margin-bottom: 7px; margin-top: 17px;"  action="<?php echo $selfUrl;?>" method="post">

    <p>
    <label for="queryName">Query Name:</label>
    <input type="text" name="queryName" id="queryName" value="<?php echo Filter::escapeForHtml($queryName); ?>"/>

    <?php
    if (empty($queryId)) {
        $queryIdString = 'NEW';
    } else {
        $queryIdString = $queryId;
    }
    ?>
    <span style="margin-left: 2em;">ID:</span>
    <input type=text" readonly="true" size="4" name="queryId"
           value="<?php echo Filter::escapeForHtml($queryIdString);?>" />
    </p>

    <p>Query Conditions:</p>
    <div id="queryBuilder" style="border: 1px solid black; border-radius: 10px; padding: 10px;">
    </div>

    <!--
    <div>
        <p>Project Table Columns:</p>
        <div id="projectTableColumns" style="border: 1px solid black; border-radius: 10px; padding: 10px;">
    </div>
    -->

    <p>&nbsp;</p>

    <!-- BUTTON DIV -->
    <div style="margin 17px 0px 17px 0px; border: 1px solid black; padding: 7px; background-color: #F8F8F8;">
        <button id="saveConditionsButton" name="saveConditionsButton" value="submitted">
            <i class="fa fa-save"></i> Save
        </button>

        &nbsp;&nbsp;

        <button id="showConditionsButton" name="showConditionsButton"><i class="fa fa-eye"></i> Show Conditions</button>

        &nbsp;&nbsp;

        <button id="showSqlQueryButton" name="showSqlQueryButton"><i class="fa fa-eye"></i> Show SQL Query</button>

        &nbsp;&nbsp;

        <button id="viewUsersButton" name="viewUsersButton"><i class="fa fa-users"></i> View Users</button>

        &nbsp;&nbsp;

        <button id="viewProjectsButton" name="viewProjectsButton"><i class="fa fa-list-alt"></i> View Projects</button>

        <!--
        &nbsp;&nbsp;

        <button id="viewUsers2Button" name="viewUsers2Button"><i class="fa fa-users"></i> View Users 2</button>
        -->
    </div>


    <input type="hidden" id="buttonValue" name="buttonValue"></input>

    <input type="hidden" id="jsonConditions" name="jsonConditions"></input>

    <div id="jsonConditionsDiv" hidden>
    <pre><?php echo Filter::escapeForHtml($jsonConditions); ?></pre>
    </div> 

    <?php
    echo '<input type="hidden" name="redcap_csrf_token" value="' . $module->getCsrfToken() . '"/>' . "\n";
    ?>

</form>


<form id="viewUsersForm" style="display: none;" target="_blank" action="<?php echo $usersUrl;?>" method="post">
    <input type="hidden" id="tableJsonConditions" name="tableJsonConditions"></input>
    <input type="hidden" id="tableQueryName" name="tableQueryName"></input>
    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>

<form id="viewProjectsForm" style="display: none;" target="_blank" action="<?php echo $projectsUrl;?>" method="post">
    <input type="hidden" id="viewProjectsJsonConditions" name="viewProjectsJsonConditions"></input>
    <input type="hidden" id="tableQueryName" name="tableQueryName"></input>
    <input type="hidden" name="redcap_csrf_token" value="<?php echo $module->getCsrfToken(); ?>"/>
</form>


<!--
<div id="columnsJson" style="margin: 4px; border: 1px solid black; padding: 4px;">
</div>
-->


<script>
$(document).ready(function() {
    // alert("JSON Conditioans: " + $("#jsonConditionsDiv").text());

    //AutoNotifyModule.test();
    // $("#jsonConditions").val();

    AutoNotifyModule.createQueryBuilder(
        $("#queryBuilder"),
        $("#variablesJson").text(),
        $("#jsonConditionsDiv").text()
    );

    /*
    AutoNotifyModule.createProjectTableColumns(
        $("#projectTableColumns"),
        $("#variablesJson").text(),
        '["project_id", "app_title"]'
        // $("#jsonConditionsDiv").text()
    );
    */

    var columnsJson = AutoNotifyModule.columnsToJson();
    $("#columnsJson").html("<pre>" + columnsJson + "</pre>");

    $("ul.anm-project-table-columns").sortable();


    $("#saveConditionsButton").click(function() {
        var query = AutoNotifyModule.toFormattedJson();
        $("#jsonConditions").val(query);
        $("#buttonValue").val('saveConditions');
    });

    $("#showConditionsButton").click(function(event) {
        var jsonConditions = AutoNotifyModule.toFormattedJson();

        var result = '';
        jQuery.post("<?php echo $conditionsServiceUrl?>", {jsonConditions: jsonConditions}, function(data) {

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

        event.preventDefault();
    });

    $("#showSqlQueryButton").click(function(event) {
        var jsonConditions = AutoNotifyModule.toFormattedJson();
        $("#buttonValue").val('showSqlQuery');

        var result = '';
        jQuery.post("<?php echo $queryServiceUrl?>", {jsonConditions: jsonConditions}, function(data) {

            $( '<div id="showSqlQuery"><pre>' + data + '</pre></div>' ).dialog({
                title: "SQL Query",
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

        event.preventDefault();
    });


    $("#viewUsersButton").on("click", function() {
        let queryName = $("#queryName").val();
        $("#tableQueryName").val(queryName);
        let jsonConditions = AutoNotifyModule.toFormattedJson();
        $("#tableJsonConditions").val(jsonConditions);
        $("#viewUsersForm").submit();
        return false;
    });

    $("#viewProjectsButton").on("click", function() {
        let queryName = $("#queryName").val();
        $("#tableQueryName").val(queryName);
        let jsonConditions = AutoNotifyModule.toFormattedJson();
        $("#viewProjectsJsonConditions").val(jsonConditions);
        $("#viewProjectsForm").submit();
        return false;
    });

    //---------------------------------------------
    //---------------------------------------------
    // Help dialog events
    //---------------------------------------------
    $('body').on("click", '.anm-query-builder-help', function () {
        $('#query-builder-help').dialog({dialogClass: 'auto-notify-help', width: 640, maxHeight: 440})
            .dialog('widget').position({my: 'left top', at: 'right+50 top-90', of: $(this)})
            ;
        return false;
    });

});
</script>

<!-- QUERY BUILDER HELP DIALOG -->
<div id="query-builder-help" title="Query Builder" style="display: none;">
    <?php echo Help::getHelpWithPageLink('query-builder', $module); ?>
</div>


<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
