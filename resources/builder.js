//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------

if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}

$(function() {
    'use strict';

    $("#formSubmit").click(function () {
        alert('submit');
        $("#tableContents").val( $("#conditionList").text() );
        $("#buildeForm").submit();
    });

    /*
    $("#conditionList").on('mouseup', 'li', function () {
        alert('Condition list changed.');
    });
    */

    //$("#conditionList").sortable();
    ////$("#subList").sortable();
    //$("#conditionList").selectable();
    $("#conditionList").selectable({
      stop: function() {
        var result = $( "#select-result" ).empty();
        $( ".ui-selected", this ).each(function() {
          var index = $( "#conditionList li" ).index( this );
          result.append( " #" + ( index + 1 ) );
        });
      }
    });

    $("#addProjectId").click(function () {
        var tr = $(this).closest('tr');
        var operator = tr.find("#projectIdOperator").find(":selected").text();
        var value    = tr.find("#projectIdValue").val();
        //alert(operator + " \"" + value + "\"");
        //alert($("#conditionTable").text());
        var tds = '<tr><td> project_id ' + operator + ' ' + value + ' </td></tr>';
        $("#conditionTable > tbody").append(tds);
        var li = '<li class="ui-widget-content">' 
            + '<input type="checkbox"/>'
            + ' project_id ' + operator + ' ' + value + ' </li>';
        $("#conditionList").append(li);
        //alert($("#conditionTable").text());
        //$("#conditionTable").each(function () {
            //var tds = '<tr><td>project_id ' + operator + ' ' + value + '</td></tr>';
            //$('tbody', this).append(tds);
        //});
        return false;
    });

    $("#addAllAny").click(function () {
        var li = '<li class="ui-widget-content">' 
            + '<input type="checkbox"/>'
            + '<select>'
            + '<option>All:</option>'
            + '<option>Any:</option>'
            + '</select>'
            + '</li>';
        $("#conditionList").append(li);
        return false;
    });
});

