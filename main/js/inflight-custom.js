//$('.mob-menu-wrap').on('click', function() {
$('.mob-menu-wrap').on('click', function(e) {
	e.stopPropagation();
	$('.mob-menu').toggleClass('clickMenuFive');
	$('body').toggleClass('activeMenu');
});

$(document).ready(function() {
	searchPop();
	searchPopOpen();
	menuHoverColor();
	bookNow();
	customLanguage();

	$('#menu-desktop-book-now').on('click', function() {
		if ($(window).width() > 1023) {
			//alert($('#mounth-desktop').val());
			//alert($('.menu-desktop-datepicker').val());
			if ($('#mounth-desktop').val() == 'hide' || $('.menu-desktop-datepicker').val() == '') {
				//alert('FRONTEND: Please add js validation');
				//make the dropdowns red
				if ($('#mounth-desktop').val() == 'hide') {
					$('.earn-dropdown').addClass('error');
				}
				if ($('.menu-desktop-datepicker').val() == '') {
					$('.date-section').addClass('error');
				}
				return false;
			} else {
				earnValDesktop = $('#mounth-desktop').val();
				dateDesktop = $('.menu-desktop-datepicker').val();
				//?plan=plan1&date=dd/mm/yyyy
				urlDesktop =
					'https://store.inflightdubai.com/inflight/main/store.php?offerid=' +
					earnValDesktop +
					'&date=' +
					dateDesktop;
				//urlDesktop = urlDesktop.searialise();
				$('#menu-desktop-book-now').attr('href', urlDesktop);
			}
		}
	});
	//For mobile
	$('#mounth-mobile-custom li').on('click', function() {
		earnValMobile = $('#mounth-mobile').val();
	});
	$('.menu-mobile-datepicker').change(function() {
		//alert($('.menu-mobile-datepicker').val());
		dateMobile = $('.menu-desktop-datepicker').val();
		//$('#menu-mobile-book-now').attr("href", url);
	});
	$('#menu-mobile-book-now').on('click', function() {
		//alert($('#mounth-mobile').val());
		//alert($('.menu-mobile-datepicker').val());
		if (
			$('#mounth-mobile').val() == '' ||
			$('#mounth-mobile').val() == 'hide' ||
			$('.menu-mobile-datepicker').val() == ''
		) {
			if ($('#mounth-mobile').val() == 'hide') {
				$('.earn-dropdown').addClass('error');
			}
			if ($('.menu-mobile-datepicker').val() == '') {
				$('.date-section').addClass('error');
			}

			//alert('FRONTEND - Please add js validation');
			return false;
		} else {
			earnValMobile = $('#mounth-mobile').val();
			dateMobile = $('.menu-mobile-datepicker').val();
			urlMobile =
				'https://store.inflightdubai.com/inflight/main/store.php?offerid=' +
				earnValMobile +
				'&date=' +
				dateMobile;
			//urlMobile = urlMobile.searialise();
			$('#menu-mobile-book-now').attr('href', urlMobile);
		}
	});
	//Code for menu book now ends
});

function customLanguage() {
	jQuery('.lang-sector .select').on('click', function(e) {
		e.stopPropagation();
		$('body').toggleClass('active');
	});
}

//mobile hover
function menuHoverColor() {
	$('.nav-listing li').hover(
		function() {
			$(this).addClass('actv-hover');
		},
		function() {
			$(this).removeClass('actv-hover');
		}
	);
}

//search pop
function searchPopOpen() {
	$('.top-search').on('click', function(event) {
		// $('.search-pop').show();
		$('.search-pop').addClass('active-large-search-pop');
	});
	$('.m-mob-search').on('click', function(event) {
		$('.search-pop').show();
	});

	$('.pop-close-btn').on('click', function(event) {
		// $('.search-pop').hide();
		$('.search-pop').removeClass('active-large-search-pop');
	});
}

function searchPop() {
	$('input.form-control').on('focusout', function(event) {
		if ($(event.target).val() == '') {
			$(event.target).next('.close-btn').hide();
		}
	});
	$('.close-btn').on('click', function(event) {
		$(event.target).prev('.form-control').val('');
		$(event.target).hide();
	});
	$('input.form-control').on('focus', function(event) {
		$(event.target).next('.close-btn').show();
	});
}
//booknow
function bookNow() {
	$('.mob-booking-close').on('click', function(event) {
		$('body, html').removeClass('activeBooking');
	});
}
