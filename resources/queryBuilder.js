//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------

if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}

// Data for variables used in query conditions, e.g., username.
AutoNotifyModule.variableData = [];

AutoNotifyModule.createQueryBuilder = function(containerDiv, variablesJson, queryJson = null) {
    this.variableData = jQuery.parseJSON( variablesJson );

    this.variableAutocompleteData = [];
    for (let i = 0; i < this.variableData.length; i++) {
        let varData = this.variableData[i];
    }

    queryJson = queryJson.trim();

    // alert('"' + queryJson + '"');
    this.container = containerDiv;
    if (queryJson === "") {
        this.container.html(AutoNotifyModule.getLogicalOp('ALL', false));
    }
    else {
        var queryObj = jQuery.parseJSON(queryJson);
        // ADD ERROR CHECKING !!!!!!!!!!!!!!!!!!!!
        operator   = queryObj.operator;
        conditions = queryObj.conditions;
        this.container.html(AutoNotifyModule.getLogicalOp(operator, false));
        var ul = this.container.find("ul:first");
        if (conditions != null) {
            AutoNotifyModule.createConditions(ul, conditions, 1);
        }
    }
    return false;
}

AutoNotifyModule.createConditions = function(ul, conditions, level) {
    for (let i = 0; i < conditions.length; i++) {
        let condition = conditions[i];
        let operator = condition.operator;
        if (operator === 'ALL' || operator === 'ANY' || operator === 'NOT ALL' || operator === 'NOT ANY') {
            let html = '<li>' + AutoNotifyModule.getLogicalOp(operator, true) + "</li>\n";
            ul.append(html); // append the logical operator
            let li = ul.find("li:last-child");
            let nestedUl = li.find("ul").first();
            let nestedConditions = condition.conditions;
            if (nestedConditions != null && nestedConditions.length > 0) {
                AutoNotifyModule.createConditions(nestedUl, nestedConditions, level + 1);
            }
        }
        else {
            let html = '<li class="condition">'
                + AutoNotifyModule.getCondition(condition.variable, condition.operator, condition.value);
                + "</li>\n";
            ul.append(html);
        }
    }
    return false;
}




AutoNotifyModule.deleteQuery = function (event) {
    var queryId = event.data.queryId;
    // alert("QUERY ID: " + queryId);
    $("#query-to-delete").text('"' + queryId + '"');
    $('#delete-query-id').val(queryId);
    $("#delete-query-form").data('queryId', queryId).dialog("open");
}

AutoNotifyModule.copyQuery = function (event) {
    var queryId = event.data.queryId;
    $("#query-to-copy").text('"' + queryId + '"');
    $('#copy-query-id').val(queryId);
    // alert("QUERY ID 2: " + queryId);
    $("#copy-query-form").data('queryId', queryId).dialog("open");
}

AutoNotifyModule.toJson = function() {
    var value = null;
    if (this.container != null) {
        var root = this.container.find("div").first();
        value = this.toJsonR(root);
    }
    return value;
}

AutoNotifyModule.toJsonR = function(node) {
    var string = '';
    var select = node.find("select").first();
    var selectClass = select.attr('class');

    if (selectClass === 'anmLogicalOperator') {
        ul = node.find("ul").first();
        operator = select.val();

        string += '{"operator": "' + operator + '", ' + '"conditions": [';

        var isFirst = true;
        ul.find("> li").each( function() {
            if (isFirst) {
                isFirst = false;
            }
            else {
                string += ", ";
            }
            string += AutoNotifyModule.toJsonR($(this));
        });

        string += "]}";
    }
    else {
        var select   = node.children().eq(0);
        var operator = select.next().next();    // Skip the variable search button
        var value    = operator.next();

        string += '{"variable": "' + select.val() + '"'
            + ', "operator": "' + operator.val() + '"'
            + ', "value": "' + value.val() + '"}';
    }
    return string;
}

AutoNotifyModule.getOptgroups = function() {
    // UNTESTED
    let optgroupsSet = new Set();
    for (let i = 0; i < this.variableData.length; i++) {
        let varData = this.variableData[i];
        optgroupsSet.add(varData.optgroup);
    }

    let optgroups = Array.from(optgroupsSet);

    return optgroups;
}

AutoNotifyModule.getLogicalOp = function(operator = 'ALL', addDelete = true) {
    var allSelected = '';
    var anySelected = '';
    var notAllSelected = '';
    var notAnySelected = '';

    if (operator === 'ANY') {
        anySelected = ' selected';
    }
    else if (operator === 'NOT_ALL') {
        notAllSelected = ' selected';
    }
    else if (operator === 'NOT_ANY') {
        notAnySelected = ' selected';
    }
    else {
        allSelected = ' selected';
    }

    var html = 
        '<div class="auto-notify-module">'
        + '<select class="anmLogicalOperator" style="font-weight: bold;">'
        + '<option value="ALL"' + allSelected + '>ALL:</option>'
        + '<option value="ANY"' + anySelected + '>ANY:</option>'
        + '<option value="NOT_ALL"' + notAllSelected + '>NOT ALL:</option>'
        + '<option value="NOT_ANY"' + notAnySelected + '>NOT ANY:</option>'
        + "</select>"
        + '<div style="margin: 0; padding: 0; float: right;">'
        + '<button class="anmAddCondition" title="Add condition"><i class="fa fa-circle-plus" style="color: green;"></i>'
        //+ 'Add Condition'
        + '</button>'
        + '<button style="margin-left: 1em; margin-right: 3em; color: #777777" class="anmAddNestedConditions" title="Add nested conditions">'
        + '<i class="fa fa-folder-plus"></i>'
        // + 'Add Nested Conditions'
        + '</button>'
    ;

    if (addDelete) {
        html +=  '<button class="anmDeleteOp">'
            + '<i class="fa fa-remove" style="color: red;"></i></button>'
    }
    else {
        html +=  '<button class="anm-query-builder-help">'
            + '<i class="fa fa-question-circle" style="color: blue;"></i></button>'
    }

    html += '</div>';

    html += '<span style="clear: both;"></span>';

    html +=
        '<ul class="anm-query-builder">'
        + '</ul>'
        + '</div>'
        ;
    return html;
}


AutoNotifyModule.getVariableData = function(variableName) {
    let varData = null;

    for (var i = 0; i < this.variableData.length; i++) {
        var data = this.variableData[i];
        if (variableName === data.name) {
            varData = data;
            break;
        }
    }

    return varData;
}


AutoNotifyModule.getCondition = function(variable = null, operator = null, value = null) {
    let variableIndex = 0;  // index of specified variable (0 by default)

    let html = '';

    //----------------------------------
    // Create variable select
    //----------------------------------
    html += '<select class="anmVariableSelect">' + "\n";
    let previousOptgroup = null;

    for (let i = 0; i < this.variableData.length; i++) {
        let data = this.variableData[i];

        if (previousOptgroup == null) {
            html += '<optgroup label="' + data.optgroup + '">';
        } 
        else if (data.optgroup != previousOptgroup) {
            html += '</optgroup>';
            html += '<optgroup label="' + data.optgroup + '">';
        } 

        if (variable === data.name) {
            variableIndex = i;
            html += '<option value="' + data.name + '" selected>' + data.label + '</option>' + "\n";
        }
        else {
            html += '<option value="' + data.name + '">' + data.label + '</option>' + "\n";
        }

        previousOptgroup = data.optgroup;
    }

    if (this.variableData.length >= 1) {
        html += '</optgroup>';
    }

    html += '</select>' + "\n";

    //-----------------------------------------------------
    // Create query variable search button
    //-----------------------------------------------------
    html += '<button class="anmVariableSearch" style="margin-right: 1em; background-color: green;">'
        + '<i class="fa fa-magnifying-glass" style="color: white;"></i>'
        + '</button>'


    let data = this.variableData[variableIndex];

    //-------------------------------------
    // Create operator select
    //-------------------------------------
    let operatorClass = data.operatorClass;
    if (operatorClass == null || typeof operatorClass === "undefined") {
        html += '<select class="anmOperatorSelect" style="margin-right: 1em;">' + "\n";
    }
    else {
        html += '<select class="anmOperatorSelect ' + operatorClass + '" style="margin-right: 1em;">' + "\n";
    }

    for (let i = 0; i < data.operators.length; i++) {
        let operatorValue = data.operators[i];
        let operatorLabel = data.operators[i].replace("<", "&lt;").replace(">", "&gt;");
        // alert("Operator: " + operator + ", Operator value:" + operatorValue);
        if (operatorValue == operator) {
            html += '<option value="' + operatorLabel + '" selected>' + operatorLabel + '</option>' + "\n";
            //html += '<option selected>' + operatorLabel + '</option>' + "\n";
        }
        else {
            html += '<option value="' + operatorLabel + '">' + operatorLabel + '</option>' + "\n";
            //html += '<option>' + operatorLabel + '</option>' + "\n";
        }
    }
    html += '</select>' + "\n";

    //--------------------------------------
    // Create value
    //--------------------------------------
    if (data.valueType === "inputText") {
        if (value == null) {
            html += '<input type="text"></input>';
        }
        else {
            html += '<input type="text" value="' + value + '"></input>';
        }
    }
    else if (data.valueType === "null") {
        html += '<input type="text" size="4" value="NULL" readonly></input>';
    }
    else if (data.valueType === "select") {
        html += '<select>' + "\n";
        for (var i = 0; i < data.selectValues.length; i++) {
            optionValue = data.selectValues[i][0];
            optionLabel = data.selectValues[i][1];
            if (value == optionValue) {
                html += '<option value="' + optionValue + '" selected>' + optionLabel + '</option>' + "\n";
            }
            else {
                html += '<option value="' + optionValue + '">' + optionLabel + '</option>' + "\n";
            }
            
        }
        html += '</select>' + "\n";
    }
    else if (data.valueType === "dateTimeNull") {
        let valueClass = '';
        if (operator == null || operator == "=" || operator == "<" || operator == "<="
                || operator == ">" || operator == ">=" || operator == "<>") {
            valueClass = ' class="anmTimeValue"';
        }

        if (value == null) {
            html += '<input type="text"' + valueClass + '></input>';
        }
        else {
            html += '<input type="text" value="' + value + '"' + valueClass + '></input>';
        }
    }

    html += 
        '<button style="float: right;" class="anmDeleteOp"><i class="fa fa-remove" style="color: red;"></i></button>'
        + '<button style="float: right; margin-right: 17px;" class="anmVariableHelp">'
        + '<i class="fa fa-question-circle" style="color: blue;"></i>'
        + '</button>'
        + '<span style="clear; both;"></span>'

    return html;
}


AutoNotifyModule.test = function() {
    for (var i = 0; i < this.variableData.length; i++) {
        var variable = this.variableData[i];
        // alert('<option value="' + variable.field + '">' + variable.name + '</option>');
    }
}


AutoNotifyModule.toFormattedJson = function() {
    var json = this.toJson();
    var obj = jQuery.parseJSON(json)
    var string = JSON.stringify(obj, null, 4);
    return string;
}

$(document).ready(function(){

    $('body').on('focus',".anmTimeValue", function(){
        $(this).datetimepicker();
    });

    // ADD CONDITION
    $("*").on("click", "button.anmAddCondition", function() {
        var ul = $(this).closest("div").parent("div").find("ul:first");
        ul.append(
            '<li class="condition">'
            + AutoNotifyModule.getCondition()
            + "</li>"
        );
        // $("select.anmVariableSelect").select2();
        return false;
    });

    // ADD NESTED CONDITIONS
    $("*").on("click", "button.anmAddNestedConditions", function() {
        var ul = $(this).closest("div").parent("div").find("ul:first");
        ul.append('<li>' + AutoNotifyModule.getLogicalOp() + "</li>");
        return false;
    });

    // CHANGE VARIABLE
    $("*").on("change", "select.anmVariableSelect", function() {
        var li = $(this).parent();  // Get the containing li element
        var selectValue = $(this).val();
        html = AutoNotifyModule.getCondition(selectValue);
        li.html(html);
        // AutoNotifyModule.processVariableSelect($(this));
        // $("select.anmVariableSelect").select2();
        return false;
    });

    //$("select.anmVariableSelect").select2();

    // SAVE TIME SELECT
    $("*").on("focusin", "select.anmTimeOpsSelect", function() {
        $(this).data('savedVal', $(this).val());
    });

    // CHANGE TIME VARIABLE OPERATION SELECT
    $("*").on("change", "select.anmTimeOpsSelect", function() {
        let previousVal = $(this).data("savedVal");

        // alert("PREVIOUS: " + previousVal + "     NEW VAL: " + $(this).val());

        // AutoNotifyModule.processVariableSelect($(this));
        var selectVal = $(this).val();

        var value = $(this).next();
        if (selectVal == 'is' || selectVal == 'is not') {
            // alert('selectVal: is/is not');
            //value.attr("class", "anmTimeNull");
            if (previousVal != 'is' && previousVal != 'is not') {
                value.attr("class", "");
                value.off('focus');
                value.attr("readonly", true);
                value.val('NULL');
            }
        }
        else if (selectVal.startsWith("age")) {
            if (!previousVal.startsWith("age")) {
                $(value).datepicker( "destroy");
                value.attr("class", "");
                value.off('focus');
                value.attr("readonly", false);
                value.val('');
            }
        }
        else {
            if (previousVal != "=" && previousVal != "<" && previousVal != "<="
                    && previousVal != ">" && previousVal != ">=" && previousVal != "<>") {
                value.attr("class", "anmTimeValue");
                value.on('focus', function(){
                    $(this).datetimepicker();
                });
                value.attr("readonly", false);
                value.val('');
            }
        }
        return false;
    });

    // DELETE LOGICAL OPERATOR (and its descendants)
    $("*").on("click", "button.anmDeleteOp", function() {
        var li = $(this).closest("li");
        li.remove();
        return false;
    });

    // VARIABLE SEARCH
    $("*").on("click", "button.anmVariableSearch", function() {
        var li = $(this).parent();  // Get the containing li element
        var varSelect = li.find('select:first');

        var variableName = varSelect.val();
        var variableLabel = varSelect.find('option:selected').text();

        let searchDialog = $(document.createElement('div'));

        let thStyle = 'border: 1px solid #426B48; color: #426B48; border-collapse: collapse; padding: 4px; background-color: #EEEEEE;';
        let tdStyle = 'border: 1px solid #426B48; border-collapse: collapse; padding: 4px;';

        let labelStyle = 'font-weight: bold; color: #426B48;';

        let contentHtml = '';
        contentHtml += '<div id="variableSearchDiv">';
        contentHtml += 
            '<fieldset style="background-color: white; border: 1px solid #462B48; border-radius: 5px; padding: 5px; margin-bottom: 14px;">'
            + '<span style="' + labelStyle + '">Search:</span>'
            + ' <input type="text" id="variableSearchText" style="margin-right: 1em;">'
            + ' <span style="' + labelStyle + '">Group:</span>'
            + ' <select id="groupSelect">'
            + '<option value="All">All</option>';

        let optgroups = AutoNotifyModule.getOptgroups();
        for (let i = 0; i < optgroups.length; i++) {
            optgroup = optgroups[i];
            contentHtml += '<option value="' + optgroup + '">' + optgroup + '</option>';
        }

        contentHtml +=
            '</select>'
            + ' <input type="checkbox" id="showDescriptionsCheckbox" style="margin-left: 2em;">'
            + ' <span style="' + labelStyle + '">Show Descriptions</span>'
            + ' <button style="float: right; margin-right: 17px;" class="anmVariableSearchHelp">'
            + ' <i class="fa fa-question-circle" style="color: blue;"></i></button>'
            + ' <span style="clear: both;"></span>'
            + '</fieldset>'
            ;

        contentHtml +=
            '<table id="searchTable" style="border: 1px solid #426B48; border-collapse: collapse; background-color: white;">'
            + '<thead>'
            + '<tr>'
            + '<th style="' + thStyle + ' width: 14em;">Variable</th>'
            + '<th style="' + thStyle + ' width: 7em;">Group</th>'
            + '<th style="' + thStyle + ' display: none;">Description</th>'
            + '</tr>'
            + '</thead>';
        contentHtml += '<tbody>';

        let variableData = AutoNotifyModule.variableData;

        for (let i = 0; i < variableData.length; i++) {
            let varData = variableData[i];
            contentHtml += '<tr display="">'
                + '<td style="' + tdStyle + '"><button style="width: 100%; text-align: left;">' + varData.label + '</button></td>'
                + '<td style="' + tdStyle + '">' + varData.optgroup + '</td>'
                + '<td style="' + tdStyle + ' display: none;">' + varData.help + '</td>'
                + '<td hidden>' + varData.name + '</td>'
                + '</tr>';
        }

        contentHtml += '</tbody></table>';
        contentHtml += '</div>';


        searchDialog.html(contentHtml);

        searchDialog.dialog({
            width: 940,
            maxHeight: 480,
            modal: true,
            buttons: {
                Cancel: function() {$(this).dialog("destroy").remove();},
            },
            title: 'Query Variable Search',
            dialogClass: 'variable-search-dialog',
            close: function( event, ui ) {
                $(this).dialog("destroy").remove();
            }
        })
        ;

        // Set variable insert events
        let trs = $("#searchTable tbody tr");
        for (i = 0; i < trs.length; i++) {
            let tr = trs[i];
            let tds = $("td", tr);
            let varTd = tds[0];
            let varName = $(tds[3]).text().trim();
            $(varTd).on("click", function() {
                //alert("Clicked " + varName + "!");
                varSelect.val(varName).trigger("change");
                searchDialog.dialog("destroy").remove();
                return false;
            });
        }

        // Show Descriptions change
        $("#showDescriptionsCheckbox").on("change", function() {
            let headerTr = $("#searchTable thead tr");
            let headerThs = $("th", headerTr);
            let descriptionTh = headerThs[2];

            let trs = $("#searchTable tbody tr");
            for (i = 0; i < trs.length; i++) {
                let tr = trs[i];
                let tds = $("td", tr);
                let descriptionTd = tds[2];
                if (this.checked) {
                    //alert('Checked!');
                    descriptionTh.style.display = '';
                    descriptionTd.style.display = '';
                }
                else {
                    //alert('Unchecked!');
                    descriptionTh.style.display = 'none';
                    descriptionTd.style.display = 'none';
                }
            }
        });

        // Set input event for search text
        $("#variableSearchText").on("input", function() {
            filterVariableSearch();
            return false;
        });

        $("#groupSelect").on("change", function() {
            filterVariableSearch();
            return false;
        });

        function filterVariableSearch() {
            let groupOption = $("#groupSelect").find(":selected").text();
            let variablePattern = $("#variableSearchText").val();

            let trs = $("#searchTable tbody tr");
            for (i = 0; i < trs.length; i++) {
                let tr = trs[i];
                let tds = $("td", tr);
                let variableName = $(tds[0]).text().trim();
                let groupName = $(tds[1]).text().trim();
                if ((groupOption === groupName || groupOption === 'All') &&
                    (variablePattern === '' || variableName.toLowerCase().indexOf(variablePattern.toLowerCase()) >= 0)
                ) {
                    tr.style.display = '';
                }
                else {
                    tr.style.display = 'none';
                }
            }
        }

        return false;
    });

    // VARIABLE HELP
    $("*").on("click", "button.anmVariableHelp", function() {
        var li = $(this).parent();  // Get the containing li element
        var varSelect = li.find('select:first');
        var variableName = varSelect.val();
        var variableLabel = varSelect.find('option:selected').text();

        var varData = AutoNotifyModule.getVariableData(variableName);

        let helpDialog = $(document.createElement('div'));
        helpDialog.html(varData.help);
        helpDialog.dialog({
            width: 620,
            modal: false,
            buttons: {
                Close: function() {$(this).dialog("close");},
            },
            title: 'Help for "' + variableLabel + '" condition variable',
            position: {
                my: "left top",
                at: "left bottom+7",
                of: li
            }
        })
        ;

        //let queryId = '';
        //$("#variable-help-form").data('queryId', queryId).dialog("open");
        // variableHelpForm.dialog();
        // var li = $(this).closest("li");
        // li.remove();
        return false;
    });

    // QUERY VARIABLE SEARCH HELP
    $("*").on("click", "button.anmVariableSearchHelp", function() {
        let helpDialog = $(document.createElement('div'));
        let helpHtml = 'Search for query variables using:'
            + '<ul>'
            + '<li>'
            + '<b>Search.</b>'
            + ' Enter text into the "Search" box to filter variables by their names. For example, if you'
            + ' enter "api" (without the quotes) into the search box, only variables that have "api" somewhere in their names'
            + ' will be displayed in the table. The filtering is case-insensitive, so variables with "API", "Api", etc. will'
            + ' also be displayed.'
            + '</li>'
            + '<li>'
            + '<b>Group.</b>'
            + ' You can select a group to limit the variables displayed in the table. For example, if you select "Project"'
            + ' for the group, then only query variables dependent on the project specified in a query (and not the user),'
            + ' such as "Project ID" and "Project Ttile", '
            + ' will be'
            + ' displayed.'
            + '</ul>'
            + '<p>'
            + 'Click the <b>Show Descriptions</b> checkbox to display variable descriptions in the table.'
            + '</p>'
            + '<p>'
            + '<b>Variable Selection.</b> Once you have found the variable you want, click on its name to set the'
            + ' condition variable selection in the query builder to that variable and return to the query builder.'
            + '</p>'
            ;
        helpDialog.html(helpHtml);
        helpDialog.dialog({
            width: 620,
            modal: false,
            buttons: {
                Close: function() {$(this).dialog("close");},
            },
            title: 'Query Variable Search Help'
        });

        return false;
    });

    // HELP
    $("*").on("click", "button.anmHelp", function() {
        return false;
    });

    // DELETE QUERY DIALOG
    var deleteForm = $("#delete-query-form").dialog({
        autoOpen: false,
        height: 160,
        width: 400,
        modal: true,
        buttons: {
            Cancel: function() {$(this).dialog("close");},
            "Delete query": function() {deleteForm.submit();}
        },
        title: "Delete query"
    });

    // COPY QUERY DIALOG
    var copyForm = $("#copy-query-form").dialog({
        autoOpen: false,
        height: 160,
        width: 400,
        modal: true,
        buttons: {
            Cancel: function() {$(this).dialog("close");},
            "Copy query": function() {copyForm.submit();}
        },
        title: "Copy query"
    });

});

