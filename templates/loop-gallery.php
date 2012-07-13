<?php
/**
 * Single Gallery Loop
 */
?>

<?php global $shutter_attachments; ?>

<ul class="shutter-gallery">
	<?php
		
	if ($shutter_attachments) {
		
		$loop = 0;
		$columns = apply_filters( 'shutter_gallery_thumbnails_columns', 3 );
		
		foreach ( $shutter_attachments as $key => $attachment ) {
			
			$classes = array( 'shutterbox' );
			$order = '';
			
			if ( $loop == 0 || $loop % $columns == 0 ) 
				$order = ' first';
			
			if ( ( $loop + 1 ) % $columns == 0 ) 
				$order = ' last';
			
			echo '<li class="gallery-image'.$order.'" >';
			
			$attachment_url = wp_get_attachment_image_src( $attachment->ID, 'shutter-gallery-lightbox' );
			printf( '<a href="%s" title="%s" rel="thumbnails" class="%s">%s</a>', $attachment_url[0], esc_attr( $attachment->post_title ), implode(' ', $classes), wp_get_attachment_image( $attachment->ID, apply_filters( 'gallery_thumbnail_size', 'shutter-gallery-thumb' ) ) );
			
			echo '</li>';
			
			$loop++;

		}
		
	}
?>
</ul>
<div style="clear: both;"></div>