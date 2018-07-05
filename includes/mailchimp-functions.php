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

require_once WP_SPP_PLUGIN_DIR . '/includes/lib/MailChimp.php';
use WP_SPP\SPP_MailChimp\SPP_MailChimp;

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
