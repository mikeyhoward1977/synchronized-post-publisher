<?php
/**
 * Meta Boxes
 *
 * @package     WP_SPP
 * @subpackage  Admin/Meta Boxes
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add the option to add the post to a sync group.
 *
 * @since	1.0
 * @param	object	$post	WP_Post object
 * @return	void
 */
function wp_spp_sync_group_post_option( $post )	{

	if ( wp_spp_post_can_be_grouped( $post ) )	{
        $current_group = __( 'None', 'synchronized-post-publisher' );
		$sync_groups   = wp_spp_get_post_sync_groups( array( 'fields' => 'ids' ));
		$sync_group    = wp_spp_get_post_sync_group( $post->ID );

        if ( $sync_group && in_array( $sync_group, $sync_groups ) )   {
            $current_group = get_the_title( $sync_group );
        } else  {
            $sync_group = 0;
        }

		ob_start(); ?>

		<div class="misc-pub-section">
		    <input type="hidden" name="wp_spp_post_with_group" id="wp_spp_post_with_group" value="<?php echo $sync_group; ?>" />
			<span class="dashicons dashicons-update" style="color: #82878c; padding-right: 3px;"></span>
            <?php _e( 'Publish Group:', 'synchronized-post-publisher' ); ?> <strong><span id="wp_spp_current_group"><?php echo $current_group; ?></strong></span>
            <a href="#wp_spp_select_group" class="edit-group hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit', 'synchronized-post-publisher' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit publish group', 'synchronized-post-publisher' ); ?></span></a>

			<div id="post-sync-group-select" class="hide-if-js">
                <select name='post_sync_group' id='post_sync_group'>
                    <option value="0"<?php selected( $sync_group, 0 ); ?>><?php _e( 'None', 'synchronized-post-publisher' ); ?></option>

					<?php foreach( $sync_groups as $group ) : ?>

						<option value="<?php echo $group; ?>"<?php selected( $sync_group, $group ); ?>><?php echo get_the_title( $group ); ?></option>

                    <?php endforeach; ?>

                </select>
                <a href="#wp_spp_select_group" class="save-post-group hide-if-no-js button"><?php _e( 'OK', 'synchronized-post-publisher' ); ?></a>
                <a href="#wp_spp_select_group" class="cancel-post-group hide-if-no-js button-cancel"><?php _e( 'Cancel', 'synchronized-post-publisher' ); ?></a>
            </div>

        </div>

        <?php echo ob_get_clean();
	}

} // wp_spp_sync_group_post_option
add_action( 'post_submitbox_misc_actions', 'wp_spp_sync_group_post_option' );

/**
 * Adds the meta boxes for the wp_spp_group post type.
 *
 * @since   1.0
 * @param	object	$post	WP_Post object.
 */
function wp_spp_add_meta_boxes( $post ) {
    if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
        add_meta_box(
            'wp-spp-group-posts-metabox',
            sprintf( __( '%s Posts', 'synchronized-post-publisher' ), get_the_title( $post ) ),
            'wp_spp_group_posts_metabox_callback',
            'wp_spp_group'
        );

		$posts = wp_spp_get_posts_in_sync_group( $post->ID, array( 'posts_per_page' => 1 ) );

		if ( ! empty( $posts ) )	{
			add_meta_box(
				'wp-spp-group-publish-metabox',
				__( 'Publish Group Posts', 'synchronized-post-publisher' ),
				'wp_spp_group_publish_metabox_callback',
				'wp_spp_group',
				'side'
			);
		}

    }
} // wp_spp_add_meta_boxes
add_action( 'add_meta_boxes_wp_spp_group', 'wp_spp_add_meta_boxes' );

/**
 * Renders the group publish posts metabox
 *
 * @since   1.0
 * @param   object  $post   WP_Post object
 */
function wp_spp_group_publish_metabox_callback( $post )   {
	$publish_url = wp_nonce_url( add_query_arg( array(
		'post_type'     => 'wp_spp_group',
		'wp_spp_action' => 'publish_group',
		'spp_group_id'  => $post->ID
	), admin_url( 'edit.php' ) ), 'spp-publish', 'spp_nonce' );
	?>

    <div id="wp_spp_publish_group" style="text-align: center;">
        <span class="spp_publish_button">
        	<a id="spp-publish-posts" href="<?php echo $publish_url; ?>" class="button button-primary"><?php _e( 'Publish all Group Posts', 'synchronized-post-publisher' ); ?></a>
        </span>
    </div>
    <?php
} // wp_spp_group_publish_metabox_callback

/**
 * Renders the group posts metabox
 *
 * @since   1.0
 * @param   object  $post   WP_Post object
 */
function wp_spp_group_posts_metabox_callback( $post )   {

    $group_posts = wp_spp_get_posts_in_sync_group( $post->ID, array( 'fields' => 'all' ) );

    ?>
    <table class="wp-list-table widefat striped emails">
        <thead>
            <tr>
                <th><?php _e( 'Title', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Type', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Author', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Date', 'synchronized-post-publisher' ); ?></th>
                <th><?php _e( 'Actions', 'synchronized-post-publisher' ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php if ( empty( $group_posts ) ) : ?>
            
            <tr>
                <td colspan="5"><?php _e( 'No posts within this group.', 'synchronized-post-publisher' ); ?></td>
            </tr>

        <?php else : ?>

            <?php foreach( $group_posts as $group_post ) : ?>
                <?php $post_type_object = get_post_type_object( $group_post->post_type ); ?>
                <?php $post_author      = get_userdata( $group_post->post_author ); ?>
                <?php $remove_url       = wp_nonce_url( add_query_arg( array(
					'wp_spp_action' => 'remove_post',
					'spp_post_id'   => $group_post->ID,
					'spp_group_id'  => $post->ID
				), admin_url() ), 'spp-remove-post', 'spp_nonce' ); ?>

                <?php $actions = array(
                    'remove' => '<a href="' . $remove_url . '" class="delete" style="color: #a00;">' . __( 'Remove', 'synchronized-post-publisher' ) . '</a>'
                );
            
                $actions = apply_filters( 'wp_spp_group_posts_metabox_table_actions', $actions, $group_post ); ?>

                <tr>
                    <td><?php printf(
                        '<a href="%s">%s</a>',
                        add_query_arg( array( 'post' => $group_post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ),
                        get_the_title( $group_post )
                    ); ?></td>
                    <td><?php echo $post_type_object->labels->singular_name; ?></td>
                    <td><?php printf(
                        '<a href="%s">%s</a>',
                        add_query_arg( 'user_id', $post_author->ID, admin_url( 'user-edit.php' ) ),
                        $post_author->display_name
                    ); ?></td>
                    <td><span class="description"><?php printf(
                        __( 'Last modified: %s at %s', 'synchronized-post-publisher' ),
                        get_the_modified_date( get_option( 'date_format' ), $group_post ),
                        get_the_modified_date( get_option( 'time_format' ), $group_post )
                    ); ?></span></td>
                    <td><?php echo implode( '|', $actions ); ?></td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>
    </table>
    <?php
} // wp_spp_group_posts_metabox_callback
