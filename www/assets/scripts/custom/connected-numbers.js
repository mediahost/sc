var ConnectedNumbers = function ()
{

	var handleConnectedNumbers = function ()
	{
		$('.connectedNumber').on('change', function ()
		{
			var value = parseInt($(this).val());
			var isMin = $(this).hasClass('min');
			var inversedType = isMin ? 'max' : 'min';
			var connectedId = $(this).attr('data-connected-id');
			var connectedItem = $('.connectedNumber.' + inversedType + '[data-connected-id=' + connectedId + ']');
			var connectedValue = parseInt(connectedItem.val());
			
			if ((isMin && value > connectedValue) || (!isMin && value < connectedValue)) {
				connectedItem.val(value);
			}
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			handleConnectedNumbers();
		}
	};

}();
