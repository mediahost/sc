var GlobalCustomInit = function () {

	return {
		init: function () {
			Maps.init();
			if (typeof MultipleFileUpload != 'undefined') {
				MultipleFileUpload.init();
			}
		}

	};

}();