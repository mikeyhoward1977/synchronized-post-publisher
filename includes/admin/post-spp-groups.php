<?php
/**
 * Manage the wp_spp_group post type
 *
 * @package     WP_SPP
 * @subpackage  Admin/WP_SPP Group Posts
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define the columns that should be displayed for the wp_spp_group post lists screen
 *
 * @since	1.0
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function wp_spp_set_group_post_columns( $columns ) {

	$columns = array(
        'cb'               => '<input type="checkbox" />',
        'title'            => _x( 'Name', 'column name', 'synchronized_post_publisher' ),
        'author'           => _x( 'Created by', 'column name', 'synchronized_post_publisher' ),
        'desc'             => _x( 'Description', 'column name', 'synchronized_post_publisher' ),
        'posts'            => _x( 'Post Count', 'column name', 'synchronized_post_publisher' ),
		'dates'            => __( 'Date', 'kb-support' )
    );
	
	return apply_filters( 'wp_spp_group_post_columns', $columns );

} // wp_spp_set_group_post_columns
add_filter( 'manage_wp_spp_group_posts_columns' , 'wp_spp_set_group_post_columns' );
