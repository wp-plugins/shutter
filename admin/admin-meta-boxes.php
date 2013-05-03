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
		add_meta_box(
			'shutter-product-images',
			__( 'Gallery Photos', 'wpshutter' ),
			array( &$this, 'shutter_product_images_box' ),
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
	
	function shutter_product_images_box() {
		global $post;
		?>
		<div id="product_images_container">
			<ul class="product_images">
				<?php
					if ( metadata_exists( 'post', $post->ID, '_shutter_image_gallery' ) ) {
						$product_image_gallery = get_post_meta( $post->ID, '_shutter_image_gallery', true );
					} else {
						// Backwards compat
						$attachment_ids = array_filter( array_diff( get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids' ), array( get_post_thumbnail_id() ) ) );
						$product_image_gallery = implode( ',', $attachment_ids );
					}

					$attachments = array_filter( explode( ',', $product_image_gallery ) );

					if ( $attachments )
						foreach ( $attachments as $attachment_id ) {
							echo '<li class="image" data-attachment_id="' . $attachment_id . '">
								' . wp_get_attachment_image( $attachment_id, 'thumbnail' ) . '
								<ul class="actions">
									<li><a href="#" class="delete" title="' . __( 'Delete image', 'wpshutter' ) . '">' . __( 'Delete', 'wpshutter' ) . '</a></li>
								</ul>
							</li>';
						}
				?>
			</ul>

			<input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( $product_image_gallery ); ?>" />

		</div>
		<p class="add_product_images hide-if-no-js">
			<a href="#"><?php _e( 'Add and Edit Photos', 'wpshutter' ); ?></a>
		</p>
		<script type="text/javascript">
			jQuery(document).ready(function($){

				// Uploading files
				var product_gallery_frame;
				var $image_gallery_ids = $('#product_image_gallery');
				var $product_images = $('#product_images_container ul.product_images');

				jQuery('.add_product_images').on( 'click', 'a', function( event ) {

					var $el = $(this);
					var attachment_ids = $image_gallery_ids.val();

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( product_gallery_frame ) {
						product_gallery_frame.open();
						return;
					}

					// Create the media frame.
					product_gallery_frame = wp.media.frames.downloadable_file = wp.media({
						// Set the title of the modal.
						title: '<?php _e( 'Add Photos to Gallery', 'wpshutter' ); ?>',
						button: {
							text: '<?php _e( 'Add to Gallery', 'shutter' ); ?>',
						},
						multiple: true
					});

					// When an image is selected, run a callback.
					product_gallery_frame.on( 'select', function() {

						var selection = product_gallery_frame.state().get('selection');

						selection.map( function( attachment ) {

							attachment = attachment.toJSON();

							if ( attachment.id ) {
								attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

								$product_images.append('\
									<li class="image" data-attachment_id="' + attachment.id + '">\
										<img src="' + attachment.url + '" />\
										<ul class="actions">\
											<li><a href="#" class="delete" title="<?php _e( 'Delete photo', 'wpshutter' ); ?>"><?php _e( 'Delete', 'wpshutter' ); ?></a></li>\
										</ul>\
									</li>');
							}

						} );

						$image_gallery_ids.val( attachment_ids );
					});

					// Finally, open the modal.
					product_gallery_frame.open();
				});

				// Image ordering
				$product_images.sortable({
					items: 'li.image',
					cursor: 'move',
					scrollSensitivity:40,
					forcePlaceholderSize: true,
					forceHelperSize: false,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'shutter-metabox-sortable-placeholder',
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
					},
					update: function(event, ui) {
						var attachment_ids = '';

						$('#product_images_container ul li.image').css('cursor','default').each(function() {
							var attachment_id = jQuery(this).attr( 'data-attachment_id' );
							attachment_ids = attachment_ids + attachment_id + ',';
						});

						$image_gallery_ids.val( attachment_ids );
					}
				});

				// Remove images
				$('#product_images_container').on( 'click', 'a.delete', function() {

					$(this).closest('li.image').remove();

					var attachment_ids = '';

					$('#product_images_container ul li.image').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});

					$image_gallery_ids.val( attachment_ids );

					return false;
				} );

			});
		</script>
		<?php
	}

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
	  
	  // Gallery Images
	  $attachment_ids = array_filter( explode( ',', sanitize_text_field( $_POST['product_image_gallery'] ) ) );
	  update_post_meta( $post_id, '_shutter_image_gallery', implode( ',', $attachment_ids ) );

	}

}