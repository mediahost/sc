var GlobalCustomInit = function () {

	return {
		init: function () {
			Maps.init();
			GridoStart.init();
			if (typeof MultipleFileUpload != 'undefined') {
				MultipleFileUpload.init();
			}
		}

	};

}();