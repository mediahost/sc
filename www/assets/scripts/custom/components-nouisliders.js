var ComponentsNoUiSliders = function () {

	var handleSkillSlider = function () {

		$('select.noUiSlider').each(function () {
			var select = $(this);
			select.hide();

			var options = select.find('option');
			var optionsObject = {};
			options.each(function () {
				optionsObject[parseInt($(this).val())] = $(this).text();
			});

			var id = select.attr('id') + '_slider';
			var slider = $('<div id="' + id + '">')
					.addClass('noUi-control')
					.addClass(select.attr('data-class'));
			var isTooltip = (select.attr('data-tooltip')) === 'true';
			var isPips = (select.attr('data-pips')) === 'true';

			slider.noUiSlider({
				direction: (Metronic.isRTL() ? "rtl" : "ltr"),
				start: parseInt(options.filter(':selected').first().val()),
				connect: "lower",
				step: 1,
				range: {
					'min': 1,
					'max': options.length
				}
			});

			slider.on({
				slide: function (e, value) {
					var selectedOption = options.filter(function () {
						return parseInt($(this).val()) === parseInt(value);
					});
					selectedOption.prop('selected', true);
				}
			});

			if (isTooltip) {
				slider.addClass('hasTooltips');
				slider.Link('lower').to('-inline-<div class="noUi-tooltip"></div>', function (value) {
					var selectedOption = options.filter(function () {
						return parseInt($(this).val()) === parseInt(value);
					});
					$(this).html('<span>' + selectedOption.text() + '</span>');
				});
			}

			if (isPips) {
				slider.addClass('hasPips');
				slider.noUiSlider_pips({
					mode: 'values',
					values: Object.keys(optionsObject).map(function (val) {
						return parseInt(val);
					}),
					density: 1,
					stepped: false,
					format: {
						to: function (value) {
							return optionsObject[value] || value;
						}
					}
				});
			}

			select.after(slider);
		});
	};

	var handleSkillsRange = function () {



	};

	return {
		//main function to initiate the module
		init: function () {

			handleSkillSlider();
			handleSkillsRange();
		}

	};
}();