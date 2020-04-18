<?php
/**
 * Install Function
 *
 * @package     WP_SPP
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Install
 *
 * Runs on plugin install by setting up options.
 *
 * @since	1.0
 * @global	$wpdb
 * @param 	bool	$network_side	If the plugin is being network-activated
 * @return	void
 */
function wp_spp_install( $network_wide = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			wp_spp_run_install();
			restore_current_blog();

		}

	} else {

		wp_spp_run_install();

	}

} // wp_spp_install
register_activation_hook( WP_SPP_PLUGIN_FILE, 'wp_spp_install' );

/**
 * Run the WP_SPP Install process
 *
 * @since	1.0
 * @return	void
 */
function wp_spp_run_install() {
	global $wpdb, $wp_version;

	// Bail if already installed
	$already_installed = get_option( 'wp_spp_installed' );
	if ( $already_installed )	{
		return;
	}

	// Add Upgraded From Option
	$current_version = get_option( 'wp_spp_version' );
	if ( $current_version ) {
		update_option( 'wp_spp_version_upgraded_from', $current_version );
	}

	// Set the default options
	update_option( 'wp_spp_post_types_enabled', array( 'post' ) );
	update_option( 'wp_spp_version', WP_SPP_VERSION );
	add_option( 'wp_spp_install_version', WP_SPP_VERSION, '', 'no' );
	add_option( 'wp_spp_installed', current_time( 'mysql' ), '', 'no' );
	add_option( 'wp_spp_published_posts', 0 );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

} // wp_spp_run_install

/**
 * When a new Blog is created in multisite, see if SPP is network activated, and run the installer.
 *
 * @since	1.0
 * @param	object	$site	The WP_Site object for the new site
 * @return	void
 */
function wp_spp_new_blog_created( $site ) {
	if ( is_plugin_active_for_network( plugin_basename( WP_SPP_PLUGIN_FILE ) ) ) {
		switch_to_blog( $site->blog_id );
		wp_spp_install();
		restore_current_blog();
	}
} // wp_spp_new_blog_created
add_action( 'wp_insert_site', 'wp_spp_new_blog_created' );

/**
 * Deactivate
 *
 * Runs on plugin deactivation to remove scheduled tasks.
 *
 * @since	1.0
 * @return	void
 */
function wp_spp_deactivate_plugin()	{
} // wp_spp_deactivate_plugin
register_deactivation_hook( WP_SPP_PLUGIN_FILE, 'wp_spp_deactivate_plugin' );
