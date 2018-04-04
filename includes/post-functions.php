<?php
/**
 * Post functions
 *
 * @package     WP_SPP
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Post types to ignore.
 *
 * @since	1.0
 * @return	array	Array of post types to ignore within this plugin
 */
function wp_spp_ignore_post_types()	{
	$ignore = array( 'attachment' );
	$ignore = apply_filters( 'wp_spp_ignore_post_types', $ignore );

	return $ignore;
} // wp_spp_ignore_post_types

/**
 * Post types to allow grouping.
 *
 * @since	1.0
 * @return	array	Array of post types which can be grouped
 */
function wp_spp_group_post_types()	{
	$post_types = get_option( 'wp_spp_post_types_enabled', array() );
	$post_types = apply_filters( 'wp_spp_group_post_types', $post_types );

	return $post_types;
} // wp_spp_group_post_types

/**
 * Post statuses that can be grouped.
 *
 * @since	1.0
 * @return	array	Array of post types which can be grouped
 */
function wp_spp_group_post_statuses()	{
	$post_statuses = array( 'auto-draft', 'draft' );
	$post_statuses = apply_filters( 'wp_spp_group_post_statuses', $post_statuses );

	return $post_statuses;
} // wp_spp_group_post_statuses

/**
 * Whether or not a post can be grouped.
 *
 * @since	1.0
 * @param	int|object	$post	Post ID or WP_Post object
 * @return	bool
 */
function wp_spp_post_can_be_grouped( $post )	{
	$can_group   = false;
	$post_status = get_post_status( $post );

	if ( in_array( $post_status, wp_spp_group_post_statuses() ) )	{
		$post_type = get_post_type( $post );
	
		$can_group = in_array( $post_type, wp_spp_group_post_types() );
	}

	$can_group = apply_filters( 'wp_spp_post_can_be_grouped', $can_group, $post_type, $post );

	return $can_group;
} // wp_spp_post_can_be_grouped

/**
 * Retrieve post sync groups.
 *
 * @since	1.0
 * @return	array	Array of sync groups
 */
function wp_spp_get_post_sync_groups()	{
	$groups = get_option( 'wp_spp_post_sync_groups', array() );

	$groups = apply_filters( 'wp_spp_post_sync_groups', $groups );

	return $groups;
} // wp_spp_get_post_sync_groups

/**
 * Retrieve the sync group for a specific post.
 *
 * @since	1.0
 * @param	int		$post_id	Post ID
 * @return	string|false		Sync group name for post, or false
 */
function wp_spp_get_post_sync_group( $post_id )	{
	$group = get_post_meta( $post_id, '_wp_spp_sync_group', true );

	return $group;
} // wp_spp_get_post_sync_group
