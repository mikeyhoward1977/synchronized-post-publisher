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
 * Renders the description field on the wp_spp_group post pages.
 *
 * @since	1.0
 * @param	object	$post	WP_Post object
 * @return	void
 */
function wp_spp_render_group_description_field( $post )	{

	if ( 'wp_spp_group' != $post->post_type )	{
		return;
	}

	$placeholder = __( 'Optionally enter a description for this group.', 'synchronized_post_publisher' );

	ob_start(); ?>

		<div id="wp_spp_group_description">
            <textarea name="content" id="content" style="width: 100%; margin-top: 5px;" placeholder="<?php echo $placeholder; ?>"><?php echo $post->post_content; ?></textarea>
        </div>

	<?php echo ob_get_clean();

} // wp_spp_render_group_description_field
add_action( 'edit_form_after_title', 'wp_spp_render_group_description_field' );

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
		'desc'             => _x( 'Description', 'column name', 'synchronized_post_publisher' ),
		'posts'            => _x( 'Posts', 'column name', 'synchronized_post_publisher' ),
        'author'           => _x( 'Created by', 'column name', 'synchronized_post_publisher' ),
		'date'             => __( 'Date', 'kb-support' )
    );
	
	return apply_filters( 'wp_spp_group_post_columns', $columns );

} // wp_spp_set_group_post_columns
add_filter( 'manage_wp_spp_group_posts_columns' , 'wp_spp_set_group_post_columns' );

/**
 * Redners the data for each of the wp_spp_group post custom columns.
 *
 * @since	1.0
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function wp_spp_output_group_post_columns( $column_name, $post_id ) {

	switch ( $column_name ) {
		case 'desc':
			echo '<p class="description">' . get_post_field( 'post_content', $post_id ) . '</p>';
			break;

		case 'posts':
			echo wp_spp_count_sync_group_posts( $post_id );
			break;
	}

} // wp_spp_output_group_post_columns
add_action( 'manage_wp_spp_group_posts_custom_column' , 'wp_spp_output_group_post_columns', 10, 2 );
