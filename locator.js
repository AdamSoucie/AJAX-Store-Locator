(function( $ ) {
	'use strict';

	var map;
	var markers = [];
	var counter = 0;
	var origins = [];

	// Draw the initial map
	drawMap();

	// Add the result markers to the map
	$('#location-filter-submit').on('click', function(e){
		e.preventDefault();

		// Clear the results display
		$('#locations-container').html('');

		var locationRadius 	= $('#location-filter-radius').val();
		var locationType 	= $('#location-filter-type').val();  // The type is used to indicate different store types, in this case a restaurant or a retail store

		$.ajax({
			type: 'POST',
			url: wpd.ajaxurl,
			data: {
				action: 'get_wpd_product_locations',
				locationRadius: locationRadius,
				locationType: locationType,
				locationLat: origins[0]['lat'],
				locationLng: origins[0]['lng'],
			},
			error: function(xhr, status, error){
				var err = eval("(" + xhr.responseText + ")");
				alert(err.Message);
			},
			success: function(data){
				var results = JSON.parse(data);
				var HTML = '';
				var geocoder = new google.maps.Geocoder;

        // Go through each location, add it to the map, and display the additional info
				$.each(results['locations'], function(index, value){
					var type;

					switch(results['locations'][index]['type'])
					{
						case 'Bar/Restaurant':
							type = 'B';
							break;

						case 'Retail':
							type = 'R';
							break;

						default:
							type = 'D';
							break;
					}

          // Place the marker on the map
					geocoder.geocode({'address': results['locations'][index]['formattedAddress']}, function(results, status) {
						if (status === google.maps.GeocoderStatus.OK)
						{
							addMarker(results[0].geometry.location, 'black', type);
    					} 
    					else 
    					{
      						alert('Geocode was not successful for the following reason: ' + status);
    					}
  					});

          // Theme called for locations to be displayed in 3 columns. Classes referred to below are part of the Genesis Framework.
					if(index % 3 === 0)
					{
						HTML += '<div class="location one-third first">';
					}
					else
					{
						HTML += '<div class="location one-third">';
					}
						HTML += '<h3 class="location-title">' + results['locations'][index]['title'] + '</h3>';
						HTML += '<h4 class="location-distance">Distance: ' + results['locations'][index]['distance'] + ' miles</h4>';
						HTML += '<p class="location-address">' + results['locations'][index]['address'] + '<br>';
						HTML += results['locations'][index]['city'] + ', '
							+ results['locations'][index]['state'] + ' ' + results['locations'][index]['zip'] + '</p>';
					HTML += '</div>';
				});

        // Print the locations to the screen
				$('#locations-container').append(HTML);

				// Reset the filters
				$('#location-filter-zip').val('');
				$('#location-filter-radius').val(0);
				$('#location-filter-type').val('Any type');
			}
		});
	});

	// Reset Button
	$('#location-filter-reset').on('click', function(e){
		e.preventDefault();

		// Clear the map
		clearMarkers();

		// Reset the filters
		$('#location-filter-zip').val('');
		$('#location-filter-radius').val(0);
		$('#location-filter-type').val('Any type');

		// Clear the results display
		$('#locations-container').html('');
	});

	// Adds a marker to the map
	function addMarker(location, color, text)
	{
		if( color == '')
		{
			color = 'black';
		}

		if( text == '' )
		{
			text = 'X';
		}

		var label = {color: color, text: text}

		var marker = new google.maps.Marker(
			{
				position: location,
				map: map,
				label: label
			}
		);

		markers.push(marker);
		counter++;

		if(counter > 1)
		{
			counter = 0;
		}
	}

	// Clears all markers from the map and removes them from the markers array
	function clearMarkers()
	{
		for (var i = 0; i < markers.length; i++) 
		{
			markers[i].setMap(null);
		}

  		markers = [];
  		origins = [];

		drawMap();
	}

	// Draws the map
	function drawMap()
	{
	 	// Draw the map
	 	map = new google.maps.Map(document.getElementById('map-container'), 
	 		{
  				center: {lat: 28.6001575, lng: -81.3823084},
  				zoom: 10
			}
		);

  		var geocoder = new google.maps.Geocoder;
		var infoWindow = new google.maps.InfoWindow({map: map});

		// Try HTML5 geolocation.
		if (navigator.geolocation)
		{
			navigator.geolocation.getCurrentPosition(function(position){
				var pos = {
					lat: position.coords.latitude,
					lng: position.coords.longitude
				};

				infoWindow.setPosition(pos);
				infoWindow.setContent('Location found.');
				map.setCenter(pos);
				addMarker(pos, 'white', 'U');
				origins.push(pos);

			}, function() {
					handleLocationError(true, infoWindow, map.getCenter());
				});
		} 
		else
		{
			// Browser doesn't support Geolocation
			handleLocationError(false, infoWindow, map.getCenter());
		}
	}

	// Error logger for Geolocation
	function handleLocationError(browserHasGeolocation, infoWindow, pos) 
	{
		infoWindow.setPosition(pos);
		infoWindow.setContent(browserHasGeolocation ?
	        'Error: The Geolocation service failed.' :
	        'Error: Your browser doesn\'t support geolocation.');
	}

})( jQuery );
