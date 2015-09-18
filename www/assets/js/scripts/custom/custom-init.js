var GlobalCustomInit = function () {

	return {
		init: function () {
			PdfPreview.init();
			ConnectedNumbers.init();

			GridoStart.init();
		},
		onReloadGridoEvent: function () {
		},
		onReloadModalEvent: function () {
			Nette.initAllForms(); // reinit all nette forms
		},
		onReloadPdfEvent: function () {
			PdfPreview.init();
		}
	};

}();