<?php
/**
 * Plugin Name: Synchronized Post Publisher
 * Plugin URI: 
 * Description: Automate the publishing of multiple posts and pages at the same time.
 * Version: 1.0
 * Date: 04 April 2018
 * Author: Mike Howard
 * Author URI: https://mikesplugins.co.uk/
 * Text Domain: synchronized-post-publisher
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/mikeyhoward1977/wp-synchronized-post-publisher
 * Tags: posts, publish, publish posts, grouped posts, groups
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
 * @version		1.0
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
            self::$instance->hooks();
		}

		return self::$instance;

	} // instance
	
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

		require_once WP_SPP_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once WP_SPP_PLUGIN_DIR . 'includes/post-functions.php';
        require_once WP_SPP_PLUGIN_DIR . 'includes/ajax-functions.php';

		if ( is_admin() )	{
			require_once WP_SPP_PLUGIN_DIR . 'includes/admin/settings.php';
			require_once WP_SPP_PLUGIN_DIR . 'includes/admin/meta-boxes.php';
            require_once WP_SPP_PLUGIN_DIR . 'includes/admin/post-spp-groups.php';
		}

		require_once WP_SPP_PLUGIN_DIR . 'includes/install.php';

	} // includes

    /**
	 * Hooks.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function hooks()	{
		// Admin notices
		add_action( 'admin_notices',          array( self::$instance, 'admin_notices'                      ) );
		add_action( 'plugins_loaded',         array( self::$instance, 'request_wp_5star_rating'            ) );

        // Posts
        add_action( 'init',                   array( self::$instance, 'register_post_type'                 ) );
        add_action( 'delete_post',            array( self::$instance, 'remove_group_association_on_delete' ) );
		add_action( 'transition_post_status', array( self::$instance, 'publish_group_posts'         ), 10, 3 );

		// Post actions
		add_action( 'admin_init',             array( self::$instance, 'publish_posts'                      ) );
		add_action( 'admin_init',             array( self::$instance, 'remove_post_from_group'             ) );

        // Scripts
		add_action( 'admin_enqueue_scripts',  array( self::$instance, 'load_admin_scripts' ) );
	} // hooks

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

    /**
     * Load the admin scripts.
     *
     * @since   1.0
     * @param	str		$hook	Page hook
     * @return	void
     */
    public function load_admin_scripts( $hook ) {
        global $typenow, $post;

        $enabled_post_types   = wp_spp_group_post_types();
        $enabled_post_types[] = 'wp_spp_group';
        $enabled_pages        = array( 'post.php', 'post-new.php' );

        if ( ! in_array( $hook, $enabled_pages ) || ! in_array( $typenow, $enabled_post_types ) )  {
            return;
        }

        $js_dir  = WP_SPP_PLUGIN_URL . 'assets/js/';

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        $can_group   = ! empty( $post ) && is_object( $post ) && wp_spp_post_can_be_grouped( $post ) ? 1 : 0;
		$total_posts = 'wp_spp_group' == $typenow ? wp_spp_count_sync_group_posts( $post->ID ) : 0;

        wp_enqueue_script( 'wp-spp-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery' ), WP_SPP_VERSION, false );

        wp_localize_script( 'wp-spp-admin-scripts', 'wp_spp_vars', array(
            'can_group'             => $can_group,
            'confirm_group_publish' => __( 'This post is part of a Synchronized Post Publisher group. Continuing will also publish all other posts within this group. Click OK to confirm and publish, or Cancel to return.', 'synchronized-post-publisher' ),
			'confirm_publish_all'   => sprintf(
				__( 'Confirm you want to publish a total of %s from this group?', 'synchronized-post-publisher' ),
				sprintf( _n( '%s post', '%s posts', $total_posts, 'synchronized-post-publisher' ), $total_posts )
			),
			'posts_in_group'        => $total_posts
        ) );
    } // load_admin_scripts

/*****************************************
 -- ADMIN NOTICES
*****************************************/
	/**
	 * Displays admin notices.
	 *
	 * @since	1.0
	 */
	public function admin_notices()	{

		if ( isset( $_GET['wp-spp-notice'] ) )	{

			if ( 'published_posts' == sanitize_text_field( $_GET['wp-spp-notice'] ) )	{

				$total = isset( $_GET['spp_total'] ) ? (int) $_GET['spp_total'] : 0;

				ob_start(); ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( _n( '%s post published from group.', '%s posts published from group.', $total, 'synchronized-post-publisher' ), $total ); ?></p>
				</div>
				<?php echo ob_get_clean();
			}

			if ( 'removed' == sanitize_text_field( $_GET['wp-spp-notice'] ) )	{
				ob_start(); ?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Post removed from group.', 'synchronized-post-publisher' ); ?></p>
				</div>
				<?php echo ob_get_clean();
			}

		}

	} // admin_notices

	/**
     * Request 5 star rating after 15 posts have been published via SPP.
     *
     * After 15 posts are published via SPP we ask the admin for a 5 star rating on WordPress.org
     *
     * @since	1.1
     * @return	void
     */
    public function request_wp_5star_rating() {

        global $typenow, $pagenow;

        $allowed_types   = wp_spp_group_post_types();
        $allowed_types[] = 'wp_spp_group';
        $allowed_pages   = array( 'edit.php', 'post.php', 'post-new.php', 'index.php' );

        if ( ! current_user_can( 'administrator' ) )	{
            return;
        }

        if ( ! in_array( $typenow, $allowed_types ) && ! in_array( $pagenow, $allowed_pages ) ) {
            return;
        }

        if ( wp_spp_is_notice_dismissed( 'wp_spp_request_wp_5star_rating' ) )   {
            return;
        }

        $wpp_published = wp_spp_get_published_posts_count();

        if ( $wpp_published > 15 ) {
            add_action( 'admin_notices', array( self::$instance, 'admin_wp_5star_rating_notice' ) );
        }

    } // request_wp_5star_rating

	/**
     * Admin WP Rating Request Notice
     *
     * @since	1.1
     * @return	void
    */
    function admin_wp_5star_rating_notice() {
        ob_start(); ?>

		<script>
		jQuery(document).ready(function ($) {
			// Dismiss admin notices
			$( document ).on( 'click', '.notice-wp-spp-dismiss .notice-dismiss', function () {
				var notice = $( this ).closest( '.notice-wp-spp-dismiss' ).data( 'notice' );

				var postData         = {
					notice    : notice,
					action       : 'wp_spp_dismiss_notice'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl
				});
			});
		});
		</script>

        <div class="updated notice notice-wp-spp-dismiss is-dismissible" data-notice="wp_spp_request_wp_5star_rating">
            <p>
                <?php _e( "<strong>Nice!</strong> You've published over 15 posts using <strong>Synchronized Post Publisher</strong> which is absolutely awesome!", 'synchronized-post-publisher' ); ?>
            </p>
            <p>
                <?php printf(
                    __( 'Would you <strong>please</strong> do us a favor and leave a 5 star rating on WordPress.org? It only takes a minute and it <strong>really helps</strong> to motivate our developers and volunteers to continue working on new features and functionality. <a href="%1$s" target="_blank">Sure thing, you deserve it!</a>', 'synchronized-post-publisher' ),
                    'https://wordpress.org/support/plugin/synchronized-post-publisher/reviews/'
                ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_wp_5star_rating_notice

/*****************************************
 -- CUSTOM POST TYPE
*****************************************/
    /**
     * Register the custom post type for WP_SPP.
     *
     * @since   1.0
     */
    public function register_post_type( $hook ) {

        $labels = apply_filters( 'wp_spp_labels', array(
            'name'                  => _x( 'Synchronized Post Publisher Groups', 'wp_spp_group post type name', 'synchronized-post-publisher' ),
            'singular_name'         => _x( 'Synchronized Post Publisher Group', 'singular wp_spp_group post type name', 'synchronized-post-publisher' ),
            'add_new_item'          => __( 'Add New Group', 'synchronized-post-publisher' ),
            'edit_item'             => __( 'Edit Group', 'synchronized-post-publisher' ),
            'new_item'              => __( 'New Group', 'synchronized-post-publisher' ),
            'all_items'             => __( 'Groups', 'synchronized-post-publisher' ),
            'view_item'             => __( 'View Group', 'synchronized-post-publisher' ),
            'search_items'          => __( 'Search Group', 'synchronized-post-publisher' ),
            'not_found'             => __( 'No groups found', 'synchronized-post-publisher' ),
            'not_found_in_trash'    => __( 'No groups found in Trash', 'synchronized-post-publisher' ),
            'parent_item_colon'     => '',
            'menu_name'             => _x( 'SPP Groups', 'wp_spp_group post type menu name', 'synchronized-post-publisher' ),
            'featured_image'        => __( 'Group Image', 'synchronized-post-publisher' ),
            'set_featured_image'    => __( 'Set Group Image', 'synchronized-post-publisher' ),
            'remove_featured_image' => __( 'Remove Group Image', 'synchronized-post-publisher' ),
            'use_featured_image'    => __( 'Use as Group Image', 'synchronized-post-publisher' ),
            'filter_items_list'     => __( 'Filter group list', 'synchronized-post-publisher' ),
            'items_list_navigation' => __( 'Groups list navigation', 'synchronized-post-publisher' ),
            'items_list'            => __( 'Groupe list', 'synchronized-post-publisher' )
        ) );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-update',
            'menu_position'      => 3,
            'query_var'          => true,
            'rewrite'            => false,
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => apply_filters( 'kbs_ticket_supports', array( 'title' ) )
        );

        register_post_type( 'wp_spp_group', apply_filters( 'wp_spp_groups_post_type_args', $args ) );

    } // register_post_type

/*****************************************
 -- GROUPS
*****************************************/
    /**
     * When a group is deleted from the database, remove any posts associated
     *
     * @since   1.0
     * @param   int     $post_id    The post ID
     * @return  void
     */
    public function remove_group_association_on_delete( $post_id )  {
        global $wpdb;

        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key'   => '_wp_spp_sync_group',
                'meta_value' => $post_id
            )
        );
    } // remove_group_association_on_delete

/*****************************************
 -- PUBLISH GROUP POSTS
*****************************************/
	/**
     * When the Publish all Group Posts button is clicked, publish all posts in the group.
     *
     * @since   1.0
     * @param	string  	$new_status		New post status.
	 * @param	string  	$old_status		Old post status.
	 * @param	WP_Post		$post       	Post object.
     * @return  void
     */
    public function publish_posts()	{
		if ( isset( $_GET['wp_spp_action'], $_GET['spp_group_id'] ) && 'publish_group' == sanitize_text_field( $_GET['wp_spp_action'] ) )	{

			$group_id = absint( $_GET['spp_group_id'] );

			if ( empty( $group_id ) )	{
				return;
			}

			if ( ! isset( $_GET['spp_nonce']) || ! wp_verify_nonce($_GET['spp_nonce'], 'spp-publish') )	{
				wp_die(
					__( 'You do not have permissions to perform this action.', 'synchronized-post-publisher' ),
					__( 'Security Error', 'synchronized-post-publisher' )
				);
			}

			// Stop looping when publishing posts within the group
			remove_action( 'transition_post_status', array( self::$instance, 'publish_group_posts' ), 10, 3 );
	
			$published_posts = wp_spp_publish_group_posts( $group_id );
	
			// Re-hook the action
			add_action( 'transition_post_status', array( self::$instance, 'publish_group_posts' ), 10, 3 );

			$redirect = add_query_arg( array(
				'post_type'     => 'wp_spp_group',
				'wp-spp-notice' => 'published_posts',
				'spp_total'     => $published_posts
			), admin_url( 'edit.php' ) );

			wp_safe_redirect( $redirect );
			exit;
		}
	} // publish_posts

	/**
     * When a grouped post is published, publish all other posts in the group.
     *
     * @since   1.0
     * @param	string  	$new_status		New post status.
	 * @param	string  	$old_status		Old post status.
	 * @param	WP_Post		$post       	Post object.
     * @return  void
     */
    public function publish_group_posts( $new_status, $old_status, $post )  {

		// Bail if new status is not publish, or the old status was publish
        if ( 'publish' !== $new_status || 'publish' === $old_status )	{
			return;
		}

		// Bail if the post type is not enabled, or old status could not be grouped
		if ( ! in_array( $post->post_type, wp_spp_group_post_types() ) || ! in_array( $old_status, wp_spp_group_post_statuses() ) )	{
			return;
		}

		// Bail if this post is not part of a group
		$post_group = wp_spp_get_post_sync_group( $post->ID );
		if ( ! $post_group )	{
			return;
		}

		wp_spp_remove_post_from_sync_group( $post->ID );

        // Increment published post count
        wp_spp_increase_published_posts_count( 1 );

		// Stop looping when publishing posts within the group
		remove_action( 'transition_post_status', array( self::$instance, 'publish_group_posts' ), 10, 3 );

		wp_spp_publish_group_posts( $post_group );

		// Re-hook this action
		add_action( 'transition_post_status', array( self::$instance, 'publish_group_posts' ), 10, 3 );

    } // publish_group_posts

	/**
	 * Removes a post from the group
	 *
	 * @since	1.0
	 * @return	void
	 */
	 public function remove_post_from_group()	{
		 if ( isset( $_GET['wp_spp_action'] ) && 'remove_post' == sanitize_text_field( $_GET['wp_spp_action'] ) )	{

			if ( ! isset( $_GET['spp_nonce']) || ! wp_verify_nonce($_GET['spp_nonce'], 'spp-remove-post') )	{
				wp_die(
					__( 'You do not have permissions to perform this action.', 'synchronized-post-publisher' ),
					__( 'Security Error', 'synchronized-post-publisher' )
				);
			}

			 $group_id = absint( $_GET['spp_group_id'] );
			 $post_id  = absint( $_GET['spp_post_id'] );

			if ( ! empty( $post_id ) )	{

				if ( wp_spp_remove_post_from_sync_group( $post_id ) )	{

					$redirect = add_query_arg( array(
						'post'          => $group_id,
						'action'        => 'edit',
						'wp-spp-notice' => 'removed'
					), admin_url( 'post.php' ) );

					wp_safe_redirect( $redirect );
					exit;

				}

			}

		 }
	 } // remove_post_from_group

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
