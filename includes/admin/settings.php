<?php
/**
 * Admin Pages
 *
 * @package     WP_SPP
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Mike Howard
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
	add_options_page(
		__( 'Synchronized Post Publisher', 'synchronized-post-publisher' ),
		__( 'Synchronized Post Publisher', 'synchronized-post-publisher' ),
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
	register_setting( 'wp_spp_settings_group', 'wp_spp_post_types_enabled', 'wp_spp_sanitize_settings' );
} // wp_spp_register_settings
add_action( 'admin_init', 'wp_spp_register_settings' );

/**
 * Output the settings page fields for WP_SPP
 *
 * @since	1.0
 */
function wp_spp_settings_page()	{
	$post_types    = get_post_types( array( 'public' => true ), 'objects' );
	$enabled_types = get_option( 'wp_spp_post_types_enabled', array() );
	$options       = array();

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
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

    <?php
} // wp_spp_settings_page

/**
 * Sanitize the settings before saving.
 *
 * @since	1.0
 * @param	array	$input	Array of values being saved
 * @return	Array of values being saved
 */
function wp_spp_sanitize_settings( $input )	{
	if ( ! isset( $input ) )	{
		$input = array();
	} else	{
		$input = array_map( 'sanitize_text_field', $input );
	}

	return $input;
} // wp_spp_sanitize_settings

