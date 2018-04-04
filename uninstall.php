<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

/**
 * Uninstall Synchronized Post Publisher.
 *
 * Removes all settings.
 *
 * @package     WP_SPP
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 *
 */

global $wpdb;

// Delete post meta keys
$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_wp_spp_sync_group' ) );

// Delete all Plugin Options
$all_options = array(
	'wp_spp_installed',
	'wp_spp_version',
	'wp_spp_version_upgraded_from',
	'wp_spp_post_types_enabled',
	'wp_spp_post_sync_groups',
	'wp_spp_install_version'
);

foreach( $all_options as $options )	{
	delete_option( $options );
}
