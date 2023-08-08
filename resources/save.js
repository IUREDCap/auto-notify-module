//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------

if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}

AutoNotifyModule.mytest.onReady = function () {
    alert('MY TEST");
}

AutoNotifyModule.initializeMessageEditor = function () {
    alert('TEST");
}

        /*
    tinymce.init({
        selector: '#message',
        branding: false,
        statusbar: true
        menu: {
            custom: { title: 'Insert Variable', items: 'User' }
        },
        setup: function(editor) {
            editor.ui.registry.addMenuItem('basicitem', {
                text: 'first name',
                onAction: function() {
                    alert('Menu item clicked');
                }
            });
        }
    });
}
        */


