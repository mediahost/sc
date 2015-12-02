var Global = function () {

	var handleInitPickers = function () {
		$('.input-daterange').datepicker();
	};

	var handleHideRightbar = function () {
		if ($('body').hasClass('hide-righ-sidebar') && ($('#toggle-right-sidebar').hasClass('hide-right-sidebar') || $('#toggle-right-sidebar i').hasClass('icomoon-icon-indent-increase'))) {
			$('#toggle-right-sidebar').click();
		}
	};

	return {
		init: function () {
			$(document).ready(function () {
				handleInitPickers();
				handleHideRightbar();
			});
		}
	};

}();
