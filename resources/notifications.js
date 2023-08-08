//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------

if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}

AutoNotifyModule.copyNotification = function (event) {
    var notification = event.data.notification;
    $("#notification-to-copy").text('"'+notification+'"');
    $('#copy-from-notification-name').val(notification);
    $("#copy-form").data('notification', notification).dialog("open");
}
    
AutoNotifyModule.deleteNotification = function (event) {
    var notificationId = event.data.notificationId;
    $("#notification-to-delete").text('"' + notificationId + '"');
    $('#delete-notification-id').val(notificationId);
    $("#delete-form").data('notificationId', notificationId).dialog("open");
}

$(function() {
    "use strict";

    // Copy notification dialog
    var copyForm = $("#copy-form").dialog({
        autoOpen: false,
        height: 200,
        width: 400,
        modal: true,
        buttons: {
            Cancel: function() {$(this).dialog("close");},
            "Copy notification": function() {copyForm.submit(); $(this).dialog("close");}
        },
        title: "Copy notification"
    });
    
    // Delete notification dialog
    var deleteForm = $("#delete-form").dialog({
        autoOpen: false,
        height: 220,
        width: 400,
        modal: true,
        buttons: {
            Cancel: function() {$(this).dialog("close");},
            "Delete notification": function() {deleteForm.submit();}
        },
        title: "Delete notification"
    });

});

