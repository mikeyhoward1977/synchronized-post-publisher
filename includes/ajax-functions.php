<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     WP_SPP
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process group selection for a post.
 *
 * @since	1.0
 * @return	void
 */
function wp_spp_ajax_select_group_for_post()	{

    $post_id  = absint( $_POST['post_id'] );
    $group_id = absint( $_POST['group_id'] );

    if ( '0' == $group_id ) {
        wp_spp_remove_post_from_sync_group( $post_id );
        $group_name = __( 'None', 'synchronized-post-publisher' );
    } elseif ( wp_spp_add_post_to_sync_group( $post_id, $group_id ) ) {
        $group_name = get_the_title( $group_id );
    } else  {
        $group_name = __( 'None', 'synchronized-post-publisher' );
    }

	wp_send_json( array( 'group_name' => $group_name ) );

} // wp_spp_ajax_select_group_for_post
add_action( 'wp_ajax_wp_spp_select_group_for_post', 'wp_spp_ajax_select_group_for_post' );

/**
 * Schedule a MailChimp campaign.
 *
 * @since   1.2
 * @return  void
 */
function wp_spp_ajax_schedule_mailchimp_campaign()  {
    $campaign_id = $_POST['campaign_id'];
    $group_id    = $_POST['group_id'];

    if ( wp_spp_add_mc_campaign_to_group( $group_id, $campaign_id ) )   {
        $campaign_data = wp_spp_get_mc_campaign( $campaign_id );
        if ( $campaign_data )   {
            ob_start();

            $campaign_url = 'https://admin.mailchimp.com/campaigns/show/?id=' . $campaign_data['web_id'];
            $list_url     = 'https://admin.mailchimp.com/lists/members/?id=' . $campaign_data['recipients']['list_id'];
            ?>
            <tr id="scheduled-<?php echo $campaign_id; ?>">
                <td style="text-align: center;">
                    <a href="#" class="remove-campaign button button-delete submitdelete" data-campaign="<?php echo $campaign_data['id']; ?>">
                        <?php _e( 'Remove', 'synchronized-post-publisher' ); ?>
                    </a></td>
                <td>
                    <a href="<?php echo $campaign_url; ?>" target="_blank">
                        <?php echo esc_attr( $campaign_data['settings']['title'] ); ?>
                    </a>
                </td>
                <td><?php echo esc_attr( $campaign_data['settings']['subject_line'] ); ?></td>
                <td>
                    <a href="<?php echo $list_url; ?>" target="_blank">
                        <?php echo esc_attr( $campaign_data['recipients']['list_name'] ); ?>
                    </a>
                </td>
                <td style="text-align: center;"><?php echo esc_attr( $campaign_data['recipients']['recipient_count'] ); ?></td>
            </tr>
        <?php
            $row   = ob_get_clean();
            $count = count( wp_spp_get_mc_scheduled_campaigns( $group_id ) );

            wp_send_json_success( array(
                'row'   => $row,
                'count' => $count
            ) );
        }
    } else  {
        wp_send_json_error();
    }
} // wp_spp_ajax_schedule_mailchimp_campaign
add_action( 'wp_ajax_wp_spp_schedule_mailchimp_campaign', 'wp_spp_ajax_schedule_mailchimp_campaign' );

/**
 * Remove a MailChimp campaign from the schedule.
 *
 * @since   1.2
 * @return  void
 */
function wp_spp_ajax_unschedule_mailchimp_campaign()  {
    $campaign_id = $_POST['campaign_id'];
    $group_id    = $_POST['group_id'];

    $campaign_data = wp_spp_get_mc_campaign( $campaign_id );
    if ( $campaign_data && wp_spp_remove_mc_campaign_from_group( $group_id, $campaign_id ) )   {
        ob_start();

        $campaign_url = 'https://admin.mailchimp.com/campaigns/show/?id=' . $campaign_data['web_id'];
        $list_url     = 'https://admin.mailchimp.com/lists/members/?id=' . $campaign_data['recipients']['list_id'];
        ?>
        <tr id="ready-<?php echo $campaign_id; ?>">
            <td style="text-align: center;">
                <a href="#" class="add-campaign button" data-campaign="<?php echo $campaign_data['id']; ?>">
                    <?php _e( 'Add', 'synchronized-post-publisher' ); ?>
                </a></td>
            <td>
                <a href="<?php echo $campaign_url; ?>" target="_blank">
                    <?php echo esc_attr( $campaign_data['settings']['title'] ); ?>
                </a>
            </td>
            <td><?php echo esc_attr( $campaign_data['settings']['subject_line'] ); ?></td>
            <td>
                <a href="<?php echo $list_url; ?>" target="_blank">
                    <?php echo esc_attr( $campaign_data['recipients']['list_name'] ); ?>
                </a>
            </td>
            <td style="text-align: center;"><?php echo esc_attr( $campaign_data['recipients']['recipient_count'] ); ?></td>
        </tr>
        <?php
        $row   = ob_get_clean();
        $count = count( wp_spp_get_mc_scheduled_campaigns( $group_id ) );

        ob_start(); ?>
        <tr id="wp-spp-campaign-scheduled">
            <td colspan="5"><?php _e( 'No campaigns will be sent when this group is published.', 'synchronized-post-publisher' ); ?></td>
        </tr>
        <?php $default = ob_get_clean();

        wp_send_json_success( array(
            'row'     => $row,
            'count'   => $count,
            'default' => $default
        ) );
    } else  {
        wp_send_json_error();
    }
} // wp_spp_ajax_unschedule_mailchimp_campaign
add_action( 'wp_ajax_wp_spp_unschedule_mailchimp_campaign', 'wp_spp_ajax_unschedule_mailchimp_campaign' );

/**
 * Dismiss admin notices.
 *
 * @since	1.1
 * @return	void
 */
function wp_spp_ajax_dismiss_admin_notice()	{

	$notice = sanitize_text_field( $_POST['notice'] );
    wp_spp_dismiss_notice( $notice );

	wp_send_json_success();

} // wp_spp_ajax_dismiss_admin_notice
add_action( 'wp_ajax_wp_spp_dismiss_notice', 'wp_spp_ajax_dismiss_admin_notice' );
