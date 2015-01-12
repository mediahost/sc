jQuery(document).ready(function () {
	Metronic.init(); // init metronic core componets
	Layout.init(); // init layout

	Login.init();

	ComponentsPickers.init();
	HtmlEditors.init();
	ComponentsFormTools.init();
	UIToastr.init();
	Fullscreen.init();
	TableManaged.init();
	ComponentsDropdowns.init();

	AppContent.init();
});

$('.modal.ajax').on('loaded.bs.modal', function (e) {
	ComponentsDropdowns.init(); // init form components after ajax load modal window
	Nette.initAllForms(); // reinit all nette forms
});
