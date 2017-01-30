var GridoStart = function () {

	var handleStart = function () {
//		$('.grido').grido({ajax: false});
//		$('.grido').grido();
	};

	var handleConfirm = function () {
		$(document).on('click', 'a[data-grido-confirm]', function (e) {
			var $target = $(e.target);
			var question = $target.attr('data-grido-confirm');
			return confirm(question);
		});
	}

	return {
		init: function () {
			handleStart();
			handleConfirm();
		}
	};

}();
