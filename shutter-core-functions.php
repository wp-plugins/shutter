<?php
/**
 * Core Shutter Functions
 *
 */

function shutter_get_template_part( $slug, $name = '' ) {
	global $wpshutter;
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/shutter/slug-name.php
	if ( $name )
		$template = locate_template( array ( "{$slug}-{$name}.php", "{$wpshutter->template_url}{$slug}-{$name}.php" ) );

	// Get default slug-name.php
	if ( !$template && $name && file_exists( $wpshutter->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = $wpshutter->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/shutter/slug.php
	if ( !$template )
		$template = locate_template( array ( "{$slug}.php", "{$wpshutter->template_url}{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}

function shutter_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	global $wpshutter;

	if ( $args && is_array($args) )
		extract( $args );

	$located = shutter_locate_template( $template_name, $template_path, $default_path );

	do_action( 'shutter_before_template_part', $template_name, $template_path, $located );

	include( $located );

	do_action( 'shutter_after_template_part', $template_name, $template_path, $located );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 */
function shutter_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $wpshutter;

	if ( ! $template_path ) $template_path = $wpshutter->template_url;
	if ( ! $default_path ) $default_path = $wpshutter->plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			$template_path . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('shutter_locate_template', $template, $template_name, $template_path);
}