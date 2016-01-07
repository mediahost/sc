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

	return {
		init: function () {
			$(document).ready(function () {
				handleInitPickers();
				handleRating();
				//handleHideRightbar();
				handleFBSharer();
				initAccordion();
			});
		}
	};

}();
