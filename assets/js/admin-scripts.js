var wp_spp_vars;
jQuery(document).ready(function ($) {

    if ( '0' !== wp_spp_vars.can_group )    {
        // Add confirmation when Publish is clicked and the post is part of an SPP group
        $('#publish').click( function (e)    {
            if( '0' !== $('#wp_spp_post_with_group').val() )    {
                var confirmPublish = confirm( wp_spp_vars.confirm_group_publish );

                if (confirmPublish === false) {
                    e.preventDefault();
                    return;
                }

            }
        });

        // Render the Sync group dropdown when edit is clicked
        $('.edit-group').click( function () {
            $('#post-sync-group-select').slideToggle( 'fast' );
            $('#post_sync_group').focus();
        });

        // Hide the Sync group drowndown when the cancel button is clicked
        $('.cancel-post-group').click( function () {
            $('#post-sync-group-select').hide( 'fast' );
        });

        // When a post group is selected
        $('.save-post-group').click( function () {
            var group = $('#post_sync_group').val();

            var postData = {
                group_id : group,
                post_id  : $('#post_ID').val(),
                action   : 'wp_spp_select_group_for_post'
            };

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: postData,
                url: ajaxurl,
                success: function (response) {
                    if (response.group_name)	{
                        $('#wp_spp_current_group').html(response.group_name);
                        $('#wp_spp_post_with_group').val(group);
                        $('#post-sync-group-select').hide('fast');
                    }
                }
            }).fail(function (data) {
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            });

        });
    }

});
