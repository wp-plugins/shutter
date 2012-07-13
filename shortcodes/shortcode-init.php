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

// All Galleries
function shutter_galleries_shortcode($atts) {
	
	global $post;
	
	extract(shortcode_atts(array(
		'title'		=> "true"
		), $atts));
	
 	$args = array(
		'post_type' => 'wps-gallery',
		'posts_per_page' => '-1'
 	);
	
  	ob_start();
	
	$the_query = new WP_Query( apply_filters( 'shutter_galleries_shortcode_args', $args ) ); ?>
	
	<ul class="shutter-gallery">
		
	<?php
	
	$loop = 0;
	$columns = apply_filters( 'shutter_gallery_thumbnails_columns', 3 );
	
	while ( $the_query->have_posts() ) : $the_query->the_post();
			
		$classes = array( '' );
		$order = '';
			
		if ( $loop == 0 || $loop % $columns == 0 ) 
			$order = ' first';
			
		if ( ( $loop + 1 ) % $columns == 0 ) 
			$order = ' last';
			
		echo '<li class="gallery-image'.$order.'" >';
			
		printf( '<a href="%s" title="%s" rel="thumbnails" class="%s">%s</a>', esc_attr( get_permalink() ), esc_attr( get_the_title() ), implode(' ', $classes), wp_get_attachment_image( get_post_thumbnail_id($post->ID), apply_filters( 'gallery_thumbnail_size', 'shutter-gallery-thumb' ) ) );
		
		if ( $title == "true" ) :
			echo '<a href="'.get_permalink().'" title="'.get_the_title().'">';
			echo '<h3>' . get_the_title() . '</h3>';
			echo '</a>';
		endif;
			
		echo '</li>';
			
		$loop++;

	?>

	<?php endwhile; ?>
	
	</ul>
	
	<?php

	wp_reset_postdata();
	
	return ob_get_clean();
	
}
add_shortcode('shutter_galleries', 'shutter_galleries_shortcode');