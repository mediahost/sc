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

	return {
		init: function () {
			$(document).ready(function () {
				handleInitPickers();
				handleRating();
				handleHideRightbar();
			});
		}
	};

}();
