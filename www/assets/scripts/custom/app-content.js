var AppContent = function () {

	var handleLoadingButton = function () {
		$('.loading-btn').click(function (e) {
			var btn = $(this);
			btn.button('loading');
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			handleLoadingButton();
		}
	};

}();
