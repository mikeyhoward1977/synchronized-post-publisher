<?php
/**
 * Admin Settings
 *
 * @package     WP_SPP
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds the settings options page within the WP Settings menu.
 *
 * @since	1.0
 */
function wp_spp_admin_settings_menu()	{
    add_submenu_page(
        'edit.php?post_type=wp_spp_group',
        __( 'Settings', 'synchronized-post-publisher' ),
        __( 'Settings', 'synchronized-post-publisher' ),
        'manage_options',
        'wp_spp',
        'wp_spp_settings_page'
    ); 
} // wp_spp_admin_settings_menu
add_action( 'admin_menu', 'wp_spp_admin_settings_menu' );

/**
 * Register WP_SPP settings.
 *
 * @since	1.0
 */
function wp_spp_register_settings()	{
	register_setting( 'wp_spp_settings_group', 'wp_spp_post_types_enabled', 'wp_spp_sanitize_post_types_enabled_setting' );
    register_setting( 'wp_spp_settings_group', 'wp_spp_delete_groups_on_publish' );
    register_setting( 'wp_spp_settings_group', 'wp_spp_mc_api_key' );
} // wp_spp_register_settings
add_action( 'admin_init', 'wp_spp_register_settings' );

/**
 * Output the settings page fields for WP_SPP
 *
 * @since	1.0
 */
function wp_spp_settings_page()	{
	$post_types        = get_post_types( array( 'public' => true ), 'objects' );
	$enabled_types     = get_option( 'wp_spp_post_types_enabled', array() );
	$options           = array();
	$mc_connected      = wp_spp_mc_is_connected();
	$mc_field_type     = $mc_connected ? 'password'             : 'text';
	$mc_field_readonly = $mc_connected ? ' readonly="readonly"' : '';
	$mc_disconnect_url = wp_nonce_url( add_query_arg( array(
		'wp-spp-action' => 'disconnect-mailchimp'
	), admin_url() ), 'disconnect-mc', 'wp-spp-nonce' );

	foreach( $post_types as $post_type )	{

		if ( in_array( $post_type->name, wp_spp_ignore_post_types() ) )	{
			continue;
		}

		$options[] = sprintf(
			'<input type="checkbox" name="wp_spp_post_types_enabled[]" value="%1$s"%2$s /> %3$s (%1$s)',
			$post_type->name,
			in_array( $post_type->name, $enabled_types ) ? ' checked="checked"' : '',
			$post_type->label
		);

	} ?>

    <div class="wrap">
        <h1><?php _e( 'Synchronized Post Publisher', 'synchronized-post-publisher' ); ?></h1>
        <form method="post" action="options.php">
			<?php settings_fields( 'wp_spp_settings_group' ); ?>
            <?php do_settings_sections( 'wp_spp_settings_group' ); ?>

			<table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Enabled Post Types', 'synchronized-post-publisher' ); ?></th>
                    <td>
                    	<?php echo implode( '<br />', $options ); ?>
                    	<p class="description"><?php _e( 'Select the post types for which Synchronized Post Publisher should be enabled.', 'synchronized-post-publisher' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Delete Groups on Publish', 'synchronized-post-publisher' ); ?></th>
                    <td>
                        <input type="checkbox" name="wp_spp_delete_groups_on_publish" value="1"<?php checked( 1, get_option( 'wp_spp_delete_groups_on_publish', 0 ) ); ?> />
                        <p class="description">
                            <?php _e( 'If enabled, post groups will be deleted once all posts within the group are successfully published.', 'synchronized-post-publisher' ); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'MailChimp API Key', 'synchronized-post-publisher' ); ?></th>
                    <td>
                        <input type="<?php echo $mc_field_type; ?>" name="wp_spp_mc_api_key" class="regular-text" value="<?php echo wp_spp_mc_get_api(); ?>"<?php echo $mc_field_readonly; ?> />
                        <p class="description">
                            <?php if ( $mc_connected ) {
                                printf(
									__( 'Successfully connected to MailChimp. <a href="%s">Disconnect</a>', 'synchronized-post-publisher' ),
									$mc_disconnect_url
								);
                            } else  {
                                printf(
                                    __( 'Enter your <a href="%s" target="_blank">MailChimp API key</a> here to enable automated sending of campaigns once all posts within the group are successfully published.', 'synchronized-post-publisher' ),
                                    'http://admin.mailchimp.com/account/api-key-popup'
                                );
                            }
                            ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

    <?php
} // wp_spp_settings_page

/**
 * Sanitize the post types enabled settings before saving.
 *
 * @since	1.0
 * @param	array	$input	Array of values being saved
 * @return	Array of values being saved
 */
function wp_spp_sanitize_post_types_enabled_setting( $input )	{
	if ( ! isset( $input ) )	{
		$input = array();
	} else	{
		$input = array_map( 'sanitize_text_field', $input );
	}

    add_settings_error( 'wp-spp-notices', '', __( 'Settings updated.', 'synchronized-post-publisher' ), 'updated' );

	return $input;
} // wp_spp_sanitize_post_types_enabled_setting

