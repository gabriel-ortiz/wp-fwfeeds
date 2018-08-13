<?php

namespace FWF\Includes\Spaces;

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


function get_space_status( $space_id ){
     
    //get today's date
    $today				= new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $formatted_date 	= $today->format('Y-m-d');
    $formatted_time 	= $today->format('g:i a');
    $today_timeStamped	= $today->getTimestamp();
    $current_confirm_number = false;
    
	$result = array(
		'status_img'			=> '',
		'current_res_time'		=> '' ,
		'next_avail_start'		=> '',
		'remaining_revs'		=> '',
		//'current_confirm'		=> ''

		);    
    
	//get the room availability
    $space_availability =	\FWF\Includes\LibCal\get_room_availability( $space_id ); 
    
    //var_dump($space_availability);
    
    //exit from function if we don't find any instances of appointments
    if( isset( $space_availability['error'] ) || array_key_exists( 'error', $space_availability ) ){
    	$result['current_status']	= 'Unavailable';
    	$result['error_message']	= $space_availability['error'];
    	return $result;
    }
   
    //get the current confirmation number
    foreach( $space_availability as $key => $booking ){
    	
    	if( array_key_exists( 'status', $booking ) && $booking['status'] == 'Confirmed' && array_key_exists( 'confirm_num', $booking ) && $booking['is_current'] == true ){
    		$current_confirm_number = $booking['confirm_num'];
    		break;
    	}
    }
    
    //get the next available start time
    foreach( $space_availability as $key => $booking ){
    	if( $booking['status'] == 'Available' ){
    		$result['next_avail_start'] = $booking['fromDate'];
    		break;
    	}
    }
    
    //locate current time in array and check for status
    $current_appt_status = array_filter( $space_availability,  function( $val ){
    	if( array_key_exists( 'is_current', $val ) ){
     		return $val['is_current'] == true;   		
    	}
    } );
    
   $result['status_img']		= ( $current_appt_status[0]['status'] == "Available" ) ? FWF_IMAGES .'TCCL-available.png' : FWF_IMAGES.'TCCL-in-use.png';
   $result['current_res_time']	= ( $current_appt_status[0]['status'] == "Available" ) ? '' : $current_appt_status[0]['rendered_time'];
   $result['current_status']	= ( $current_confirm_number ) ? 'CONFIRM #: ' . $current_confirm_number : 'Available';

    //number of bookings
    $reservation_bookings		= array_filter( $space_availability,  function( $val ){
    	if( array_key_exists( 'status', $val ) ){
    		return $val['status'] == 'Confirmed';
    	}
    } );
    $result['remaining_revs'] = count( $reservation_bookings );
    
	//$result['name'] = array_column(  $space_availability, 'name' );
	//locate the name
	foreach( $space_availability as $space ){
		if( array_key_exists( 'name', $space ) ){
			$result['name'] = $space['name'];
		}
	}
    
    return $result;

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
	
	//send the request
	$request = wp_remote_get( 'https://api2.libcal.com/1.1/space/bookings?' . $params , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );
	

	if ( $request['response']['code'] !== (int)200 ) {
		//return $request->get_error_message();
		$ReadyForFW['error'] =  $request->get_error_message();
		
		return $ReadyForFW;
		
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
        
        
        return $bookings;
        
		function get_current_appt_display( $current_time, $space_availability ){
			//loop through appointments and assign variables
			$result = array(
				'status_img'			=> '',
				'current_res_time'		=> '' ,
				'current_res_confirm'	=> '',
				'next_avail_start'		=> '',
				'remaining_revs'			=> '',

				);
				
			//check if $space_availability has any value - if not then exit function because there are no appts to traverse
			if( empty( $space_availability ) ){

				$result = array(
					'status_img'			=> 'availabile.jpg',
					'current_res_time'		=> 'No current reservations' ,
					'current_res_confirm'	=> 'N/A',
					'next_avail_start'		=> 'Now',
					'remaining_revs'			=> count( $space_availability )
					);				
				
				return $result;	
			}else{
				$result['remaining_revs'] = count( $space_availability );
			}
				
			//If there are revs, check if we are currently in revs	
			foreach( $space_availability as $key => $appt ){

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
				$next_appt_data					= $space_availability[$current_key + 1];
			}else{
				//set all return text to show open availability
				$result['status_img']			= 'available.jpg';
				$result['current_res_time']		= 'No current reservations';				
				$result['current_res_confirm']	= 'N/A';
				$result['next_avail_start']		= 'Now';
				return $result;
				
			}
			
			
			//if there are more events afterward, the get the end time of event, or show available if last event
			end($space_availability);
			$lastKey = key($space_availability);
			
			//check if we are in last event	
			if( $lastKey != $current_key  ){
				
				var_dump( 'im in' );
				reset( $space_availability );
				
				foreach( $space_availability as $key => $appt ){
					if( $key < $current_key)continue;
					
					var_dump( $key );
					var_dump( array_key_exists( ($key + 1) , $space_availability ) );
					if( array_key_exists( ($key + 1) , $space_availability ) ){
						$current_end	= strtotime( $appt['toDate'] );
						$next_start		= strtotime( $space_availability[$key + 1]['fromDate'] );
						
						var_dump( $current_end );
						var_dump( $next_start );
						
						if( $current_end != $next_start ){
							var_dump('this is the last break');	
							$result['next_avail_start'] =  APIHelpers\fixDateTime( $space_availability[$key]['toDate'] );	
							
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
				
				reset($space_availability);
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