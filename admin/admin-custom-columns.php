<?php

function shutter_gallery_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb'] = $columns['cb'];
	$new_columns['gallery-thumb'] = __('Image', 'woocommerce');
	unset($columns['cb']);
	$columns = array_merge( $new_columns, $columns );
	return $columns;
}
add_filter('manage_wps-gallery_posts_columns', 'shutter_gallery_columns');
 
function shutter_gallery_column( $column ) {
	
	global $wpshutter;
	global $post;
	
	if ( $column == 'gallery-thumb') :
		
		$edit_link = get_edit_post_link( $post->ID );
    	
    	if ( has_post_thumbnail($post->ID) ) :
				
			echo '<a class="row-title" href="'.$edit_link.'">'.get_the_post_thumbnail($post->ID, 'shutter-custom-column-thumb').'</a>';
				
		else :
				
			echo '<a class="row-title" href="'.$edit_link.'"><img src="'.$wpshutter->plugin_url().'/images/placeholder.png" alt="Placeholder" width="100" height="100" /></a>';
				
		endif;
 	
	endif;
 	
	return $column;	
}
add_filter('manage_wps-gallery_posts_custom_column', 'shutter_gallery_column');