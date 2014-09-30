var Content = function () {

	var handleExtendedTabs = function () {
		$('.toggleSetPassword').on('click', function (e) {
			e.preventDefault();
			$('.profile-account .tabbable [data-toggle="tab"][href="#set-password"]').tab('show');
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			handleExtendedTabs();
		}
	};

}();