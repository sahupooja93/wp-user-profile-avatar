var AvatarAdmin = function () {

    return {

	    init: function() 
        {
            jQuery( '#wp-user-profile-avatar-add' ).on('click', AvatarAdmin.actions.chooseAvatar);
            jQuery( '#wp-user-profile-avatar-remove' ).on('click', AvatarAdmin.actions.removeAvatar);
            jQuery( '#wp-user-profile-avatar-undo' ).on('click', AvatarAdmin.actions.undoAvatar);

            jQuery( 'body' ).on('click', '#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap .nav-tab-wrapper a', AvatarAdmin.actions.showShortcodeAvatarTab);

            jQuery( 'body' ).on('change', '#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_link_to', AvatarAdmin.actions.imageLinkTo);

            jQuery( 'body' ).on('click', '#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #user_avatar_form_btn', AvatarAdmin.actions.addUserAvatarShortcode);

            jQuery( 'body' ).on('click', '#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-upload_avatar #upload_avatar_form_btn', AvatarAdmin.actions.addUploadAvatarShortcode);

            if(typeof(tinyMCE) != "undefined")
            {
                tinymce.PluginManager.add('wp_user_profile_avatar_shortcodes', function( editor, url ) {
                    editor.addButton('wp_user_profile_avatar_shortcodes', {
                                title: 'Insert WP User Avatar',
                                classes: 'thickbox',
                                image: wp_user_profile_avatar_admin_avatar.default_avatar,
                                icon: false,
                                onclick: function() {
                                    
                                }
                     });
                });

                setTimeout(function(){ 
                    jQuery('.mce-thickbox button').remove();
                    jQuery('.mce-thickbox').html('<a href="' + wp_user_profile_avatar_admin_avatar.thinkbox_ajax_url + '" class="thickbox mce-toolbar" title="' + wp_user_profile_avatar_admin_avatar.thinkbox_title + '"><img class="mce-ico" src="' + wp_user_profile_avatar_admin_avatar.default_avatar + '"></a>');
                }, 1000);
            }            

        },

	    actions:
	    {
            showShortcodeAvatarTab: function (event) 
            {
                jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap .admin-setting-left .settings_panel').hide();

                var id = jQuery(event.target).attr('href'); 

                jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap .admin-setting-left ' + id).show();
            },

            imageLinkTo: function (event) 
            {
                var link_to = jQuery(event.target).val(); 

                if(link_to == 'custom')
                {
                    jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_custom_link_to').attr('type','text');
                }
                else
                {
                    jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_custom_link_to').attr('type','hidden');  
                }
            },

            addUserAvatarShortcode: function (event) 
            {
                var user_id = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_user_id').val();
                
                var size = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_size').val();

                var align = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_alignment').val();
                
                var link = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_link_to').val();
                
                var custom_link = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_custom_link_to').val();
                
                var target = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_open_new_window').val();
                
                var caption = jQuery('body').find('#TB_window #TB_ajaxContent .wp-user-profile-avatar-shortcode-wrap #settings-user_avatar #wp_image_caption').val();

                var user_tag = (user_id != "") ? ' user_id="' + user_id + '"' : "";

                var size_tag = (size != "") ? ' size="' + size + '"' : "";

                var align_tag = (align != "") ? ' align="' + align + '"' : "";

                var link_tag = (link != "" && link != 'custom' && custom_link == "") ? ' link="' + link + '"' : "";
                link_tag = (custom_link != "") ? ' link="' + custom_link + '"' : link_tag;

                var target_tag = (target != "") ? ' target="' + target + '"' : "";

                var shortcode = "<p>[user_profile_avatar" + user_tag + size_tag + align_tag + link_tag + target_tag + "] " + caption + " [/user_profile_avatar]</p>";

                tinymce.activeEditor.insertContent(shortcode);
                //editor.insertContent('[wdm_shortcode]');

                jQuery('body').find('#TB_window #TB_title #TB_closeAjaxWindow #TB_closeWindowButton').trigger("click");
            },

            addUploadAvatarShortcode: function (event) 
            {
                var shortcode = "<p>[user_profile_avatar_upload]</p>";

                tinymce.activeEditor.insertContent(shortcode);
                //editor.insertContent('[wdm_shortcode]');

                jQuery('body').find('#TB_window #TB_title #TB_closeAjaxWindow #TB_closeWindowButton').trigger("click");
            },

            chooseAvatar: function (event) 
	    	{
                var upload = wp.media({
                    library: {
                        type: 'image'
                    },
                    title: wp_user_profile_avatar_admin_avatar.media_box_title, /*Title for Media Box*/
                    multiple: false /*For limiting multiple image*/
                })
                .on('select', function ()
                {
                    var select = upload.state().get('selection');
                    var attach = select.first().toJSON();

                    jQuery('#wp-user-profile-avatar-preview img').attr('src', attach.url);
                    jQuery('#wp-user-profile-avatar-thumbnail img').attr('src', attach.url);
                    jQuery('#wp_user_profile_avatar_attachment_id').attr('value', attach.id);
                    jQuery('#wp_user_profile_avatar_radio').trigger('click');
                    jQuery('#wp-user-profile-avatar-undo-button').show();
                })
                .open();
	        },

            removeAvatar: function (event) 
            {
                jQuery('#wp-user-profile-avatar-preview img').attr('src', wp_user_profile_avatar_admin_avatar.default_avatar);
                jQuery('#wp-user-profile-avatar-thumbnail img').attr('src', wp_user_profile_avatar_admin_avatar.default_avatar);
                jQuery('#wp_user_profile_avatar_attachment_id').attr('value', '');
                jQuery('#wp_user_profile_avatar_url').attr('value', '');

                jQuery('#wp-user-profile-avatar-remove').hide();
            },

            undoAvatar: function (event) 
            {
                jQuery('#wp-user-profile-avatar-preview img').attr('src', wp_user_profile_avatar_admin_avatar.default_avatar);
                jQuery('#wp-user-profile-avatar-thumbnail img').attr('src', wp_user_profile_avatar_admin_avatar.default_avatar);
                jQuery('#wp_user_profile_avatar_attachment_id').attr('value', '');

                jQuery('#wp-user-profile-avatar-undo-button').hide();
            },
		
		} /* end of action */

    }; /* enf of return */

}; /* end of class */

AvatarAdmin = AvatarAdmin();

jQuery(document).ready(function($) 
{
   AvatarAdmin.init();
});
