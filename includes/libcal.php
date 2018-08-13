<?php

namespace FWF\Includes\LibCal;

use DateTime;
use DateTimeZone;

use \FWF\Includes\APIHelpers as APIHelpers;

//exit if file is called directly
if( ! defined('ABSPATH') ){
    exit;
}


/**
 * 
 * Set up defaults and run hooks and filters on setup
 * 
 */
 
 function setup(){
    
    $n = function( $function ){
        return __NAMESPACE__ . "\\$function";
    };
    
    //add_action( 'hook_action', $n('this_function') );
 }
 
 /**
  * 
  * Helper function to get Access token to interact with LibCal API
  * 
  * @return string|\WP_Error will return access code or an error message
  * 
  */
  
  function get_libcal_token(){
      
      //get the credentials from the settings page
      $libcal_settings          = get_option( 'fwf_options' );
      
      //var_dump($libcal_settings );
      //get the transient which contains the access token
      $access_token             = get_transient( 'libcal_token_session' );
      
      $libcal_client_id         = $libcal_settings['libcal_client_id'];
      $libcal_client_secret     = $libcal_settings['libcal_client_secret'];
      
      //check for credentials from the settings page
      if( ! $libcal_client_id || ! $libcal_client_secret ){
          $credential_errors =  new \WP_Error('api_error', 'Missing API Settings');
          
          return $credential_errors;
      }
    
    //if there is no credential access token, then request a new one
    if( ! $access_token){
		
		//send the request to get an access token
		$token_request = wp_remote_post( 'https://api2.libcal.com/1.1/oauth/token', array(
    			'header' => array(),
    			'body'   => array(
    				'client_id'     => $libcal_client_id,
    				'client_secret' => $libcal_client_secret,
    				'grant_type'    => 'client_credentials'
    			)
    		) 
		);
		
		//decode the body response
		$token_request_data     = json_decode( $token_request['body'] );

		//if this is null, then we we have an error
		if( ! $token_request_data->access_token ){
			
          $credential_errors =  new \WP_Error( $token_request_data->error, $token_request_data->error_description );
          
          return $credential_errors;			
			
		}else{
			
			$access_token  = $token_request_data->access_token;
			// token is valid for an hour (3600 seconds), store in transient for 30 minutes
			set_transient( 'libcal_token_session', $access_token, 30 * MINUTE_IN_SECONDS );				
		}

    }
      
    return $access_token;
  
  	
  }
  
  /**
 * Fetch Events from LibCal
 *
 * @return array|string|\WP_Error
 */
function get_libcal_events( $cal_id ) {
    
    $params = array();
    
	//get access token
	$access_token = get_libcal_token();
	
	//$params['cal_id'] = 1889; // hardcoded calendar id for now
	//assign the LYL calendar if no arguments are passed in
	$params['cal_id'] = $cal_id ?: 1889;
	
	//set up results array
	$ReadyForFW = array();

	
	// turn array/object into query string
	$params = http_build_query( $params );
	
	
	$request = wp_remote_get( '	https://api2.libcal.com/1.1/events?' . $params , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );
	
	//var_dump($request);
	$events = json_decode( $request['body'], true) ;
	$events = $events['events'];	
	
	if ( $request['response']['code'] !== (int)200 || empty( $events ) ) {
		//return $request->get_error_message();
		$ReadyForFW['title']			= 'No upcoming events';
		$ReadyForFW['featured_img']		= FWF_IMAGES_CCL;
		
		return $ReadyForFW;
		
	} else {

	
		$headerData = array();
		
		$headerData['response']			= $request['response']['code'];
		$headerData['header-expires']	= $request['headers']['expires'];
		$headerData['access_token']		= get_transient('libcal_token_session');
		
		array_push($ReadyForFW, $headerData);
	
	    foreach($events as $event){
	        $LYL_FW = array();
	            $LYL_FW['title']            = $event['title'];
	            $LYL_FW['featured_img']     = APIHelpers\testImage( $event['featured_image'] );
	            $LYL_FW['location']         = $event['location']['name'];
	            $LYL_FW['start']            = APIHelpers\fixDateTime( $event['start'] );
	            $LYL_FW['end']              = APIHelpers\fixDateTime( $event['end'] );
	            $LYL_FW['weekday']          = APIHelpers\getWeekDay( $event['start'] );
	            $LYL_FW['Month_Day']        = APIHelpers\getMonthDay( $event['start'] );
	            $LYL_FW['month']            = APIHelpers\getMonthOnly( $event['start'] );
	            $LYL_FW['day']              = APIHelpers\getDayOnly( $event['start'] );
	            $LYL_FW['description']      = APIHelpers\stringCleaner( $event['description'] );
	        
	        //add results to ready for FW array    
	        array_push($ReadyForFW, $LYL_FW);
	    }   

	}
	
	//return $request;
	return $ReadyForFW;

}

/**
 * 
 * function that returns formatted availability for rooms based on Room ID
 * 
 * Function will be used for space-availability and spaces endpoints
 * 
 * @params Room ID # string
 * 
 */
 
 function get_room_availability( $space_id ){
 	//set up fourwinds array
	$ReadyForFW = array();  
 	
 	//set up necessary variables
    $today				= new \DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('Y-m-d');
	$tomorrow			= clone $today;
	$tomorrow->modify( '+1 day' );
	$availability_range		= $formatted_date . ',' . $tomorrow->format('Y-m-d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();
    
    //get the library hours IDs for the API
    $fwf_settings = get_option( 'fwf_options' );
   
    $library_hours_iid = $fwf_settings['fwf_Hours_iid'];
    $library_hours_lid = $fwf_settings['fwf_Hours_lid'];
    
    //check to make sure we have the proper ID's for the library hours
    if( empty( $library_hours_iid ) || empty( $library_hours_lid ) ){
    	$ReadyForFW['error'] = 'Missing Library Hours IDs';
    	return $ReadyForFW;
    }
  
    //get access token
	$access_token = \FWF\Includes\LibCal\get_libcal_token();
	
		//if we get an wp_error, then we exit out of the function and return error message to browser
		if( is_wp_error( $access_token ) ){
			return $access_token;
		}
    
 	
 	//Get room availability
		//construct API url
		$avail_api_url = 'https://api2.libcal.com/1.1/space/item/'. $space_id .'?availability='. $availability_range;
		
		$request_availability = wp_remote_get( $avail_api_url , array(
			'headers' => array(
				'Authorization' => 'Bearer '. $access_token
			),
			'body'   => array(),
			'debug'  => true
		) );
	
 	
 	//get bookings
 	    $booking_params = array(
	        'eid'   => $space_id,
	        'date'  => $formatted_date,
	        ); 
	    // turn array/object into query string
		$booking_params = http_build_query( $booking_params );
		
		//send the request
		$request_bookings = wp_remote_get( 'https://api2.libcal.com/1.1/space/bookings?' . $booking_params , array(
			'headers' => array(
				'Authorization' => 'Bearer '. $access_token
			),
			'body'   => array(),
			'debug'  => true
		) ); 	
 	
 	//get library hours
 	$library_hours = \FWF\Includes\LibraryHours\get_library_hours_transient( $library_hours_iid, $library_hours_lid );
 	//$library_hours = \FWF\Includes\LibraryHours\get_library_hours( $library_hours_iid, $library_hours_lid ); 	
    
    //convert json into array
	$availability	= json_decode( $request_availability['body'], true) ;
	$bookings		= json_decode( $request_bookings['body'], true ); 
	
	//remember that at this stage, the request result is an array with JSON as the body
	if (  $request_bookings['response']['code'] !== 200 ) {
		//check to make sure there isn't an http request error
		$ReadyForFW['error']		= 'Bookings HTTP Request Error';				
		return $ReadyForFW;
		
	}elseif( $request_availability['response']['code'] !== 200 ){
		//check to make sure there isn't an http request error
		$ReadyForFW['error']	= 'Availability HTTP Request Error';
		return $ReadyForFW;		
		
	}elseif( array_key_exists( 'error', $availability[0] ) ){
		//check to make sure we data from the availability api
		$ReadyForFW['error']	= 'No Data returned from the Availability API';		
		return $ReadyForFW;		
		
	}elseif( empty( $library_hours) ){
		
		//check to make sure we get data back from the hours api
		$ReadyForFW['error']	= 'No Data returned from the LibraryHours API';		
		return $ReadyForFW;		
	}
	
	
	//var_dump( $library_hours['start_timestamp'], $library_hours['end_timestamp'] );
	//var_dump( $library_hours['start_readable'], $library_hours['end_readable'] );	
	
	//put availability data is array
	$all_availability	= $availability[0]['availability'];
	$request_space_info	= $availability[0];
	
	
	//loop through all availability, convert time and add to array
	foreach( $all_availability as $timeSlot ){
		
		// $test_date = new DateTime( $timeSlot['from'] );
		
		// var_dump( $test_date->format( 'Y-m-d g:i a' ) );
		//$library_hours['start_timestamp'] <=  strtotime( $timeSlot['from'] ) &&
		
		if( $library_hours['start_timestamp'] <=  strtotime( $timeSlot['from'] ) && $library_hours['end_timestamp'] >= strtotime( $timeSlot['to'] )  ){

			$slot_data					= array();
			$slot_data['fromDate']		= APIHelpers\fixDateTime( $timeSlot['from'] );
			$slot_data['toDate']		= APIHelpers\fixDateTime( $timeSlot['to'] );
			$slot_data['rendered_time']	= $slot_data['fromDate'] . ' - ' . $slot_data['toDate'];
			$slot_data['status']		= 'Available';
			$slot_data['date']			= date( 'Y-m-d', strtotime( $timeSlot['from'] ) );
			
			//calculate the number of 30 minute segments for flex-grow in display
			//@params $startTime, $endTime 
			$slot_data['30_min_seg'] = APIHelpers\get_30_segments( $slot_data['fromDate'] , $slot_data['toDate'] );
			
			//idenitfy the current time slot so we can add an icon to display
			//@params $appt_start, $appt_end, $current_moment
			$slot_data['is_current'] = APIHelpers\is_current_res( $timeSlot['from'], $timeSlot['to'], $today_timeStamped );	
			
			$slot_data['name']			= $request_space_info['name'];
			$slot_data['description']	= "<div style='color: white; font-size: 12px;'>" . $request_space_info['description'] . "</div>";
			$slot_data['image']			= $request_space_info['image'];
			$slot_data['capacity']		= $request_space_info['capacity'];
			
			array_push( $ReadyForFW, $slot_data );
			
		}
		

	}
	
	//add each booking reservation to the fourwinds array
	foreach( $bookings as $booking ){
		
		//check to make sure we are only adding the reservations for this specifc room
		if( $booking['eid'] != $space_id ) continue;
		
		//we only want bookings that contain availability or confirmations
		if( ! in_array( $booking['status'], array( 'Available', 'Confirmed' ) ) ) continue;
		
		//skip over events in the past
		if( strtotime( $booking['toDate'] ) < $today_timeStamped ) continue;
		
		$booking_data 					= array();
		$booking_data['fromDate']   	= APIHelpers\fixDateTime( $booking['fromDate'] );
		$booking_data['toDate']     	= APIHelpers\fixDateTime( $booking['toDate'] );
		$booking_data['rendered_time']	= $booking_data['fromDate'] . ' - ' . $booking_data['toDate'] ;
		$booking_data['confirm_num']	= $booking['bookId'];
		$booking_data['status']			= $booking['status'];
		
		//get the number of 30 minute segments
		$booking_data['30_min_seg'] = APIHelpers\get_30_segments( $booking_data['fromDate'] , $booking_data['toDate'] );
		
		//@params $appt_start, $appt_end, $current_moment
		$booking_data['is_current'] = APIHelpers\is_current_res( $booking['fromDate'], $booking['toDate'], $today_timeStamped );
		
		$booking_data['name']			= $request_space_info['name'];
		$booking_data['description']	= $request_space_info['description'];
		$booking_data['image']			= $request_space_info['image'];
		$booking_data['capacity']		= $request_space_info['capacity'];		
		
		array_push( $ReadyForFW, $booking_data );
	}
	
	//ensure that times are sorted by start time
    usort($ReadyForFW, function($a1, $a2) {

        $v1 =  strtotime( $a1['fromDate'] ) ;
        $v2 = strtotime( $a2['fromDate'] ) ;
      return $v1 - $v2;
      //return $v2 - $v1; this is is for reverse order
    });	
	
	
	//add space data to the fourwinds array
	// //adding this data AFTER we do the sorting
	// $space_data = array();
	// $space_data['name']			= $request_space_info['name'];
	// $space_data['description']	= $request_space_info['description'];
	// $space_data['image']		= $request_space_info['image'];
	// $space_data['capacity']		= $request_space_info['capacity'];
	
	// array_push( $ReadyForFW, $space_data );
	
	return $ReadyForFW;
	//return $availability;
	//return $bookings;	
	//return $library_hours;

 	
 	
 }