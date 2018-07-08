var wp_spp_vars;
jQuery(document).ready(function ($) {

	// When the Publish all Group Posts button is clicked
	$('#spp-publish-posts').click( function(e)	{
		var confirmPublishAll = confirm( wp_spp_vars.confirm_publish_all );

		if (confirmPublishAll === false) {
			e.preventDefault();
			return;
		}
	});

    // Add a mailchimp campaign to the schedule
    $(document).on('click', '.add-campaign', function(e)   {
        e.preventDefault();

        var campaign_id = $(this).data('campaign'),
            group_id    = $('#post_ID').val();

        var postData = {
            campaign_id : campaign_id,
            group_id    : group_id,
            action      : 'wp_spp_schedule_mailchimp_campaign'
        };

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: postData,
            url: ajaxurl,
            beforeSend: function()	{
                $('#wp-spp-mc-lists').fadeTo(0, 0.4);
            },
            success: function (response) {
                if ( true === response.success )	{
                    if ( response.data.count >= 1 && $('#wp-spp-campaign-scheduled').length ) {
                        $('#wp-spp-campaign-scheduled').remove();
                    }

                    $('#wp-spp-table-scheduled tbody').append( response.data.row );
                    $('#ready-' + campaign_id).remove();
                }

                $('#wp-spp-mc-lists').fadeTo(0, 1);
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });
    });

    // Remove a mailchimp campaign from the schedule
    $(document).on('click', '.remove-campaign', function(e)   {
        e.preventDefault();

        var campaign_id = $(this).data('campaign'),
            group_id    = $('#post_ID').val();

        var postData = {
            campaign_id : campaign_id,
            group_id    : group_id,
            action      : 'wp_spp_unschedule_mailchimp_campaign'
        };

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: postData,
            url: ajaxurl,
            beforeSend: function()	{
                $('#wp-spp-mc-lists').fadeTo(0, 0.4);
            },
            success: function (response) {
                if ( true === response.success )	{
                    if ( $('#wp-spp-campaign-list').length ) {
                        $('#wp-spp-campaign-list').remove();
                    }

                    if ( response.data.count === 0 && ! $('wpp-spp-campaign-scheduled').length )    {
                        $('#wp-spp-table-scheduled tbody').append( response.data.default);
                    }

                    $('#wp-spp-table-campaigns tbody').append( response.data.row );
                    $('#scheduled-' + campaign_id).remove();
                }

                $('#wp-spp-mc-lists').fadeTo(0, 1);
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });
    });

    if ( '0' !== wp_spp_vars.can_group )    {
        // Add confirmation when Publish is clicked and the post is part of an SPP group
        $('#publish').click( function(e)    {
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
