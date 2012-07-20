jQuery(function($){
	
	(function ($, F) {
    
	    // Opening animation - fly from the top
	    F.transitions.dropIn = function() {
	        var endPos = F._getPosition(true);

	        endPos.top = (parseInt(endPos.top, 10) - 200) + 'px';
	        endPos.opacity = 0;
        
	        F.wrap.css(endPos).show().animate({
	            top: '+=200px',
	            opacity: 1
	        }, {
	            duration: F.current.openSpeed,
	            complete: F._afterZoomIn
	        });
	    };

	    // Closing animation - fly to the top
	    F.transitions.dropOut = function() {
	        F.wrap.removeClass('fancybox-opened').animate({
	            top: '-=200px',
	            opacity: 0
	        }, {
	            duration: F.current.closeSpeed,
	            complete: F._afterZoomOut
	        });
	    };
    
	    // Next gallery item - fly from left side to the center
	    F.transitions.slideIn = function() {
	        var endPos = F._getPosition(true);

	        endPos.left = (parseInt(endPos.left, 10) - 200) + 'px';
	        endPos.opacity = 0;
        
	        F.wrap.css(endPos).show().animate({
	            left: '+=200px',
	            opacity: 1
	        }, {
	            duration: F.current.nextSpeed,
	            complete: F._afterZoomIn
	        });
	    };
    
	    // Current gallery item - fly from center to the right
	    F.transitions.slideOut = function() {
	        F.wrap.removeClass('fancybox-opened').animate({
	            left: '+=200px',
	            opacity: 0
	        }, {
	            duration: F.current.prevSpeed,
	            complete: function () {
	                $(this).trigger('onReset').remove();
	            }
	        });
	    };

	}(jQuery, jQuery.fancybox));
	
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
				
        openMethod : 'dropIn',
        openSpeed : 250,

        closeMethod : 'dropOut',
        closeSpeed : 150,
        
        nextMethod : 'slideIn',
        nextSpeed : 250,
        
        prevMethod : 'slideOut',
        prevSpeed : 250,
		
		padding     : 0,
		margin      : 50
    });

});