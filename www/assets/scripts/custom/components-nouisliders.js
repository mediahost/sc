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
			var isTooltipFixed = (select.attr('data-tooltip-fixed')) === 'true';
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

			var getSelectedOption = function (options, value) {
				return options.filter(function () {
					return parseInt($(this).val()) === parseInt(value);
				});
			};

			if (isTooltip) {
				slider.addClass('hasTooltip');
				slider.Link('lower').to('-inline-<div class="noUi-tooltip"></div>', function (value) {
					var selectedOption = getSelectedOption(options, value);
					$(this).html('<span>' + selectedOption.text() + '</span>');
				});
			}

			if (isTooltipFixed) {
				slider.addClass('hasTooltipFixed');
				var fixedTooltip = $('<div id="' + id + '_tooltip">')
						.addClass('noUi-tooltip-fixed');
				fixedTooltip.text(getSelectedOption(options, select.val()).text());
				select.after(fixedTooltip);
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

			slider.on({
				slide: function (e, value) {
					var selectedOption = getSelectedOption(options, value);
					selectedOption.prop('selected', true);
					fixedTooltip.text(selectedOption.text());
				}
			});
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