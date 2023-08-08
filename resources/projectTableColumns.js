//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------

if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}

AutoNotifyModule.variableData = [];

// columnsJson represents array of variable names to use as columns
AutoNotifyModule.createProjectTableColumns = function(containerDiv, variablesJson, columnsJson = null) {
    this.variableData = jQuery.parseJSON( variablesJson );

    columnsJson = columnsJson.trim();
    let columns = jQuery.parseJSON(columnsJson);

    this.columnsContainer = containerDiv;

    let html = '<div style="margin-bottom: 7px;">'
        + '<button class="anm-add-column" title="Add column">'
        + '<i class="fa fa-circle-plus" style="color: green;"></i>'
        + '</button>'
        + '</div>';
    ;

    html += '<ul class="anm-project-table-columns">' + "\n";
    for (i = 0; i < columns.length; i++) {
        html += this.createColumn(columns[i]);
        //html += '==='<li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'
        //html += '<li>' + '==='
        //    + "123" + "</li>\n";
        //    // + this.createColumn(columns[i]) + "</li>\n";
        // html += '<li>' + '<i class="fa fa-sort"></i>&nbsp;' + this.createColumn(columns[i]) + "</li>\n";
        // html += '<li class="ui-state-default">' + "=== " + this.createColumn(columns[i]) + "</li>\n";
    }
    html += "</ul>\n";

    this.columnsContainer.html(html);
    return false;
}

AutoNotifyModule.createColumn = function(column) {
    let html = '<li>' + '<i class="fa fa-sort"></i>&nbsp;'
    html += '<select class="anm-column-select" style="margin-right: 1em;">' + "\n";
    for (var i = 0; i < this.variableData.length; i++) {
        var data = this.variableData[i];
        if (column === data.name) {
            html += '<option value="' + data.name + '" selected>' + data.label + '</option>' + "\n";
        }
        else {
            html += '<option value="' + data.name + '">' + data.label + '</option>' + "\n";
        }
    }
    html += '</select>' + "\n";
    html += '<button class="anm-delete-column"><i class="fa fa-remove" style="color: red;"></i></button>';
    html += '</li>' + "\n";
    return html;
}

AutoNotifyModule.columnsToJson = function() {
    let value = '[';
    if (this.columnsContainer != null) {
        ul = this.columnsContainer.find("ul").first();

        let isFirst = true;
        ul.find("> li").each( function() {
            if (isFirst) {
                isFirst = false;
            }
            else {
                value += ", ";
            }

            let select   = $(this).find("select").first();

            value += '"' + select.val() + '"';
        });
    }
    value += ']';
    return value;
}


$(document).ready(function(){

    // ADD COLUMN
    $("*").on("click", "button.anm-add-column", function() {
        let ul = $(this).closest("div").parent("div").find("ul:first");
        ul.append( AutoNotifyModule.createColumn('project_id') );
        return false;
    });

    $("*").on("click", "button.anm-delete-column", function() {
        let li = $(this).closest("li");
        li.remove();
        return false;
    });

});

