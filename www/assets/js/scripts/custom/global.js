var Global = function () {

	var handleHideRightbar = function () {
		$(document).ready(function () {
			if ($('body').hasClass('hide-righ-sidebar')) {
				$('#toggle-right-sidebar').click();
			}
		});
	};

	return {
		init: function () {
			handleHideRightbar();
		}
	};

}();
