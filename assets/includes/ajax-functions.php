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

    $post_id  = $_POST['post_id'];
    $group_id = $_POST['group_id'];

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
