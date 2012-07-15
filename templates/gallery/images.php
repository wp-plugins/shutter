<?php
/**
 * Single Gallery Images
 */
?>

<?php global $post, $wpshutter; ?>

<?php do_action( 'shutter_before_gallery' ); ?>

<ul class="shutter-gallery">
	<?php	
	$attachments = get_posts( array(
		'post_type' 	=> 'attachment',
		'numberposts' 	=> -1,
		'post_status' 	=> null,
		'post_parent' 	=> $post->ID,
		'post_mime_type'=> 'image',
		'orderby'		=> 'menu_order',
		'order'			=> 'ASC'
	) );
		
	if ($attachments) {
		
		$loop = 0;
		$columns = apply_filters( 'shutter_gallery_thumbnails_columns', 3 );
		
		foreach ( $attachments as $key => $attachment ) {
			
			$classes = array( 'shutterbox' );
			$order = '';
			
			if ( $loop == 0 || $loop % $columns == 0 ) 
				$order = ' first';
			
			if ( ( $loop + 1 ) % $columns == 0 ) 
				$order = ' last';
			
			echo '<li class="gallery-image'.$order.'" >';
			
			do_action( 'shutter_before_gallery_image', $attachment );
			
			$attachment_url = wp_get_attachment_image_src( $attachment->ID, 'shutter-gallery-lightbox' );
			printf( '<a href="%s" title="%s" rel="thumbnails" class="%s">%s</a>', $attachment_url[0], esc_attr( $attachment->post_title ), implode(' ', $classes), wp_get_attachment_image( $attachment->ID, apply_filters( 'gallery_thumbnail_size', 'shutter-gallery-thumb' ) ) );
			
			do_action( 'shutter_after_gallery_image', $attachment );
			
			echo '</li>';
			
			$loop++;

		}
		
	}
?>
</ul>
<div style="clear: both;"></div>

<?php do_action( 'shutter_after_gallery' ); ?>