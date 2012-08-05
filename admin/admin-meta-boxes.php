<?php
/**
 * Shutter Proofing Meta Boxes
 */

function init_shutter_meta_box() {
    return new Shutter_Meta_Box();
}
add_action( 'load-post.php', 'init_shutter_meta_box' );

class Shutter_Meta_Box {
	
    public function __construct() {
        add_action( 'add_meta_boxes', array( &$this, 'add_proofing_meta_box' ) );
		add_action( 'save_post', array( &$this, 'add_proofing_save_postdata' ) );
    }

    public function add_proofing_meta_box() {
        add_meta_box( 
            'shutter_gallery_meta_box',
			__( 'Gallery Options', 'wpshutter'),
			array( &$this, 'render_meta_box_content' ),
			'wps-gallery',
			'normal',
			'default'
        );
    }

    public function render_meta_box_content() {
		
		global $post;

		wp_nonce_field( plugin_basename( __FILE__ ), 'shutter_metabox_nonce' );
		
		// Gallery Titles
		$gallery_image_title = get_post_meta( $post->ID, '_shutter_gallery_image_title', true ); ?>
		<p>
			<label for="shutter_gallery_image_title"><?php _e( 'Image Title:', 'wpshutter' ); ?></label>
			<select name="shutter_gallery_image_title">
				<option value="0" <?php selected( $gallery_image_title, 0 ); ?>><?php _e( 'None', 'wpshutter' ); ?></option>
				<option value="1" <?php selected( $gallery_image_title, 1 ); ?>><?php _e( 'Filename', 'wpshutter' ); ?></option>
				<option value="2" <?php selected( $gallery_image_title, 2 ); ?>><?php _e( 'Title/Alt', 'wpshutter' ); ?></option>
			</select>
		</p>
		
	<?php }
	
	function add_proofing_save_postdata( $post_id ) {

	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return;

	  if ( !isset($_POST['shutter_metabox_nonce']) || !wp_verify_nonce( $_POST['shutter_metabox_nonce'], plugin_basename( __FILE__ ) ) )
	      return;
	  
	  if ( !current_user_can( 'edit_post', $post_id ) )
	        return;

	  // Gallery Type
	  $gallery_image_title = $_POST['shutter_gallery_image_title'];
	  update_post_meta( $post_id, '_shutter_gallery_image_title', $gallery_image_title );

	}
	
}