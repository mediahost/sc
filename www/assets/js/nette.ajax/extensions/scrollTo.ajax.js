(function ($, undefined) {

	/**
	 * Depends on 'snippets' extension
	 */
	$.nette.ext('scrollTo', {
		init: function () {
			this.ext('snippets', true).before($.proxy(function (el) {
				var $el = $(el);
				if ($el.hasClass('scrollHere')) {
					var $target = $el;
					if ($el.data('target')) {
						$target = $($el.data('target'));
					}
					var offset = $target.offset();

					scrollTo(offset.left, offset.top - 60 - 20);
					this.shouldTry = false;
				}
			}), this);
		},
		success: function () {
			this.shouldTry = true;
		}
	}, {
		shouldTry: true
	});

})(jQuery);
