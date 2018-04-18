<?php
/**
 * Post functions
 *
 * @package     WP_SPP
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve all dismissed notices.
 *
 * @since	1.1
 * @return	array	Array of dismissed notices
 */
function wp_spp_dismissed_notices() {

	global $current_user;

	$user_notices = (array) get_user_option( 'wp_spp_dismissed_notices', $current_user->ID );

	return $user_notices;

} // wp_spp_dismissed_notices

/**
 * Check if a specific notice has been dismissed.
 *
 * @since	1.1
 * @param	string	$notice	Notice to check
 * @return	bool	Whether or not the notice has been dismissed
 */
function wp_spp_is_notice_dismissed( $notice ) {

	$dismissed = wp_spp_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed ) ) {
		return true;
	} else {
		return false;
	}

} // wp_spp_is_notice_dismissed

/**
 * Dismiss a notice.
 *
 * @since	1.1
 * @param	string		$notice	Notice to dismiss
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function wp_spp_dismiss_notice( $notice ) {

	global $current_user;

	$dismissed_notices = $new = (array) wp_spp_dismissed_notices();

	if ( ! array_key_exists( $notice, $dismissed_notices ) ) {
		$new[ $notice ] = 'true';
	}

	$update = update_user_option( $current_user->ID, 'wp_spp_dismissed_notices', $new );

	return $update;

} // wp_spp_dismiss_notice

/**
 * Restore a dismissed notice.
 *
 * @since	1.1
 * @param	string		$notice	Notice to restore
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function wp_spp_restore_notice( $notice ) {

	global $current_user;

	$dismissed_notices = (array) wp_spp_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed_notices ) ) {
		unset( $dismissed_notices[ $notice ] );
	}

	$update = update_user_option( $current_user->ID, 'wp_spp_dismissed_notices', $dismissed_notices );

	return $update;

} // wp_spp_restore_notice
