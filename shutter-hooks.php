<?php
/**
 * Shutter Hooks
 *
 * Action/filter hooks used for Shutter function and templates
 *
 */
 
if ( !is_admin() || defined('DOING_AJAX') ) :

// Wrappers
add_action( 'gallery_before_main_content', 'shutter_output_content_wrapper', 10);
add_action( 'gallery_after_main_content', 'shutter_output_content_wrapper_end', 10);

/* Sidebar */
add_action( 'get_sidebar', 'shutter_prevent_sidebar_cache' );
add_action( 'shutter_sidebar', 'shutter_get_sidebar', 10);

// Single Gallery
add_action( 'shutter_single_gallery_content_inner', 'shutter_do_single_gallery_content_inner' );
add_action( 'shutter_before_single_gallery', 'shutter_template_single_title', 5);
add_action( 'shutter_before_single_gallery', 'shutter_show_gallery', 20);

endif;