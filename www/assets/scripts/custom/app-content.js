var AppContent = function () {

	var handleExtendedTabs = function () {
		$('.toggleSetPassword').on('click', function (e) {
			e.preventDefault();
			$('.profile-account .tabbable [data-toggle="tab"][href="#set-password"]').tab('show');
		});
	};

	var handleLoadingButton = function () {
		$('.loading-btn').click(function (e) {
			var btn = $(this);
			btn.button('loading');
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			handleExtendedTabs();
			handleLoadingButton();
		}
	};

}();
