var Global = function () {

	var handleInitPickers = function () {
		$('.input-daterange').datepicker();
	};

	var handleHideRightbar = function () {
		if ($('body').hasClass('hide-righ-sidebar') && ($('#toggle-right-sidebar').hasClass('hide-right-sidebar') || $('#toggle-right-sidebar i').hasClass('icomoon-icon-indent-increase'))) {
			$('#toggle-right-sidebar').click();
		}
	};
	
	var handleRating = function() {
		$('input.rating').rating();
	};
	
	var handleFBSharer = function() {
		$('[href="#share"]').on('click', function() {
			var fburl = 'https://source-code.com/';
			var sharerURL = "http://www.facebook.com/sharer/sharer.php?s=100&p[url]=" + encodeURI(fburl);
			window.open(sharerURL, 'facebook-share-dialog', 'width=600,height=400'); 
			return  false;
		})
	};
	
	var initAccordion = function() {
		$('.expandAll').find('.panel-collapse').removeClass('collapse');
	};

	var handleSocialLinks = function () {
		$(document).on('mouseover', '.social-editor .data', function (e) {
			$(this).find('a.edit:not(.empty)').show();
		});
		$(document).on('mouseout', '.social-editor .data', function (e) {
			$(this).find('a.edit:not(.empty)').hide();
		});
		$(document).on('click', '.social-editor a.edit', function (e) {
			e.preventDefault();
			$('.social-editor .data').hide();
			$('.social-editor .editor').show();
		});
		$(document).on('click', '.social-editor input[type=submit]', function (e) {
			e.preventDefault();
			$('.social-editor .data').show();
			$('.social-editor .editor').hide();
		});
	};

	var handlePersonalDetails = function () {
		var main = $('.personal-detail-editor .profile');
		$(document).on('click', '.personal-detail-editor a.edit', function (e) {
			e.preventDefault();
			var target = $(this).attr('href');
			$(target).show();
			$(main).hide();
		});
		$(document).on('click', '.personal-detail-editor input[type=submit]', function (e) {
			e.preventDefault();
			$(main).show();
			$('.personal-detail-editor .profile-edit').hide();
		});
	};

	var handleMessages = function () {
		$(document).on('click', '.recent-comments li.list-group-item', function (e) {
			window.location.href = $(this).find('a').attr('href');
		});
	};

	return {
		init: function () {
			$(document).ready(function () {
				handleInitPickers();
				handleRating();
				//handleHideRightbar();
				handleFBSharer();
				initAccordion();
				handleSocialLinks();
				handlePersonalDetails();
				handleMessages();
			});
		}
	};

}();
