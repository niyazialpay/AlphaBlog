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


    $('#yorum_panel').on('submit', function () {
    	var response = $('#response_message');
		response.html("");
		$.ajax({
			url: "/blog",
			type: 'post',
			dataType: 'json',
			data: $('#yorum_panel').serialize(),
			success: function (result) {
				if(result.result){
					getRecaptchaToken();
					$('#yorum_panel').trigger("reset");
					$('#_token').val(result.reason.csrf_token);
				}
				response.html(result.reason.description);
			},
			error: function (xhr, status, error) {
				console.log(xhr);
				if(!jQuery.hasData(xhr.responseJSON)){
					for(var key in xhr.responseJSON){
						if(key === "isim"){
							if(xhr.responseJSON.isim[0] === "validation.required"){
								response.html('İsim bilgisi boş geçilemez!');
							}
						}
						else if(key === "email"){
							if(xhr.responseJSON.email[0] === "validation.required"){
								response.html('Eposta bilgisi boş geçilemez!');
							}
							else if(xhr.responseJSON.email[0] === "validation.email"){
								response.html('Geçerli bir eposta adresi girmeniz gerekmektedir!');
							}
							else{
								response.html('Geçerli bir eposta adresi girmeniz gerekmektedir!');
							}
						}
						else if(key === "yorum"){
							if(xhr.responseJSON.yorum[0] === "validation.required"){
								response.html('Yorum bilgisi boş geçilemez!');
							}
						}
						else{
							response.html('Formdaki tüm alanların dolu olması gerekmektedir!');
						}
					}
				}
			}
		});
	});

    $("#search_button").on("click", function () {
    	$("#search_form").submit();
	});

    $("#search_form").on('submit', function () {
		var search_message = $("#search_message");
		search_message.html("");
		$.ajax({
			url: "/ara",
			type: 'post',
			dataType: 'json',
			data: $('#search_form').serialize(),
			success: function (result) {
				search_message.html(result.message);
				if(result.result){
					window.location = result.redirect
				}
			},
			error: function (xhr, status, error) {
				console.log(xhr);
				$("#search_input").focus();
				search_message.html('<strong><em>Arama alanı boş geçilemez</em></strong>');
			}
		});
	});

    $("#contactForm").on('submit', function () {
		$.ajax({
			url: "/iletisim",
			type: 'post',
			dataType: 'json',
			data: $('#contactForm').serialize(),
			success: function (result) {
				console.log(result);
				if(result){
                    getRecaptchaToken();
                    $('#success').html(result.reason.description);
                    $('#contactForm').trigger("reset");
                    $('#_token').val(result.reason.csrf_token);
                }
			},
			error: function (xhr, status, error) {
				console.log(xhr);
                if(!jQuery.hasData(xhr.responseJSON)){
                    for(var key in xhr.responseJSON){
                        if(key === "name"){
                            if(xhr.responseJSON.isim[0] === "validation.required"){
                                response.html('İsim soyisim bilgisi boş geçilemez!');
                            }
                        }
                        else if(key === "email"){
                            if(xhr.responseJSON.email[0] === "validation.required"){
                                response.html('Eposta bilgisi boş geçilemez!');
                            }
                            else if(xhr.responseJSON.email[0] === "validation.email"){
                                response.html('Geçerli bir eposta adresi girmeniz gerekmektedir!');
                            }
                            else{
                                response.html('Geçerli bir eposta adresi girmeniz gerekmektedir!');
                            }
                        }
                        else if(key === "subject"){
                            if(xhr.responseJSON.yorum[0] === "validation.required"){
                                response.html('Konu bilgisi boş geçilemez!');
                            }
                        }
                        else if(key === "message"){
                            if(xhr.responseJSON.yorum[0] === "validation.required"){
                                response.html('Mesaj bilgisi boş geçilemez!');
                            }
                        }
                        else{
                            response.html('Formdaki tüm alanların dolu olması gerekmektedir!');
                        }
                    }
                }
			}
		});
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
