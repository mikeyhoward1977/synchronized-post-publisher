<?php
/**
 * Plugin Name: Synchronized Post Publisher
 * Plugin URI: https://mikesplugins.co.uk/
 * Description: Synchronized publishing of multiple posts when a master post is published.
 * Version: 1.0
 * Date: 04 April 2018
 * Author: Mike Howard
 * Author URI: https://mikesplugins.co.uk/
 * Text Domain: synchronized-post-publisher
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/mikeyhoward1977/wp-synchronized-post-publisher
 * Tags: posts, publish
 *
 *
 * Synchronized_Post_Publisher is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * Synchronized_Post_Publisher is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Synchronized_Post_Publisher; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package		WP_SPP
 * @category	Core
 * @author		Mike Howard
 * @version		1.2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Synchronized_Post_Publisher' ) ) :
/**
 * Main Synchronized_Post_Publisher Class.
 *
 * @since 1.0
 */
final class Synchronized_Post_Publisher {
	/** Singleton *************************************************************/

	/**
	 * @var		Synchronized_Post_Publisher The one true Synchronized_Post_Publisher
	 * @since	1.0
	 */
	private static $instance;

	/**
	 * Main Synchronized_Post_Publisher Instance.
	 *
	 * Insures that only one instance of Synchronized_Post_Publisher exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since	1.0
	 * @static
	 * @static	var		arr		$instance
	 * @uses	Synchronized_Post_Publisher::setup_constants()	Setup the constants needed.
	 * @uses	Synchronized_Post_Publisher::includes()			Include the required files.
	 * @uses	Synchronized_Post_Publisher::load_textdomain()	Load the language files.
	 * @see WP_SPP()
	 * @return	obj	Synchronized_Post_Publisher	The one true Synchronized_Post_Publisher
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Synchronized_Post_Publisher ) )	{
			self::$instance = new Synchronized_Post_Publisher;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
		}

		return self::$instance;

	}
	
	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since	1.0
	 * @access	protected
	 * @return	void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'synchronized-post-publisher' ), '1.0' );
	} // __clone

	/**
	 * Disable unserializing of the class.
	 *
	 * @since	1.0
	 * @access	protected
	 * @return	void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'synchronized-post-publisher' ), '1.0' );
	} // __wakeup
	
	/**
	 * Setup plugin constants.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function setup_constants()	{

		if ( ! defined( 'WP_SPP_VERSION' ) )	{
			define( 'WP_SPP_VERSION', '1.0' );
		}

		if ( ! defined( 'WP_SPP_PLUGIN_DIR' ) )	{
			define( 'WP_SPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'WP_SPP_PLUGIN_URL' ) )	{
			define( 'WP_SPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		
		if ( ! defined( 'WP_SPP_PLUGIN_FILE' ) )	{
			define( 'WP_SPP_PLUGIN_FILE', __FILE__ );
		}

	} // setup_constants
			
	/**
	 * Include required files.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function includes()	{
		require_once WP_SPP_PLUGIN_DIR . 'includes/post-functions.php';
		if ( is_admin() )	{
			require_once WP_SPP_PLUGIN_DIR . 'includes/admin/settings.php';
		}
	} // includes
	
	/**
	 * Load the text domain for translations.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	public function load_textdomain()	{

        // Set filter for plugin's languages directory.
		$wp_spp_lang_dir  = dirname( plugin_basename( WP_SPP_PLUGIN_FILE ) ) . '/languages/';
		$wp_spp_lang_dir  = apply_filters( 'wp_spp_languages_directory', $wp_spp_lang_dir );

		// Traditional WordPress plugin locale filter.
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'synchronized-post-publisher' );

        load_textdomain( 'synchronized-post-publisher', WP_LANG_DIR . '/synchronized-post-publisher/synchronized-post-publisher-' . $locale . '.mo' );
        load_plugin_textdomain( 'synchronized-post-publisher', false, $wp_spp_lang_dir );

	} // load_textdomain
	
} // class Synchronized_Post_Publisher
endif;

/**
 * The main function for that returns Synchronized_Post_Publisher
 *
 * The main function responsible for returning the one true Synchronized_Post_Publisher
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wp_spp = WP_SPP(); ?>
 *
 * @since	1.0
 * @return	obj		Synchronized_Post_Publisher	The one true Synchronized_Post_Publisher Instance.
 */
function WP_SPP()	{
	return Synchronized_Post_Publisher::instance();
} // KBS

// Get WP_SPP Running
WP_SPP();
