<?php

namespace FWF\Includes\Spaces;

use DateTime;
use DateTimeZone;

use \FWF\Includes\APIHelpers as APIHelpers;

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

function get_spaces( $space_id ){
     
    //get today's date
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('Y-m-d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();
    
    
    //var_dump( $today->format('Y-m-d') );
    
     
    $params = array(
        'eid'   => $space_id,
        'date'  => $formatted_date,
        );
    
	//get access token
	$access_token = \FWF\Includes\LibCal\get_libcal_token();
	
	//set up fourwinds array
	$ReadyForFW = array();
	
    // turn array/object into query string
	$params = http_build_query( $params );
	
	
	$request = wp_remote_get( 'https://api2.libcal.com/1.1/space/bookings?' . $params , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );
	
	//var_dump($request);
	
	if ( is_wp_error ( $request ) ) {
		//return $request->get_error_message();
		$ReadyForFW['error'] =  $request->get_error_message();
		
	} else {

        //convert json into array
		$bookings = json_decode( $request['body'], true) ;
		
		//ensure that times are sorted by start time
        usort($bookings, function($a1, $a2) {

            $v1 =  strtotime( $a1['fromDate'] ) ;
            $v2 = strtotime( $a2['fromDate'] ) ;
           return $v1 - $v2;
           //return $v2 - $v1; this is is for reverse order
        });
        
        
		function get_current_appt_display( $current_time, $appt_array ){
			//loop through appointments and assign variables
			$result = array(
				'status_img'			=> '',
				'current_res_time'		=> '' ,
				'current_res_confirm'	=> '',
				'next_avail_start'		=> '',
				'num_of_revs'			=> '',

				);
				
			//check if $appt_array has any value - if not then exit function because there are no appts to traverse
			if( empty( $appt_array ) ){

				$result = array(
					'status_img'			=> 'availabile.jpg',
					'current_res_time'		=> 'No current reservations' ,
					'current_res_confirm'	=> 'N/A',
					'next_avail_start'		=> 'Now',
					'num_of_revs'			=> count( $appt_array )
					);				
				
				return $result;	
			}else{
				$result['num_of_revs'] = count( $appt_array );
			}
				
			//If there are revs, check if we are currently in revs	
			foreach( $appt_array as $key => $appt ){

				$appt_start 	= strtotime( $appt['fromDate'] );
				$appt_end		= strtotime( $appt['toDate'] );
				$current_moment	= $current_time ;
				//return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
				
				//get current appointment time
				if( $appt_start <= $current_moment && $appt_end >= $current_moment  ){
					$current_appt	= $appt;
					$current_key	= $key;
				}
			}
			
			
			//if there is a curret reservation - and there is value, assign the confirmation ID and background img
			if( ! is_null( $current_appt ) ){
				
				$from	= APIHelpers\fixDateTime( $current_appt['fromDate'] );
				$to		= APIHelpers\fixDateTime( $current_appt['toDate'] ) ;

				$result['status_img']			= 'in_use.jpg';
				$result['current_res_time']		= $from . ' to ' . $to;
				$result['current_res_confirm']	= $current_appt['bookId'];
				$next_appt_data					= $appt_array[$current_key + 1];
			}else{
				//set all return text to show open availability
				$result['status_img']			= 'available.jpg';
				$result['current_res_time']		= 'No current reservations';				
				$result['current_res_confirm']	= 'N/A';
				$result['next_avail_start']		= 'Now';
				return $result;
				
			}
			
			
			//if there are more events afterward, the get the end time of event, or show available if last event
			end($appt_array);
			$lastKey = key($appt_array);
			
			//check if we are in last event	
			if( $lastKey != $current_key  ){
				
				var_dump( 'im in' );
				reset( $appt_array );
				
				foreach( $appt_array as $key => $appt ){
					if( $key < $current_key)continue;
					
					var_dump( $key );
					var_dump( array_key_exists( ($key + 1) , $appt_array ) );
					if( array_key_exists( ($key + 1) , $appt_array ) ){
						$current_end	= strtotime( $appt['toDate'] );
						$next_start		= strtotime( $appt_array[$key + 1]['fromDate'] );
						
						var_dump( $current_end );
						var_dump( $next_start );
						
						if( $current_end != $next_start ){
							var_dump('this is the last break');	
							$result['next_avail_start'] =  APIHelpers\fixDateTime( $appt_array[$key]['toDate'] );	
							
						}else{
							continue;
						}
						break;
						
					}else{
						var_dump('This is the last item');
						break;
					}
				
				}
				
			}else{
				$result['next_avail_start'] = $current_appt['toDate'];
				
				reset($appt_array);
			}

		return $result;
			
		};
		
		$current_appt = get_current_appt_display( $today_timeStamped, $bookings );
		$current_appt['current_time']	= $formatted_time;
		$current_appt['current_date']	= $formatted_date;
 		
		array_push( $ReadyForFW, $current_appt );

	
		//add metadata to the array product
		$res_data   = array();
		$res_data['current_time']   	= $formatted_time;
		$res_data['current_date']		= $formatted_date;
		$res_data['confirm_num']    	= count($bookings);
		$res_data['current_confirm']    = $current_appt['bookId'];
		$res_data['next_confirm']		= $bookings[ $current_appt['next_appt'] ]['bookId'] ?: '';
		//$res_data['raw']			= $current_appt;
		
		//array_push($ReadyForFW, $res_data);

		foreach( $bookings as $booking ){
		    $booking_FW                 = array();
		    $booking_FW['fromDate']     = APIHelpers\fixDateTime( $booking['fromDate'] );
		    $booking_FW['toDate']       = APIHelpers\fixDateTime( $booking['toDate'] );
		    
		    //array_push($ReadyForFW, $booking_FW);
		    
		    
		}
		
		array_push($ReadyForFW, $bookings);
	}
	
	
	//return $request;
	//return $bookings;
	
	return $ReadyForFW;
 }