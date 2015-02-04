//(function ($, undefined) {
//
//	$.nette.ext('spinner', {
//		init: function () {
//			this.spinner = this.createSpinner();
//			this.spinner.appendTo('body');
//		},
//		start: function () {
//			this.spinner.show(this.speed);
//		},
//		complete: function () {
//			this.spinner.hide(this.speed);
//		}
//	}, {
//		createSpinner: function () {
//			return $('<div>', {
//				id: 'ajax-spinner',
//				css: {
//					display: 'none'
//				}
//			});
//		},
//		spinner: null,
//		speed: undefined
//	});
//
//})(jQuery);

(function ($, undefined) {

	$.nette.ext('loader', {
		start: function (jqXHR, settings) {
			if (settings.nette.form.length) {
				this.element = settings.nette.form;
				var parentPortlet = this.element.closest('.portlet');
				if (parentPortlet.length) {
					this.element = parentPortlet;
				}
			}
			Metronic.blockUI({
				target: this.element,
				animate: true
			});
		},
		complete: function () {
			Metronic.unblockUI(this.element);
		}
	}, {
		element: undefined
	});

})(jQuery);
