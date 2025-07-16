//-------------------------------------------------------
// Copyright (C) 2023 The Trustees of Indiana University
// SPDX-License-Identifier: BSD-3-Clause
//-------------------------------------------------------


if (typeof AutoNotifyModule === 'undefined') {
    var AutoNotifyModule = {};
}


AutoNotifyModule.initializeMessageEditor = function() {
    tinymce.init({
        promotion: false,
        selector: '#message',
        branding: false,
        statusbar: true,
        elementpath: false,
        plugins: 'autolink lists link image searchreplace code fullscreen table directionality hr media',
        contextmenu: "copy paste | link image inserttable | cell row column deletetable",

        table_default_attributes: {
            border: '1'
        },
        //plugins: ['lists link image searchreplace code fullscreen table hr'],

        menubar: 'custom',
        toolbar1: 'blocks bold italic underline forecolor backcolor | link image | fullscreen',
        toolbar2: 'align table pre hr | bullist numlist outdent indent | searchreplace removeformat undo redo code',

        // Embedded image uploading
        file_picker_types: 'image',
        images_upload_handler: rich_text_image_upload_handler,

        menu: {
            custom: { title: 'Insert Variable', items: 'redcap user' }
        },
        setup: (editor) => {

            editor.ui.registry.addNestedMenuItem('redcap', {
                text: 'REDCap',
                getSubmenuItems: () => [
                    {
                        type: 'menuitem',
                        text: 'institution',
                        onAction: () => editor.insertContent(`[redcap_institution]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'URL',
                        onAction: () => editor.insertContent(`[redcap_url]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'version',
                        onAction: () => editor.insertContent(`[redcap_version]`)
                    }
                ]
            });

            editor.ui.registry.addNestedMenuItem('user', {
                text: 'user',
                getSubmenuItems: () => [
                    {
                        type: 'menuitem',
                        text: 'first name',
                        onAction: () => editor.insertContent(`[first_name]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'last name',
                        onAction: () => editor.insertContent(`[last_name]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'username',
                        onAction: () => editor.insertContent(`[username]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'e-mail',
                        onAction: () => editor.insertContent(`[email]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'last login',
                        onAction: () => editor.insertContent(`[last_login]`)
                    },
                    {
                        type: 'menuitem',
                        text: 'applicable project info',
                        onAction: () => editor.insertContent(`[applicable_project_info]`)
                    }
                ]
            });
        }
    });
}

