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