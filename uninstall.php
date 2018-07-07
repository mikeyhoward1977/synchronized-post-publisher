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

// Call the SPP main class file
include_once( 'synchronized-post-publisher.php' );

global $wpdb;

// Delete the Custom Post Types
$items = get_posts( array(
	'post_type'   => 'wp_spp_group',
	'post_status' => 'any',
	'numberposts' => -1,
	'fields'      => 'ids'
) );

if ( $items ) {
	foreach ( $items as $item )	{
		wp_delete_post( $item, true );
	}
}

// Delete post meta keys
$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_wp_spp_sync_group' ) );

// Delete all transients
$wpdb->query( 
	"
	DELETE FROM $wpdb->options
	WHERE option_name LIKE '_transient_%wp_spp_get_campaign%'
	"
);

// Delete all Plugin Options
$all_options = array(
	'wp_spp_installed',
	'wp_spp_version',
	'wp_spp_version_upgraded_from',
	'wp_spp_post_types_enabled',
	'wp_spp_delete_groups_on_publish',
	'wp_spp_mc_api_key',
	'wp_spp_install_version',
	'wp_spp_published_posts'
);

foreach( $all_options as $options )	{
	delete_option( $options );
}
