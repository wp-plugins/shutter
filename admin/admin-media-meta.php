<?php

/**
 * Add custom fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function shutter_attachment_custom_fields( $form_fields, $post ) {
	
	// Only add for Shutter galleries
	if ( get_post_type( $post->post_parent ) == 'wps-gallery' ) {
	
		$form_fields['shutter-photographer-name'] = array(
			'label' => __( 'Photographer', 'wpshutter' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'shutter_photographer_name', true ),
			'helps' => __( '', 'wpshutter' ),
		);
	
		$form_fields['shutter-photographer-url'] = array(
			'label' => __( 'URL', 'wpshutter' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'shutter_photographer_url', true ),
			'helps' => __( '', 'wpshutter' ),
		);
		
	}
	
	return $form_fields;
	
}

add_filter( 'attachment_fields_to_edit', 'shutter_attachment_custom_fields', 10, 2 );

/**
 * Save values in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function shutter_attachment_custom_fields_save( $post, $attachment ) {
	
	if ( isset( $attachment['shutter-photographer-name'] ) )
		update_post_meta( $post['ID'], 'shutter_photographer_name', $attachment['shutter-photographer-name'] );
		
	if ( isset( $attachment['shutter-photographer-url'] ) )
		update_post_meta( $post['ID'], 'shutter_photographer_url', $attachment['shutter-photographer-url'] );
	
	return $post;
}

add_filter( 'attachment_fields_to_save', 'shutter_attachment_custom_fields_save', 10, 2 );