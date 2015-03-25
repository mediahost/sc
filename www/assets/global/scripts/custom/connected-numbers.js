var ConnectedNumbers = function ()
{

	var handleConnectedNumbers = function ()
	{
		$('.connectedNumber').each(function ()
		{
			var isMin = $(this).hasClass('min');
			var isMax = $(this).hasClass('max');
			var inversedType = isMin ? 'max' : 'min';
			var connectedId = $(this).attr('data-connected-id');
			var connectedItem = $('.connectedNumber.' + inversedType + '[data-connected-id=' + connectedId + ']');

			$(this).on('change', function ()
			{
				var value = parseInt($(this).val());
				var connectedValue = parseInt(connectedItem.val());

				if (isMax && value < connectedValue && parseInt(value) === 1) {
					value = connectedValue;
					$(this).val(value);
				}
				if ((isMin && value > connectedValue) || (isMax && value < connectedValue)) {
					connectedItem.val(value);
				}
			});
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			handleConnectedNumbers();
		}
	};

}();
