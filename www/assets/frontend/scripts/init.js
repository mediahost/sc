jQuery(document).ready(function () {
	Metronic.init(); // init metronic core componets
	Layout.init(); // init layout
	Layout.initUniform();
	$.nette.init(); // https://github.com/vojtech-dobes/nette.ajax.js
	
	Layout.initTwitter();

	// special for pages
	Login.init(); // remember me
	
	// Global components
	GlobalCustomInit.init();
});

$('.modal.ajax').on('loaded.bs.modal', function (e) {
	GlobalCustomInit.onReloadModalEvent();
});

$.nette.ext('netteAjax', {
	complete: function () {
		GlobalCustomInit.onReloadGridoEvent();
		GlobalCustomInit.onReloadModalEvent();
		GlobalCustomInit.onReloadPdfEvent();
	}
});
