jQuery(document).ready(function () {
	$.nette.init();
	
	Global.init();
	Login.init();
	PdfPreview.init();
	// Global components
	
	$.nette.ext('netteAjax', {
		complete: function () {
			Global.init();
		}
	});
});

//$('.modal.ajax').on('loaded.bs.modal', function (e) {
//	GlobalCustomInit.onReloadModalEvent();
//});

//$.nette.ext('netteAjax', {
//	complete: function () {
//		GlobalCustomInit.onReloadGridoEvent();
//		GlobalCustomInit.onReloadModalEvent();
//		GlobalCustomInit.onReloadPdfEvent();
//	}
//});

