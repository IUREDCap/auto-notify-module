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
        var operator = select.next();
        var value    = operator.next();

        string += '{"variable": "' + select.val() + '"'
            + ', "operator": "' + operator.val() + '"'
            + ', "value": "' + value.val() + '"}';
    }
    return string;
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

    //----------------------------------------
    // Create autocomplete field
    //----------------------------------------
    //var autocompleteData = [];
    //for (let i = 0; i < this.variableData.length; i++) {
    //    let data = this.variableData[i];
    //}
    //html += '<input class="anmVariableAutocomplete" size="7">' + "\n";

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

    html += '<button class="anmVariableSearch" style="margin-right: 1em;">'
        + '<i class="fa fa-magnifying-glass" style="color: gray;"></i>'
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

    // CHANGE TIME SELECT
    $("*").on("change", "select.anmTimeOpsSelect", function() {
        let previousVal = $(this).data("savedVal");

        // alert("PREVIOUS: " + previousVal + "     NEW VAL: " + $(this).val());

        // AutoNotifyModule.processVariableSelect($(this));
        var selectVal = $(this).val();
        // alert("TIMES OP CHANGE: '" + selectVal + "'");
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

        let thStyle = '"border: 1px solid black; border-collapse: collapse; padding: 4px; background-color: #EEEEEE;"';
        let tdStyle = '"border: 1px solid black; border-collapse: collapse; padding: 4px;"';

        let contentHtml = '<p>Search: <input type="text" id="variableSearchText"></p>';
        contentHtml += '<table id="searchTable" style="border: 1px solid black; border-collapse: collapse;">'
            + '<thead>'
            + '<tr>'
            + '<th style=' + thStyle + '>Variable</th>'
            + '<th style=' + thStyle + '>Group</th>'
            + '<th style=' + thStyle + '>Description</th>'
            + '</tr>'
            + '</thead>';
        contentHtml += '<tbody>';

        let variableData = AutoNotifyModule.variableData;

        for (let i = 0; i < variableData.length; i++) {
            let varData = variableData[i];
            contentHtml += '<tr display="">'
                + '<td style=' + tdStyle + '><button>' + varData.label + '</button></td>'
                + '<td style=' + tdStyle + '>' + varData.optgroup + '</td>'
                + '<td style=' + tdStyle + '>' + varData.help + '</td>'
                + '<td hidden>' + varData.name + '</td>'
                + '</tr>';
        }

        contentHtml += '</tbody></table>';


        searchDialog.html(contentHtml);

        searchDialog.dialog({
            width: 840,
            maxHeight: 480,
            modal: true,
            buttons: {
                Cancel: function() {$(this).dialog("destroy").remove();},
            },
            title: 'Query Variable Search',
            //position: {
            //    my: "left top",
            //    at: "left bottom+7",
            //    of: li
            //}
        })
        ;

        // Initialize all rows to being visible
        //let trs = $("#searchTable tbody tr");
        //for (i = 0; i < trs.length; i++) {
            //tr.style.display = '';
        //}

        // Set variable insert events
        let trs = $("#searchTable tbody tr");
        for (i = 0; i < trs.length; i++) {
            let tr = trs[i];
            let tds = $("td", tr);
            let varTd = tds[0];
            let varName = $(tds[3]).text().trim();
            $(varTd).on("click", function() {
                //alert("Clicked " + varName + "!");
                varSelect.val(varName);
                searchDialog.dialog("destroy").remove();
                return false;
            });
        }

        // Set input event for search text
        $("#variableSearchText").on("input", function() {
        // $("*").on("input", "#variableSearchText", function() {
            let pattern = $("#variableSearchText").val();

            let trs = $("#searchTable tbody tr");

            for (i = 0; i < trs.length; i++) {
                let tr = trs[i];
                let tds = $("td", tr);
                let varName = $(tds[0]).text().trim();
                if (varName.indexOf(pattern) >= 0) {
                    tr.style.display = '';
                }
                else {
                    tr.style.display = 'none';
                }
            }

            return false;
        });

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

