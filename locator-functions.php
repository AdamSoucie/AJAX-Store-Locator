public function get_wpd_product_locations()
{
	$results = '';
	$product_locations 	= '';
	$product_counter 	= 0;

	// From the AJAX call
	$type 	= sanitize_text_field( $_POST['locationType'] );
	$radius = intval( $_POST['locationRadius'] );

	if($radius < 1)
	{
		$radius = 1000;
	}

	$lat 	= sanitize_text_field( $_POST['locationLat'] );
	$long 	= sanitize_text_field( $_POST['locationLng'] );

	$location_args = array(
		'post_type' 		=> 'wpd_product_location',
		'posts_per_page'	=> -1,
	);

	if( 'Any type' !== $type )
	{
		$location_args['orderby'] = 'meta_value';
		$location_args['meta_query'] =  array(	
			array(
				'key' 		=> 'wpd_product_location_type',
				'compare'	=> '=',
				'value'		=> $type,
			),
		); 
	}

	$location_posts = get_posts( $location_args );

	foreach( $location_posts as $location ) 
	{
		// Get all of the location data
		$location_address 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_address', true ) );
		$location_city 		= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_city', true ) );
		$location_state 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_state', true ) );
		$location_zip 		= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_zip', true ) );
		$location_latitude 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_latitude', true ) );
		$location_longitude = esc_attr( get_post_meta( $location->ID, 'wpd_product_location_longitude', true ) );

		$location_distance  = round( $this->wpd_get_distance( $lat, $long, $location_latitude, $location_longitude ), 2 );

		update_post_meta( $location->ID, 'wpd_product_location_distance', $location_distance );
	}

	$distance_args = array(
		'post_type' 		=> 'wpd_product_location',
		'posts_per_page'	=> -1,
	);

	if( 'Any type' !== $type )
	{
		$distance_args['order'] = 'ASC';
		$distance_args['orderby'] = 'meta_value';
		$distance_args['meta_query'] =  array(	
			array(
				'key' 		=> 'wpd_product_location_distance',
				'compare'	=> '<=',
				'value'		=> $radius,
				'type'		=> 'NUMERIC',
			),
			array(
				'key' 		=> 'wpd_product_location_type',
				'compare'	=> '=',
				'value'		=> $type,
			),
		); 
	}
	else
		{
			$distance_args['order'] = 'ASC';
			$distance_args['orderby'] = 'meta_value';
			$distance_args['meta_query'] =  array(	
				array(
					'key' 		=> 'wpd_product_location_distance',
					'compare'	=> '<',
					'value'		=> $radius,
					'type'		=> 'NUMERIC',
				),
			); 
		}

    $distance_posts = get_posts( $distance_args );

    foreach( $distance_posts as $location ) 
    {
        // Get all of the location data
        $location_name 		= get_the_title( $location->ID );
        $location_type 		= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_type', true ) );
        $location_address 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_address', true ) );
        $location_city 		= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_city', true ) );
        $location_state 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_state', true ) );
        $location_zip 		= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_zip', true ) );
        $location_full		= "$location_address, $location_city, $location_state $location_zip";
        $location_distance 	= esc_attr( get_post_meta( $location->ID, 'wpd_product_location_distance', true ) );

        // Add it to the array that will be JSON encoded
        $product_locations[$product_counter]['title'] 				= $location_name;
        $product_locations[$product_counter]['type'] 				= $location_type;
        $product_locations[$product_counter]['address'] 			= $location_address;
        $product_locations[$product_counter]['city'] 				= $location_city;
        $product_locations[$product_counter]['state'] 				= $location_state;
        $product_locations[$product_counter]['zip'] 				= $location_zip;
        $product_locations[$product_counter]['origin']				= $origin;
        $product_locations[$product_counter]['formattedAddress'] 	= $location_full;
        $product_locations[$product_counter]['distance'] 			= $location_distance;

        // Increment the counter
        $product_counter++;
    }

    echo json_encode( array( 'locations' => $product_locations ), JSON_PRETTY_PRINT );

    die();
}


public function wpd_get_distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
{
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;

    return $miles;
}
