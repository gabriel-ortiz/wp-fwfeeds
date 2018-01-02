<?php

namespace FWF\Includes\LibraryHours;

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
     	// turn array/object into query string
    
    $params = array(
        'iid'       => '333',
        'lid'       => $lib_id,
        'format'    => 'json',
        'systemTome'=> '1'
        ); 	
    
	$params = http_build_query( $params );
	
	$request = wp_remote_get( 'https://api3.libcal.com/api_hours_today.php?' . $params );
	
	$library_hours = json_decode( $request['body'], true );
	
	if ( is_wp_error ( $request ) ) {
    	//return $request->get_error_message();
	    $ReadyForFW['error'] =  $request->get_error_message();
	}else{
	    
	    return  $library_hours ;
	}
	
 }