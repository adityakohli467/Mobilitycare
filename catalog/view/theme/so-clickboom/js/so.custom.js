/* Add Custom Code Jquery
 ========================================================*/
$(document).ready(function(){
	// Fix hover on IOS
	$('body').bind('touchstart', function() {}); 
	// Messenger posmotion
	$( "#close-posmotion-header" ).click(function() {
		$('.promotion-top').toggleClass('hidden-promotion');
		$('body').toggleClass('hidden-promotion-body');

		if($(".promotion-top").hasClass("hidden-promotion")){
			$.cookie("open", 0);
			
		} else{
			$.cookie("open", 1);
		}

	});
	
	if($.cookie("open") == 0){
		$('.promotion-top').addClass('hidden-promotion');
		$('body').addClass('hidden-promotion-body');
	}

	// Messenger Top Link
	$('.list-msg').owlCarousel2({
		pagination: false,
		center: false,
		nav: false,
		dots: false,
		loop: true,
		slideBy: 1,
		autoplay: true,
		margin: 30,
		autoplayTimeout: 4500,
		autoplayHoverPause: true,
		autoplaySpeed: 1200,
		startPosition: 0, 
		responsive:{
			0:{
				items:1
			},
			480:{
				items:1
			},
			768:{
				items:1
			},
			1200:{
				items:1
			}
		}
	});


	
	// Slider Clients Say
	$('.slider-clients-say').owlCarousel2({
		pagination: false,
		center: false,
		nav: false,
		loop: true,
		slideBy: 1,
		autoplay: true,
		margin: 0,
		autoplayTimeout: 4500,
		autoplayHoverPause: true,
		autoplaySpeed: 1200,
		startPosition: 0, 
		responsive:{
			0:{
				items:1
			},
			480:{
				items:1
			},
			768:{
				items:1
			},
			1200:{
				items:1
			}
		}
	});

	// =========================================

	// Slider Logo Footer 2
	jQuery(document).ready(function($) {
	    var slider = $(".slider-above-footer .slider-brand");
	    slider.owlCarousel2({    
	    margin:30,
	    nav:true,
	    loop:true,
	    dots: false,
	    navText: ['',''],
	    responsive:{
	            0:{
	                items:1
	            },
	            480:{
	                items:2
	            },
	            768:{
	                items:4
	            },
	            992:{
	                items:5
	            },
	            1200:{
	                items:6
	            },
	        },
	    })
	});

	// Slider Logo Footer 4
	jQuery(document).ready(function($) {
	    var slider2 = $(".slider-brand-footer .slider-brand");
	    slider2.owlCarousel2({    
	    margin:30,
	    nav:true,
	    loop:true,
	    dots: false,
	    responsive:{
	            0:{
	                items:1
	            },
	            480:{
	                items:1
	            },
	            768:{
	                items:4
	            },
	            992:{
	                items:5
	            },
	            1200:{
	                items:6
	            },
	        },
	    })
	});

	// Slider Brands Layout 3
    jQuery(document).ready(function($) {
        var slider3 = $(".slider-brand-v3 .slider-brand");
        slider3.owlCarousel2({
        margin:30,
        nav:true,
        loop:true,
        dots: false,
        navText: ['',''],
        responsive:{
            0:{
              items:2
            },
            480:{
              items:3
            },
            768:{
              items:4
            },
            992:{
              items:4
            },
            1200:{
              items:5
            },
          },
        })
    });

    // Testimonials layout 3
    jQuery(document).ready(function($) {
      var owl_testimonial = $(".slider-clients-say-h3");
      owl_testimonial.owlCarousel2({
        
        responsive:{
          0:{
            items:1
          },
          480:{
            items:1
          },
          768:{
            items:1
          },
          992:{
            items:1
          },
          1200:{
            items:1
          }
        },

        autoplay:false,
        loop:true,
        nav : true, // Show next and prev buttons
        dots: false,
        navText: ['',''],
        autoplaySpeed : 500,
        navSpeed : 500,
        dotsSpeed : 500,
        autoplayHoverPause: true,
        margin:0,

      });   
    }); 


    // Testimonials layout 4
	jQuery(document).ready(function($) {
		$('.testimonials4').owlCarousel2({
			animateOut: 'fadeOut',
          animateIn: 'fadeIn',
          autoplay: true,
          autoplayTimeout: 5000,
          autoplaySpeed:  1000,
          smartSpeed: 500,
          dotsContainer: false,
          autoplayHoverPause: true,
          startPosition: 0,
          mouseDrag:  true,
          touchDrag: true,
          nav: true,
          // dotsContainer: true,
          // dotsContainer: '.dotsCont',
          dots: true,
          autoWidth: false,
          dotClass: "owl2-dot",
          dotsClass: "owl2-dots",
          loop: true,
          navText: ["Next", "Prev"],
          navClass: ["owl2-prev", "owl2-next"],
          responsive:{
				0:{
					items:1
				},
				480:{
					items:1
				},
				768:{
					items:1
				},
				1200:{
					items:1
				}
			}

			
		});
	});


	// click header search header 5
	jQuery(document).ready(function($){
		$( ".header_search .icon-search" ).click(function() {
		$('.sosearchpro-wrapper').slideToggle(200);
		$(this).toggleClass('active');
		});
	});

	
	jQuery(document).ready(function($){
		if($("body").hasClass("layout-5") && typeof WOW !== 'undefined'){
		    wow = new WOW(
		      {
		        animateClass: 'animated',
		        offset:       100,
		        callback:     function(box) {
		          console.log("WOW: animating <" + box.tagName.toLowerCase() + ">")
		        }
		      }
		    );
		    wow.init();
		}
	});




	// banner top
	$(document).ready(function(){
	    $(".topbar-close").click(function(){
	        $(".coupon-code").slideToggle();
	    });
	    // $(".button").on('click',function(){
	    //         if($('.button').hasClass('active')){
	    //             $('.button').removeClass('active');
	    //         }else{
	    //             $('.button').removeClass('active');
	    //             $('.button').addClass('active');
	    //         }
	    //  });
	});

	//SLIDE
	$(document).ready(function(){
	$(".contentslider-home1").slick({
	    dots: true,
	    arrows: false,
	    vertical: true,
	    verticalSwiping:true,
	    infinite: true,
	    margin: 20,
	    autoplay: true,
	  	autoplaySpeed: 2000,
	    customPaging : function(slider, i) {
	      var thumb = $(slider.$slides[i]).data();
	      var value = '<span>'+(i+1)+'</span>';
	      if (($(slider).length) <= 8) {
	        value = '<span>0'+(i+1)+'</span>';
	      }
	      return value;
	    },
	  });
	});


	// video

	  $(document).ready(function() {
	    $('.home1-video').magnificPopup({
	      type: 'iframe',
	      iframe: {
	      patterns: {
	         youtube: {
	          index: 'youtube.com/', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).
	          id: 'v=', // String that splits URL in a two parts, second part should be %id%
	          src: '//www.youtube.com/embed/%id%?autoplay=1' // URL that will be set as a source for iframe. 
	          },
	        }
	      }
	    });
	  });


	// Close pop up countdown
	 $( "#so_popup_countdown .customer a" ).click(function() {
	  $('body').toggleClass('hidden-popup-countdown');
	 });
	// =========================================


	// click header search header 
	jQuery(document).ready(function($){
		$('.search-header-w .icon-search').click(function(e){
          e.preventDefault();
          $('#sosearchpro .search').toggleClass("nav-open");
          $('.search-screen').addClass("nav-open");
          $(this).toggleClass('active');
        });
	
        $('.search-screen ').click(function(e){
          e.preventDefault();
          $(this).toggleClass("nav-open");
          $('#sosearchpro .search').removeClass("nav-open");

        });
	});
   
	
	// slider categories
	jQuery(document).ready(function($) {
	    var slidercate = $(".slider-cates .cat-wrap");
	    slidercate.owlCarousel2({    
	    margin:20,
	    nav:false,
	    loop:true,
	    dots: false,
	    navText: ['',''],
	    responsive:{
	            0:{
	                items:1
	            },
	            480:{
	                items:2
	            },
	            768:{
	                items:4
	            },
	            992:{
	                items:5
	            },
	            1200:{
	                items:6
	            },
	        },
	    })
	});

	// slick testimonials

		$('.client-main').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: true,
			fade: true,
			prevArrow: '<div class="slick-prev" aria-label="Previous"><span>Previous</span></div>',
			nextArrow: '<div class="slick-next" aria-label="Next"><span>Next</span></div>',
			asNavFor: '.client-image'
		});
		
		$('.client-image').slick({
			slidesToShow: 5,
			slidesToScroll: 1,
			asNavFor: '.client-main',
			dots: false,
			arrows: false,
			centerMode: true,
			centerPadding: 0,
			focusOnSelect: true,
			
			responsive: [
				{
				  breakpoint: 767,
				  settings: {
					slidesToShow: 4,
				  }
				},
				{
				  breakpoint: 560,
				  settings: {
					slidesToShow: 3,
				  }
				}
			]
		});


	// custom to show footer center
	$(".description-toggle").click(function () {
		$('.description-toggle').addClass('active 234567');
		if($('.showmore').hasClass('active 4567812'))
			$('.showmore').removeClass('active');
		else
			$('.showmore').addClass('active');
	}); 


	$(".content-product-content .button-toggle").click(function () {
		if($(this).children('.showmore').hasClass('active')) $(this).children().removeClass('active');
		else $(this).children().addClass('active');
		
		
		
		if($(this).prev().hasClass('showdown')) $(this).prev().removeClass('showdown').addClass('showup');
		else $(this).prev().removeClass('showup').addClass('showdown');
	}); 

	$(".clearable").each(function() {
  
	  var $inp = $(this).find("input:text"),
	      $cle = $(this).find(".clearable__clear");

	  $inp.on("input", function(){
	    $cle.toggle(!!this.value);
	  });
	  
	  $cle.on("touchstart click", function(e) {
	    e.preventDefault();
	    $inp.val("").trigger("input");
	  });
	  
	});

	jQuery(document).ready(function($){
		if($("body").hasClass("layout-5") && typeof WOW !== 'undefined'){
		    wow = new WOW(
		      {
		        animateClass: 'animated',
		        offset:       100,
		        callback:     function(box) {
		          console.log("WOW: animating <" + box.tagName.toLowerCase() + ">")
		        }
		      }
		    );
		    wow.init();
		}
	});

});
