jQuery(document).ready(function () {
	$.nette.ext('netteAjax', {
		init: function() {
			Global.initAccordion();
		},
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
	
	$.nette.init();
	
        Global.handleModals();
	Global.handleRightbar();
	Global.init();
	Login.init();
	PdfPreview.init();
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

