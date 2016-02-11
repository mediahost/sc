jQuery(document).ready(function () {
	$.nette.init();
	
	Global.handleRightbar();
	Global.init();
	Login.init();
	PdfPreview.init();
	// Global components
	
	$.nette.ext('netteAjax', {
		complete: function (data) {
			Global.init();
			if(data.reloadPreview) {
				PdfViewer.renderPage();
			}
		}
	});
	
	$.nette.ext('closePopup', {
		complete: function (data) {
			if(data.closePopup) {
				$('.bootbox-close-button').click();
			}
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

