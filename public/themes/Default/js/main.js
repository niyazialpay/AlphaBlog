function openModal(modal_id, modal_effect){
	var modal = new Custombox.modal({
		content: {
			effect: modal_effect,
			target: '#' + modal_id
		}
	});
	// Open
	modal.open();
}

jQuery(document).ready(function(){

  "use strict";

   /*---------------------------------------
     Stiky Menu
    -------------------------------------------*/
    $(window).bind('scroll', function() {
        if ($(window).scrollTop()) {
            $('.navbar-default').addClass('navbar-fixed-top');
        } else {
            $('.navbar-default').removeClass('navbar-fixed-top');
        }
    });

    $('.searchbutton').on('click', function () {
    	openModal('search_modal', 'newspaper');
    	$('#search_input').focus();
	});

    $('.close').on('click', function () {
		Custombox.modal.closeAll()
	});


    $("#search_button").on("click", function () {
    	$("#search_form").submit();
	});




	/*------------------------
		Scroll to Top
	----------------------------------*/

    (function() {
	"use strict";

	var docElem = document.documentElement,
		didScroll = false,
		changeHeaderOn = 550;
		document.querySelector( '#back-to-top' );
	function init() {
		window.addEventListener( 'scroll', function() {
			if( !didScroll ) {
				didScroll = true;
				setTimeout( scrollPage, 50 );
			}
		}, false );
	}

   })();

   $(window).scroll(function(event){
	var scroll = $(window).scrollTop();
	if (scroll >= 50) {
	    $("#back-to-top").addClass("show");
	} else {
	    $("#back-to-top").removeClass("show");
	}
});

$('a[href="#top"]').on('click',function(){
    $('html, body').animate({scrollTop: 0}, 'slow');
    return false;
});

});

document.addEventListener("DOMContentLoaded", function() {
	var lazyloadImages = document.querySelectorAll("img.lazy");
	var lazyloadThrottleTimeout;

	function lazyload () {
		if(lazyloadThrottleTimeout) {
			clearTimeout(lazyloadThrottleTimeout);
		}

		lazyloadThrottleTimeout = setTimeout(function() {
			var scrollTop = window.pageYOffset;
			lazyloadImages.forEach(function(img) {
				if(img.offsetTop < (window.innerHeight + scrollTop)) {
					img.src = img.dataset.src;
					img.classList.remove('lazy');
				}
			});
			/*if(lazyloadImages.length === 0) {
				document.removeEventListener("scroll", lazyload);
				window.removeEventListener("resize", lazyload);
				window.removeEventListener("orientationChange", lazyload);
				window.removeEventListener("onload", lazyload);
			}*/
		}, 1);
	}
	/*document.addEventListener("scroll", lazyload);
	window.addEventListener("resize", lazyload);
	window.addEventListener("orientationChange", lazyload);
	window.addEventListener("onload", lazyload);*/
	$(window).on('load', function(){
		lazyload();
	});

});

	 /*------------------------------
    Preloader
    --------------------------------------*/

  	/*$(window).on( 'load',function() {

   	// will first fade out the loading animation
    	$("#status").fadeOut("slow");

    	// will fade out the whole DIV that covers the website.
    	$("#preloader").delay(500).fadeOut("slow").remove();

  	}) ; */
