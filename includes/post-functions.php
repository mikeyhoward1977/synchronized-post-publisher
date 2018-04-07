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
	$post_statuses = array( 'auto-draft', 'draft', 'pending' );
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
    $post_type   = get_post_type( $post );

	$can_group = in_array( $post_status, wp_spp_group_post_statuses() ) && in_array( $post_type, wp_spp_group_post_types() );
	$can_group = apply_filters( 'wp_spp_post_can_be_grouped', $can_group, $post );

	return $can_group;
} // wp_spp_post_can_be_grouped

/**
 * Publish all posts in a sync group.
 *
 * @since	1.0
 * @param	int		$group_id	Group post ID
 * @return	int		The total number of posts published
 */
function wp_spp_publish_group_posts( $group_id )	{

	// Bail if there are no other posts in the group
	$posts_in_group = wp_spp_get_posts_in_sync_group( $group_id );
	if ( empty( $posts_in_group ) )	{
		return;
	}

	$count = 0;

	// Publish the remaining posts
	foreach( $posts_in_group as $post_id )	{
		$type   = get_post_type( $post_id );
		$status = get_post_status( $post_id );

		if ( ! in_array( $type, wp_spp_group_post_types() ) || ! in_array( $status, wp_spp_group_post_statuses() ) )	{
			continue;
		}

		if ( wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) ) )	{
			$count++;
			wp_spp_remove_post_from_sync_group( $post_id );
		}
	}

	// Delete group
	if ( get_option( 'wp_spp_delete_groups_on_publish', false ) )	{
		$remaining_posts = wp_spp_get_posts_in_sync_group( $group_id );

		if ( empty( $remaining_posts ) )	{
			wp_delete_post( $group_id, true );
		}
	}

	return $count;
} // wp_spp_publish_group_posts

/**
 * Retrieve post sync groups.
 *
 * @since	1.0
 * @param   array   $args   Array of arguments to parse to get_posts()
 * @return	array	Array of sync group post objects
 */
function wp_spp_get_post_sync_groups( $args = array() )	{
    $defaults = array(
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'name',
        'order'          => 'ASC'
    );

    $args = wp_parse_args( $args, $defaults );
    $args['post_type'] = 'wp_spp_group';

	$groups = get_posts( $args );

	return $groups;
} // wp_spp_get_post_sync_groups

/**
 * Adds a post to an SPP group.
 *
 * @since	1.0
 * @param	int          $post_id	Post ID
 * @param   int          $group_id   Group ID
 * @return	int|false    Meta ID on success, false on failure
 */
function wp_spp_add_post_to_sync_group( $post_id, $group_id )	{
    $added = false;

    if ( $group_id == get_post_meta( $post_id, '_wp_spp_sync_group', true ) ) {
        $added = true;
    }

    if ( ! $added ) {
        $group = get_post( $group_id );

        if ( $group && 'wp_spp_group' == get_post_type( $group ) )    {
            $added = update_post_meta( $post_id, '_wp_spp_sync_group', $group_id );
        }
    }

    do_action( 'wp_spp_add_post_to_sync_group', $post_id, $group_id, $added );

	return $added;
} // wp_spp_add_post_to_sync_group

/**
 * Removes a post from an SPP group.
 *
 * @since	1.0
 * @param	int      $post_id	Post ID
 * @return	bool     True on success, false on failure
 */
function wp_spp_remove_post_from_sync_group( $post_id )	{
	return delete_post_meta( $post_id, '_wp_spp_sync_group' );
} // wp_spp_remove_post_from_sync_group

/**
 * Retrieve the sync group post ID for a specific post.
 *
 * @since	1.0
 * @param	int		$post_id	Post ID
 * @return	string|false		Sync group post ID, or false
 */
function wp_spp_get_post_sync_group( $post_id )	{
	$group = get_post_meta( $post_id, '_wp_spp_sync_group', true );

    if ( empty( $group ) )  {
        $group = false;
    }

	return $group;
} // wp_spp_get_post_sync_group

/**
 * Retrieve all posts in the sync group.
 *
 * @since	1.0
 * @param	int		$post_id	Group post ID
 * @param   array   $args       Array of args for get_posts
 * @return	array	Array of post ID's within the group
 */
function wp_spp_get_posts_in_sync_group( $post_id, $args = array() )	{

    $defaults = array(
		'post_type'      => wp_spp_group_post_types(),
		'posts_per_page' => -1,
		'post_status'    => wp_spp_group_post_statuses(),
		'meta_key'       => '_wp_spp_sync_group',
		'meta_value'     => $post_id,
        'meta_type'      => 'NUMERIC',
		'fields'         => 'ids'
	);

    $args  = wp_parse_args( $args, $defaults );
	$posts = get_posts( $args );

	return $posts;
} // wp_spp_get_posts_in_sync_group

/**
 * Count the number of posts in the sync group.
 *
 * @since	1.0
 * @param	int		$post_id	Group post ID
 * @return	int		Number of posts
 */
function wp_spp_count_sync_group_posts( $post_id )	{
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( 
		"
			SELECT count(*)
			FROM $wpdb->postmeta
			WHERE
			meta_key = %s
			AND
			meta_value = %d
		",
		'_wp_spp_sync_group',
		$post_id
	) );

	return $count;
} // wp_spp_count_sync_group_posts
