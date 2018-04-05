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
		$sync_groups   = wp_spp_get_post_sync_groups();
		$sync_group    = wp_spp_get_post_sync_group( $post->ID );

        if ( $sync_group && array_key_exists( $sync_group, $sync_groups ) )   {
            $current_group = $sync_groups[ $sync_group ];
        }

		ob_start(); ?>

		<div class="misc-pub-section">
			<span class="dashicons dashicons-update" style="color: #82878c; padding-right: 3px;"></span>
            <?php _e( 'Publish Group:', 'synchronized-post-publisher' ); ?> <strong><?php echo $current_group; ?></strong>
            <a href="#wp_spp_select_group" class="edit-group hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit', 'synchronized-post-publisher' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit publish group', 'synchronized-post-publisher' ); ?></span></a>

			<div id="post-sync-group-select" class="hide-if-js">
                <select name='post_sync_group' id='post_sync_group'>
                    <option value="0"<?php selected( $sync_group, 0 ); ?>><?php _e( 'None', 'synchronized-post-publisher' ); ?></option>

					<?php foreach( $sync_groups as $slug => $name ) : ?>

						<option value="<?php echo $slug; ?>"<?php selected( $sync_group, $slug ); ?><?php echo $name; ?></option>

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
