
/*
 * User Role Editor: support of 'Grant Roles' button for Users page (wp-admin/users.php)
 */

jQuery(function() {
    URE_Users_Grant_Roles.init();
});


var URE_Users_Grant_Roles = {

    init: function() {

        jQuery('#ure_grant_roles').on('click', function() {
            URE_Users_Grant_Roles.prepare_grant_roles_dialog();
        });
        jQuery('#ure_grant_roles_2').on('click', function() {
            URE_Users_Grant_Roles.prepare_grant_roles_dialog();
        });
        jQuery('#ure_add_role_button').on('click', function() {
            URE_Users_Grant_Roles.add_role( 1 );
        });
        jQuery('#ure_add_role_button_2').on('click', function() {
            URE_Users_Grant_Roles.add_role( 2 );
        });
        jQuery('#ure_revoke_role_button').on('click', function() {
            URE_Users_Grant_Roles.revoke_role( 1 );
        });
        jQuery('#ure_revoke_role_button_2').on('click', function() {
            URE_Users_Grant_Roles.revoke_role( 2 );
        });

        if (ure_users_grant_roles_data.show_wp_change_role!=1) {
            jQuery('#new_role').hide();
            jQuery('#new_role2').hide();
            jQuery('#changeit').hide();
            jQuery('[id=changeit]:eq(1)').hide();   // for 2nd 'Change' button with the same ID.
        }

    },
    // end of init()


    get_selected_checkboxes: function(item_name) {
        var items = jQuery('input[type="checkbox"][name="'+ item_name +'\\[\\]"]:checked').map(function() { return this.value; }).get();

        return items;
    },
    // end of get_selected_checkboxes()


    grant_roles: function() {

        var primary_role = jQuery('#primary_role').val();
        var other_roles = URE_Users_Grant_Roles.get_selected_checkboxes('ure_roles');
        jQuery('#ure_task_status').show();
        var users = URE_Users_Grant_Roles.get_selected_checkboxes('users');
        var data = {
            'action': 'ure_ajax',
            'sub_action':'grant_roles',
            'users': users,
            'primary_role': primary_role,
            'other_roles': other_roles,
            'wp_nonce': ure_users_grant_roles_data.wp_nonce
        };
        jQuery.post(ajaxurl, data, URE_Users_Grant_Roles.page_reload, 'json');

        return true;
    },
    // end of grant_roles()


    show_grant_roles_dialog: function( data ) {

        jQuery('#ure_grant_roles_dialog').dialog({
            dialogClass: 'wp-dialog',
            modal: true,
            autoOpen: true,
            closeOnEscape: true,
            width: 600,
            height: 400,
            resizable: false,
            title: ure_users_grant_roles_data.dialog_title,
            'buttons': {
                'OK': function () {
                    URE_Users_Grant_Roles.grant_roles();
                    jQuery(this).dialog('close');
                    return true;
                    },
                Cancel: function () {
                    jQuery(this).dialog('close');
                    return false;
                    }
                }
            });
        jQuery('#ure_grant_roles_container').html( data.html );

    },
    // end of show_grant_roles_dialog()


    get_user_roles_markup: function( user_id ) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'ure_ajax',
                sub_action: 'get_grant_roles',
                user_id : user_id,
                wp_nonce: ure_users_grant_roles_data.wp_nonce
            },
            success: function(response) {
                var data = jQuery.parseJSON( response );
                if (typeof data.result !== 'undefined') {
                    if (data.result === 'success') {
                        URE_Users_Grant_Roles.show_grant_roles_dialog( data );
                    } else if (data.result === 'failure') {
                        alert(data.message);
                    } else {
                        alert('Wrong response: ' + response)
                    }
                } else {
                    alert('Wrong response: ' + response)
                }
            },
            error: function(XMLHttpRequest, textStatus, exception) {
                alert("Ajax failure\n" + XMLHttpRequest.statusText);
            },
            async: true
        });
    },
    // end of get_user_roles_markup()


    prepare_grant_roles_dialog: function() {
        var users = URE_Users_Grant_Roles.get_selected_checkboxes('users');
        if ( users.length===0 ) {
            alert(ure_users_grant_roles_data.select_users_first);
            return;
        }
        var user_id = ( users.length===1) ? users[0] : 0;
        URE_Users_Grant_Roles.get_user_roles_markup( user_id );
    },
    // end of prepare_grant_roles_dialog()


    add_role: function( control_number ) {
        var users = URE_Users_Grant_Roles.get_selected_checkboxes('users');
        if ( users.length===0 ) {
            alert( ure_users_grant_roles_data.select_users_to_add_role );
            return;
        }

        var modifier = ( control_number===2 ) ? '_2' : '';
        var role = jQuery('#ure_add_role'+ modifier).val();
        if ( role.length===0 ) {
             alert( ure_users_grant_roles_data.select_role_first );
             return;
        }

        jQuery('#ure_task_status').show();
        var data = {
            'action': 'ure_ajax',
            'sub_action':'add_role_to_user',
            'users': users,
            'role': role,
            'wp_nonce': ure_users_grant_roles_data.wp_nonce
        };
        jQuery.post(ajaxurl, data, URE_Users_Grant_Roles.page_reload, 'json');

        return true;
    },
    // end of add_role()


    revoke_role: function( control_number ) {
        var users = URE_Users_Grant_Roles.get_selected_checkboxes('users');
        if ( users.length===0 ) {
            alert( ure_users_grant_roles_data.select_users_to_revoke_role );
            return;
        }

        var modifier = ( control_number===2 ) ? '_2' : '';
        var role = jQuery('#ure_revoke_role'+ modifier).val();
        if ( role.length===0 ) {
             alert( ure_users_grant_roles_data.select_role_first );
             return;
        }

        jQuery('#ure_task_status').show();
        var data = {
            'action': 'ure_ajax',
            'sub_action':'revoke_role_from_user',
            'users': users,
            'role': role,
            'wp_nonce': ure_users_grant_roles_data.wp_nonce
        };
        jQuery.post(ajaxurl, data, URE_Users_Grant_Roles.page_reload, 'json');

        return true;
    },
    // end of revoke_role()


    set_url_arg: function(arg_name, arg_value) {
        var url = window.location.href;
        var hash = location.hash;
        url = url.replace(hash, '');
        if (url.indexOf(arg_name + "=")>=0) {
            var prefix = url.substring(0, url.indexOf(arg_name));
            var suffix = url.substring(url.indexOf(arg_name));
            suffix = suffix.substring(suffix.indexOf("=") + 1);
            suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
            url = prefix + arg_name + "=" + arg_value + suffix;
        } else {
            if (url.indexOf("?") < 0) {
                url += "?" + arg_name + "=" + arg_value;
            } else {
                url += "&" + arg_name + "=" + arg_value;
            }
        }
        url = url + hash;

        return url;
    },
    // end of set_url_arg()


    page_reload: function(response) {

        if (response!==null && response.result=='error') {
            jQuery('#ure_task_status').hide();
            alert(response.message);
            return;
        }

        var url = URE_Users_Grant_Roles.set_url_arg('update', 'promote');
        document.location = url;

    }
    // end of page_reload()

};
