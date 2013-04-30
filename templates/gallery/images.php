<?php
/**
 * Single Gallery Images
 */
?>

<?php global $post, $wpshutter; ?>

<?php do_action( 'shutter_before_gallery' ); ?>

<ul class="shutter-gallery">
	<?php
	

	if ( metadata_exists( 'post', $post->ID, '_shutter_image_gallery' ) ) {
		
		$product_image_gallery = get_post_meta( $post->ID, '_shutter_image_gallery', true );
		
	} else {

		$attachments = get_posts( array(
			'post_type' 	=> 'attachment',
			'numberposts' 	=> -1,
			'post_status' 	=> null,
			'post_parent' 	=> $post->ID,
			'post_mime_type'=> 'image',
			'orderby'		=> 'menu_order',
			'order'			=> 'ASC',
			'fields'		=> 'ids'
		) );
			
		$product_image_gallery = implode( ',', $attachments );
	}
	
	$attachments = array_filter( explode( ',', $product_image_gallery ) );

	if ($attachments) {
		
		$loop = 0;
		$columns = apply_filters( 'shutter_gallery_thumbnails_columns', 3 );
		
		foreach ( $attachments as $key => $attachment_id ) {
			
			$attachment = get_post($attachment_id);
			
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
			
			do_action( 'shutter_after_gallery_image', $attachment );
			
			echo '</li>';
			
			$loop++;

		}
		
	}
?>
</ul>
<div style="clear: both;"></div>

<?php do_action( 'shutter_after_gallery' ); ?>