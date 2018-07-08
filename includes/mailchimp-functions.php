<?php
/**
 * MailChimp Functions
 *
 * Many of these are wrapper functions for the MailChimp class.
 *
 * @package     WP_SPP
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

require_once WP_SPP_PLUGIN_DIR . 'lib/MailChimp.php';
use WP_SPP\WP_SPP_MailChimp\WP_SPP_MailChimp;

/**
 * Retrieve the MailChimp API key.
 *
 * @since	1.2
 * @return	string|bool	The MailChimp API as saved in settings or false
 */
function wp_spp_mc_get_api()	{
	return get_option( 'wp_spp_mc_api_key' );
} // wp_spp_mc_get_api

/**
 * Retrieve the MailChimp Timeout.
 *
 * @since	1.2
 * @return	int		The MailChimp timeout
 */
function wp_spp_mc_get_timeout()	{
	return get_option( 'wp_spp_mc_timeout', 15 );
} // wp_spp_mc_get_timeout

/**
 * Instantiate the class.
 *
 * @since	1.2
 * @return	object		The MailChimp API Class Object.
 */
function wp_spp_mc_connect()	{
	$api_key = wp_spp_mc_get_api();

	if ( ! $api_key )	{
		return false;
	}

	try	{
		$MailChimp = new WP_SPP_MailChimp( $api_key );
	} catch( Exception $e )	{
		return false;
	}

	return $MailChimp;
} // wp_spp_mc_connect

/**
 * Whether or not we have a connection to MailChimp.
 *
 * @since	1.2
 * @return	bool	True if connected, otherwise false.
 */
function wp_spp_mc_is_connected()	{
	$connected = false;

	if ( wp_spp_mc_connect() )	{
		$connected = true;
	}

	return $connected;
} // wp_spp_mc_is_connected

/**
 * Add a MailChimp campaign to the SPP group.
 *
 * @since   1.2
 * @param   int         $group_id       The SPP group ID
 * @param   string      $campaign_id    Campaign ID
 * @return  bool|int    Meta ID or false on failure
 */
function wp_spp_add_mc_campaign_to_group( $group_id, $campaign_id ) {
    return add_post_meta( $group_id, '_wp_spp_mc_campaign', $campaign_id );
} // wp_spp_add_mc_campaign_to_group

/**
 * Remove a MailChimp campaign from the SPP group.
 *
 * @since   1.2
 * @param   int         $group_id       The SPP group ID
 * @param   string      $campaign_id    Campaign ID
 * @return  bool|int    Meta ID or false on failure
 */
function wp_spp_remove_mc_campaign_from_group( $group_id, $campaign_id ) {
    return delete_post_meta( $group_id, '_wp_spp_mc_campaign', $campaign_id );
} // wp_spp_remove_mc_campaign_from_group

/**
 * Retrieve campaigns that are scheduled to be sent when the SPP group is published.
 *
 * @since	1.2
 * @param	int      $group_id	The SPP group ID
 * @return	array    Array of campaigns scheduled with group
 */
function wp_spp_get_mc_scheduled_campaigns( $group_id )	{
	$scheduled = get_post_meta( $group_id, '_wp_spp_mc_campaign' );

    if ( empty( $scheduled ) )  {
        $scheduled = array();
    }

    return $scheduled;
} // wp_spp_get_mc_scheduled_campaigns

/**
 * Retrieve the count of campaigns scheduled to be sent when the group is publishd.
 *
 * @since	1.2
 * @param	int		$group_id	SPP group ID
 * @return	int		Count of campaigns
 */
function wp_spp_count_mc_scheduled_campaigns_in_group( $group_id )	{
	return (int) count( wp_spp_get_mc_scheduled_campaigns( $group_id ) );
} // wp_spp_count_mc_scheduled_campaigns_in_group

/**
 * Retrieve a single campaign from MailChimp.
 *
 * @since	1.2
 * @param	string		$campaign_id	The MailChimp campaign ID
 * @param	bool|object	$mailchimp		WP_SPP_MailChimp class object or false
 * @return	array	Array of campaign data
 */
function wp_spp_get_mc_campaign( $campaign_id, $mailchimp = false )	{
	if ( ! $mailchimp )	{
		$mailchimp = wp_spp_mc_connect();
	}

    if ( $mailchimp )   {
		$cache_key = 'wp_spp_get_campaign_' . $campaign_id;
		$campaign  = get_transient( $cache_key );
		$force     = false;

		if ( isset( $_GET['force-campaign-refresh'] ) )	{
			$force = true;
		}

		$force = apply_filters( 'wp_spp_force_campaign_refresh', $force );

		if ( false === $campaign || $force )	{
			$campaign = $mailchimp->get(
				"/campaigns/$campaign_id",
				array(),
				wp_spp_mc_get_timeout()
			);
			set_transient( $cache_key, $campaign, DAY_IN_SECONDS / 2 );
		}

        if ( ! empty( $campaign ) )	{
            return $campaign;
        }
    }

	return array();
} // wp_spp_get_mc_campaign

/**
 * Retrieve campaigns from MailChimp.
 *
 * @since	1.2
 * @param	array	$args	Array of args
 * @return	array	Array of campaigns data
 */
function wp_spp_get_mc_campaigns( $args = array() )	{
	$defaults = array(
		'count'  => 0,
		'status' => 'save'
	);

	$args = wp_parse_args( $args, $defaults );

	$cache_key = 'wp_spp_get_campaigns_' . $args['status'];
	$campaigns = get_transient( $cache_key );
	$force     = false;

	if ( isset( $_GET['force-campaign-refresh'] ) )	{
		$force = true;
	}

	$force = apply_filters( 'wp_spp_force_campaigns_refresh', $force );

	if ( false === $campaigns || $force )	{
		$mailchimp = wp_spp_mc_connect();

		if ( $mailchimp )   {
			$campaigns = $mailchimp->get( '/campaigns', $args, wp_spp_mc_get_timeout() );

			if ( ! empty( $campaigns ) && ! empty( $campaigns['campaigns'] ) )	{
				set_transient( $cache_key, $campaigns, DAY_IN_SECONDS / 2 );

				// Update individual campaign caches
				foreach( $campaigns['campaigns'] as $campaign )	{
					wp_spp_get_mc_campaign( $campaign['id'], $mailchimp );
				}
			}
		}
	}

   if ( ! empty( $campaigns ) && ! empty( $campaigns['campaigns'] ) )	{
	   return $campaigns['campaigns'];
   }


	return false;
} // wp_spp_get_mc_campaigns

/**
 * Send the MailChimp campaigns for the group.
 *
 * @since   1.2
 * @param   int     $group_id   SPP group ID
 * @return  int     The number of campaigns successfully sent
 */
function wp_spp_mc_send_scheduled_campaigns( $group_id )    {
    $scheduled = wp_spp_get_mc_scheduled_campaigns( $group_id );
    $count     = 0;

    if ( ! empty( $scheduled ) )   {
        $mailchimp = wp_spp_mc_connect();

        if ( $mailchimp )   {
            do_action( 'wp_spp_before_send_campaigns', $scheduled, $group_id );

            foreach( $scheduled as $campaign_id )   {

                do_action( "wp_spp_before_send_campaign_$campaign_id", $group_id );

                $sent = $mailchimp->post(
					"/campaigns/$campaign_id/actions/send",
					array(),
					wp_spp_mc_get_timeout()
				);

                do_action( "wp_spp_after_send_campaign_$campaign_id", $group_id, $sent );
                if ( true === $sent )   {
                    $count++;
                } else	{
					error_log( var_export( $sent, true ) );
				}
				wp_spp_remove_mc_campaign_from_group( $group_id, $campaign_id );
            }

            do_action( 'wp_spp_after_send_campaigns', $scheduled, $group_id, $count );
        }
    }

    return $count;
} // wp_spp_mc_send_scheduled_campaigns

/**
 * Disconnect from MailChimp.
 *
 * @since	1.2
 * @return	void
 */
function wp_spp_mc_disconnect_action()	{
	if ( ! isset( $_GET['wp-spp-action'] ) || 'disconnect-mailchimp' != $_GET['wp-spp-action'] )	{
		return;
	}

	if ( ! isset( $_GET['wp-spp-nonce'] ) || ! wp_verify_nonce( $_GET['wp-spp-nonce'], 'disconnect-mc' ) )	{
		return;
	}

	delete_option( 'wp_spp_mc_api_key' );

	wp_safe_redirect( add_query_arg( array(
		'post_type' => 'wp_spp_group',
		'page'      => 'wp_spp'
	), admin_url( 'edit.php' ) ) );
	exit;
} // wp_spp_mc_disconnect_action
add_action( 'admin_init', 'wp_spp_mc_disconnect_action' );

/**
 * Display the scheduled campaigns list.
 *
 * @since   1.2
 * @param   int     $group_id   The SPP group ID
 * @return  string  Output for list
 */
function wp_spp_mc_display_scheduled_campaigns( $group_id ) {
    $scheduled = wp_spp_get_mc_scheduled_campaigns( $group_id );
    ob_start();

    ?>
    <table id="wp-spp-table-scheduled" class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th style="width: 5%;"><?php _e( 'Action', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 25%;"><?php _e( 'Name', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Subject', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 15%;"><?php _e( 'List', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 15%; text-align: center;"><?php _e( 'Recipients', 'synchronized-post-publisher' ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php if ( empty( $scheduled ) ) : ?>

            <tr id="wp-spp-campaign-scheduled">
                <td colspan="5"><?php _e( 'No campaigns will be sent when this group is published.', 'synchronized-post-publisher' ); ?></td>
            </tr>

        <?php else : ?>

            <?php foreach( $scheduled as $campaign_id ) :

                $campaign_data = wp_spp_get_mc_campaign( $campaign_id );
                if ( ! $campaign_data )   {
                    continue;
                }
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
            <?php endforeach; ?>

        <?php endif; ?>
        </tbody>
    </table>
    <?php

    return ob_get_clean();
} // wp_spp_mc_display_scheduled_campaigns

/**
 * Display the available campaigns list.
 *
 * @since   1.2
 * @param   array   $exclude   Array of campaign IDs to exclude
 * @return  string  Output for list
 */
function wp_spp_mc_display_available_campaigns( $exclude ) {
    $campaigns = wp_spp_get_mc_campaigns();;
    ?>

    <table id="wp-spp-table-campaigns" class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th style="width: 5%;"><?php _e( 'Action', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 25%;"><?php _e( 'Name', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Subject', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 15%;"><?php _e( 'List', 'synchronized-post-publisher' ); ?></th>
                <th style="width: 15%; text-align: center;"><?php _e( 'Recipients', 'synchronized-post-publisher' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $campaigns ) ) : ?>
                <tr id="wp-spp-campaign-list">
                    <td colspan="5">
                        <?php printf(
                            __( 'There are no <a href="%s" target="_blank">campaigns</a> ready to send.', 'synchronized-post-publisher' ),
                            'https://admin.mailchimp.com/campaigns/'
                        ); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach( $campaigns as $campaign ) :
                    if ( ! empty( $exclude ) && in_array( $campaign['id'], $exclude ) ) continue;
                        $campaign_url = 'https://admin.mailchimp.com/campaigns/show/?id=' . $campaign['web_id'];
                        $list_url     = 'https://admin.mailchimp.com/lists/members/?id=' . $campaign['recipients']['list_id'];
                    ?>
                    <tr id="ready-<?php echo $campaign['id']; ?>">
                        <td style="text-align: center;">
                            <a href="#" class="add-campaign button" data-campaign="<?php echo $campaign['id']; ?>">
                                <?php _e( 'Add', 'synchronized-post-publisher' ); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo $campaign_url; ?>" target="_blank">
                                <?php echo esc_attr( $campaign['settings']['title'] ); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_attr( $campaign['settings']['subject_line'] ); ?>
                        </td>
                        <td>
                            <a href="<?php echo $list_url; ?>" target="_blank">
                                <?php echo esc_attr( $campaign['recipients']['list_name'] ); ?>
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <?php echo esc_attr( $campaign['recipients']['recipient_count'] ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
    <?php

    return ob_get_clean();
} // wp_spp_mc_display_available_campaigns

/**
 * Add/Remove cron schedule based on settings.
 *
 * @since	1.2
 * @param	mixed	$old_value	The old option value
 * @param	mixed	$new_value	The new option value
 * @param	string	$option		The option name
 * @return	void
 */
function wp_spp_manage_mailchimp_schedule()	{
	if ( ! wp_spp_mc_get_api() || ! wp_spp_mc_is_connected() )	{

		if ( $timestamp = wp_next_scheduled( 'wp_spp_refresh_mailchimp_campaigns_task' ) )	{
			wp_unschedule_event( $timestamp, 'wp_spp_refresh_mailchimp_campaigns_task' );
		}

	} elseif ( ! wp_next_scheduled( 'wp_spp_refresh_mailchimp_campaigns_task' ) && wp_spp_mc_is_connected() )	{
		wp_schedule_event(
			time(),
			'twicedaily',
			'wp_spp_refresh_mailchimp_campaigns_task'
		);

		wp_schedule_single_event( time(), 'wp_wpp_refresh_campaigns_task' );
	}
} // wp_spp_manage_mailchimp_schedule
add_action( 'init', 'wp_spp_manage_mailchimp_schedule' );

/**
 * Refresh the cache of MailChimp campaigns.
 *
 * @since	1.2
 * @return	void
 */
function wp_wpp_refresh_campaigns_task()	{
	global $wpdb;

	$wpdb->query( 
		"
		DELETE FROM $wpdb->options
		WHERE option_name LIKE '_transient_%wp_spp_get_campaign%'
		"
	);

	wp_spp_get_mc_campaigns();
} // wp_wpp_refresh_campaigns_task
add_action( 'wp_spp_refresh_mailchimp_campaigns_task', 'wp_wpp_refresh_campaigns_task' );

