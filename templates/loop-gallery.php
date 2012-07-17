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
			
			// Title
			$show_attachment_title = get_post_meta( $attachment->post_parent, '_shutter_gallery_image_title', true );
			if ( $show_attachment_title == '1' ) {
				$attachment_title = basename ( get_attached_file( $attachment->ID ) );
				echo '<h3>'.$attachment_title.'</h3>';
			} elseif ( $show_attachment_title == '2' ) {
				$attachment_title = $attachment->post_title;
				if ( $attachment_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) )
					$attachment_title = $attachment_alt;
				echo '<h3>'.$attachment_title.'</h3>';
			}
			
			// Hidden Attachment Meta Data
			echo '<div class="shutter-attachment-meta">';
			
			// Image Alt
			if ( $attachment_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) )
				echo '<span class="shutter-attachment-alt">'.$attachment_alt.'</span>';
			
			// Image Caption
			if ( !empty($attachment->post_excerpt) )
				echo '<span class="shutter-attachment-caption">'.$attachment->post_excerpt.'</span>';
			
			echo '</div>';
			
			echo '</li>';
			
			$loop++;

		}
		
	}
?>
</ul>
<div style="clear: both;"></div>