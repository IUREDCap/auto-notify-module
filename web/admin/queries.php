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
use IU\AutoNotifyModule\Filter;
use IU\AutoNotifyModule\Notification;
use IU\AutoNotifyModule\RedCapDb;
use IU\AutoNotifyModule\Schedule;

$selfUrl    = $module->getUrl(AutoNotifyModule::QUERIES_PAGE);
$builderUrl = $module->getUrl(AutoNotifyModule::QUERY_PAGE);

$queryConditionsServiceUrl = $module->getUrl(AutoNotifyModule::QUERY_CONDITIONS_SERVICE);

$cssFile = $module->getUrl('resources/notify.css');

# Check for notification deletion
if (array_key_exists('deleteQueryId', $_POST)) {
    $deleteQueryId = Filter::sanitizeInt($_POST['deleteQueryId']);
    $module->deleteQueryById($deleteQueryId);
}

if (array_key_exists('copyQueryId', $_POST)) {
    $copyQueryId = Filter::sanitizeInt($_POST['copyQueryId']);
    $module->copyQueryById($copyQueryId);
    $success = 'Query with ID ' . $copyQueryId . ' copied.';
}

$notifications = $module->getNotifications();


?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
$cssFile = $module->getUrl('resources/notify.css');
$link = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" media="all">';
$jsInclude = '<script type="text/javascript" src="' . ($module->getUrl('resources/queryBuilder.js')) . '"></script>';
$buffer = str_replace('</head>', "    {$link}\n{$jsInclude}\n</head>", $buffer);
echo $buffer;
?>



<h4>
<i class="fas fa-envelope"></i>&nbsp;
<!-- <img style="margin-right: 7px;" src="<?php echo APP_PATH_IMAGES ?>email.png" alt=""> -->
Auto-Notify
</h4>

<?php

$module->renderAdminPageContentHeader($builderUrl, $error, $warning, $success);
$module->renderAdminQuerySubTabs($selfUrl);


$users = null;

$queries = $module->getQueries();

$prefix = "auto-notify-module";
$config = ExternalModules::getConfig($prefix);

# DEBUGGING
# print "<pre>\n";
# print_r($_POST);
# print "</pre>\n";

#-------------------------
# Get the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = Filter::sanitizeButtonLabel($_POST['submitValue']);
}




?>

<h5>Queries</h5>

<div style="margin-bottom: 14px;">
<button onclick="window.location.href='<?php echo $builderUrl; ?>';">
    <i class="fa fa-circle-plus" style="color: green;"></i> Add Query
</button>
</div>


<table class="data-table">
    <tr>
        <th>ID</th> <th>Name</th> <th>Conditions</th> <th>Edit</th> <th>Copy</th> <th>Delete</th>
        <!--
        </th> <th>Copy</th> <th>Delete</th>
        -->
    </tr>
    <?php
    foreach ($queries->getQueries() as $query) {
        $id = $query->getId();
        $editUrl = $builderUrl . '&queryId=' . Filter::escapeForUrlParameter($id);

        ////$schedule = $notification->getSchedule();

        echo "<tr>\n";
        echo '<td style="text-align: right;">' . $id . "</td>\n";
        echo '<td>' . '<a href="' . $editUrl . '">' . $query->getName() . '</a>' . "</td>\n";
        # echo '<td style="text-align: left;"><pre>' . $query->getConditions() . "</pre></td>\n";
        echo '<td style="text-align: center;">'
            . '<button class="conditionsButton" value="' . $id . '" style="border: 0; background-color: white;">'
            . '<img src="' . APP_PATH_IMAGES . 'page_white_text.png" alt="VIEW">'
            . '</button></td>' . "\n";

        # Edit
        echo '<td style="text-align:center;">'
            . '<a href="' . $editUrl . '">'
            . '<img src="' . APP_PATH_IMAGES . 'page_white_edit.png" alt="EDIT"></a>'
            . "</td>\n";

        # Copy
        echo '<td style="text-align:center;">'
            . '<input type="image" src="' . APP_PATH_IMAGES . 'page_copy.png" alt="COPY"'
            . ' id="copyQuery' . $id . '"'
            . ' style="vertical-align: middle; cursor: pointer;"/>'
            . "</td>\n";

        # Delete
        echo '<td style="text-align:center;">'
            .  '<input type="image" src="' . APP_PATH_IMAGES . 'delete.png" alt="DELETE"'
            . ' id="deleteQuery' . $id . '"'
            . ' style="vertical-align: middle; cursor: pointer;"/>'
            . "</td>\n";

#        echo "</tr>\n";
    }
    ?>
</table>

<?php
#foreach ($queries->getQueries() as $query) {
#    print("<pre>");
#    print_r($query);
#    print("</pre>");
#}
?>

<?php
#--------------------------------------
# Copy query dialog
#--------------------------------------
?>
<div id="copy-dialog"
    title="Query Copy"
    style="display: none;"
    >
    <form id="copy-query-form" action="<?php echo $selfUrl;?>" method="post">
    To copy the query <span id="query-to-copy" style="font-weight: bold;"></span>,
    click on the <span style="font-weight: bold;">Copy query</span> button.
    <input type="hidden" name="copyQueryId" id="copy-query-id" value="">
    </form>
</div>

<?php
#--------------------------------------
# Delete query dialog
#--------------------------------------
?>
<div id="delete-dialog"
    title="Query Deleteion"
    style="display: none;"
    >
    <form id="delete-query-form" action="<?php echo $selfUrl;?>" method="post">
    To delete the query <span id="query-to-delete" style="font-weight: bold;"></span>,
    click on the <span style="font-weight: bold;">Delete query</span> button.
    <input type="hidden" name="deleteQueryId" id="delete-query-id" value="">
    <?php # Csrf::generateFormToken(); ?>
    </form>
</div>

<script>
    // Event handler script
    $( document ).ready(function() {

        $(".conditionsButton").on("click", function() {
            let id = $(this).attr('value');
            jQuery.post("<?php echo $queryConditionsServiceUrl; ?>", {queryId: id}, function(data) {
                $( '<div id="showConditions"><pre>' + data + '</pre></div>' ).dialog({
                    title: "Query Conditions",
                    resizable: false,
                    height: "auto",
                    width: 800,
                    // position: {my:"right top", at:"right-400 top+400", of: "body"},
                    modal: true,
                    buttons: {
                        Close: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            });
        });

        <?php

        #-----------------------------------------------------------------------
        # Set up click event handlers for the query copy/rename/delete buttons
        #-----------------------------------------------------------------------
        foreach ($queries->getQueries() as $query) {
            $id = $query->getId();
            echo '$("#copyQuery' . $id . '").on("click", {queryId: "' . $id . '"},'
               . ' AutoNotifyModule.copyQuery);' . "\n";
            echo '$("#deleteQuery' . $id . '").on("click", {queryId: "' . $id . '"},'
               . ' AutoNotifyModule.deleteQuery);' . "\n";
        }
        ?>
    });
</script>

<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
