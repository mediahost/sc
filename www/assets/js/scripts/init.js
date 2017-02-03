jQuery(document).ready(function () {
	$.nette.ext('netteAjax', {
		init: function () {
			Global.initAccordion();
		},
		start: function (jqXHR, settings) {
			var el = settings.nette.el;
			if (el.hasClass('loadingOnClick')) {
				var message = el.attr('data-loading-text') ? el.attr('data-loading-text') : 'Loading...';
				if (el.is('input')) {
					el.val(message);
					el.addClass('disabled');
				} else if (el.is('button')) {
					el.addClass('disabled');
				}
			}
		},
		complete: function (data) {
			Global.init();
		}
	});

	$.nette.ext('closePopup', {
		complete: function (data) {
			if (data.closePopup) {
				$('.bootbox-close-button').click();
			}
		}
	});

	$.nette.init();

	Global.handleModals();
	Global.handleRightbar();
	Global.init();
	Global.handleJobPage();
	GridoStart.init();
	Login.init();
	// PdfPreview.init();
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

