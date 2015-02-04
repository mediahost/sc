jQuery(document).ready(function () {
	Metronic.init(); // init metronic core componets
	Layout.init(); // init layout
	$.nette.init(); // https://github.com/vojtech-dobes/nette.ajax.js

	Login.init();

	// components
	ComponentsPickers.init();
	HtmlEditors.init();
	ComponentsFormTools.init();
	UIToastr.init();
	Fullscreen.init();
	TableManaged.init();
	ComponentsDropdowns.init();
	ComponentsNoUiSliders.init();
	PdfPreview.init();

	AppContent.init();
});

$('.modal.ajax').on('loaded.bs.modal', function (e) {
	reloadAfterAjax();
});

var reloadAfterAjax = function () {
	ComponentsDropdowns.init(); // init form components after ajax load
	Nette.initAllForms(); // reinit all nette forms
};

$.nette.ext('netteAjax', {
	complete: function () {
		reloadAfterAjax();
		PdfPreview.init();
	}
});
