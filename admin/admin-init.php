<?php
/**
 * Shutter Settings
 *
 * Create the WPShutter_Settings Class for all Shutter options using the WordPress Settings API
 *
 */


// Install
function install_wpshutter() {
	// Silence is golden.
}

class WPShutter_Settings {
	
	private $shutter_general_settings = 'shutter_general_settings';
	private $shutter_advanced_settings = 'shutter_advanced_settings';
	private $shutter_regenerate_thumbnails = 'shutter_regenerate_thumbnails';
	private $shutter_options_key = 'shutter_options';
	private $shutter_settings_tabs = array();
	
	/*
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 * current ones speak for themselves.
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		// add_action( 'admin_init', array( &$this, 'register_advanced_settings' ) );
		add_action( 'admin_init', array( &$this, 'shutter_regenerate_thumbnails' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
	}
	
	/*
	 * Loads both the general and advanced settings from
	 * the database into their respective arrays. Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 */
	function load_settings() {
		
		// Get Options
		$this->general_settings = (array) get_option( $this->shutter_general_settings );
		$this->advanced_settings = (array) get_option( $this->shutter_advanced_settings );
		
		// Merge Default Values
		$this->general_settings = array_merge( array(
			'shutter_gallery_thumb_width' => '300',
			'shutter_gallery_thumb_height' => '300',
			'shutter_gallery_thumb_crop' => '0',
			'shutter_gallery_lightbox_width' => '600',
			'shutter_gallery_lightbox_height' => '9999',
			'shutter_gallery_lightbox_crop' => '0',
			'shutter_disable_css' => '0',
			'shutter_disable_lightbox' => '0',
			'shutter_javascript_position' => '0'
		), $this->general_settings );
		
		$this->advanced_settings = array_merge( array(
			'advanced_option' => __('Advanced value', 'wpshutter')
		), $this->advanced_settings );
	}
	
	/*
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_general_settings() {
		
		$this->shutter_settings_tabs[$this->shutter_general_settings] = __('General', 'wpshutter');
		
		register_setting( $this->shutter_general_settings, $this->shutter_general_settings );
		
		/*
		** CSS and JavaScript
		*/		
		add_settings_section( 'section_shutter_css_and_js', __('CSS & JavaScript', 'wpshutter'), array( &$this, 'section_shutter_css_and_js_desc' ), $this->shutter_general_settings );
		
		// CSS
		add_settings_field( 'shutter_css', __('CSS', 'wpshutter'), array( &$this, 'field_shutter_css' ), $this->shutter_general_settings, 'section_shutter_css_and_js' );
		
		// JS
		add_settings_field( 'shutter_js', __('JS', 'wpshutter'), array( &$this, 'field_shutter_js' ), $this->shutter_general_settings, 'section_shutter_css_and_js' );
		
		/*
		** Image Sizes
		*/		
		add_settings_section( 'section_shutter_image_sizes', __('Image Sizes', 'wpshutter'), array( &$this, 'section_shutter_image_sizes_desc' ), $this->shutter_general_settings );
		
		// Gallery Thumbnail Sizes
		add_settings_field( 'shutter_gallery_thumb', __('Gallery Thumbnail', 'wpshutter'), array( &$this, 'field_shutter_gallery_thumb' ), $this->shutter_general_settings, 'section_shutter_image_sizes' );
		
		// Gallery Lightbox Sizes
		add_settings_field( 'shutter_gallery_lightbox', __('Gallery Lightbox', 'wpshutter'), array( &$this, 'field_shutter_gallery_lightbox' ), $this->shutter_general_settings, 'section_shutter_image_sizes' );
		
	}
	
	// Section Descriptions 
	function section_shutter_image_sizes_desc() {
		_e('Note: Please rebuild your images if you change these sizes', 'wpshutter');
	}
	function section_shutter_css_and_js_desc() {
		_e('Enable or Disable Shutter CSS/JavaScript for theme and plugin compatibility', 'wpshutter');
	}
	function section_advanced_desc() { _e('Advanced section description goes here.', 'wpshutter'); }
	function section_regenerate_thumbnails_desc() { _e('', 'wpshutter'); }
	
	// CSS Field
	function field_shutter_css() {
		?>
		<input type="checkbox" <?php if ( 1 == $this->general_settings['shutter_disable_css'] ) echo 'checked="checked"'; ?> value="1" name="<?php echo $this->shutter_general_settings; ?>[shutter_disable_css]" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_disable_css]"><?php _e('Disable CSS styles', 'wpshutter'); ?></label>
		<?php
	}
	
	// JS Field
	function field_shutter_js() {
		?>
		<input type="checkbox" <?php if ( 1 == $this->general_settings['shutter_disable_lightbox'] ) echo 'checked="checked"'; ?> value="1" name="<?php echo $this->shutter_general_settings; ?>[shutter_disable_lightbox]" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_disable_lightbox]"><?php _e('Disable Lightbox', 'wpshutter'); ?></label> <br />
		<input type="checkbox" <?php if ( 1 == $this->general_settings['shutter_javascript_position'] ) echo 'checked="checked"'; ?> value="1" name="<?php echo $this->shutter_general_settings; ?>[shutter_javascript_position]" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_javascript_position]"><?php _e('Output JavaScript in the footer', 'wpshutter'); ?></label>
		<?php
	}

	// Gallery Thumb Field 
	function field_shutter_gallery_thumb() {
		?>
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_width]"><?php _e('Width', 'wpshutter'); ?></label>
		<input type="text" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_width]" value="<?php echo esc_attr( $this->general_settings['shutter_gallery_thumb_width'] ); ?>" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_height]"><?php _e('Height', 'wpshutter'); ?></label>
		<input type="text" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_height]" value="<?php echo esc_attr( $this->general_settings['shutter_gallery_thumb_height'] ); ?>" />
		<br />
		<input type="checkbox" <?php if ( 1 == $this->general_settings['shutter_gallery_thumb_crop'] ) echo 'checked="checked"'; ?> value="1" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_crop]" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_thumb_crop]"><?php _e('Hard Crop', 'wpshutter'); ?></label>
		<?php
	}
	
	// Gallery Lightbox Field
	function field_shutter_gallery_lightbox() {
		?>
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_width]"><?php _e('Width', 'wpshutter'); ?></label>
		<input type="text" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_width]" value="<?php echo esc_attr( $this->general_settings['shutter_gallery_lightbox_width'] ); ?>" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_height]"><?php _e('Height', 'wpshutter'); ?></label>
		<input type="text" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_height]" value="<?php echo esc_attr( $this->general_settings['shutter_gallery_lightbox_height'] ); ?>" />
		<br />
		<input type="checkbox" <?php if ( 1 == $this->general_settings['shutter_gallery_lightbox_crop'] ) echo 'checked="checked"'; ?> value="1" id="lightboxnail_crop" name="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_crop]" />
		<label for="<?php echo $this->shutter_general_settings; ?>[shutter_gallery_lightbox_crop]"><?php _e('Hard Crop', 'wpshutter'); ?></label>
		<?php
	}
	
	// Register Advanced Settings
	function register_advanced_settings() {
		$this->shutter_settings_tabs[$this->shutter_advanced_settings] = __('Advanced', 'wpshutter');
		register_setting( $this->shutter_advanced_settings, $this->shutter_advanced_settings );
		add_settings_section( 'section_advanced', __('Advanced Plugin Settings', 'wpshutter'), array( &$this, 'section_advanced_desc' ), $this->shutter_advanced_settings );
		add_settings_field( 'advanced_option', __('An Advanced Option', 'wpshutter'), array( &$this, 'field_advanced_option' ), $this->shutter_advanced_settings, 'section_advanced' );
	}
	
	// Advanced Settings Field
	function field_advanced_option() {
		?>
		<input type="text" name="<?php echo $this->shutter_advanced_settings; ?>[advanced_option]" value="<?php echo esc_attr( $this->advanced_settings['advanced_option'] ); ?>" />
		<?php
	}
	
	// Regenerate Gallery Thumbnails
	function shutter_regenerate_thumbnails() {
		$this->shutter_settings_tabs[$this->shutter_regenerate_thumbnails] = __('Rebuild Images', 'wpshutter');
	}
	
	function shutter_regenerate_thumbnails_content() { ?>
		
		<div id="message" class="updated fade" style="display:none"></div>
		<script type="text/javascript">
		// <![CDATA[

		function setMessage(msg) {
			jQuery("#message").html('<p>' + msg + '</p>');
			jQuery("#message").show();
		}

		function regenerate() {
			jQuery("#ajax_thumbnail_rebuild").attr("disabled", true);
			setMessage("<p><?php _e('Reading attachments...', 'wpshutter') ?></p>");

			inputs = jQuery( 'input:checked' );
			var thumbnails= '';
			if( inputs.length != jQuery( 'input[type=checkbox]' ).length ){
				inputs.each( function(){
					thumbnails += '&thumbnails[]='+jQuery(this).val();
				} );
			}

			jQuery.ajax({
				url: "<?php echo admin_url('admin-ajax.php'); ?>",
				type: "POST",
				data: "action=ajax_thumbnail_rebuild&do=getlist",
				success: function(result) {
					var list = eval(result);
					var curr = 0;

					if (!list) {
						setMessage("<?php _e('No attachments found.', 'wpshutter')?>");
						jQuery("#ajax_thumbnail_rebuild").removeAttr("disabled");
						return;
					}

					function regenItem() {
						if (curr >= list.length) {
							jQuery("#ajax_thumbnail_rebuild").removeAttr("disabled");
							setMessage("<?php _e('Done.', 'wpshutter') ?>");
							return;
						}
						setMessage(<?php printf( __('"Rebuilding " + %s + " of " + %s + " (" + %s + ")..."', 'wpshutter'), "(curr+1)", "list.length", "list[curr].title"); ?>);

						jQuery.ajax({
							url: "<?php echo admin_url('admin-ajax.php'); ?>",
							type: "POST",
							data: "action=ajax_thumbnail_rebuild&do=regen&id=" + list[curr].id + thumbnails,
							success: function(result) {
								jQuery("#thumb").show();
								jQuery("#thumb-img").attr("src",result);

								curr = curr + 1;
								regenItem();
							}
						});
					}

					regenItem();
				},
				error: function(request, status, error) {
					setMessage("<?php _e('Error', 'wpshutter') ?>" + request.status);
				}
			});
		}

		jQuery(document).ready(function() {
			jQuery('#size-toggle').click(function() {
				jQuery("#sizeselect").find("input[type=checkbox]").each(function() {
					jQuery(this).attr("checked", !jQuery(this).attr("checked"));
				});
			});
		});

		// ]]>
		</script>

		<form method="post" action="" style="display:inline; float:left; padding-right:30px;">
			
			<div style="display: none;">
			
		    <h4><?php _e('Select which thumbnails you want to rebuild', 'wpshutter'); ?>:</h4>
			<a href="javascript:void(0);" id="size-toggle"><?php _e('Toggle all', 'wpshutter'); ?></a>
			<div id="sizeselect">
			<?php
			foreach ( shutter_thumbnail_rebuild_get_sizes() as $s ):
			?>

				<input type="checkbox" name="thumbnails[]" id="sizeselect" checked="checked" value="<?php echo $s['name'] ?>" />
				<label>
					<em><?php echo $s['name'] ?></em>
					&nbsp;(<?php echo $s['width'] ?>x<?php echo $s['height'] ?>
					<?php if ($s['crop']) _e('cropped', 'wpshutter'); ?>)
				</label>
				<br/>
			<?php endforeach;?>
			</div>
			
			</div>

			<p><?php _e('Note: Your images will not be deleted', 'wpshutter'); ?></p>
			
			<p>
			<input type="button" onClick="javascript:regenerate();" class="button-primary"
			       name="ajax_thumbnail_rebuild" id="ajax_thumbnail_rebuild"
			       value="<?php _e( 'Rebuild Images', 'wpshutter' ) ?>" />
			</p>
		</form>

		<div id="thumb" style="display:none;"><h4><?php _e('Last image', 'wpshutter'); ?>:</h4><img id="thumb-img" /></div>
		
	<?php }
	
	/*
	 * Called during admin_menu, adds an options
	 * page under Settings called My Settings, rendered
	 * using the plugin_options_page method.
	 */
	function add_admin_menus() {
		global $wpshutter;
		add_submenu_page( 'edit.php?post_type=wps-gallery', __('Shutter Settings', 'wpshutter'), __('Settings', 'wpshutter'), 'manage_options', $this->shutter_options_key, array( &$this, 'plugin_options_page' ) );
	}
	
	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the plugin_options_tabs method
	 * to render the tabs.
	 */
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->shutter_general_settings;
		?>
		<div class="wrap">
			
			<?php if( isset($_GET['settings-updated']) ) { ?>
			    <div id="message" class="updated">
			        <p><strong><?php _e('Your settings have been saved.', 'wpshutter') ?></strong></p>
			    </div>
			<?php } ?>
			
			<?php $this->plugin_options_tabs(); ?>
			<?php if ( $tab != $this->shutter_regenerate_thumbnails ) : ?>
			<form method="post" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( $tab );
				do_settings_sections( $tab );
				submit_button();
				?>
			</form>
			<?php else: ?>
				<?php $this->shutter_regenerate_thumbnails_content(); ?>
			<?php endif;?>
		</div>
		<?php
	}
	
	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * plugin_options_page method.
	 */
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->shutter_general_settings;
		screen_icon('shutter-options');
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->shutter_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?post_type=wps-gallery&page=' . $this->shutter_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}
};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$GLOBALS["wpshutter_settings"] = new WPShutter_Settings();' ) );

include_once( 'admin-custom-columns.php' );