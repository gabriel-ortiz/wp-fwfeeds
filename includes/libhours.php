<?php

namespace FWF\Includes\LibraryHours;

use DateTime;
use DateTimeZone;


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
  * Processes and formats Library hours data for fourwinds
  * 
  * @params $iid = insitution ID, $lid = library ID
  * 
  * return Array|string
  * 
  */
 
 function get_library_hours( $iid, $lid ){
 	
    // turn array/object into query string
    $ReadyForFW 		= array(
	    'open_icon'	        => FWF_IMAGES . 'open.png',
	    'calendar_icon'     => FWF_IMAGES . 'calendar.png',
	    'phone_icon'        => FWF_IMAGES . 'telephone.png',
	    'email_icon'        => FWF_IMAGES . 'envelope.png',
	    'res_QR'            => FWF_IMAGES . 'room-res-QR.png'    	
    	); 	
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $start_timestamp	= null;
    $end_timestamp		= null;

	//params to send query
	//@todo turn library ID
    $params = array(
        'iid'       => $iid, //real 333 testing 3499
        'lid'       => $lid, //testing 8739
        'format'    => 'json',
        'systemTime'=> '1'
        ); 	
    
	$params = http_build_query( $params );
	
	$request = wp_remote_get( 'https://api3.libcal.com/api_hours_today.php?' . $params );
	
	$library_hours = json_decode( $request['body'], true );
	
	//return $library_hours;
	
	if ( $request['response']['code'] !== 200 ) {
    	//return $request->get_error_message();
	    return 'HTTP request error';
	} else if( empty( $library_hours['locations'][0] ) ){
		return false;
	}
	
	if( array_key_exists( 'hours', $library_hours['locations'][0]['times'] ) ){

		//get the data from the API
		$start_timestamp	= $library_hours['locations'][0]['times']['hours'][0]['from'] ;
		$end_timestamp		= $library_hours['locations'][0]['times']['hours'][0]['to'];
		
		//configure the unix timestamp for start
		$start_timestamp = strtotime( $today->format('Y-m-d') . ' '. $start_timestamp );
		
		//configure unix timestamp for end
		if( strpos( $end_timestamp, 'am' ) !== false ){
			//if this string contains am - then we add a day to the date
			$tomorrow = $today->modify( '+1 day' );
			$end_timestamp = strtotime( $tomorrow->format( 'Y-m-d' ) . ' ' . $end_timestamp  );
		}else{
			$end_timestamp = strtotime( $today->format( 'Y-m-d' ) . ' ' . $end_timestamp  );		
		}		
		
	}elseif( $library_hours['locations'][0]['times']['status'] == '24hours' ){
		
		//if the status is 24 hours, we need to set the to the beginning of day
		//http://php.net/manual/en/datetime.formats.relative.php
		$start_timestamp	= new DateTime( 'today' );
		$start_timestamp->setTime( 0,0,0 ) ;
		$start_timestamp->setTimezone(new Datetimezone('America/Los_Angeles'));
		
		$end_timestamp		= clone $start_timestamp;
		$end_timestamp		=  $end_timestamp->modify( '+1 day' )  ;
		
		$start_timestamp	= $start_timestamp->getTimestamp();
		$end_timestamp		= $end_timestamp->getTimestamp(); 

		
	}
	

	
	//add to object date('d M Y H:i:s', $start_timestamp);
	$ReadyForFW['start_timestamp']	=  $start_timestamp;
	$ReadyForFW['end_timestamp'] 	=  $end_timestamp;
	
	$ReadyForFW['start_readable']	=  date('Y-m-d g:i a', $start_timestamp);
	$ReadyForFW['end_readable'] 	=  date('Y-m-d g:i a', $end_timestamp);
	
	$ReadyForFW['date']				= $today->format( 'm-d-Y' );
	$ReadyForFW['textual_date']		= $today->format( 'F jS Y' );
	$ReadyForFW['rendered_hours']	= date( 'g:ia', $start_timestamp ) . " - " . date( 'g:ia', $end_timestamp );
	$ReadyForFW['iid']				= $iid;
	$ReadyForFW['lid']				= $lid;
	
	return $ReadyForFW;
	
 }
 
 
 function get_library_hours_transient(  $iid, $lid ){
 	//Check to see if we get transient data back
 	$hours_cache = get_transient( 'fwf_library_hours_data' );
 	
 	if( $hours_cache ){
 		//if we still have a transiet, then get that data
 		$hours_data = $hours_cache;
 	} else {
 		//if we don't have our hours data then get that data from our handy function
 		$hours_data = get_library_hours( $iid, $lid );
 		
 		if( ! empty( $hours_data ) ){
 			//set the transient
 			set_transient( 'fwf_library_hours_data', $hours_data, 1* HOUR_IN_SECONDS );
 		}
 	}
 	
 	return $hours_data;
 }
 
 /**
  * 
  * Takes the library hours data and formats it into content readable in FourWinds
  * 
  * @params $lib_hours_data (array)
  * 
  */
 function render_library_hours( $lib_hours_data ){
    //setup time variables
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('m/d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();
    
    // turn array/object into query string
    $ReadyForFW = array();
    
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