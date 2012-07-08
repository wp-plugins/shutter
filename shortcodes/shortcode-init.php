<?php
/**
 * Shortcodes
 */

 // Single Gallery
function shutter_gallery_shortcode($atts){
	
	global $shutter_attachments;
	
  	if (empty($atts)) return;
  
	extract(shortcode_atts(array(
		'name'		=> null,
		'columns' 	=> '4',
	  	'orderby'   => 'title',
	  	'order'     => 'asc'
		), $atts));
		
	if ( !$gallery = get_page_by_title( $name, 'OBJECT', 'wps-gallery' ) ) return;
	
	$shutter_attachments = get_posts( array(
		'post_type' 	=> 'attachment',
		'numberposts' 	=> -1,
		'post_status' 	=> null,
		'post_parent' 	=> $gallery->ID,
		'post_mime_type'=> 'image',
		'orderby'		=> 'menu_order',
		'order'			=> 'ASC'
	) );
	
  	ob_start();
	
	shutter_get_template_part( 'loop', 'gallery' );
	
	return ob_get_clean();
	
}
add_shortcode('shutter_gallery', 'shutter_gallery_shortcode');