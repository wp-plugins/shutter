<?php
/*
Plugin Name: Shutter
Plugin URI: http://wpshutter.com
Description: A WordPress Plugin Specifically for Photographers.
Version: 1.2.1
Author: http://wpshutter.com
Author URI: http://wpshutter.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !defined( 'ABSPATH' ) ) exit; // Lock It Down

if ( !class_exists( 'WPShutter' ) ) :

	/**
	 * Main Shutter Class
	 *
	 * @since Shutter 0.1
	 */
	class WPShutter {

		// Version
		var $version = '1.2.1';

		// URLS
		var $plugin_url;
		var $plugin_path;
		var $template_url;

		// Errors & Messages
		var $errors = array(); // Stores Errors
		var $messages = array(); // Stores Messages

		// Body Classes
		private $_body_classes = array();

		/**
		 * Shutter Constructor
		 */
		function __construct() {

			// PHP Session
			if ( ! session_id() ) session_start();

			// Define Version
			define( 'WPSHUTTER_VERSION', $this->version );

			// Require Files
			$this->includes();

			// Install
			if ( is_admin() && !defined('DOING_AJAX') ) $this->install();

			// Actions
			add_action( 'init', array( &$this, 'init' ), 0 );
			add_action( 'init', array( &$this, 'include_template_functions' ), 25 );
			add_action( 'after_setup_theme', array( &$this, 'compatibility' ) );

			// We Are Loaded
			do_action( 'wpshutter_loaded' );
		}

		/**
		 * Required Files
		 */
		function includes() {

			// Backend Only
			if ( is_admin() ) $this->admin_includes();

			// Frontend Only
			if ( ! is_admin() || defined('DOING_AJAX') ) $this->frontend_includes();

			// Core Shutter Functions
			include( 'shutter-core-functions.php' );

			// PressTrends Tracking
			include ( 'presstrends.php' );

		}

		/**
		 * Backend Only
		 */
		function admin_includes() {
			include( 'admin/admin-init.php' );
			include( 'admin/rebuild-images.php' );
		}

		/**
		 * Frontend Only
		 */
		function frontend_includes() {

			include( 'shutter-hooks.php' );
			include( 'shortcodes/shortcode-init.php' );

		}

		/**
		 * Template Functions
		 */
		function include_template_functions() {
			include( 'template.php' );
		}

		/**
		 * Install
		 */
		function install() {
			// register_activation_hook( __FILE__, 'activate_wpshutter' );
			// register_activation_hook( __FILE__, 'flush_rewrite_rules' );
			if ( get_option('wpshutter_db_version') != $this->version )
				add_action( 'init', 'install_wpshutter', 1 );
		}

		/**
		 * Initialize
		 */
		function init() {

			$this->load_plugin_textdomain(); // Localization

			// Template URL
			$this->template_url	= apply_filters( 'wpshutter_template_url', 'shutter/' );

			// Frontend Only
			if ( ! is_admin() || defined('DOING_AJAX') ) {

				// Load Messages
				$this->load_messages();

				// Hooks
				add_filter( 'template_include', array(&$this, 'template_loader') );
				add_filter( 'wp_redirect', array(&$this, 'redirect'), 1, 2 );
				add_action( 'wp_enqueue_scripts', array(&$this, 'frontend_scripts') );
				add_action( 'wp_head', array(&$this, 'generator') );
				add_action( 'wp_head', array(&$this, 'wp_head') );
				add_filter( 'body_class', array(&$this, 'output_body_class') );
			}

			// Register Globals
			$this->register_globals();

			// User Roles
			$this->init_user_roles();

			// Taxonomies
			$this->init_taxonomy();

			// Image Sizes
			$this->init_image_sizes();

			// Styles
			if ( ! is_admin() ) $this->init_styles();
			if ( is_admin() ) $this->init_admin_styles();

			do_action( 'wpshutter_init' );
		}

		/**
		 * Localization
		 */
		function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'wpshutter' );
			load_plugin_textdomain( 'wpshutter', false, dirname( plugin_basename( __FILE__ ) ).'/languages' );
		}

		/**
		 * Template Loader
		 */
		function template_loader( $template ) {

			do_action( 'shutter_template_loader_before' );

			$find 	= array( 'shutter.php' );
			$file 	= '';

			if ( is_single() && get_post_type() == 'wps-gallery' ) {

				$file 	= 'single-wps-gallery.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;

			}

			if ( $file ) {
				$template = locate_template( $find );
				if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
			}

			return $template;
		}

		/**
		 * Register Globals
		 */
		function register_globals() {
		}

		/**
		 * Add Theme Compatibility
		 */
		function compatibility() {

			// Post Thumbnail Support
			if ( ! current_theme_supports( 'post-thumbnails' ) ) :
				add_theme_support( 'post-thumbnails' );
				remove_post_type_support( 'post', 'thumbnail' );
				remove_post_type_support( 'page', 'thumbnail' );
			else :
				add_post_type_support( 'wps-gallery', 'thumbnail' );
			endif;

			// IIS
			if ( ! isset($_SERVER['REQUEST_URI'] ) ) {
				$_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 1 );
				if ( isset( $_SERVER['QUERY_STRING'] ) )
					$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
			}

			// NGINX Proxy
			if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && isset( $_SERVER['HTTP_REMOTE_ADDR'] ) )
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_REMOTE_ADDR'];
			if ( ! isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTP_HTTPS'] ) )
				$_SERVER['HTTPS'] = $_SERVER['HTTP_HTTPS'];

		}

		/**
		 * Output Shutter Details
		 */
		function generator() {
			echo "\n\n" . '<!-- WPShutter Version -->' . "\n" . '<meta name="generator" content="WPShutter ' . $this->version . '" />' . "\n\n";
		}

		/**
		 * Add Body Classes
		 */
		function wp_head() {
			$theme_name = ( function_exists( 'wp_get_theme' ) ) ? wp_get_theme() : get_current_theme();
			$this->add_body_class( "theme-{$theme_name}" );
			// if ( is_wpshutter() ) $this->add_body_class('wpshutter');
		}

		/**
		 * User Roles
		 */
		function init_user_roles() {
			global $wp_roles;

			if ( class_exists('WP_Roles') ) if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

			if ( is_object($wp_roles) ) {

				// Gallery Viewer
				add_role( 'gallery_viewer', __('Gallery Viewer', 'wpshutter'), array(
				    'read' 						=> true,
				    'edit_posts' 				=> false,
				    'delete_posts' 				=> false
				) );

				// Gallery Manager
				add_role( 'gallery_manager', __('Gallery Manager', 'wpshutter'), array(
				    'read' 						=> true,
				    'read_private_pages'		=> true,
				    'read_private_posts'		=> true,
				    'edit_posts' 				=> true,
				    'edit_pages' 				=> true,
				    'edit_published_posts'		=> true,
				    'edit_published_pages'		=> true,
				    'edit_private_pages'		=> true,
				    'edit_private_posts'		=> true,
				    'edit_others_posts' 		=> true,
				    'edit_others_pages' 		=> true,
				    'publish_posts' 			=> true,
				    'publish_pages'				=> true,
				    'delete_posts' 				=> true,
				    'delete_pages' 				=> true,
				    'delete_private_pages'		=> true,
				    'delete_private_posts'		=> true,
				    'delete_published_pages'	=> true,
				    'delete_published_posts'	=> true,
				    'delete_others_posts' 		=> true,
				    'delete_others_pages' 		=> true,
				    'manage_categories' 		=> true,
				    'manage_links'				=> true,
				    'moderate_comments'			=> true,
				    'unfiltered_html'			=> true,
				    'upload_files'				=> true,
				   	'export'					=> true,
					'import'					=> true,
					'manage_wpshutter'			=> true,
					'manage_wpshutter_galleries' => true
				) );

				// Add Gallery Capabilities for Admin
				$wp_roles->add_cap( 'administrator', 'manage_wpshutter' );
				$wp_roles->add_cap( 'administrator', 'manage_wpshutter_galleries' );
			}
		}

		/**
		 * Taxonomies
		 */
		function init_taxonomy() {

			if ( post_type_exists('wps-gallery') ) return;

			register_post_type( 'wps-gallery',
				array(
					'labels' => array(
							'menu_name'				=> __( 'Shutter', 'wpshutter' ),
							'all_items'				=> __( 'Galleries', 'wpshutter' ),
							'name' 					=> __( 'Galleries', 'wpshutter' ),
							'singular_name' 		=> __( 'Gallery', 'wpshutter' ),
							'add_new' 				=> __( 'Add Gallery', 'wpshutter' ),
							'add_new_item' 			=> __( 'Add New Gallery', 'wpshutter' ),
							'edit' 					=> __( 'Edit', 'wpshutter' ),
							'edit_item' 			=> __( 'Edit Gallery', 'wpshutter' ),
							'new_item' 				=> __( 'New Gallery', 'wpshutter' ),
							'view' 					=> __( 'View Gallery', 'wpshutter' ),
							'view_item' 			=> __( 'View Gallery', 'wpshutter' ),
							'search_items' 			=> __( 'Search Galleries', 'wpshutter' ),
							'not_found' 			=> __( 'No Galleries found', 'wpshutter' ),
							'not_found_in_trash' 	=> __( 'No Galleries found in trash', 'wpshutter' ),
							'parent' 				=> __( 'Parent Gallery', 'wpshutter' )
						),
					'description' 			=> __( 'This is where you can add new galleries with photos.', 'wpshutter' ),
					'public' 				=> true,
					'menu_icon'				=> $this->plugin_url() . '/images/shutter-icon16x16.png',
					'show_ui' 				=> true,
					'capability_type' 		=> 'post',
					'capabilities' => array(
						'publish_posts' 		=> 'manage_wpshutter_galleries',
						'edit_posts' 			=> 'manage_wpshutter_galleries',
						'edit_others_posts' 	=> 'manage_wpshutter_galleries',
						'delete_posts' 			=> 'manage_wpshutter_galleries',
						'delete_others_posts'	=> 'manage_wpshutter_galleries',
						'read_private_posts'	=> 'manage_wpshutter_galleries',
						'edit_post' 			=> 'manage_wpshutter_galleries',
						'delete_post' 			=> 'manage_wpshutter_galleries',
						'read_post' 			=> 'manage_wpshutter_galleries'
					),
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'hierarchical' 			=> false, // Hierarcal causes memory issues - WP loads all records!
					'rewrite' 				=> array( 'slug' => 'gallery', 'with_front' => false, 'feeds' => 'shutter' ),
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor', 'thumbnail' ),
					'has_archive' 			=> 'shutter',
					'show_in_nav_menus' 	=> true
				)
			);

		}

		/**
		 * Additional Image Sizes
		 */
		function init_image_sizes() {

			$shutter_general_settings = get_option('shutter_general_settings');

			// Gallery Thumbnail
			$shutter_gallery_thumb_width = ( isset($shutter_general_settings['shutter_gallery_thumb_width']) ) ? $shutter_general_settings['shutter_gallery_thumb_width'] : 300;
			$shutter_gallery_thumb_height = ( isset($shutter_general_settings['shutter_gallery_thumb_height']) ) ? $shutter_general_settings['shutter_gallery_thumb_height'] : 300;
			$shutter_gallery_thumb_crop = ( isset($shutter_general_settings['shutter_gallery_thumb_crop']) && $shutter_general_settings['shutter_gallery_thumb_crop'] == '1' ) ? true : false;

			// Gallery Lightbox
			$shutter_gallery_lightbox_width = ( isset($shutter_general_settings['shutter_gallery_lightbox_width']) ) ? $shutter_general_settings['shutter_gallery_lightbox_width'] : 600;
			$shutter_gallery_lightbox_height = ( isset($shutter_general_settings['shutter_gallery_lightbox_height']) ) ? $shutter_general_settings['shutter_gallery_lightbox_height'] : 9999;
			$shutter_gallery_lightbox_crop = ( isset($shutter_general_settings['shutter_gallery_lightbox_crop']) && $shutter_general_settings['shutter_gallery_lightbox_crop'] == '1' ) ? true : false;


			add_image_size( 'shutter-gallery-thumb', $shutter_gallery_thumb_width, $shutter_gallery_thumb_height, $shutter_gallery_thumb_crop );
			add_image_size( 'shutter-gallery-lightbox', $shutter_gallery_lightbox_width, $shutter_gallery_lightbox_height, $shutter_gallery_lightbox_crop );

			// Custom Column Thumb
			add_image_size( 'shutter-custom-column-thumb', 100, 100, true );

		}

		/**
		 * Frontend CSS
		 */
		function init_styles() {

			$shutter_general_settings = get_option('shutter_general_settings');

			// CSS
			$css = ( isset($shutter_general_settings['shutter_disable_css']) && $shutter_general_settings['shutter_disable_css'] == '1' ) ? false : true;

			if ( ( defined('WPSHUTTER_USE_CSS') && WPSHUTTER_USE_CSS ) || ( ! defined('WPSHUTTER_USE_CSS') && $css ) ) :
				$css = file_exists( get_stylesheet_directory() . '/shutter/style.css' ) ? get_stylesheet_directory_uri() . '/shutter/style.css' : $this->plugin_url() . '/css/style.css';
				wp_register_style( 'shutter_frontend_styles', $css );
				wp_enqueue_style( 'shutter_frontend_styles' );
			endif;

			// Lightbox
			$lightbox = ( isset($shutter_general_settings['shutter_disable_lightbox']) && $shutter_general_settings['shutter_disable_lightbox'] == '1' ) ? false : true;
			if ( $lightbox )
				wp_enqueue_style( 'shutter_fancybox_styles', $this->plugin_url() . '/tools/fancybox/jquery.fancybox.css' );

		}

		/**
		 * Frontend Scripts
		 */
		function frontend_scripts() {

			$shutter_general_settings = get_option('shutter_general_settings');

			$scripts_position = ( isset($shutter_general_settings['shutter_javascript_position']) && $shutter_general_settings['shutter_javascript_position'] == '1' ) ? true : false;

			// Lightbox
			$lightbox = ( isset($shutter_general_settings['shutter_disable_lightbox']) && $shutter_general_settings['shutter_disable_lightbox'] == '1' ) ? false : true;

			if ( $lightbox ) :

				$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
				wp_enqueue_script( 'shutter', $this->plugin_url() . '/js/global'.$suffix.'.js', array('jquery'), '1.0', $scripts_position );
				wp_enqueue_script( 'fancybox', $this->plugin_url() . '/tools/fancybox/jquery.fancybox.js', array('jquery'), '1.0', $scripts_position );

			endif;
		}

		// Admin Styles
		function init_admin_styles() {
			wp_register_style( 'shutter_admin_styles', $this->plugin_url() . '/admin/css/style.css' );
			wp_enqueue_style( 'shutter_admin_styles' );
		}

		/**
		 * Logging
		 */
		function logger() {
		}

		/**
		 * Validation
		 */
		function validation() {
		}

		/**
		 * Mail
		 */
		function mailer() {
		}

		// Helper Functions

		/**
		 * Plugin URL
		 */
		function plugin_url() {
			if ( $this->plugin_url ) return $this->plugin_url;
			return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}

		/**
		 * Plugin Path
		 */
		function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;

			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * AJAX URL
		 */
		function ajax_url() {
			return str_replace( array('https:', 'http:'), '', admin_url( 'admin-ajax.php' ) );
		}

		/**
		 * SSL URL
		 */
		function force_ssl( $content ) {
			if ( is_ssl() ) {
				if ( is_array($content) )
					$content = array_map( array( &$this, 'force_ssl' ) , $content );
				else
					$content = str_replace( 'http:', 'https:', $content );
			}
			return $content;
		}

		// Messages

		/**
		 * Load Messages
		 */
		function load_messages() {

			if ( isset( $_SESSION['errors'] ) ) $this->errors = $_SESSION['errors'];
			if ( isset( $_SESSION['messages'] ) ) $this->messages = $_SESSION['messages'];

			unset( $_SESSION['messages'] );
			unset( $_SESSION['errors'] );

			// Load Errors from Query String
			if ( isset( $_GET['wps_error'] ) ) {
				$this->add_error( esc_attr( $_GET['wps_error'] ) );
			}

		}

		/**
		 * Add an Error
		 */
		function add_error( $error ) { $this->errors[] = $error; }

		/**
		 * Add a Message
		 */
		function add_message( $message ) { $this->messages[] = $message; }

		/**
		 * Clear Messages and Session
		 */
		function clear_messages() {
			$this->errors = $this->messages = array();
			unset( $_SESSION['messages'], $_SESSION['errors'] );
		}

		/**
		 * Get Error Count
		 */
		function error_count() { return sizeof($this->errors); }

		/**
		 * Get Message Count
		 */
		function message_count() { return sizeof($this->messages); }

		/**
		 * Get Errors
		 */
		function get_errors() { return (array) $this->errors; }

		/**
		 * Get Messages
		 */
		function get_messages() { return (array) $this->messages; }

		/**
		 * Output Errors and Messages
		 */
		function show_messages() {}

		/**
		 * Set Session Data for Messages
		 */
		function set_messages() {
			$_SESSION['errors'] = $this->errors;
			$_SESSION['messages'] = $this->messages;
		}

		/**
		 * Redirection Hook for Message Session Data
		 */
		function redirect( $location, $status ) {
			global $is_IIS;

			$this->set_messages();

			// IIS fix
			if ( $is_IIS ) session_write_close();

			return apply_filters( 'shutter_redirect', $location );
		}

		// Nonces

		/**
		 * Return a Nonce Field
		 */
		function nonce_field ( $action, $referer = true , $echo = true ) { return wp_nonce_field('wpshutter-' . $action, '_n', $referer, $echo ); }

		/**
		 * Return a url with a nonce appended
		 */
		function nonce_url ( $action, $url = '' ) { return add_query_arg( '_n', wp_create_nonce( 'wpshutter-' . $action ), $url ); }

		/**
		 * Check a nonce and sets shutter error in case it is invalid
		 * To fail silently, set the error_message to an empty string
		 *
		 * @param 	string $name the nonce name
		 * @param	string $action then nonce action
		 * @param   string $method the http request method _POST, _GET or _REQUEST
		 * @param   string $error_message custom error message, or false for default message, or an empty string to fail silently
		 *
		 * @return   bool
		 */
		function verify_nonce( $action, $method='_POST', $error_message = false ) {

			$name = '_n';
			$action = 'wpshutter-' . $action;

			if ( $error_message === false ) $error_message = __('Action failed. Please refresh the page and retry.', 'wpshutter');

			if ( ! in_array( $method, array( '_GET', '_POST', '_REQUEST' ) ) ) $method = '_POST';

			if ( isset($_REQUEST[$name] ) && wp_verify_nonce( $_REQUEST[$name], $action ) ) return true;

			if ( $error_message ) $this->add_error( $error_message );

			return false;
		}

		// Shortcodes

		/**
		 * Shortcode Wrapper
		 */
		function shortcode_wrapper( $function, $atts = array() ) {
			ob_start();
			call_user_func( $function, $atts );
			return ob_get_clean();
		}

		// Cache

		/**
		 * Prevent plugins from caching a page.
		 */
		function nocache() {
			if ( ! defined('DONOTCACHEPAGE') ) define('DONOTCACHEPAGE', 'true');
		}

		// Transients

		/**
		 * Clear Gallery Transients
		 */
		function clear_wpshutter_transients( $post_id = 0 ) {
			delete_transient('wpshutter');
			wp_cache_flush();
		}

		/**
		 * Add Body Class
		 */
		function add_body_class( $class ) {
			$this->_body_classes[] = sanitize_html_class( strtolower($class) );
		}

		/**
		 * Output Body Class
		 */
		function output_body_class( $classes ) {
			if ( sizeof( $this->_body_classes ) > 0 ) $classes = array_merge( $classes, $this->_body_classes );

			if ( is_singular('wps-gallery') ) {
				$key = array_search( 'singular', $classes );
				if ( $key !== false ) unset( $classes[$key] );
			}

			return $classes;
		}

	}

	$GLOBALS['wpshutter'] = new WPShutter(); // All Systems Go!

endif;