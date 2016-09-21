
(function($){
	
	//ready functions
	$(document).ready(function(){
	
		//initialize flexslider
		 $('.portfolio-gallery-slider').flexslider({
		 	selector: '.slides > .slide',
		    animation: "slide",
		    smoothHeight: true,
		    slideshowSpeed: 5000,
		    animationSpeed: 400,
		    pauseOnHover: true
		  });
	  
		//initialize image popups
		$('.lightbox-element').magnificPopup({
			type: 'image',
			gallery: {
				enabled: true
			}
		});
		
		//masonry for 'traditional' images in the gallery
		//This needs to be called again on document load as images will affect the layout
		$('.portolio-gallery.traditional').masonry({
			itemSelector: '.portfolio-image',
		});
		
	
		//isotope for portfolio filtering
		$('.portfolios').isotope({
			itemSelector: '.portfolio-card',
			layoutMode: 'fitRows'
		});
		
		//on click, filter by term ID
		$('.portfolio-filter').on('click', '.term', function(){
			
			var term = $(this);
			
			term.siblings('.term').removeClass('active');
			term.addClass('active');
			
			//reset button
			if(term.hasClass('term-reset')){
				var filterValue = '*';
			}else{
				var filterValue = '.' + $(this).attr('data-filter');
				$('.portfolios').isotope({filter: filterValue})
			}
			
			$('.portfolios').isotope({filter: filterValue})
			
		});
		
	});
	
	//load functions
	jQuery(window).load(function(){
	
		$('.portolio-gallery.traditional').masonry({
			itemSelector: '.portfolio-image',
		});
	});
	
	
})(jQuery); 




