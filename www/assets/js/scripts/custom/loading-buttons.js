var LoadingButtons = function () {

	var handleInitButtons = function () {
		$(document).on('click', '.loadingOnClick', function (e) {
			console.log('...');
		});
	};

	return {
		init: function () {
			handleInitButtons();
		}
	};

}();
