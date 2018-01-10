<?php

namespace FWF\Includes\LibraryHours;

use DateTime;
use DateTimeZone;
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
 
 function get_library_hours( $lib_id ){
    //setup time variables
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('m/d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();
    
    // turn array/object into query string
    $ReadyForFW = array();
    
    $params = array(
        'iid'       => '333',
        'lid'       => $lib_id,
        'format'    => 'json',
        'systemTome'=> '1'
        ); 	
    
	$params = http_build_query( $params );
	
	$request = wp_remote_get( 'https://api3.libcal.com/api_hours_today.php?' . $params );
	
	$library_hours = json_decode( $request['body'], true );
	
	if ( $request['response']['code'] !== 200 ) {
    	//return $request->get_error_message();
	    $ReadyForFW['error'] =  $request['response']['code'];
	}
	
	//construct the array with useful images for display
	$time_display = array(
	    'formatted_date'    => $formatted_date,
	    'formatted_time'    => $formatted_time,
	    "time_and_date"     => $formatted_date . " | " . $formatted_time,
	    'clock_icon'        => FWF_IMAGES . 'time.png',
	    'calendar_icon'     => FWF_IMAGES . 'calendar.png',
	    'phone_icon'        => FWF_IMAGES . 'telephone.png',
	    'email_icon'        => FWF_IMAGES . 'envelope.png',
	    'res_QR'            => FWF_IMAGES . 'room-res-QR.png'
	    );
	 
	 //add all the array of useful images to the fourwinds feed
	 array_push( $ReadyForFW, $time_display );
	 
	 //loop through and un-nest the location data
	 foreach($library_hours['locations'] as $location){
	     
	    array_push( $ReadyForFW, $location);
	 }
	 
	 //return the compiled array of data
	 return $ReadyForFW;
	
 }