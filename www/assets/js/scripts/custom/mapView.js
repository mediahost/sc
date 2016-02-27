
// MapView plugin
(function( $ ) {
	$.fn.mapView = function() {
		if(Global.initMap) {
			Global.initMap();
			return;
		}
		
		
		var input = $('.googleSearch')[0];
		var setResult = function(place) {
			alert(JSON.stringify(place))
//			var form = input.closest('form');
//			form.placeId.value = place.id;
//			form.placeName.value = place.name;
//			form.placeIcon.value = place.icon;
//			form.placeLocation.value = place.geometry.location;
		}
		var options = {
			types: ['locality','country']
		};
		
		Global.initMap = function() {
			var mapDiv = $('#mapView')[0];
			var map = new google.maps.Map(mapDiv, {
				center: {lat: 44.540, lng: -78.546},
				zoom: 8,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});
			var searchBox = new google.maps.places.SearchBox(input);
			
			map.addListener('bounds_changed', function() {
				searchBox.setBounds(map.getBounds());
			});
			
			var markers = [];
			searchBox.addListener('places_changed', function() {
				var places = searchBox.getPlaces();
				if (places.length == 0) {
					return;
				}
				markers.forEach(function(marker) {
					marker.setMap(null);
				});
				markers = [];
				
				var bounds = new google.maps.LatLngBounds();
				places.forEach(function(place) {
					setResult(place);
					var icon = {
						url: place.icon,
						size: new google.maps.Size(71, 71),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(17, 34),
						scaledSize: new google.maps.Size(25, 25)
					};
					markers.push(
						new google.maps.Marker({
							map: map,
							icon: icon,
							title: place.name,
							position: place.geometry.location
						})
					);
					if (place.geometry.viewport) {
						bounds.union(place.geometry.viewport);
					}else {
						bounds.extend(place.geometry.location);
					}
				});
				map.fitBounds(bounds);
			});
		};
  
		var script = document.createElement('script'); script.defer = true; script.async = true;
		script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&callback=Global.initMap';
		$('body').append(script);
	
		return self;
	};
}( jQuery ));