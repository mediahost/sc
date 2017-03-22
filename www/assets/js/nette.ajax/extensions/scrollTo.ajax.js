(function ($, undefined) {

	/**
	 * Depends on 'snippets' extension
	 */
	$.nette.ext('scrollTo', {
		init: function () {
			this.ext('snippets', true).before($.proxy(function ($el) {
				if ($($el).hasClass('scrollHere')) {
					var offset = $el.offset();
					scrollTo(offset.left, offset.top);
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
