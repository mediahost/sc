
// MapView plugin
(function ($) {
	$.fn.mapView = function () {
		if (Global.initMap) {
			Global.initMap();
			return;
		}

		var map;
		var mapDiv = $('#mapView')[0];
		var input = $('.googleSearch')[0];
		var form = input.closest('form');
		var options = {
			types: ['(regions)']
		};
		var setFormValues = function (place) {
			form['location[placeId]'].value = place.id;
			form['location[placeName]'].value = place.formatted_address;
			form['location[placeType]'].value = place.types[0];
			form['location[placeIcon]'].value = place.icon;
			form['location[placeLocation]'].value = place.geometry.location;
			form['location[placeViewport]'].value = place.geometry.viewport;
		}

		Global.initMap = function () {
			var map = new google.maps.Map(mapDiv, {
				zoom: 8,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});
			if (form['location[placeId]'].value !== '') {
				map.setCenter({
					lat: parseFloat(form['location[lat]'].value),
					lng: parseFloat(form['location[lng]'].value)
				});
			}

			var autocomplete = new google.maps.places.Autocomplete(input, options);
			autocomplete.bindTo('bounds', map);

			var marker = new google.maps.Marker({
				map: map
			});

			google.maps.event.addListener(autocomplete, 'place_changed', function () {
				var place = autocomplete.getPlace();
				if (!place.geometry) {
					return;
				}
				setFormValues(place);

				if (place.geometry.viewport) {
					map.fitBounds(place.geometry.viewport);
				} else {
					map.setCenter(place.geometry.location);
					map.setZoom(17);
				}

				marker.setPlace({
					placeId: place.place_id,
					location: place.geometry.location
				});
				marker.setVisible(true);
			});
		};

		var script = document.createElement('script');
		script.defer = true;
		script.async = true;
		script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&callback=Global.initMap';
		$('body').append(script);

		return self;
	};
}(jQuery));