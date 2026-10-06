/* User Role Editor: support of 'Without Roles' button for users.php */

jQuery(function() {
    URE_No_Role_Users.init();
});


var URE_No_Role_Users = {

    init: function() {

        jQuery('#move_from_no_role_content').append(ure_users_data.to +' <select id="ure_new_role" name="ure_new_role"></select>');
        var ure_new_role = jQuery('#ure_new_role');
        var options = jQuery("#new_role > option").clone();
        jQuery('#ure_new_role').empty().append(options);
        if (jQuery('#ure_new_role option[value="no_rights"]').length === 0) {
            jQuery('#ure_new_role').append('<option value="no_rights">' + ure_users_data.no_rights_caption + '</option>');
        }

        // Exclude change role to
        jQuery('#ure_new_role option[value=""]').remove();
        var new_role = jQuery('#new_role').find(":selected").val();
        if (new_role.length > 0) {
            ure_new_role.val(new_role);
        }
        ure_new_role.trigger('updated');

    },
    // end of init()


    move_dialog: function() {

        jQuery('#move_from_no_role_dialog').dialog({
            dialogClass: 'wp-dialog',
            modal: true,
            autoOpen: true,
            closeOnEscape: true,
            width: 400,
            height: 200,
            resizable: false,
            title: ure_users_data.move_from_no_role_title,
            'buttons': {
                'OK': function () {
                    URE_No_Role_Users.move_users();
                    jQuery(this).dialog('close');
                },
                Cancel: function () {
                    jQuery(this).dialog('close');
                    return false;
                }
            }
        });

    },
    // end of move_dialog()


    move_users: function() {
        var new_role = jQuery('#ure_new_role').find(":selected").val();
        if (new_role.length==0) {
            alert(ure_users_data.provide_new_role_caption);
            return;
        }
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'ure_ajax',
                sub_action: 'get_users_without_role',
                wp_nonce: ure_users_data.wp_nonce,
                new_role: new_role
            },
            success: function(response) {
                var data = jQuery.parseJSON(response);
                if (typeof data.result !== 'undefined') {
                    if (data.result === 'success') {
                        URE_No_Role_Users.post_move_command(data);
                    } else if (data.result==='error' || data.result==='failure') {
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
    // end of move_users()


    post_move_command: function(data) {
        var options = jQuery("#ure_new_role > option").clone();
        jQuery('#new_role').empty().append(options);
        jQuery("#new_role").val(data.new_role);
        var el = jQuery('.bulkactions').append();
        for(var i=0; i<data.users.length; i++) {
            if (jQuery('#user_'+ data.users[i]).length>0) {
                jQuery('#user_'+ data.users[i]).prop('checked', true);
            } else {
                var html = '<input type="checkbox" name="users[]" id="user_'+ data.users[i] +'" value="'+ data.users[i] +'" checked="checked" style="display: none;">';
                el.append(html);
            }
        }

        // submit form
        jQuery('#changeit').trigger('click');
    }
    // end of post_move_command()

};
