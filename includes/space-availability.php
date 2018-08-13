<?php

namespace FWF\Includes\SpaceAvailability;

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
 
 function get_space_availability( $space_id ){

    //get today's date
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('Y-m-d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();

    //get access token
	$access_token = \FWF\Includes\LibCal\get_libcal_token();
	
		//if we get an wp_error, then we exit out of the function and return error message to browser
		if( is_wp_error( $access_token ) ){
			return $access_token;
		}
	
    $params = array(
        'eid'   => $space_id,
        'date'  => $formatted_date,
        );
    
    // turn array/object into query string
	$params = http_build_query( $params );
	
	//set up fourwinds array
	$ReadyForFW = array();	
	
	//construct API url
	$avail_api_url = 'https://api2.libcal.com/1.1/space/item/'. $space_id .'?availability='. $formatted_date;
	
	$request_availability = wp_remote_get( $avail_api_url , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );
	

	$request_bookings = wp_remote_get( 'https://api2.libcal.com/1.1/space/bookings?' . $params , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );

	
	if (  $request_bookings['response']['code'] != 200 || $request_availability['response']['code'] != 200 ) {

		$ReadyForFW['availability_error']	=  $request_availability->get_error_message();
		$ReadyForFW['bookings_error']		= $request_bookings->get_error_message();				
		return $ReadyForFW;
		
	} else {

        //convert json into array
		$availability	= json_decode( $request_availability['body'], true) ;
		$bookings		= json_decode( $request_bookings['body'], true ); 
	
	}

	
	//put availability data is array
	$all_availability	= $availability[0]['availability'];
	$request_space_info	= $availability[0];
	
	
	//loop through all availability, convert time and add to array
	foreach( $all_availability as $timeSlot ){
		$slot_data					= array();
		$slot_data['fromDate']		= APIHelpers\fixDateTime( $timeSlot['from'] );
		$slot_data['toDate']		= APIHelpers\fixDateTime( $timeSlot['to'] );
		$slot_data['rendered_time']	= $slot_data['fromDate'] . ' - ' . $slot_data['toDate'];
		$slot_data['status']		= 'Available';
		
		//calculate the number of 30 minute segments for flex-grow in display
		//@params $startTime, $endTime 
		$slot_data['30_min_seg'] = APIHelpers\get_30_segments( $slot_data['fromDate'] , $slot_data['toDate'] );
		
		//idenitfy the current time slot so we can add an icon to display
		//@params $appt_start, $appt_end, $current_moment
		$slot_data['is_current'] = APIHelpers\is_current_res( $timeSlot['from'], $timeSlot['to'], $today_timeStamped );		
		
		array_push( $ReadyForFW, $slot_data );
	}
	
	//add each booking reservation to the fourwinds array
	foreach( $bookings as $booking ){
		
		//check to make sure we are only adding the reservations for this specifc room
		if( $booking['eid'] != $space_id ) continue;
		
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
	//adding this data AFTER we do the sorting
	$space_data = array();
	$space_data['name']			= $request_space_info['name'];
	$space_data['description']	= $request_space_info['description'];
	$space_data['image']		= $request_space_info['image'];
	$space_data['capacity']		= $request_space_info['capacity'];
	
	array_push( $ReadyForFW, $space_data );
	
	return $ReadyForFW;
	//return $availability;
	//return $bookings;
	
 }