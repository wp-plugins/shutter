jQuery(function($){

	$("a.shutterbox").fancybox({

		beforeLoad: function() {

			var parent_image = $(this.element).closest('.gallery-image');
			var image_alt = $('.shutter-attachment-alt', parent_image).text();
			var image_caption = $('.shutter-attachment-caption', parent_image).text();

			if (image_alt)
				this.title = image_alt;

			if (image_caption)
				this.title = this.title + '<br />' + image_caption;

        },

		padding     : 0,
		margin      : 50
    });

});