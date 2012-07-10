<?php
/**
 * Shutter Template Functions
 *
 * Functions used in the template files to output content - in most cases hooked in via the template actions. All functions are pluggable.
 *
 */
 
/**
 * Content Wrapper
 */
if ( ! function_exists( 'shutter_output_content_wrapper' ) ) {
	function shutter_output_content_wrapper() {
		shutter_get_template( 'gallery/wrapper-start.php' );
	}
}
if ( ! function_exists( 'shutter_output_content_wrapper_end' ) ) {
	function shutter_output_content_wrapper_end() {
		shutter_get_template( 'gallery/wrapper-end.php' );
	}
}

/**
 * Sidebar
 **/
if ( ! function_exists( 'shutter_get_sidebar' ) ) {
	function shutter_get_sidebar() {
		shutter_get_template( 'gallery/sidebar.php' );
	}
}

// Prevent Cache
if ( ! function_exists( 'shutter_prevent_sidebar_cache' ) ) {
	function shutter_prevent_sidebar_cache( $sidebar  ) {
		echo '<!--mfunc get_sidebar( "' . $sidebar . '" ) --><!--/mfunc-->';
	}
}

/**
 * Single Gallery
 */
if ( ! function_exists( 'shutter_single_gallery_content' ) ) {
	function gallery_single_content() {
		global $wp_query;
		if ( $wp_query->have_posts() ) while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
			<?php do_action( 'shutter_before_single_gallery' ); ?>
			<?php the_content(); ?>
			<?php do_action( 'shutter_after_single_gallery' ); ?>
		<?php endwhile;
	}
}

if ( ! function_exists( 'shutter_template_single_title' ) ) {
	function shutter_template_single_title() {
		shutter_get_template( 'gallery/title.php' );
	}
}

if ( ! function_exists( 'shutter_show_gallery' ) ) {
	function shutter_show_gallery() {
		shutter_get_template( 'gallery/images.php' );
	}
}