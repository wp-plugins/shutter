<?php

function shutter_gallery_columns( $columns ) {

	$new_columns = array();

	// Checkbox
	$new_columns['cb'] = $columns['cb'];

	// Thumbnail
	$new_columns['gallery-thumb'] = __('', 'wpshutter');

	// Title
	$new_columns['title'] = $columns['title'];

	// Count
	$new_columns['gallery-count'] = __('# Photos', 'wpshutter');

	// Hook for Add-ons
	$new_columns = apply_filters( 'shutter_gallery_columns', $new_columns );

	// Date
	$new_columns['date'] = $columns['date'];

	$columns = $new_columns;

	return $columns;
}
add_filter('manage_wps-gallery_posts_columns', 'shutter_gallery_columns');

function shutter_gallery_column( $column ) {

	global $wpshutter;
	global $post;

	// Thumbnail
	if ( $column == 'gallery-thumb' ) :

		$edit_link = get_edit_post_link( $post->ID );

    	if ( has_post_thumbnail($post->ID) ) :

			echo '<a class="row-title" href="'.$edit_link.'">'.get_the_post_thumbnail($post->ID, 'shutter-custom-column-thumb').'</a>';

		else :

			echo '<a class="row-title" href="'.$edit_link.'"><img src="'.$wpshutter->plugin_url().'/images/placeholder.png" alt="Placeholder" width="100" height="100" /></a>';

		endif;

	// Count
	elseif ( $column == 'gallery-count' ) :

		$attachment_count = count(get_children( array( 'post_parent' => $post->ID ) ));
 		echo $attachment_count;

	endif;

	return $column;
}
add_filter('manage_wps-gallery_posts_custom_column', 'shutter_gallery_column');